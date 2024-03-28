<?php
  require "php_header.php";
	$user_hash = $_GET["hash"] ;
	$_SESSION["user_id"] = rot13decrypt2($user_hash);

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
				header("Location:".ROOT_URL.'settlement.php?key='.$_GET["hash"]."&stripe_setting=unable");
				exit();
		
			}else{
				header("Location:".ROOT_URL.'settlement.php?key='.$_GET["hash"]."&stripe_setting=able");
				exit();
		
			}

			/*
			$sql = "update Users_online set stripe_id = '".$id."' where uid = ".$_SESSION["user_id"];
			$stmt = $pdo_h->prepare( $sql );
			$sqllog .= rtn_sqllog($sql,[]);
			$status = $stmt->execute();
			$sqllog .= rtn_sqllog("--execute():正常終了",[]);
			*/

			$alert_status = "success";
		}catch(Exception $e){
			log_writer2("\$e",$e,"lv3");
			$alert_status = "danger";
			header("Location:".ROOT_URL.'settlement.php?key='.$_GET["hash"]."&stripe_setting=unable");
			exit();
		}
		
				
	}
?>
