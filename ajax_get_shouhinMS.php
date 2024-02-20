<?php
  require "php_header.php";
	
	log_writer2("",$hinmei,"lv3");
	$rtn = true;//csrf_checker(["xxx.php","xxx.php"],["P","C","S"]);
	if($rtn !== true){
	  $msg=$rtn;
	  $alert_status = "alert-warning";
	  $reseve_status = true;
	}else{
	  //log_writer('\$_SESSION["uid"]',++$a);
	  $sql = "select 
				rezMS.shouhinCD
				,rezMS.shouhinNM
				,rezMS.tanka
				,rezMS.bunrui1
				,rezMS.bunrui2
				,rezMS.bunrui3
				,rezMS.hyoujiKBN2
			from shouhinMS rezMS 
	    where rezMS.uid = :uid order by rezMS.shouhinNM";
		$stmt = $pdo_h->prepare($sql);
		$stmt->bindValue("uid", $_SESSION["user_id"], PDO::PARAM_STR);
		$stmt->execute();
		$dataset = $stmt->fetchAll(PDO::FETCH_ASSOC);
		$alert_status = "alert-success";
		
		$return = array(
	    "alert" => $alert_status,
	    "dataset" => $dataset
	  );
	}
  header('Content-type: application/json');  
  echo json_encode($return, JSON_UNESCAPED_UNICODE);
  exit();
?>
