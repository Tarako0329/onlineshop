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

			if($id==="none"){
				$account = $stripe->accounts->create([
					'type' => 'standard',
					'country' => 'JP',
					'email' => $_GET["mail"],
					/*'capabilities' => [
						'card_payments' => ['requested' => true],
						'transfers' => ['requested' => true],
					],*/
				]);
				log_writer2("\$account",$account->id,"lv3");
				$id = $account->id;
			}else{
				log_writer2("\$account","skip create stripe id","lv3");
			}

			$link = $stripe->accountLinks->create([
				'account' => $id,
				'return_url' => ROOT_URL.'ajax_create_stripe_return_url.php?hash='.$_GET["hash"]."&id=".$id,	//うまくいった？
				'refresh_url' => ROOT_URL.'settlement.php?key='.$_GET["hash"]."&stripe_setting=unable",		//うまくいかなかった
				'type' => 'account_onboarding',
			]);
			log_writer2("\$link",$link,"lv3");

			$sql = "update Users_online set stripe_id = '".$id."' where uid = ".$_SESSION["user_id"];
			$stmt = $pdo_h->prepare( $sql );
			$sqllog .= rtn_sqllog($sql,[]);
			$status = $stmt->execute();
			$sqllog .= rtn_sqllog("--execute():正常終了",[]);

			$error="";
			$alert_status = "success";
		}catch(Exception $e){
			log_writer2("\$e",$e,"lv0");
			$alert_status = "danger";
			$error = json_encode($e, JSON_UNESCAPED_UNICODE);
		}
		

		$return_sts = array(
			"status" => $alert_status
			,"stripe_id" => $id
			,"link" => $link->url
			,"error" => $error
		);
				
	}
  header('Content-type: application/json');  
  echo json_encode($return_sts, JSON_UNESCAPED_UNICODE);
  exit();
?>
