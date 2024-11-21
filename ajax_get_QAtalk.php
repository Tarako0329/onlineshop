<?php
  require "php_header.php";

	if(empty($_SESSION["askNO"])){
	  $msg=$rtn;
	  $alert_status = "alert-warning";
	  $reseve_status = true;
	}else{
	  //log_writer('\$_SESSION["uid"]',++$a);
		$askNO = rot13decrypt2($_SESSION["askNO"]);
		
		$sql = "select qa.*,us.logo,us.yagou,us.mail,us.line_id
		from online_q_and_a qa
		inner join Users_online us
		on qa.shop_id = us.uid
		where askNO = :askNO order by seq";
		$stmt = $pdo_h->prepare($sql);
		$stmt->bindValue("askNO", $askNO, PDO::PARAM_INT);
		//$stmt->bindValue("askNO", 0, PDO::PARAM_INT);
		$stmt->execute();
		$talk = $stmt->fetchAll(PDO::FETCH_ASSOC);
	  //log_writer('\$talk',$talk);
	}
  header('Content-type: application/json');  
  echo json_encode($talk, JSON_UNESCAPED_UNICODE);
  exit();
?>
