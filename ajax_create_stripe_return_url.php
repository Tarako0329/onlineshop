<?php
	//StripeConnectの登録が終わった場合、もしくは戻るで戻った場合に処理されるPG
  require "php_header.php";
	$user_hash = $_GET["hash"] ;
	$_SESSION["user_id"] = rot13decrypt2($user_hash);
	log_writer2("ajax_create_stripe_return_url.php start","","lv3");

	$rtn = true;//csrf_checker(["xxx.php","xxx.php"],["P","C","S"]);
	if($rtn !== true){
	  $alert_status = "warning";
	  $reseve_status = true;
	}else{
		try{
			$stripe = new \Stripe\StripeClient(S_KEY);
			$id = $_GET["id"];
			$account = $stripe->accounts->retrieve($id, []);

			log_writer2("\$account",$account,"lv3");

			if(empty($account->settings->payments->statement_descriptor)){
				//$_SESSION["stripe_setting"]="unable";
				$stripe_setting = "Registering";	//登録中
			}else{
				//$_SESSION["stripe_setting"]="able";
				$stripe_setting = "Registered";	//登録済み
			}

			$sql = "UPDATE Users_online set Stripe_Approval_Status = :stripe_setting WHERE uid = :uid";
			$stmt = $pdo_h->prepare( $sql );
			$params["stripe_setting"] = $stripe_setting;
			$params["uid"] = $_SESSION["user_id"];
			$stmt->bindValue("stripe_setting", $params["stripe_setting"], PDO::PARAM_STR);
			$stmt->bindValue("uid", $params["uid"], PDO::PARAM_STR);
			$sqllog .= rtn_sqllog($sql,$params);
			$status = $stmt->execute();
			$sqllog .= rtn_sqllog("-- execute():正常終了",[]);

			header("Location:".ROOT_URL.'settlement.php?key='.$_GET["hash"]);
			exit();


			$alert_status = "success";
		}catch(Exception $e){
			log_writer2("\$e",$e,"lv0");
			$alert_status = "danger";
			header("Location:".ROOT_URL.'settlement.php?key='.$_GET["hash"]."&stripe_setting=unable");
			exit();
		}
		
				
	}
?>
