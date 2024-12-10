<?php
  require "php_header.php";
	register_shutdown_function('shutdown_ajax',basename(__FILE__));

	$rtn = true;//csrf_checker(["xxx.php","xxx.php"],["P","C","S"]);
	if($rtn !== true){
	  $msg=$rtn;
	  $alert_status = "alert-warning";
	  $reseve_status = true;
	}else{
	  //log_writer('\$_SESSION["uid"]',++$a);
		$alert_status = "alert-success";
		$msg="";
		$sql = "select 
				um.*
				,ifnull(sm.sel_cnt,0) as sel_cnt
			from Users_online um
			left join (SELECT uid,count(*) as sel_cnt FROM `shouhinMS_online` where status <> 'stop' group by uid) as sm
			on um.uid = sm.uid
			where um.uid like :uid 
			order by RAND()";
		$stmt = $pdo_h->prepare($sql);
		$stmt->bindValue("uid", $_SESSION["user_id"], PDO::PARAM_STR);
		$stmt->execute();

		$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

		if(empty($data)){
			$alert_status = "alert-danger";
			$msg="User_Not_Found";
		}

		$_SESSION["stripe_connect_id"] = $data[0]["stripe_id"];

		$sql = "select 
				*,if(flg=1,'true','false') as flg
			from Users_online_payinfo
			where uid like :uid ";
		$stmt = $pdo_h->prepare($sql);
		$stmt->bindValue("uid", $_SESSION["user_id"], PDO::PARAM_STR);
		$stmt->execute();

		$data2 = $stmt->fetchAll(PDO::FETCH_ASSOC);

		//$alert_status = "alert-success";

		$return_sts = array(
			"status" => $alert_status
			,"msg" => $msg
			,"Users_online" => $data
			,"Users_online_payinfo" => $data2
		);
				
	}
  header('Content-type: application/json');  
  echo json_encode($return_sts, JSON_UNESCAPED_UNICODE);
  exit();
?>
