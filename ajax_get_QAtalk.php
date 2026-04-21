<?php
  require "php_header.php";

	if(empty($_SESSION["askNO"])){
	  $msg=$rtn;
	  $alert_status = "alert-warning";
	  $reseve_status = true;
	}else{
	  //log_writer('\$_SESSION["uid"]',++$a);
		$askNO = rot13decrypt2($_SESSION["askNO"]);
		$shop_id = $_SESSION["user_id"];
		
		$sql = "SELECT qa.*,us.logo,us.yagou,us.mail,us.line_id
			from online_q_and_a qa
			inner join Users_online us
			on qa.`shop_id` = us.uid
			where `askNO` = :askNO 
			and `shop_id` = :shop_id
			order by seq";
		$talk = $db->SELECT($sql,["shop_id" => $shop_id,"askNO" => $askNO]);
		
	  //log_writer('\$talk',$talk);
	}
  header('Content-type: application/json');  
  echo json_encode($talk, JSON_UNESCAPED_UNICODE);
  exit();
?>
