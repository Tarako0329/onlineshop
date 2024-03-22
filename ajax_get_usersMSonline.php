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
			where uid like :uid ";
		$stmt = $pdo_h->prepare($sql);
		$stmt->bindValue("uid", $_SESSION["user_id"], PDO::PARAM_STR);
		$stmt->execute();

		$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

		$sql = "select 
				*,if(flg=1,'true','false') as flg
			from Users_online_payinfo
			where uid like :uid ";
		$stmt = $pdo_h->prepare($sql);
		$stmt->bindValue("uid", $_SESSION["user_id"], PDO::PARAM_STR);
		$stmt->execute();

		$data2 = $stmt->fetchAll(PDO::FETCH_ASSOC);

		$alert_status = "alert-success";

		$return_sts = array(
			"status" => $alert_status
			,"Users_online" => $data
			,"Users_online_payinfo" => $data2
		);
				
	}
  header('Content-type: application/json');  
  echo json_encode($return_sts, JSON_UNESCAPED_UNICODE);
  exit();
?>
