<?php
  require "php_header.php";

	$rtn = true;//csrf_checker(["xxx.php","xxx.php"],["P","C","S"]);
	if($rtn !== true){
	  $msg=$rtn;
	  $alert_status = "alert-warning";
	  $reseve_status = true;
	}else{
		try{
			$msg="OK";
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
		}catch(Exception $e){
			log_writer2("\$e",$e,"lv3");
			$msg = $e;
		}
		$alert_status = "alert-success";

		

		$return_sts = array(
			"status" => $alert_status
			,"new_account" => $account
			,"msg" => $msg
		);
				
	}
  header('Content-type: application/json');  
  echo json_encode($return_sts, JSON_UNESCAPED_UNICODE);
  exit();
?>
