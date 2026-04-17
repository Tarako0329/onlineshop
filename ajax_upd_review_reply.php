<?php
  //reviewのreplyを更新する。keyはSEQ。reply_dateは今日
  require "php_header.php";
  register_shutdown_function('shutdown_ajax',basename(__FILE__));

  $alert_status = "alert_success";
  $rtn = csrf_checker(["review_management.php","xxx.php"],["P","S"]);
  if($rtn !== true){
    $msg=$rtn;
    $alert_status = "alert-warning";
    $reseve_status = true;
  }else{
  
	  try{
	  	//トランザクションスタート
	  	//$pdo_h->beginTransaction();
			$db->begin_tran();
	  	//トランザクションログ
	  	//$sqllog .= rtn_sqllog("START TRANSACTION",[]);
    
	  	$params["reply"] = $_POST["reply"];
	  	$params["seq"] = $_POST["seq"];
	  	$params["reply_date"] = date('Y-m-d');
	  	//$sql = "UPDATE review_online set reply = :reply, reply_date = CURDATE() where seq = :seq";
	  	$sql = "UPDATE review_online set reply = :reply, reply_date = :reply_date where seq = :seq";
			$db->UP_DEL_EXEC($sql,$params);
			/*
	  	$stmt = $pdo_h->prepare($sql);
	  	$stmt->bindValue("reply", $params["reply"], PDO::PARAM_STR);
	  	$stmt->bindValue("seq", $params["seq"], PDO::PARAM_STR);
	  	$sqllog .= rtn_sqllog($sql_upd,$params);

	  	$stmt->execute();
	  	$sqllog .= rtn_sqllog("-- execute():正常終了",[]);
			*/
	  	//レビューに返信があったことを投稿者にsend_mail関数を利用してメールで通知
	  	$sql = "SELECT 
	  			h.mail 
	  			,u.yagou
	  		from review_online r 
	  		inner join juchuu_head h 
	  		on r.orderNO = h.orderNO 
	  		inner join Users_online u
	  		on h.uid = u.uid
	  		where 
	  			r.seq = :seq";
			$mail_data = $db->SELECT($sql,["seq" => $_POST["seq"]]);
			/*
	  	$stmt = $pdo_h->prepare($sql);
	  	$stmt->bindValue("seq", $_POST["seq"], PDO::PARAM_STR);
	  	$stmt->execute();
	  	$mail_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
			*/
	  	$mail = $mail_data[0]["mail"];
	  	$subject = "【".APP_NAME."】レビューへの返信がありました。";
	  	$body = $mail_data[0]["yagou"]." よりレビューへの返信がありました。\r\n\r\n".$params["reply"];
    
	  	//コミット
	  	//$pdo_h->commit();
	  	//トランザクションログ
	  	//$sqllog .= rtn_sqllog("commit",[]);
	  	//sqllogger($sqllog,0);
			$db->commit_tran();
    
	  	U::send_mail($mail,$subject,$body,APP_NAME,"");
	  }catch(Exception $e){
			$db->rollback_tran($e->getMessage());
			/*
			$pdo_h->rollBack();
			$sqllog .= rtn_sqllog("rollBack",[]);
			sqllogger($sqllog,$e);
			*/
			log_writer2("\$e",$e,"lv0");
			$msg = "システムエラーによる更新失敗。管理者へ通知しました。";
			$alert_status = "alert-danger";
			$reseve_status=true;
	  }
  }
  $token = csrf_create();

  $return_sts = array(
    "MSG" => $msg
    ,"status" => $alert_status
    ,"csrf_create" => $token
  );
  header('Content-type: application/json');
  echo json_encode($return_sts, JSON_UNESCAPED_UNICODE);
  
  exit();
?>