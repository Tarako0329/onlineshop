<?php
  require "php_header.php";
	$rtn = csrf_checker(["xxx.php","xxx.php"],["P","C","S"]);
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
				,online.shouhinNM as onName
				,online.infomation
				,online.tanka
				,online.zeikbn
				,pic.pic
			from shouhinMS rezMS 
			left join shouhinMS_online online
			on rezMS.uid = online.uid
			and rezMS.shouhinCD = online.shouhinCD
			left join shouhinms_online_pic pic
			on rezMS.uid = pic.uid
			and rezMS.shouhinCD = pic.shouhinCD;
	    where shouhinMS.uid = :uid order by rezMS.shouhinNM";
		$stmt = $pdo_h->prepare($sql);
		$stmt->bindValue("uid", $_SESSION["uid"], PDO::PARAM_STR);
	  $stmt->bindValue("from", $_GET["fm"], PDO::PARAM_STR);
	  $stmt->bindValue("to", (empty($_GET["to"]))?$_GET["fm"]:$_GET["to"], PDO::PARAM_STR);
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
