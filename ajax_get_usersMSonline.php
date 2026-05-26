<?php
	//ajaxでUsers_onlineテーブルの全レコードを取得する。
	//PGNAME:ajax_get_usersMSonline.php
  require "php_header_admin.php";
	register_shutdown_function('shutdown_ajax',basename(__FILE__));

	$rtn = csrf_checker(["order_rireki.php","acc_analysis.php","configration.php","settlement.php","order_management.php"],[]);
	if($rtn !== true){
	  $msg="アクセス元が不正です";
	  $alert_status = "alert-warning";
	  $reseve_status = true;
	}else{
	  //log_writer('\$_SESSION["uid"]',++$a);
		$alert_status = "alert-success";
		$msg="";
		$sql = "SELECT 
				um.*
				,ifnull(sm.sel_cnt,0) as sel_cnt
				,'' as key2
			from Users_online um
			left join (SELECT uid,count(*) as sel_cnt FROM `shouhinMS_online` where status <> 'stop' group by uid) as sm
			on um.uid = sm.uid
			where um.uid like :uid 
			order by RAND()";

		$data = $db->SELECT($sql,["uid" => $_SESSION["user_id"] ?? "%"]);

		if(empty($data)){
			$alert_status = "alert-danger";
			$msg="User_Not_Found";
		}

		$i=0;
		foreach($data as $row){
			$data[$i]["key2"] = rot13encrypt2($row["uid"]);
			$i++;
		}
		

		$_SESSION["stripe_connect_id"] = $data[0]["stripe_id"];

		$sql = "SELECT 
				*,if(flg=1,'true','false') as flg
			from Users_online_payinfo
			where uid like :uid ";

		$data2 = $db->SELECT($sql,["uid" => $_SESSION["user_id"] ?? "%"]);
	
	}

	$return_sts = array(
		"status" => $alert_status
		,"msg" => $msg
		,"Users_online" => $data ?? []
		,"Users_online_payinfo" => $data2 ?? []
	);
  header('Content-type: application/json');  
  echo json_encode($return_sts, JSON_UNESCAPED_UNICODE);
  exit();
?>
