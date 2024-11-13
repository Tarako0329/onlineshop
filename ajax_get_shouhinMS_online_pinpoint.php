<?php
	//１品検索用
  require "php_header.php";
	//$shop = (($_GET["s"])!=="undefined")?$_GET["s"]:"-";
	$product = (($_GET["p"])!=="undefined")?$_GET["p"]:"-";
	log_writer2("\$product",$product,"lv3");
	//log_writer2("",$hinmei,"lv3");
	//$_SESSION["user_id"] = "%";
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
			where concat(online.uid,online.shouhinCD) = :hinmei 
			order by online.uid,online.shouhinCD";

		$stmt = $pdo_h->prepare($sql);
		//$stmt->bindValue("uid", $shop, PDO::PARAM_INT);
		$stmt->bindValue("hinmei", $product, PDO::PARAM_STR);
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
			where concat(online.uid,online.shouhinCD) = :hinmei 
			order by online.uid,online.shouhinCD,pic.sort";
		$stmt = $pdo_h->prepare($sql);
		//$stmt->bindValue("uid", $shop, PDO::PARAM_INT);
		$stmt->bindValue("hinmei", $product, PDO::PARAM_STR);
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
