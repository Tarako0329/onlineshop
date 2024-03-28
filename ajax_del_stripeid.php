<?php
//テスト用
require "php_header.php";
$stripe = new \Stripe\StripeClient(S_KEY);

$stripe->accounts->delete('acct_1Oy7ftEJy7v7RoQL', []);
echo "削除しました";
?>