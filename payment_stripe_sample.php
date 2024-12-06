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
  try{
    $stripe = new \Stripe\StripeClient(S_KEY);
  
    $product = $stripe->products->create(
        ['name' => 'Fundraising dinner']
        ,['stripe_account' => $_GET["i"]]
    );
    log_writer2("\$product",$product,"lv3");
    
    $price = $stripe->prices->create(
        [
            'currency' => 'jpy',
            //'custom_unit_amount' => ['enabled' => true],
            'unit_amount' => 10000,
            'product' => $product->id,
        ]
        ,['stripe_account' => $_GET["i"]]
    );
    log_writer2("\$price",$price,"lv3");
    
    $session = $stripe->checkout->sessions->create(
      [
        'payment_method_types' => ['card'],
        'line_items' => [
          [
          'price' => $price->id,
          'quantity' => 1,
          ],
        ],
        'payment_intent_data' => ['application_fee_amount' => 100],
        'mode' => 'payment',
        // ご自身のサイトURLを入力
        'success_url' => 'https://onlineshop-test.greeen-sys.com/settlement.php?key=543758632f78346f4a632b7856414d71512b6b3541773d3d',
        'cancel_url' => 'https://onlineshop-test.greeen-sys.com/settlement.php?key=543758632f78346f4a632b7856414d71512b6b3541773d3d',
      ],
      ['stripe_account' => $_GET["i"]]
    );
    log_writer2("\$session",$session,"lv3");
  }catch(Exception $e){
    log_writer2("Exception \$e",$e,"lv0");
  }
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

