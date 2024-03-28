<?php
//テスト用
require "php_header.php";
$stripe = new \Stripe\StripeClient(S_KEY);

//$stripe->accounts->delete('acct_1OybDqAQ64vUhFxt', []);
$account = $stripe->accounts->retrieve('acct_1OzF6iEA6e52aUl3', []);
echo $account->settings->payments->statement_descriptor;
echo "<br>削除しました<br>";
echo $account;
?>