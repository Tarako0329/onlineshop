<?php
/*テーブルJuchuu‗headのsent=1かつreview_irai=stillとなっているデータをもとにレビュー依頼のメールを送信する
データ更新はトライキャッチでくくり、トランザクション処理とする
依頼メールを送信したら、review_iraiにdoneをセットする
*/
  require "php_header.php";
	date_default_timezone_set('Asia/Tokyo'); 
	$sqllog="";
	$sql = "select * from juchuu_head where sent = 1 and review_irai = 'still'";
	$stmt = $pdo_h->prepare($sql);
	$stmt->execute();
	$data = $stmt->fetchAll(PDO::FETCH_ASSOC);
	try{
		$pdo_h->beginTransaction();
		$sqllog .= rtn_sqllog("START TRANSACTION",[]);
		foreach($data as $row){
			$params["orderNO"] = $row["orderNO"];
			$params["mail"] = $row["mail"];
			$params["name"] = $row["name"];
			$params["uid"] = $row["uid"];
			$params["key"] = rot13encrypt2($row["orderNO"]);
			$params["url"] = ROOT_URL."review_post.php?key=".$params["key"];
			$params["body"] = <<<EOM
			$params[name] 様

			この度は、商品をお買い上げいただき、ありがとうございました。
			お届けした商品はいかがでしたでしょうか？
			差し支えなければ、ご感想・レビューをお聞かせください。

			レビュー投稿はこちらから
			$params[url]

			ご協力よろしくお願いいたします。
			EOM;
			$params["subject"] = "【".TITLE."】レビュー投稿のお願い";
			$params["fromname"] = TITLE."@".EXEC_MODE;
			$params["bcc"] = "";
			$rtn = send_mail($params["mail"],$params["subject"],$params["body"],$params["fromname"],$params["bcc"]);
			log_writer2("\$rtn",$rtn,"lv3");
			$sql_upd = "update juchuu_head set review_irai = 'done' where orderNO = :orderNO";
			$sqllog .= rtn_sqllog($sql_upd,$params);
			$stmt2 = $pdo_h->prepare($sql_upd);
			$stmt2->bindValue("orderNO", $params['orderNO'], PDO::PARAM_STR);
			$stmt2->execute();
			$sqllog .= rtn_sqllog("--execute():正常終了",[]);
		}
		$pdo_h->commit();
		$sqllog .= rtn_sqllog("commit",[]);
		sqllogger($sqllog,0);

    /*
		$to="green.green.midori@gmail.com";
		$subject="【".EXEC_MODE."】ONLINESHOP_レビュー依頼メール送信完了";
		$body="レビュー依頼メールを送信しました。";
		$fromname=TITLE."@".EXEC_MODE;
		$bcc="";
		send_mail($to,$subject,$body,$fromname,$bcc);
		exit();
    */
	}catch(Exception $e){
      $pdo_h->rollBack();
      $sqllog .= rtn_sqllog("rollBack",[]);
      sqllogger($sqllog,$e);
      log_writer2("\$e",$e,"lv0");
      $msg .= "システムエラーによる更新失敗。管理者へ通知しました。";
      $alert_status = "alert-danger";
      $reseve_status=true;
			echo $msg."<br>".$e;
  }
  exit();
  
?>
