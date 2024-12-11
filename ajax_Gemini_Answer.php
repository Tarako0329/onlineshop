<?php
//テスト用
require "php_header.php";
register_shutdown_function('shutdown_ajax',basename(__FILE__));
log_writer2("\$_POST",$_POST,"lv3");

$user_hash = $_POST["hash"] ;
$_SESSION["user_id"] = rot13decrypt2($user_hash);

$discription = "URL：".ROOT_URL."product.php?id=".$_SESSION["user_id"]."-".$_POST["hinCD"]." 商品名：".$_POST["hinmei"]."。説明：".$_POST["sort_info"]." ".$POST["information"];

use GeminiAPI\Client;
use GeminiAPI\Resources\ModelName;
use GeminiAPI\Resources\Parts\TextPart;

$client = new Client(GEMINI);

$response = $client->withV1BetaVersion()
    ->generativeModel(ModelName::GEMINI_1_5_FLASH)
    ->withSystemInstruction('凄腕インフルエンサーとして')
    ->generateContent(
        new TextPart('Xでバズるハッシュタグと日本語の投稿例を３つ、javascriptでそのまま使えるJSON形式で簡潔に提案してください。JSONの形式について、ハッシュタグはtags。投稿例はrei1,rei2,rei3で。'.$discription),
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