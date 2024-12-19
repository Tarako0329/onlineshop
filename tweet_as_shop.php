<?php
//１日５０ツイートまでかつ月１５００ツイートまで
require "php_header.php";
register_shutdown_function('shutdown_ajax',basename(__FILE__));
log_writer2("\$_POST",$_POST,"lv1");
use Abraham\TwitterOAuth\TwitterOAuth;

$status="false";
$rtn = csrf_checker(["sales_via_SNS.php"],["P","C","S"]);
if($rtn !== true){
    $msg=$rtn;
    $alert_status = "alert-warning";
    $reseve_status = true;
    $msg='不正アクセス';
}

if(EXEC_MODE<>"Product"){
    $msg = "ツイートが送信されました！";
    $status = "success";
}else{
    $text = $_POST["tweet"].$_POST["hash_tag"]." ".$_POST["URL"];
    $user_hash = $_POST["hash"] ;
    $_SESSION["user_id"] = rot13decrypt2($user_hash);
    
    
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
      
      //$text = "Twitter APIテストです。\n";
      
      //if(strlen($_POST["tweet"]) <= (280-23)){//URLが半角23文字扱い。ハッシュタグは含まない
        $result = $connection->post("tweets", ["text"=>$text], ['jsonPayload'=>true]);
      
        $httpCode = $connection->getLastHttpCode();
        
        if ($httpCode == 201) { // 201は作成成功を示すステータスコード
          //$this->info("ツイートが送信されました！");
          $msg = "ツイートが送信されました！\n";
          $status = "success";
        } else {
          $errorMessage = isset($result->errors) ?json_encode($result->errors, JSON_UNESCAPED_UNICODE) :'不明なエラー';
          //$this->error("ツイートの送信に失敗しました。HTTPコード: $httpCode, エラーメッセージ: $errorMessage");
          $msg = "ツイートの送信に失敗しました。HTTPコード: $httpCode, エラーメッセージ: $errorMessage ";
          log_writer2("\$msg",$msg,"lv1");
          $msg = "ツイートの送信に失敗しました。文章をもう少し短くしてみてください。 250バイト程度が目安です。";
        }
      /*}else{
        $msg = "文章が長すぎます。全角100文字程度に収めてください。 - ".strlen($_POST["tweet"]);
      }*/
    }catch(Exception $e){
      print_r($e,true);
      echo "catch(Exception \$e)";
      log_writer2("\$e",$e,"lv0");
    }
}

$token = csrf_create();
$return_sts = array(
    "MSG" => $msg
    ,"status" => $status
    ,"csrf_create" => $token
);
header('Content-type: application/json');
echo json_encode($return_sts, JSON_UNESCAPED_UNICODE);

exit();
?>