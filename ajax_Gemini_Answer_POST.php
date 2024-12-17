<?php
//テスト用
require "php_header.php";
register_shutdown_function('shutdown_ajax',basename(__FILE__));
log_writer2("\$_POST",$_POST,"lv3");
if(EXEC_MODE==="Local"){
    $sample='{"posts": {"tags": ["#りんごのパウンドケーキ","#手作りおやつ","#秋のスイーツ","#おうちカフェ","#焼き菓子","#プレゼントに最適","#絶品スイーツ","#パティシエ","#美味しいもの","#幸せの瞬間"],"texts": ["【秋の味覚🍂】甘酸っぱいリンゴがゴロゴロ入った、こだわりのパウンドケーキ🍎✨見てください、この美しい断面！甘みと酸味のバランスが絶妙で、一口食べたら止まらない美味しさなんです😋大切な方へのプレゼントにもぴったり🎁自分へのご褒美にもおすすめです💕今すぐチェック👉 https://onlineshop-test.greeen-sys.com/product.php?id=2-2#りんごのパウンドケーキ #手作りおやつ #秋のスイーツ #おうちカフェ #焼き菓子 #プレゼントに最適 #絶品スイーツ #パティシエ #美味しいもの #幸せの瞬間","🍎🍎🍎リンゴ好き必見！🍎🍎🍎ゴロゴロ入ったリンゴの食感がたまらない、贅沢パウンドケーキが登場！甘酸っぱいリンゴと、しっとりとした生地のハーモニーが口の中に広がります🤤ティータイムのお供に、特別な日のデザートに…✨様々なシーンで活躍してくれること間違いなしです😊販売元：Present商品名：りんごのパウンドケーキ購入はこちら➡️ https://onlineshop-test.greeen-sys.com/product.php?id=2-2#りんごのパウンドケーキ #手作りおやつ #秋のスイーツ #おうちカフェ #焼き菓子 #プレゼントに最適 #絶品スイーツ #パティシエ #美味しいもの #幸せの瞬間","【本日のおすすめスイーツ🍰】秋の味覚、リンゴを贅沢に使用したパウンドケーキをご紹介！厳選されたリンゴの甘酸っぱさと、しっとりとした生地の絶妙なバランスが、口の中に幸せを運んでくれます🥰大切な人への贈り物にも最適です🎁数量限定なので、お早めにどうぞ💨販売元: Present商品名: りんごのパウンドケーキご購入はこちら👉 https://onlineshop-test.greeen-sys.com/product.php?id=2-2#りんごのパウンドケーキ #手作りおやつ #秋のスイーツ #おうちカフェ #焼き菓子 #プレゼントに最適 #絶品スイーツ #パティシエ #美味しいもの #幸せの瞬間"]}}';

    echo $sample;
    exit();
}

$user_hash = $_POST["hash"] ;
$_SESSION["user_id"] = rot13decrypt2($user_hash);

$discription = "URL：".ROOT_URL."product.php?id=".$_SESSION["user_id"]."-".$_POST["hinCD"]."販売元:".$_POST["yagou"]." 商品名：".$_POST["hinmei"]."。説明：".$_POST["sort_info"]." ".$POST["information"];

use GeminiAPI\Client;
use GeminiAPI\Resources\ModelName;
use GeminiAPI\Resources\Parts\TextPart;

$client = new Client(GEMINI);

$response = $client->withV1BetaVersion()
    ->generativeModel(ModelName::GEMINI_1_5_FLASH)
    ->withSystemInstruction('凄腕インフルエンサーとして')
    ->generateContent(
        //new TextPart('Xでバズるハッシュタグとハッシュタグを含めた日本語の投稿例を３つ、javascriptでそのまま使えるJSON形式で簡潔に提案してください。JSONの形式について、ハッシュタグはtags。投稿例はrei1,rei2,rei3で。'.$discription),
        new TextPart('Xでバズるハッシュタグを10個と,URL,ハッシュタグを含めた日本語の投稿例をXに投稿できる文字数内で３つをJSON形式{"posts":{"tags":[tag1,tag2], "texts":[紹介文1,紹介文2,紹介文3]}}で出力。'.$discription),
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