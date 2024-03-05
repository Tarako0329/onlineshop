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
			"select HD.uid,HD.orderNO,HD.juchuu_date,HD.name,HD.yubin,HD.jusho,HD.tel,HD.mail,HD.st_name,HD.st_yubin,HD.st_jusho,HD.st_tel,HD.bikou
			,if(first_answer=0,'未','済') as オーダー受付
			,if(sent=0,'未','済') as 発送
			,if(payment=0,'未','済') as 入金
			,if(sent_flg=0,'無','有')	as 発送先有無
			,sum(MS.goukeitanka + MS.zei) as 税込総額
			from juchuu_head HD
			inner join juchuu_meisai MS
			on HD.orderNO = MS.orderNO 
	    where HD.uid = :uid
			group by HD.uid,HD.orderNO,HD.juchuu_date,HD.name,HD.yubin,HD.jusho,HD.tel,HD.mail,HD.st_name,HD.st_yubin,HD.st_jusho,HD.st_tel,HD.bikou,if(first_answer=0,'未','済'),if(sent=0,'未','済'),if(payment=0,'未','済'),if(sent_flg=0,'無','有')
			order by juchuu_date";
		$stmt = $pdo_h->prepare($sql);
		$stmt->bindValue("uid", $_SESSION["user_id"], PDO::PARAM_STR);
		$stmt->execute();
		$dataset = $stmt->fetchAll(PDO::FETCH_ASSOC);

	  $sql = 
			"select MS.*
			from juchuu_head HD
			inner join juchuu_meisai MS
			on HD.orderNO = MS.orderNO 
	    where HD.uid = :uid
			order by MS.orderNO,MS.shouhinCD";
		$stmt = $pdo_h->prepare($sql);
		$stmt->bindValue("uid", $_SESSION["user_id"], PDO::PARAM_STR);
		$stmt->execute();
		$dataset2 = $stmt->fetchAll(PDO::FETCH_ASSOC);

		$alert_status = "success";
		
		$return = array(
	    "alert" => $alert_status,
	    "header" => $dataset,
	    "body" => $dataset2
	  );
	}
  header('Content-type: application/json');  
  echo json_encode($return, JSON_UNESCAPED_UNICODE);
  exit();
?>
