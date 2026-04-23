<?php
	//pgname: ajax_get_OAtalk_list.php
	//概要: 問い合わせのスレッド一覧を取得するためのAJAX
  require "php_header.php";

	if(empty($_SESSION["user_id"])){
	  $msg=$rtn;
	  $alert_status = "alert-warning";
	  $reseve_status = true;
	}else{
	  log_writer('\$_SESSION["uid"]',$_SESSION["user_id"]);
		
		$sql = "SELECT shop_id,askNO,customer,`name`,shouhinNM,max(insdate) as 最終更新日
			from online_q_and_a 
			where shop_id = :shop_id 
			group by shop_id,askNO,customer,`name`,shouhinNM
			order by askNO desc";
		$talk = $db->SELECT($sql,["shop_id" => $_SESSION["user_id"]]);

		//$talkの中のaskNOをrot13encrypt2で処理
		$i=0;
		foreach($talk as $row){
			$talk[$i]["askNO_hash"] = rot13encrypt2($row["askNO"]);
			$i++;
		}
	  //log_writer('\$talk',$talk);
	}
  header('Content-type: application/json');  
  echo json_encode($talk, JSON_UNESCAPED_UNICODE);
  exit();
?>
