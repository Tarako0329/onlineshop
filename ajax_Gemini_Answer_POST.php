<?php
//テスト用
require "php_header.php";
register_shutdown_function('shutdown_ajax',basename(__FILE__));
log_writer2("\$_POST",$_POST,"lv3");
if(EXEC_MODE==="Local"){
    $sample='{"posts":{"tags": ["#りんごのパウンドケーキ","#アップルケーキ","#手作りケーキ","#おうちカフェ","#ティータイム","#スイーツ","#焼き菓子","#プレゼント","#ギフト","#美味しい"],"texts": [{"text": "想像を超えるリンゴの贅沢！🍎ゴロゴロ入った甘酸っぱいリンゴが、しっとり生地と絶妙なハーモニーを奏でる、りんごのパウンドケーキ。上品な甘さと爽やかな酸味で、至福のティータイムを演出します✨","tags": ["#りんごのパウンドケーキ","#アップルケーキ","#おうちカフェ","#ティータイム"],"URL": "https://onlineshop-test.greeen-sys.com/product.php?id=2-2&z="},{"text": "特別な日のプレゼントに🎁　青森県産の厳選りんごを使った、こだわりのパウンドケーキ。口に入れた瞬間広がる、りんごの芳醇な香り…至福のひとときをお届けします。","tags": ["#りんごのパウンドケーキ","#プレゼント","#ギフト","#スイーツ","#焼き菓子"],"URL": "https://onlineshop-test.greeen-sys.com/product.php?id=2-2&z=","tag_disp": "#りんごのパウンドケーキ,#プレゼント,#ギフト,#スイーツ,#焼き菓子","tag_param": "りんごのパウンドケーキ,プレゼント,ギフト,スイーツ,焼き菓子"},{"text": "贅沢したりんごがたっぷり！🍎しっとりふわふわのパウンドケーキは、紅茶やコーヒーとの相性抜群。一口食べたら止まらない美味しさです😋　自分へのご褒美にも、大切な人への贈り物にも最適です。","tags": ["#りんごのパウンドケーキ","#スイーツ","#おうちカフェ","#自分にご褒美","#ギフト"],"URL": "https://onlineshop-test.greeen-sys.com/product.php?id=2-2&z="}]}}';

    echo $sample;
    exit();
}

$user_hash = $_POST["hash"] ;
$_SESSION["user_id"] = rot13decrypt2($user_hash);

$discription = "URL：".ROOT_URL."product.php?id=".$_SESSION["user_id"]."-".$_POST["hinCD"]."&z= 販売元:".$_POST["yagou"]." 商品名：".$_POST["hinmei"]."。説明：".$_POST["sort_info"]." ".$_POST["information"];

use GeminiAPI\Client;
use GeminiAPI\Resources\ModelName;
use GeminiAPI\Resources\Parts\TextPart;

$client = new Client(GEMINI);

$response = $client->withV1BetaVersion()
    ->generativeModel(ModelName::GEMINI_1_5_FLASH)
    ->withSystemInstruction('凄腕インフルエンサーとして')
    ->generateContent(
        //new TextPart('Xでバズるハッシュタグとハッシュタグを含めた日本語の投稿例を３つ、javascriptでそのまま使えるJSON形式で簡潔に提案してください。JSONの形式について、ハッシュタグはtags。投稿例はrei1,rei2,rei3で。'.$discription),
        //new TextPart('Xでバズるハッシュタグを10個と,URL,ハッシュタグを含めた日本語の投稿例をXに投稿できる文字数内で３つをJSON形式{"posts":{"tags":[tag1,tag2], "texts":[紹介文1,紹介文2,紹介文3]}}で出力。'.$discription),
        new TextPart('Xでバズるハッシュタグを10個と,Xに投稿できる文字数内でハッシュタグを除いた日本語の投稿例を３つJSON形式{"posts":{"tags":[tag1,tag2], "texts":[{text:"",tags:[...],URL:""}]}}で出力。'.$discription),
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