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
$sns_type = !empty($_POST["sns_type"])?$_POST["sns_type"]:"SNS" ;
$_SESSION["user_id"] = rot13decrypt2($user_hash);

$discription = "URL：".ROOT_URL."product.php?id=".$_SESSION["user_id"]."-".$_POST["hinCD"]."&z= 販売元:".$_POST["yagou"]." 商品名：".$_POST["hinmei"]."。説明：".$_POST["sort_info"]." ".$_POST["information"];

/*
use GeminiAPI\Client;
use GeminiAPI\Resources\ModelName;
use GeminiAPI\Resources\Parts\TextPart;

$client = new Client(GEMINI);

$response = $client->withV1BetaVersion()
    ->generativeModel(ModelName::GEMINI_1_5_FLASH)
    ->withSystemInstruction('凄腕インフルエンサーとして')
    ->generateContent(
        new TextPart($sns_type.'でバズるハッシュタグを10個と,購買意欲を掻き立てる日本語の投稿例を３つJSON形式{"posts":{"tags":[tag1,tag2], "texts":[{text:"",tags:[...],URL:""}]}}で出力。投稿例は'.$sns_type.'文字程度でハッシュタグ不要。投稿例はtexts.textに格納。URLはtexts.URLに格納。ハッシュタグはtexts.tagsに格納。'.$discription),
    );
    //print nl2br($response->text());


$answer = $response->text();
$answer = str_replace('```json','',$answer);
$answer = str_replace('```','',$answer);
$answer = str_replace('\n','',$answer);
$answer = str_replace('\r','',$answer);
$answer = str_replace('\r\n','',$answer);
$answer = substr($answer,1);
*/

$url = GEMINI_URL.GEMINI;
$data = [
    'contents' => [
        [
            'parts' => [
                //['text' => 'こんにちは、Gemini！']
                ['text' => '凄腕インフルエンサーとして'.$sns_type.'でバズるハッシュタグを10個と,購買意欲を掻き立てる日本語の投稿例を３つJSON形式{"posts":{"tags":[tag1,tag2], "texts":[{text:"",tags:[...],URL:""}]}}で出力。投稿例は'.$sns_type.'文字程度でハッシュタグ不要。投稿例はtexts.textに格納。URLはtexts.URLに格納。ハッシュタグはtexts.tagsに格納。'.$discription]
            ]
        ]
    ]
];

$options = [
    'http' => [
        'method' => 'POST',
        'header' => [
            'Content-Type: application/json',
        ],
        'content' => json_encode($data),
    ],
];

$context = stream_context_create($options);
$response = file_get_contents($url, false, $context);

log_writer2("\$response",$response,"lv3");

if ($response === false) {
    $answer =  'Gemini呼び出しに失敗しました。時間をおいて、再度実行してください';
} else {
    $result = json_decode($response, true);
    $result = $result['candidates'][0]['content']['parts'][0]['text'];
    
    $result = str_replace('```json','',$result);
    $result = str_replace('```','',$result);
    $result = str_replace('\n','',$result);
    $result = str_replace('\r','',$result);
    $result = str_replace('\r\n','',$result);
    $answer = substr($result,1);
    //log_writer2("\$result",$result,"lv3"); 
}
header('Content-type: application/json');
echo $answer;
exit();
?>