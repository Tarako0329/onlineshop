<?php
	require "php_header.php";
	$token = csrf_create();
	if(empty($_GET["key"])){
		echo "参照用のURLが異なります。";
		exit();
	}
	$user_hash = $_GET["key"] ;
	$_SESSION["user_id"] = rot13decrypt2($user_hash);

  /*
  orderNOから請求額を取得して整合性をチェックする処理を追加する
  */

  $stripe = new \Stripe\StripeClient(S_KEY);

  $pay = $stripe->paymentIntents->create(
    [
      'amount' => $_SESSION["kingaku"],//請求額
      'currency' => 'jpy',
      'automatic_payment_methods' => ['enabled' => true],
      'application_fee_amount' => 0,//手数料
    ],
    ['stripe_account' => '{{CONNECTED_ACCOUNT_ID}}']
  );
/*
  $sessionM = $stripe->checkout->sessions->create([
    'payment_method_types' => ['card'],
    'line_items' => [[
      'price' => $plan_m,
      'quantity' => 1,
      ]],
      'mode' => 'subscription',
      // ご自身のサイトURLを入力
      'success_url' => $return_url.'?session_id={CHECKOUT_SESSION_ID}&M=1',
      'cancel_url' => $return_url.'?session_id={CHECKOUT_SESSION_ID}',
      'subscription_data' => ['trial_end' => "$trialdate"],
    ]);
*/
?>
<!DOCTYPE html>
<html lang='ja'>
  <head>
    <title>Checkout</title>
    <script src="https://js.stripe.com/v3/"></script>
  </head>
  <BODY>
    <form id="payment-form" data-secret="<?php echo $pay->client_secret ?>">
      <div id="payment-element">
        <!-- Elements will create form elements here -->
      </div>
      <button id="submit">Submit</button>
      <div id="error-message">
        <!-- Display error message to your customers here -->
      </div>
    </form>
    <script>
      // Set your publishable key: remember to change this to your live publishable key in production
      // See your keys here: https://dashboard.stripe.com/apikeys
      const stripe = Stripe('<?php echo P_KEY;?>');
      
      const options = {
        clientSecret: '{{CLIENT_SECRET}}',
        // Fully customizable with appearance API.
        //appearance: {/*...*/},
      };

      // Set up Stripe.js and Elements to use in checkout form, passing the client secret obtained in a previous step
      const elements = stripe.elements(options);

      // Create and mount the Payment Element
      const paymentElement = elements.create('payment');
      paymentElement.mount('#payment-element');

    </script>
  </BODY>

