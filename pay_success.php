<?php
	require "php_header.php";
	//$rtn = true;//csrf_checker(["payment.php"],["G","C","S"]);
	$rtn = csrf_checker(["payment.php"],["G","C","S"]);
	$token = csrf_create();
	if(empty($_GET["key"])){
		echo "参照用のURLが異なります。";
		exit();
	}
	$user_hash = $_GET["key"] ;
	$_SESSION["user_id"] = rot13decrypt2($user_hash);

	$kingaku = ($_GET["val"]);
	$orderNO = ($_GET["orderNO"]);

	log_writer2("\$orderNO",$orderNO,"lv3");
	log_writer2("\$kingaku",$kingaku,"lv3");

	try{
		$pdo_h->beginTransaction();
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
		<h1>お支払いを受付いたしました。</h1>
	</MAIN>
	</div>
</BODY>
</html>