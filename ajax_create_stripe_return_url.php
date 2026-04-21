<?php
	//StripeConnectの登録が終わった場合、もしくは戻るで戻った場合に処理されるPG
  require "php_header.php";
	$user_hash = $_GET["hash"] ;
	$_SESSION["user_id"] = rot13decrypt2($user_hash);
	log_writer2("ajax_create_stripe_return_url.php start","","lv3");

	$rtn = true;//csrf_checker(["xxx.php","xxx.php"],["P","C","S"]);
	$rtn = csrf_checker(["ajax_create_stripe.php"]);
	if(empty($_SESSION["user_id"]) || $rtn !== true){
		//ユーザーIDが取得できない場合は不正アクセスの可能性があるため、処理を中止してadmin_login.phpにリダイレクトする
	  $reseve_status = true;
		header("Location:".ROOT_URL.'admin_login.php?key='.$_GET["hash"]);
		exit();
	}else{
		try{
			$stripe = new \Stripe\StripeClient(S_KEY);
			$id = $_GET["id"];
			$account = $stripe->accounts->retrieve($id, []);

			log_writer2("\$account",$account,"lv3");

			if(empty($account->settings->payments->statement_descriptor)){
				$stripe_setting = "Registering";	//登録中
			}else{
				$stripe_setting = "Registered";	//登録済み
			}
			$db->begin_tran();
			$sql = "UPDATE Users_online set Stripe_Approval_Status = :stripe_setting WHERE `uid` = :uid";
			$db->UP_DEL_EXEC($sql,["stripe_setting" => $stripe_setting,"uid" => $_SESSION["user_id"]]);
			$db->commit_tran();

			$get_value = 'key='.$_GET["hash"];
		}catch(Exception $e){
			$get_value = 'key='.$_GET["hash"]."&stripe_setting=unable";
			$db->rollback_tran($e->getMessage());
			log_writer2("\$e",$e,"lv0");
		}
	}
	header("Location:".ROOT_URL.'settlement.php?'.$get_value);
	exit();

?>
