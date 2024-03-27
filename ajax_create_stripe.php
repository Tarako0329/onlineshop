<?php
  require "php_header.php";
	$user_hash = $_GET["key"] ;
	$_SESSION["user_id"] = rot13decrypt2($user_hash);

	$rtn = true;//csrf_checker(["xxx.php","xxx.php"],["P","C","S"]);
	if($rtn !== true){
	  $alert_status = "warning";
	  $reseve_status = true;
	}else{
		try{
			$stripe = new \Stripe\StripeClient(S_KEY);
			$account = $stripe->accounts->create([
				'type' => 'standard',
				'country' => 'JP',
				'email' => $_GET["mail"],
				/*'capabilities' => [
					'card_payments' => ['requested' => true],
					'transfers' => ['requested' => true],
				],*/
			]);
			log_writer2("\$account",$account,"lv3");

			$link = $stripe->accountLinks->create([
				'account' => $account->id,
				'refresh_url' => 'https://example.com/reauth',
				'return_url' => 'https://example.com/return',
				'type' => 'account_onboarding',
			]);
			log_writer2("\$link",$link,"lv3");

			$sql = "update Users_online set stripe_id = '".$account->id."' where uid = ".$_SESSION["user_id"];
			$stmt = $pdo_h->prepare( $sql );
			$sqllog .= rtn_sqllog($sql,[]);
			$status = $stmt->execute();
			$sqllog .= rtn_sqllog("--execute():正常終了",[]);

		}catch(Exception $e){
			log_writer2("\$e",$e,"lv3");
			$alert_status = "danger";
		}
		$alert_status = "success";


		

		$return_sts = array(
			"status" => $alert_status
			,"link" => $link->url
			,"msg" => $msg
		);
				
	}
  header('Content-type: application/json');  
  echo json_encode($return_sts, JSON_UNESCAPED_UNICODE);
  exit();
?>
