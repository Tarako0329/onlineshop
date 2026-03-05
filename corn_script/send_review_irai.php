<?php
	/*テーブルJuchuu‗headのsent=1かつreview_irai=stillとなっているデータをもとにレビュー依頼のメールを送信する
	データ更新はトライキャッチでくくり、トランザクション処理とする
	依頼メールを送信したら、review_iraiにdoneをセットする
	*/
	if (php_sapi_name() != 'cli') {
		exit('このスクリプトはCLIからのみ実行可能です。');
	}
	$mypath = dirname(__DIR__);
	chdir($mypath);
	require "php_header_admin.php";
	
	//$db = new Database();

	$sql = "SELECT 
			h.*
			,u.mail as shop_mail
			,u.yagou
			,u.shacho
			,u.line_id 
		from juchuu_head h 
		inner join Users_online u 
		on h.uid = u.uid 
		where 
			sent = 1 
			and review_irai = 'still' 
			and sent_ymd <= DATE_SUB(CURDATE(), INTERVAL 1 WEEK) 
		order by uid";
	$data = $db->SELECT($sql,[]);
	
	try{
		$db->begin_tran();

		$cnt = 0;
		$taishou_list = "";
		$shop_id = "";
		$lineID = "";
		$shop_mail = "";
		
		foreach($data as $row){
			if($cnt <> 0 && $shop_id<>$row["uid"]){
				//出店者にメール送信
				if(Utilities::exist($lineID)){
					$rtn = Utilities::send_line($lineID,"レビュー依頼メール送信完了\r\n\r\n".$taishou_list."へ、レビュー依頼を送信しました。");//出店者へお知らせLINE
				}else{
					$rtn = send_mail($shop_mail,"レビュー依頼メール送信完了",$taishou_list."へ、レビュー依頼を送信しました。",TITLE." onLineShop","");
				}
				sleep(2);
				$taishou_list = "";
			}
			$shop_id = $row["uid"];
			
			$lineID =$row["line_id"];
			$shop_mail = $row["shop_mail"];
			$taishou_list .= $row["name"]." 様\r\n";

			//juchuu_meisaiをorderNOで検索
			$sql_meisai = "SELECT * from juchuu_meisai where orderNO = :orderNO and su>0";
			$meisai = $db->SELECT($sql_meisai,["orderNO" => $row["orderNO"]]);

			//商品リストのセット
			$shouhinList = "";
			foreach($meisai as $row2){
				$shouhinList .= "　・".$row2["shouhinNM"]."\r\n";
			}
			
			$url = ROOT_URL."review_post.php?key=".rot13encrypt2($row["orderNO"]);
			$site = TITLE;
			$body = <<<EOM
				{$row['name']} 様
				
				この度は、$site より商品をお買い上げいただき、ありがとうございました。
				お届けした商品はいかがでしたでしょうか？
				差し支えなければ、ご感想・レビューをお聞かせください♪
				
				【ご購入商品】
				{$shouhinList}

				レビュー投稿はこちらから
				{$url}
				
				ご協力よろしくお願いいたします。

				通販サイト『{$site}』
				販売元：{$row['yagou']}
				https://cafe-present.greeen-sys.com/

				EOM;
			
			$mail = $row["mail"];
			$subject = "【".TITLE."】レビュー投稿のお願い";
			$fromname = TITLE;
			
			$rtn = send_mail($mail,$subject,$body,$fromname,"");
			sleep(2);
			//log_writer2("\$rtn",$rtn,"lv3");

			$sql_upd = "UPDATE juchuu_head set review_irai = 'done' where orderNO = :orderNO";
			$db->UP_DEL_EXEC($sql_upd,["orderNO" => $row["orderNO"]]);
			
			$cnt++;
			
		}
		
		//出店者にメール送信 (ループの最後の店舗)
		if ($cnt > 0 && !empty($shop_mail)) {
			if(Utilities::exist($lineID)){
				$rtn = Utilities::send_line($lineID,"レビュー依頼メール送信完了\r\n\r\n".$taishou_list."へ、レビュー依頼を送信しました。");//出店者へお知らせLINE
			}else{
				$rtn = send_mail($shop_mail,"レビュー依頼メール送信完了",$taishou_list."へ、レビュー依頼を送信しました。",TITLE." onLineShop","");
			}
			$taishou_list = "";
		}

		$db->commit_tran();

		$msg = ($cnt==0)?"レビュー依頼対象者なし":"レビュー依頼メール送信完了(".$cnt." 件)";
		echo $msg."\n";
    
	}catch(Exception $e){
      $db->rollback_tran($e->getMessage());
  		echo "レビュー依頼処理でエラー\n".$e;
  }
  exit();
  
?>
