<?php
//１日５０ツイートまでかつ月１５００ツイートまで
if (php_sapi_name() != 'cli') {
  //exit('このスクリプトはCLIからのみ実行可能です。');
}
require "php_header.php";
register_shutdown_function('shutdown_ajax',basename(__FILE__));
//log_writer2("\$_POST",$_POST,"lv1");
use Abraham\TwitterOAuth\TwitterOAuth;
use GeminiAPI\Client;
use GeminiAPI\Resources\ModelName;
use GeminiAPI\Resources\Parts\TextPart;

$status="false";

//実行時間check
$stmt = $pdo_h->prepare("select * from online_shop_config");
$stmt->execute();
$row = $stmt->fetchAll(PDO::FETCH_ASSOC);

if($row["next_post_time"] >= date('Y-m-d H:i:s')){
  //実行時間前
  //exit();
}

if(EXEC_MODE==="Local"){
    $msg = "ツイートが送信されました！";
    $status = "success";
}else{
  //ポスト内容取得

  //商品選定
  $stmt = $pdo_h->prepare("select U.yagou,M.* from shouhinMS_online as M inner join Users_online as U on M.uid=U.uid where status='show' and IFNULL(auto_post_sns,'') not like '%X%' order by shouhinCD");
  $stmt->execute();
  $row = $stmt->fetchAll(PDO::FETCH_ASSOC);
  $post_index = rand(0,(count($row)-1));
  
  echo "<p>対象商品:".$row[$post_index]["shouhinNM"]."</p>";
  //log_writer2("\$post_index",$post_index,"lv1");
  //log_writer2("\$row",$row[$post_index],"lv1");

  //つぶやき作成
  $sns_type = "X" ;
  $uid = $row[$post_index]["uid"];
  $shouhinCD = $row[$post_index]["shouhinCD"];
  $yagou = $row[$post_index]["yagou"];
  $hinmei = $row[$post_index]["shouhinNM"];
  $sort_info = $row[$post_index]["short_info"];
  $information = $row[$post_index]["infomation"];
  
  $discription = "URL：".ROOT_URL."product.php?id=".$uid."-".$shouhinCD."&z=".$sns_type." 販売元:".$yagou." 商品名：".$hinmei."。説明：".$sort_info." ".$information;
  
  
  $client = new Client(GEMINI);
  
  $response = $client->withV1BetaVersion()
      ->generativeModel(ModelName::GEMINI_1_5_FLASH)
      ->withSystemInstruction('凄腕インフルエンサーとして')
      ->generateContent(
          new TextPart($sns_type.'で購買意欲を掻き立てる日本語の投稿例を10個出力。'.$sns_type.'にそのまま投稿できるようにＵＲＬとハッシュタグも含めて作成。phpのjson_decodeで処理できるように[{"post":投稿例},{"post":投稿例}]で出力。JSONオブジェクトを、プレーンテキスト形式で出力してください'.$discription),
      );
      
  //print nl2br($response->text());
      
  $answer = $response->text();
  $answer = str_replace('```json','',$answer);
  $answer = str_replace('```','',$answer);
  $answer = str_replace('\n','',$answer);
  $answer = str_replace('\r','',$answer);
  $answer = str_replace('\r\n','',$answer);
  $answer = substr($answer,1);
  $answer = json_decode($answer,true);
  //print_r($answer);
  log_writer2("\$answer",$answer,"lv1");
  
  
  $text = $answer[rand(0,9)]["post"];
  
  
  define("API_KEY",$_ENV["X_API_KEY"]);
  define("API_SECRET_KEY",$_ENV["X_API_SECRET_KEY"]);
  define("ACCESS_TOKEN",$_ENV["X_ACCESS_TOKEN"]);
  define("SECRET_ACCESS_TOKEN",$_ENV["X_SECRET_ACCESS_TOKEN"]);
  
  try{
    $connection = new TwitterOAuth(
      API_KEY,
      API_SECRET_KEY,
      ACCESS_TOKEN,
      SECRET_ACCESS_TOKEN
    );
    
    $connection->setApiVersion('2');
    
    //URLが半角23文字扱い。ハッシュタグは含まない
    $result = $connection->post("tweets", ["text"=>$text], ['jsonPayload'=>true]);
  
    $httpCode = $connection->getLastHttpCode();
    
    echo $text."\n";
    if ($httpCode == 201) { // 201は作成成功を示すステータスコード
      //$this->info("ツイートが送信されました！");
      echo "ツイートが送信されました！\n";
      $status = "success";
    } else {
      $errorMessage = isset($result->errors) ?json_encode($result->errors, JSON_UNESCAPED_UNICODE) :'不明なエラー';
      //$this->error("ツイートの送信に失敗しました。HTTPコード: $httpCode, エラーメッセージ: $errorMessage");
      echo "ツイートの送信に失敗しました。HTTPコード: $httpCode, エラーメッセージ: $errorMessage ";
      log_writer2("\$msg",$msg,"lv1");
    }
  }catch(Exception $e){
    //print_r($e,true);
    echo "catch(Exception \$e)";
    log_writer2("\$e",$e,"lv0");
  }
}

exit();
?>