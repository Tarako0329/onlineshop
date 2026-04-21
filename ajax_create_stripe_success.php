<?php
	//StripeConnectの登録が終わった場合、もしくは戻るで戻った場合に処理されるPG
  require "php_header.php";
	log_writer2("ajax_create_stripe_success.php start","","lv1");

	
	// 1. Stripeシークレットキーを安全に読み込む (環境変数などから)
	$stripe = new \Stripe\StripeClient([
			'api_key' => S_KEY,
	]);
	$webhookSecret = WEBHOOK_SKEY; // whsec_xxx...
	
	// 2. リクエストボディとヘッダーを取得
	$payload = @file_get_contents('php://input');
	$sigHeader = $_SERVER['HTTP_STRIPE_SIGNATURE'];
	
	try {
			// 3. Webhookイベントの検証と構築
			$event = \Stripe\Webhook::constructEvent(
					$payload, $sigHeader, $webhookSecret
			);
	} catch (\UnexpectedValueException $e) {
			// 無効なペイロード
			log_writer2("無効なペイロード","","lv1");
			http_response_code(400);
			exit();
	} catch (\Stripe\Exception\SignatureVerificationException $e) {
			// 無効な署名（不正なリクエスト）
			log_writer2("無効な署名（不正なリクエスト）","","lv1");
			http_response_code(400);
			exit();
	}
	
	// 4. イベントのタイプを処理
	switch ($event->type) {
			case 'account.updated':
					$account = $event->data->object; // 更新されたアカウントオブジェクト
					log_writer2("stripe_webhook sent \$account",$account,"lv1");
					handleAccountUpdated($account);
					break;
					
			// 他に必要なイベント（例：payment_intent.succeeded など）もここで処理
	
			default:
					// 処理しないイベント
					break;
	}
	
	// 成功レスポンスを返す
	// Stripeは成功を示す200 OKレスポンスを期待しています。
	http_response_code(200);
	
	// --- 登録完了を判断する関数の例 ---
	
	function handleAccountUpdated($account) {
			global $db;
			// ① `details_submitted` が true であることを確認
			$isSubmitted = $account->details_submitted;
	
			// ② 主要な機能（ケイパビリティ）が active になっているか確認
			// Standardアカウントの場合、最低限これらが active になる必要があります。
			$isPaymentsActive = $account->capabilities->card_payments === 'active';
			$isTransfersActive = $account->capabilities->transfers === 'active';

			// 完全に利用可能かどうかの判断
			if ($isSubmitted && $isPaymentsActive && $isTransfersActive) {//  登録完了！
				// データベースの該当ユーザーのステータスを「Stripeアカウント連携済み」などに更新
				$accountId = $account->id;
				
				//Users_online から Stripe_Approval_Statusとmail　を取得
				$sql = "SELECT Stripe_Approval_Status,mail from Users_online where stripe_id = :accountId";
				$user_data = $db->SELECT($sql, [":accountId" => $accountId]);
				$user_data = !empty($user_data) ? $user_data[0] : null;
				
				if ($user_data['Stripe_Approval_Status'] === 'Available') {// 既にAvailableの場合は何もしない
					log_writer2("Stripe Account ID: {$account->id} は既にAvailableです。","","lv1");
					return;
				}
				
				$sql = "UPDATE Users_online set Stripe_Approval_Status = 'Available',credit=IF(credit = 'use', 'use', 'no_use') where stripe_id = :accountId";
				$db->UP_DEL_EXEC($sql, [":accountId" => $accountId]);
				
				// ログ記録など
				log_writer2("Stripe Account ID: {$account->id} の登録が完了しました。","","lv1");
				
				//users_onlineテーブルからメアドを取得し、クレジットが利用可能となった旨のメールを送信する
				if ($user_data && $user_data['mail']) {
					$mail = $user_data['mail'];
					$subject = "【".APP_NAME."】Stripeクレジット決済が利用可能になりました";
					$body = "いつもご利用ありがとうございます。\r\n\r\nStripeクレジット決済の登録が完了し、現在ご利用可能となっております。\r\n\r\n今後ともよろしくお願いいたします。\r\n\r\n".APP_NAME;
					U::send_mail($mail, $subject, $body, APP_NAME, "");
					log_writer2("Stripeクレジット決済利用可能メールを送信しました。", "メールアドレス: " . $mail, "lv1");
				}
			} else {// 登録情報の提出はされたが、まだStripeの審査が完了していない、取引が停止になったなどの状態
				$accountId = $account->id;
				$sql = "UPDATE Users_online set Stripe_Approval_Status = 'Registered' where stripe_id = :accountId";
				$db->UP_DEL_EXEC($sql, [":accountId" => $accountId]);

				log_writer2("Stripe Account ID: {$account->id} は更新されましたが、まだアクティブではありません。","","lv1");
			}
	}	
	
?>
