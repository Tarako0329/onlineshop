<?php
  require "php_header.php";
	$hinmei = (($_GET["f"])!=="undefined")?$_GET["f"]:"%";
	//log_writer2("",$hinmei,"lv3");
	$_SESSION["user_id"] = "%";
	$rtn = true;//csrf_checker(["xxx.php","xxx.php"],["P","C","S"]);
	if($rtn !== true){
	  $msg=$rtn;
	  $alert_status = "false";
	  $reseve_status = true;
	}else{
	  //log_writer('\$_SESSION["uid"]',++$a);
		$sql = "select 
				online.shouhinCD
				,online.shouhinNM
				,online.status
				,online.short_info
				,online.infomation
				,online.customer_bikou
				,online.tanka
				,online.zeikbn
				,online.shouhizei
				,NULL as rezCD
				,online.tanka + online.shouhizei as zeikomikakaku
				,'0' as ordered
				,'0' as goukeikingaku
				,ums_inline.*
			from shouhinMS_online online 
			inner join Users_online ums_inline
			on online.uid = ums_inline.uid
			where online.uid like :uid and online.shouhinNM like :hinmei 
			order by online.uid,online.shouhinCD";

		$stmt = $pdo_h->prepare($sql);
		$stmt->bindValue("uid", $_SESSION["user_id"], PDO::PARAM_INT);
		$stmt->bindValue("hinmei", $hinmei, PDO::PARAM_STR);
		$stmt->execute();
		$count = $stmt->rowCount();
		$dataset = $stmt->fetchAll(PDO::FETCH_ASSOC);
		
		
		$sql = "select 
				online.uid
				,online.shouhinCD
				,online.shouhinNM
				,pic.sort
				,pic.pic as filename
			from shouhinMS_online online 
			left join shouhinMS_online_pic pic 
			on online.uid = pic.uid 
			and online.shouhinCD = pic.shouhinCD
			where online.uid like :uid and online.shouhinNM like :hinmei 
			order by online.uid,online.shouhinCD,pic.sort";
		$stmt = $pdo_h->prepare($sql);
		$stmt->bindValue("uid", $_SESSION["user_id"], PDO::PARAM_STR);
		$stmt->bindValue("hinmei", $hinmei, PDO::PARAM_STR);
		$stmt->execute();
		$pic_set = $stmt->fetchAll(PDO::FETCH_ASSOC);


		
		if($count!==0){
			$alert_status = "success";
		}
		

		
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
