<?php
date_default_timezone_set('Asia/Tokyo'); 
define("VERSION","ver1.33.41");

//ini_set('max_execution_time', -1);
//ini_set('max_input_time', -1);
require "./vendor/autoload.php";
require "functions.php";

//リファイラの取得
$get_z = !empty($_GET["amp;z"])?$_GET["amp;z"]:$_GET["z"];

if($get_z==="X"){
  $get_z = "X.com";
}else if($get_z==="fb"){
  $get_z = "facebook";
}else if($get_z==="ln"){
  $get_z = "Line";
}else{
  $get_z = "direct";
}
// クライアントのユーザエージェントを取得
$ua = $_SERVER['HTTP_USER_AGENT'];
$pattern_list_string = file_get_contents('bot_list.txt');
// 作成したパターン文字列を使い正規表現によるマッチングを行う
if(preg_match('/' . $pattern_list_string . '/', $ua) === 1){
  //bot
  aclog_writer("bot",$_SERVER['REMOTE_ADDR']."：".$ua);
}else{
  aclog_writer("IP：リファイラ：SNS",",".$_SERVER['REMOTE_ADDR'].",".$_SERVER['HTTP_REFERER'].",".$get_z);
  aclog_writer("\$_GET",$_GET);
  aclog_writer("human",$_SERVER['REMOTE_ADDR']."：".$ua);
}

//aclog_writer("\$_SERVER",$_SERVER['REMOTE_ADDR']);
//aclog_writer("\$_GET['z']",$get_z);

//.envの取得
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();
define("MAIN_DOMAIN",$_ENV["MAIN_DOMAIN"]);
define("ROOT_URL",$_ENV["HTTP"]);
define("EXEC_MODE",$_ENV["EXEC_MODE"]);
define("TITLE",$_ENV["TITLE"]);
//システム通知
define("SYSTEM_NOTICE_MAIL",$_ENV["SYSTEM_NOTICE_MAIL"]);

//$rtn=session_set_cookie_params(24*60*60*24*3,'/',MAIN_DOMAIN,true,true);
session_start();
//$_SESSION = [];

//if(MAIN_DOMAIN==="localhost:81"){
if(EXEC_MODE<>"Product"){
  $time=date('Ymd-His');
  $id="demo";
  $pass="00000000";
}else{
  $time=VERSION;
  $id="";
  $pass="";
}


// DBとの接続
define("DNS","mysql:host=".$_ENV["SV"].";dbname=".$_ENV["DBNAME"].";charset=utf8");
define("USER_NAME", $_ENV["DBUSER"]);
define("PASSWORD", $_ENV["PASS"]);

//メール送信関連
define("HOST", $_ENV["HOST"]);
define("PORT", $_ENV["PORT"]);
define("FROM", $_ENV["FROM"]);
define("PROTOCOL", $_ENV["PROTOCOL"]);
define("POP_HOST", $_ENV["POP_HOST"]);
define("POP_USER", $_ENV["POP_USER"]);
define("POP_PASS", $_ENV["POP_PASS"]);

//stripe
define("S_KEY",$_ENV["SKey"]);
define("P_KEY",$_ENV["PKey"]);
define("OAuth",$_ENV["OAuth"]);

define("GEMINI",$_ENV["GOOGLE_API"]);

$pdo_h = new PDO(DNS, USER_NAME, PASSWORD, get_pdo_options());

//$_SESSION["user_id"] = '2';
//define("SAVEDIR", $_ENV["SAVEDIR"]);
//define("NOM", $_ENV["SIO"]);
/*
if(!empty($_GET["v"])){
  setCookie("vpool", $_GET["v"], time()+60*60*24*7, "/", "",true,true);
  $token = $_GET["v"];
}else{
  $token = !empty($_COOKIE["vpool"])?$_COOKIE["vpool"]:"";
}

if($_SESSION["MSG"] <> "ログオフしました"){
  if(!empty($_SESSION["user_id"])){
    //ログイン継続・期間延長

    setCookie("vpool", $token, time()+60*60*24*7, "/", "", TRUE, TRUE);//1week
    //log_writer("login延長",time()+60*60*24*7);
    try{
      $pdo_h->beginTransaction();
      $sql = "update loginkeeper set keepdatetime =:kdatetime where user_id =:id and token =:token)";
      $stmt = $pdo_h->prepare($sql);
      $stmt->bindValue("id", $_SESSION["user_id"], PDO::PARAM_STR);
      $stmt->bindValue("token", $_COOKIE["vpool"], PDO::PARAM_STR);
      $stmt->bindValue("kdatetime", strtotime("+7 day"), PDO::PARAM_STR);
      $stmt->execute();
      $pdo_h->commit();
    }catch(Exception $e){
      $_SESSION["MSG"]="loginkeeper延長登録失敗。";
      $pdo_h->rollBack();
    }
  }else{
    //トークンからuser_idを取得
    log_writer("トークンからuser_idを取得",$_SESSION);
    $sql = "select * from loginkeeper where token =:token and keepdatetime >=:kdatetime";
    $stmt = $pdo_h->prepare($sql);
    $stmt->bindValue("token", $token, PDO::PARAM_STR);
    $stmt->bindValue("kdatetime", date("Y-m-d"), PDO::PARAM_STR);
    $stmt->execute();
    $user = $stmt->fetchAll();
    if(!empty($user[0]["user_id"])){
      $_SESSION["user_id"] = $user[0]["user_id"];
      $_SESSION["name"] = "hoge";
    }else{
      $_SESSION["MSG"]="ログイン有効期限切れです。再ログインしてください。";
    }
  }
}
*/
?>