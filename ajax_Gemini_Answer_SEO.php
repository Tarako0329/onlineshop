<?php
//テスト用
require "php_header.php";
register_shutdown_function('shutdown_ajax',basename(__FILE__));
log_writer2("\$_POST",$_POST,"lv3");
if(EXEC_MODE==="Local"){
    $sample='{"posts": [{"tags": ["#千葉グルメ", "#魚介類", "#贅沢グルメ", "#特産品", "#お取り寄せグルメ"],"rei1": "千葉県産の新鮮な魚介がたっぷり！厳選魚介盛で贅沢なひとときを✨特産品がぎっしり詰まった、この上ない美味しさ！😋今すぐチェック👉https://onlineshop-test.greeen-sys.com/product.php?id=- #厳選魚介盛"},{"tags": ["#海鮮丼", "#晩ごはん", "#家ごはん", "#おうちごはん", "#贅沢ディナー"],"rei2": "今日の晩ごはんはコレで決まり！😋千葉県産の厳選魚介盛で、豪華海鮮丼を作っちゃいました！新鮮でプリプリの魚介がたまらない…🤤あなたも贅沢な海鮮丼を味わってみませんか？詳細はこちら👉https://onlineshop-test.greeen-sys.com/product.php?id=- #厳選魚介盛"},{"tags": ["#ギフト", "#贈り物", "#プレゼント", "#お歳暮", "#お中元"],"rei3": "大切な人への贈り物にいかがですか？🎁千葉県産の厳選された魚介の詰め合わせ、厳選魚介盛は、特別な贈り物にぴったりです✨感謝の気持ちを込めて、贈ってみませんか？詳細はこちら👉https://onlineshop-test.greeen-sys.com/product.php?id=- #厳選魚介盛 #贈り物に最適"}]}';

    echo $sample;
    exit();
}

$user_hash = $_POST["hash"] ;
$_SESSION["user_id"] = rot13decrypt2($user_hash);

$discription = "商品名：".$_POST["hinmei"]."。説明：".$_POST["sort_info"]." ".$POST["information"];

use GeminiAPI\Client;
use GeminiAPI\Resources\ModelName;
use GeminiAPI\Resources\Parts\TextPart;

$client = new Client(GEMINI);

$response = $client->withV1BetaVersion()
    ->generativeModel(ModelName::GEMINI_1_5_FLASH)
    ->withSystemInstruction('商品販売SEO対策のプロとして')
    ->generateContent(
        new TextPart('GOOGLE検索で上位になりやすい魅力的な紹介文(100文字程度)を３つ、javascriptでそのまま使えるJSON形式で簡潔に提案してください。JSONの形式について、紹介文はrei1,rei2,rei3で。'.$discription),
    );

//print nl2br($response->text());

$answer = $response->text();
$answer = str_replace('```json','',$answer);
$answer = str_replace('```','',$answer);
$answer = str_replace('\n','',$answer);
$answer = str_replace('\r','',$answer);
$answer = str_replace('\r\n','',$answer);
$answer = substr($answer,1);
//log_writer2("response",$response->text(),"lv3");
//log_writer2("\$answer",$answer,"lv3");


//echo json_encode($answer, JSON_UNESCAPED_UNICODE);
//echo $response->text();
//echo $answer;
header('Content-type: application/json');
//echo json_encode($answer, JSON_UNESCAPED_UNICODE);
echo $answer;
exit();
?>