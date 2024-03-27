<?php
  require "php_header.php";

	$rtn = true;//csrf_checker(["xxx.php","xxx.php"],["P","C","S"]);
	if($rtn !== true){
	  $msg=$rtn;
	  $alert_status = "alert-warning";
	  $reseve_status = true;
	}else{

		$stripe = new \Stripe\StripeClient(S_KEY);
		$connect = $stripe->accounts->create([
			'type' => 'standard',
			'country' => 'JP',
			'email' => $_GET["mail"],
			'capabilities' => [
				'card_payments' => ['requested' => true],
				'transfers' => ['requested' => true],
			],
		]);

		$alert_status = "alert-success";

		$return_sts = array(
			"status" => $alert_status
			,"new_account" => $connect
		);
				
	}
  header('Content-type: application/json');  
  echo json_encode($return_sts, JSON_UNESCAPED_UNICODE);
  exit();
?>
