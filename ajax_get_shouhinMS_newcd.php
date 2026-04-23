<?php
  require "php_header.php";

	$rtn = true;//csrf_checker(["xxx.php","xxx.php"],["P","C","S"]);
	if($rtn !== true){
	  $msg=$rtn;
	  $alert_status = "alert-warning";
	  $reseve_status = true;
	}else{
	  //log_writer('\$_SESSION["uid"]',++$a);
		$sql = "SELECT 
				MAX(shouhinCD) + 1 as nextCD
			from shouhinMS_online
			where `uid` = :uid group by `uid` order by shouhinNM";
		$row = $db->SELECT($sql,["uid" => $_SESSION["user_id"]]);
		if(Count($row) === 0){
			$NewCD = 1;
		}else{
			$NewCD = $row[0]["nextCD"];
		}

		$alert_status = "alert-success";
		
		$return = $NewCD;
	}
  header('Content-type: application/json');  
  echo json_encode($return, JSON_UNESCAPED_UNICODE);
  exit();
?>
