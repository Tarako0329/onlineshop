<?php
  require "php_header.php";
	$hinmei = (($_GET["f"])!=="undefined")?$_GET["f"]:"%";

	//log_writer2("\$_GET",$_GET,"lv3");
	$rtn = true;//csrf_checker(["xxx.php","xxx.php"],["P","C","S"]);
	if($rtn !== true){
	  $msg=$rtn;
	  $alert_status = "alert-warning";
	  $reseve_status = true;
	}else{
	  //log_writer('\$_SESSION["uid"]',++$a);
	  $sql = 
			"select 
				shouhinCD
				,shouhinNM
				,tanka
				,zeiKBN as zeikbn
				,bunrui1
				,bunrui2
				,bunrui3
				,hyoujiKBN2
			from shouhinMS rezMS 
	    where rezMS.uid = :uid
			and shouhinNM like :hinmei
			order by shouhinNM";
		$stmt = $pdo_h->prepare($sql);
		$stmt->bindValue("uid", $_SESSION["user_id"], PDO::PARAM_STR);
		$stmt->bindValue("hinmei", $hinmei, PDO::PARAM_STR);
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
