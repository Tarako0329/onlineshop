<?php
	if($_GET["key"] ==="customer"){
  	require "php_header.php";
	}else if($_GET["key"] ==="shop"){
		require "php_header_admin.php";
	}else{
		//require "php_header.php";
	}

	//U::log("\$_SESSION",$_SESSION,4);
	U::log("\$_GET",$_GET,4);

	if(empty($_SESSION["askNO"])){
	  $alert_status = "alert-warning";
	  $reseve_status = true;
		$talk = [];
	}else{
		$askNO = rot13decrypt2($_SESSION["askNO"]);
		$shop_id = $_SESSION["user_id"] ?? "%";
		U::log("\$askNO",$askNO,4);
		
		$sql = "SELECT qa.*,us.logo,us.yagou,us.mail,us.line_id
			from online_q_and_a qa
			inner join Users_online us
			on qa.`shop_id` = us.uid
			where `askNO` = :askNO 
			and `shop_id` like :shop_id
			order by seq";
		$talk = $db->SELECT($sql,["shop_id" => $shop_id,"askNO" => $askNO]);
		//U::log("\$talk",$talk,4);
	}
  header('Content-type: application/json');  
  echo json_encode($talk, JSON_UNESCAPED_UNICODE);
  exit();
?>
