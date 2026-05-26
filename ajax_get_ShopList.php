<?php
	//ajaxでUsers_onlineテーブルのから出店準備が出来てるショップリストを取得。
	//お客サイト側のショップリスト表示用
	//PGNAME:ajax_get_ShopList.php
	$userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';

// ユーザーエージェントが空、または「PHP」という文字が含まれているかチェック
	if (empty($userAgent) || str_contains($userAgent, 'PHP')) {
		require "php_header_admin.php";
	}else{
		//ショッピングサイトのショップ一覧から呼び出し
		require "php_header.php";
	}

	register_shutdown_function('shutdown_ajax',basename(__FILE__));

	$rtn = csrf_checker(["shops.php","send_access_report.php"],[]);
	U::log("\$rtn",$rtn,4);
	if($rtn !== true){
	  $msg=$rtn;
	  $alert_status = "alert-warning";
	  $reseve_status = true;
	}else{
		$alert_status = "alert-success";
		$msg="";
		/*
		$sql = "SELECT 
				um.*
				,ifnull(sm.sel_cnt,0) as sel_cnt
				,'' as key2
			from Users_online um
			left join (SELECT uid,count(*) as sel_cnt FROM `shouhinMS_online` where status <> 'stop' group by uid) as sm
			on um.uid = sm.uid
			where um.uid like :uid 
			order by RAND()";

		$data = $db->SELECT($sql,["uid" => $_SESSION["user_id"]]);
		*/
		$sql = "SELECT 
				um.*
				,ifnull(sm.sel_cnt,0) as sel_cnt
				,'' as key2
			from Users_online um
			left join (SELECT uid,count(*) as sel_cnt FROM `shouhinMS_online` where status <> 'stop' group by uid) as sm
			on um.uid = sm.uid
			order by RAND()";

		$data = $db->SELECT($sql,[]);

		if(empty($data)){
			$alert_status = "alert-danger";
			$msg="User_Not_Found";
		}

		$i=0;
		foreach($data as $row){
			$data[$i]["key2"] = rot13encrypt2($row["uid"]);
			$i++;
		}
	}

	$return_sts = array(
		"status" => $alert_status
		,"msg" => $msg
		,"Users_online" => $data ?? []
	);
  header('Content-type: application/json');  
  echo json_encode($return_sts, JSON_UNESCAPED_UNICODE);
  exit();
?>
