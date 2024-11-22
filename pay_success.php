<?php
	require "php_header.php";
	//$rtn = true;//csrf_checker(["payment.php"],["G","C","S"]);
	/*$rtn = csrf_checker(["payment.php"],["G","C","S"]);
	if($rtn !== true){
		echo "不正アクセス。";
		exit();
	}*/
	$token = csrf_create();
	if(empty($_GET["key"])){
		echo "参照用のURLが異なります。";
		exit();
	}
	$user_hash = $_GET["key"] ;
	$_SESSION["user_id"] = rot13decrypt2($user_hash);

	log_writer2("\$_GET",$_GET,"lv3");

	try{
		$pdo_h->beginTransaction();

		
		$params["uid"] = $_SESSION["user_id"];
		$params["orderNO"] = $_GET["orderNO"];

		$sqlstr_h = "update juchuu_head set payment = 1 where orderNO = :orderNO and uid like :uid";
		$sqllog .= rtn_sqllog("START TRANSACTION",[]);

		//受注ヘッダ登録

		$stmt = $pdo_h->prepare( $sqlstr_h );
		//bind処理
		$stmt->bindValue("orderNO", $params["orderNO"], PDO::PARAM_STR);
		$stmt->bindValue("uid", $params["uid"], PDO::PARAM_INT);

		$sqllog .= rtn_sqllog($sqlstr_h,$params);

		$status = $stmt->execute();
		$sqllog .= rtn_sqllog("--execute():正常終了",[]);
		$pdo_h->commit();
		$sqllog .= rtn_sqllog("commit",[]);
		sqllogger($sqllog,0);

		
		//入金完了を出店者へ通知
		$stmt = $pdo_h->prepare("select * from Users_online where uid = :uid");
		$stmt->bindValue("uid", $params["uid"], PDO::PARAM_INT);
		$stmt->execute();
		$owner = $stmt->fetchAll(PDO::FETCH_ASSOC);

		$stmt = $pdo_h->prepare("select juchuu_head.*,(本体額+postage) as 入金額 from juchuu_head 
		inner join (select orderNO,sum(goukeitanka+zei) as 本体額 from juchuu_meisai group by orderNO) as Meisai 
		on juchuu_head.orderNO = Meisai.orderNO 
		where juchuu_head.orderNO = :orderNO and juchuu_head.uid like :uid");
		$stmt->bindValue("orderNO", $params["orderNO"], PDO::PARAM_STR);
		$stmt->bindValue("uid", $params["uid"], PDO::PARAM_INT);
		$stmt->execute();
		$juchuu_head = $stmt->fetchAll(PDO::FETCH_ASSOC);

		$kokyaku = $juchuu_head[0]['name'];
		$orderno = $juchuu_head[0]['orderNO'];
		$nyukin = $juchuu_head[0]['入金額'];

		$body = <<<EOM
			$kokyaku 様よりクレジットカード決済による入金を確認しました。

			受付No：$orderno
			ご入金額：￥ $nyukin -

			受注管理画面の入金ステータスを「入金済み」に変更しました。

			念のため、Stripe管理画面を確認してください。
			https://dashboard.stripe.com/payments
		EOM;

		$lineID =(!empty($owner[0]["line_id"]) && $owner[0]["line_id"] <> "null")?$owner[0]["line_id"]:"none";
		//if(!empty($owner[0]["line_id"]) && EXEC_MODE <> "Local"){//LINEで通知
		if($lineID <> "none"){//LINEで通知
			$html = send_line($owner[0]["line_id"],$body);
		}else if(!empty($owner[0]["mail"])){
			$rtn = send_mail($owner[0]["mail"],"入金通知[No:".$juchuu_head[0]["orderNO"]."]",$body,TITLE." onLineShop",$owner[0]["mail"]);
		}

		//クレジット支払自動返信のONOFF設定を追加してから有効にする
		/*
		$ryoushu_url = ROOT_URL."pdf_receipt.php?hash=".$user_hash."&val=".$orderno."&tp=1";
		$body2 = <<<EOM
			$kokyaku 様よりクレジットカード決済によるお支払いを確認しました。

			受付No：$orderno
			ご入金額：￥ $nyukin -

			お支払いありがとうございました。
			商品の発送まで今しばらくお待ちください。

			領収書は下記ＵＲＬよりダウンロードをお願いいたします。
			$ryoushu_url
		EOM;
		$rtn = send_mail($owner[0]["mail"],"お支払い完了通知[No:".$juchuu_head[0]["orderNO"]."]",$body2,TITLE." onLineShop",$juchuu_head[0]["mail"]);
		*/

		$reseve_status=true;
	}catch(Exception $e){
		$pdo_h->rollBack();
		$sqllog .= rtn_sqllog("rollBack",[]);
		sqllogger($sqllog,$e);
		$msg = "システムエラーによる更新失敗。管理者へ通知しました。";
		$reseve_status=true;
	}


?>

<!DOCTYPE html>
<html lang='ja'>
<head>
    <?php 
    //共通部分、bootstrap設定、フォントCND、ファビコン等
    include "head_bs5.php" 
    ?>
    <style>
      .btn{
        min-width: 50px;
      }
    </style>
    <TITLE><?php echo TITLE;?></TITLE>
</head>
<BODY >
  <div id='app' style='min-height: 100%'>
  <?php include "header_tag.php"  ?>
  <MAIN class='container common_main' data-bs-spy="scroll" data-bs-target="#scrollspy">
		<h1>ありがとうございます。</h1>
		<h1>お支払いを受付いたしました。</h1>
	</MAIN>
	</div>
</BODY>
</html>