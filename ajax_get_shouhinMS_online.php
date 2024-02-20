<?php
  require "php_header.php";
	$hinmei = (($_GET["f"])!=="undefined")?$_GET["f"]:"%";
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
				,rezMS.tanka as rez_tanka
				,rezMS.zeiKBN as rez_zeikbn
				,rezMS.bunrui1
				,rezMS.bunrui2
				,rezMS.bunrui3
				,rezMS.hyoujiKBN2
				,online.shouhinNM as onName
				,online.infomation
				,online.tanka
				,online.zeikbn
			from shouhinMS rezMS 
			left join shouhinMS_online online 
			on rezMS.uid = online.uid 
			and rezMS.shouhinCD = online.shouhinCD 
	    where rezMS.uid = :uid and rezMS.shouhinNM like :hinmei order by rezMS.shouhinNM";
		$stmt = $pdo_h->prepare($sql);
		$stmt->bindValue("uid", $_SESSION["user_id"], PDO::PARAM_STR);
		$stmt->bindValue("hinmei", $hinmei, PDO::PARAM_STR);
		$stmt->execute();
		$dataset = $stmt->fetchAll(PDO::FETCH_ASSOC);
		
		$sql = "select 
				rezMS.shouhinCD
				,rezMS.shouhinNM
				,pic.pic
			from shouhinMS rezMS 
			left join shouhinms_online_pic pic 
			on rezMS.uid = pic.uid 
			and rezMS.shouhinCD = pic.shouhinCD
			where rezMS.uid = :uid and rezMS.shouhinNM like :hinmei order by rezMS.shouhinNM";
		$stmt = $pdo_h->prepare($sql);
		$stmt->bindValue("uid", $_SESSION["user_id"], PDO::PARAM_STR);
		$stmt->bindValue("hinmei", $hinmei, PDO::PARAM_STR);
		$stmt->execute();
		$pic_set = $stmt->fetchAll(PDO::FETCH_ASSOC);

		
		
		$alert_status = "alert-success";

		
		$return = array(
	    "alert" => $alert_status,
	    "dataset" => $dataset,
			"pic_set" => $pic_set
	  );
	}
  header('Content-type: application/json');  
  echo json_encode($return, JSON_UNESCAPED_UNICODE);
  exit();
?>
