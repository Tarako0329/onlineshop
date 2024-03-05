<?php
  require "php_header.php";

	$rtn = true;//csrf_checker(["xxx.php","xxx.php"],["P","C","S"]);
	if($rtn !== true){
	  $msg=$rtn;
	  $alert_status = "alert-warning";
	  $reseve_status = true;
	}else{
	  //log_writer('\$_SESSION["uid"]',++$a);
		$sql = "select 
				*
			from Users_online
			where uid = :uid ";
		$stmt = $pdo_h->prepare($sql);
		$stmt->bindValue("uid", $_SESSION["user_id"], PDO::PARAM_STR);
		$stmt->execute();

		$data = $stmt->fetchAll(PDO::FETCH_ASSOC);
		$alert_status = "alert-success";
		
	}
  header('Content-type: application/json');  
  echo json_encode($data, JSON_UNESCAPED_UNICODE);
  exit();
?>
