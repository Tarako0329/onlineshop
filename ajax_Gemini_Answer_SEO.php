<?php
//テスト用
require "php_header.php";
register_shutdown_function('shutdown_ajax',basename(__FILE__));
log_writer2("\$_POST",$_POST,"lv3");
if(EXEC_MODE==="Local"){
    $sample='{"introductions": [{"rei": "甘酸っぱいリンゴがごろごろ入った、絶品パウンドケーキ！しっとりとした生地とジューシーなリンゴのハーモニーが口いっぱいに広がります。こだわりの材料で焼き上げた、幸せを呼ぶ美味しさ。ぜひご堪能ください！"},{"rei": "青森県産りんごを贅沢に使用した、こだわりのパウンドケーキ。甘みと酸味のバランスが絶妙で、後味もスッキリ。紅茶やコーヒーとの相性も抜群です。特別な日の贈り物にも最適です。"},{"rei": "想像をはるかに超える、りんごの贅沢パウンドケーキ！ごろっと入ったリンゴの食感と、上品な甘さが特徴。手作りならではの温かみと、素材本来の美味しさを味わえます。オンライン限定販売！"}]}';

    echo $sample;
    exit();
}

$user_hash = $_POST["hash"] ;
$_SESSION["user_id"] = rot13decrypt2($user_hash);

$discription = '商品販売SEO対策のプロとして、GOOGLE検索でクリックしたくなる魅力的な紹介文(全角160文字程度)を5つ、javascriptでそのまま使えるJSON形式{introductions:[{rei:紹介文},{rei:紹介文},{rei:紹介文}]}で提案してください。'.
"商品名：[".$_POST["hinmei"]."],アピールポイント：[".$_POST["sort_info"]."], 商品の詳細・仕様・成分など：[".$_POST["information"]."]";

$msg = gemini_api($discription,"json");
log_writer2("\$msg",$msg,"lv3");

$answer = $msg["result"];

header('Content-type: application/json');
echo $answer;
exit();
?>