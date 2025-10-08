<?php
	//StripeConnectの登録が終わった場合、もしくは戻るで戻った場合に処理されるPG
  require "php_header.php";
	log_writer2("ajax_create_stripe_success.php start","","lv3");

	
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
			http_response_code(400);
			exit();
	} catch (\Stripe\Exception\SignatureVerificationException $e) {
			// 無効な署名（不正なリクエスト）
			http_response_code(400);
			exit();
	}
	
	// 4. イベントのタイプを処理
	switch ($event->type) {
			case 'account.updated':
					$account = $event->data->object; // 更新されたアカウントオブジェクト
					log_writer2("stripe_webhook sent \$account",$account,"lv3");
					handleAccountUpdated($account,$pdo_h);
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
	
	function handleAccountUpdated($account,$pdo_h) {
			// ① `details_submitted` が true であることを確認
			$isSubmitted = $account->details_submitted;
	
			// ② 主要な機能（ケイパビリティ）が active になっているか確認
			// Standardアカウントの場合、最低限これらが active になる必要があります。
			$isPaymentsActive = $account->capabilities->card_payments === 'active';
			$isTransfersActive = $account->capabilities->transfers === 'active';

			// 完全に利用可能かどうかの判断
			if ($isSubmitted && $isPaymentsActive && $isTransfersActive) {
					//  登録完了！
					
					// データベースの該当ユーザーのステータスを「Stripeアカウント連携済み」などに更新
					$accountId = $account->id;
					// update_user_status_in_db($accountId, 'active');

					$sql = "update Users_online set Stripe_Approval_Status = 'Available',credit=IF(credit = 'use', 'use', 'no_use') where stripe_id = '$accountId'";
					$stmt = $pdo_h->prepare( $sql );
					$sqllog = rtn_sqllog($sql,[]);
					$status = $stmt->execute();
					$sqllog .= rtn_sqllog("--execute():正常終了",[]);

					sqllogger($sqllog,0);
		
					
					// ログ記録など
					log_writer2("Stripe Account ID: {$account->id} の登録が完了しました。","","lv3");
					
					//users_onlineテーブルからメアドを取得し、クレジットが利用可能となった旨のメールを送信する
					$sql = "select mail from Users_online where stripe_id = '$accountId'";
					$stmt = $pdo_h->prepare( $sql );
					$stmt->execute();
					$mail_data = $stmt->fetch(PDO::FETCH_ASSOC);

					if ($mail_data && $mail_data['mail']) {
						$mail = $mail_data['mail'];
						$subject = "【".TITLE."】Stripeクレジット決済が利用可能になりました";
						$body = "いつもご利用ありがとうございます。\r\n\r\nStripeクレジット決済の登録が完了し、現在ご利用可能となっております。\r\n\r\n今後ともよろしくお願いいたします。\r\n\r\n".TITLE;
						send_mail($mail, $subject, $body, TITLE, "");
						log_writer2("Stripeクレジット決済利用可能メールを送信しました。", "メールアドレス: " . $mail, "lv3");
					}
					
			} else {
					// 登録情報の提出はされたが、まだStripeの審査が完了していない、などの状態
					log_writer2("Stripe Account ID: {$account->id} は更新されましたが、まだアクティブではありません。","","lv3");
			}
	}	
	
?>
