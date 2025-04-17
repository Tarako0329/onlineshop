<?php
/*テーブルJuchuu‗headのsent=1かつreview_irai=stillとなっているデータをもとにレビュー依頼のメールを送信する
データ更新はトライキャッチでくくり、トランザクション処理とする
依頼メールを送信したら、review_iraiにdoneをセットする
*/
date_default_timezone_set('Asia/Tokyo'); 
define("VERSION","ver1.37.0");

require "./vendor/autoload.php";
require "functions.php";

//.envの取得
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();
define("MAIN_DOMAIN",$_ENV["MAIN_DOMAIN"]);
define("ROOT_URL",$_ENV["HTTP"]);
define("EXEC_MODE",$_ENV["EXEC_MODE"]);
define("TITLE",$_ENV["TITLE"]);
//システム通知
define("SYSTEM_NOTICE_MAIL",$_ENV["SYSTEM_NOTICE_MAIL"]);

if(EXEC_MODE<>"Product"){
  $time=date('Ymd-His');
  $id="demo";
  $pass="00000000";
}else{
  $time=VERSION;
  $id="";
  $pass="";
}


// DBとの接続
define("DNS","mysql:host=".$_ENV["SV"].";dbname=".$_ENV["DBNAME"].";charset=utf8");
define("USER_NAME", $_ENV["DBUSER"]);
define("PASSWORD", $_ENV["PASS"]);

//メール送信関連
define("HOST", $_ENV["HOST"]);
define("PORT", $_ENV["PORT"]);
define("FROM", $_ENV["FROM"]);
define("PROTOCOL", $_ENV["PROTOCOL"]);
define("POP_HOST", $_ENV["POP_HOST"]);
define("POP_USER", $_ENV["POP_USER"]);
define("POP_PASS", $_ENV["POP_PASS"]);

//stripe
define("S_KEY",$_ENV["SKey"]);
define("P_KEY",$_ENV["PKey"]);
define("OAuth",$_ENV["OAuth"]);

define("GEMINI",$_ENV["GOOGLE_API"]);

$pdo_h = new PDO(DNS, USER_NAME, PASSWORD, get_pdo_options());

	$sqllog="";

	$sql = "SELECT h.*,u.mail as shop_mail,u.yagou,u.line_id from juchuu_head h inner join Users_online u on h.uid = u.uid where sent = 1 and review_irai = 'still' and sent_ymd <= DATE_SUB(CURDATE(), INTERVAL 1 WEEK) order by uid";
	$stmt = $pdo_h->prepare($sql);
	$stmt->execute();
	$data = $stmt->fetchAll(PDO::FETCH_ASSOC);
	try{
		$pdo_h->beginTransaction();
		$sqllog .= rtn_sqllog("START TRANSACTION",[]);

		$cnt = 0;
		$taishou_list = "";
		$shop_id = "";
		
		foreach($data as $row){
			if($cnt <> 0 && $shop_id<>$row["uid"]){
				//出店者にメール送信
				$rtn = send_mail($shop_mail,"レビュー依頼メール送信完了",$taishou_list." へレビュー依頼を送信しました。",TITLE." onLineShop","");
				$taishou_list = "";
			}
			$shop_id = $row["uid"];
			$shop_mail = $row["shop_mail"];
			$taishou_list .= $row["name"]." 様\r\n";

			$params["name"] = $row["name"];
			$params["uid"] = $row["uid"];
			$params["key"] = rot13encrypt2($row["orderNO"]);
			$params["url"] = ROOT_URL."review_post.php?key=".$params["key"];
			/*
			$params["body"] = <<<EOM
				$params[name] 様
				
				この度は、商品をお買い上げいただき、ありがとうございました。
				お届けした商品はいかがでしたでしょうか？
				差し支えなければ、ご感想・レビューをお聞かせください。
				
				レビュー投稿はこちらから
				$params[url]
				
				ご協力よろしくお願いいたします。
				EOM;
			*/
			$body = <<<EOM
				$params[name] 様

				以前、Present Selectionより商品をお買い上げ頂いた方にお送りしております。


				この度、当サイトにレビュー投稿・閲覧機能が追加されました。

				つきましては、お買い上げいただいた商品について、ご感想・レビューをお聞かせいただければ幸いです。

				レビュー投稿はこちらから
				$params[url]

				ご協力よろしくお願いいたします。

				Present Selection
				https://cafe-present.greeen-sys.com/

				EOM;
			
			$mail = $row["mail"];
			$subject = "【".TITLE."】レビュー投稿のお願い";
			$fromname = TITLE;
			
			$rtn = send_mail($mail,$subject,$body,$fromname,"");
			//log_writer2("\$rtn",$rtn,"lv3");

			$sql_upd = "update juchuu_head set review_irai = 'done' where orderNO = :orderNO";
			$stmt2 = $pdo_h->prepare($sql_upd);
			$stmt2->bindValue("orderNO", $params['orderNO'], PDO::PARAM_STR);
			$stmt2->execute();

			$sqllog .= rtn_sqllog($sql_upd,$params);
			$sqllog .= rtn_sqllog("--execute():正常終了",[]);
			
			//5seconds wait
			sleep(5);
			$cnt++;
			
		}
		$pdo_h->commit();
		$sqllog .= rtn_sqllog("commit",[]);
		sqllogger($sqllog,0);

		//出店者にメール送信
		$rtn = send_mail($shop_mail,"レビュー依頼メール送信完了",$taishou_list." へレビュー依頼を送信しました。",TITLE." onLineShop","");
		$taishou_list = "";
		
		
		$msg = ($cnt==0)?"レビュー依頼対象者なし":"レビュー依頼メール送信完了(".$cnt." 件)";
		echo $msg."\n";
    
	}catch(Exception $e){
      $pdo_h->rollBack();
      $sqllog .= rtn_sqllog("rollBack",[]);
      sqllogger($sqllog,$e);
  		echo "レビュー依頼処理でエラー\n".$e;
  }
  exit();
  
?>
