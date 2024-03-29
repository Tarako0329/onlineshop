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

  /*
  $pay = $stripe->paymentIntents->create(
    [
      'amount' => $_SESSION["kingaku"],//請求額
      'currency' => 'jpy',
      'automatic_payment_methods' => ['enabled' => true],
      'application_fee_amount' => 0,//手数料
    ],
    ['stripe_account' => $_GET["i"]]
  );
  */
  $product = $stripe->products->create(['name' => 'Fundraising dinner']);
  $price = $stripe->prices->create([
    'currency' => 'jpy',
    'custom_unit_amount' => ['enabled' => true],
    'product' => $product->id,
  ]);

  $session = $stripe->checkout->sessions->create([
    'payment_method_types' => ['card'],
    'line_items' => [
      [
      'price' => $$price->id,
      'quantity' => 1,
      ]
    ],
    'mode' => 'payment',
    'payment_intent_data' => ['application_fee_amount' => 100],
    ['stripe_account' => $_GET["i"]],
      // ご自身のサイトURLを入力
    'success_url' => $return_url.'?session_id={CHECKOUT_SESSION_ID}&M=1',
    'cancel_url' => $return_url.'?session_id={CHECKOUT_SESSION_ID}',
    ]
  );

?>
<!DOCTYPE html>
<html lang='ja'>
  <head>
    <title>Checkout</title>
    <script src="https://js.stripe.com/v3/"></script>
  </head>
  <BODY>
    <button class="btn--topmenu btn-view" style="width:250px;height:80px;" id="checkout-buttonM">月額500円コース</button>
        
    <script type="text/javascript">
      let stripe = Stripe('<?php echo P_KEY;?>');
    
      let checkoutButton = document.getElementById('checkout-buttonM');
      checkoutButton.addEventListener('click', function() {
        stripe.redirectToCheckout({sessionId: "<?php echo $session->id;?>"})
        .then(function (result) {
          if (result.error) {
            // var displayError = document.getElementById('error-message');
            // displayError.textContent = result.error.message;
          }
        });
      });
    </script>

  </BODY>

