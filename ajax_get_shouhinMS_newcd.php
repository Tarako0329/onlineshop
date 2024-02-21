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
				max(shouhinCD) + 1 as nextCD
			from shouhinMS_online
			where uid = :uid group by uid order by shouhinNM";
		$stmt = $pdo_h->prepare($sql);
		$stmt->bindValue("uid", $_SESSION["user_id"], PDO::PARAM_STR);
		$stmt->execute();
		if($stmt->rowCount() == 0){
			$NewCD = 1;
		}else{
			$NewCD = $stmt->fetchAll(PDO::FETCH_ASSOC);
			$NewCD = $NewCD[0]["nextCD"];
		}

		$alert_status = "alert-success";
		
		$return = $NewCD;
	}
  header('Content-type: application/json');  
  echo json_encode($return, JSON_UNESCAPED_UNICODE);
  exit();
?>
