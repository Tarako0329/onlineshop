<?php
  require "php_header.php";

	$rtn = true;//csrf_checker(["xxx.php","xxx.php"],["P","C","S"]);
	if($rtn !== true){
	  $msg=$rtn;
	  $alert_status = "alert-warning";
	  $reseve_status = true;
	}else{

		$stripe = new \Stripe\StripeClient('sk_test_51KY4B4CYDinnBLacPrR8MaoNwcOPxwQnCMLbB2VKRjiJo97V8mt3ssxdxXFDtymw4eMNPJn3cq7bTmcpU27Ctjh400C8WmO9pY');
		$stripe->accounts->create([
			'type' => 'custom',
			'country' => 'US',
			'email' => 'jenny.rosen@example.com',
			'capabilities' => [
				'card_payments' => ['requested' => true],
				'transfers' => ['requested' => true],
			],
		]);
		$alert_status = "alert-success";

		$return_sts = array(
			"status" => $alert_status
			,"Users_online" => $data
			,"Users_online_payinfo" => $data2
		);
				
	}
  header('Content-type: application/json');  
  echo json_encode($return_sts, JSON_UNESCAPED_UNICODE);
  exit();
?>
