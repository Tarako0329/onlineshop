<?php
//テスト用
require "php_header.php";
register_shutdown_function('shutdown_ajax',basename(__FILE__));


use GeminiAPI\Client;
use GeminiAPI\Resources\ModelName;
use GeminiAPI\Resources\Parts\TextPart;

$client = new Client(GEMINI);

$response = $client->withV1BetaVersion()
    ->generativeModel(ModelName::GEMINI_1_5_FLASH)
    ->withSystemInstruction('凄腕インフルエンサーとして')
    ->generateContent(
        new TextPart('Xでバズるハッシュタグと日本語の投稿例を３つJSON形式で簡潔に提案してください。JSONの形式について、ハッシュタグはtags。投稿例はrei1,rei2,rei3で。URL：https://cafe-present.greeen-sys.com/product.php?id=2-8 商品名：ざくざくナッツタルト。説明：米粉のタルトに豆腐ベースのダマンド、ナッツの上にメープルシロップを少しコーディングした人気のタルトです。リピーターさんが多いのでネットにて販売させて頂きます。《原材料》米粉、植物油、メープルシロップ、アーモンドプードル、カシューナッツ、アーモンド、くるみ、豆腐、片栗粉、食塩'),
    );

//print str_replace('\n','<BR>\n',$response->text());
print nl2br($response->text());
//log_writer2("response",$response,"lv3");

header('Content-type: application/json');
echo json_encode($response->text(), JSON_UNESCAPED_UNICODE);

exit();

?>