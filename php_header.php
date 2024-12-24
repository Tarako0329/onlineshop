<?php
date_default_timezone_set('Asia/Tokyo'); 
define("VERSION","ver1.33.6");

//ini_set('max_execution_time', -1);
//ini_set('max_input_time', -1);
require "./vendor/autoload.php";
require "functions.php";

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

//リファイラの取得

if(!empty($_GET["amp;z"])){
  $get_z = $_GET["amp;z"];
}else if(!empty($_GET["z"])){
  $get_z = $_GET["z"];
}else{
  $get_z = "";
}

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
$ua = !empty($_SERVER['HTTP_USER_AGENT'])?$_SERVER['HTTP_USER_AGENT']:"";
$pattern_list_string = file_get_contents('bot_list.txt');

// 作成したパターン文字列を使い正規表現によるマッチングを行うbot判定
if(preg_match('/' . $pattern_list_string . '/', $ua) === 1){
  $bot = "bot";
}else{
  //訪問者のマーキング
  if(empty($_COOKIE["aclu"])){//アクセスログユーザの略
    $aclu = rot13encrypt2(date('Y/m/d-H:i:s')."__".$_SERVER['REMOTE_ADDR']);
    setCookie("aclu",$aclu , time() + 365*24*60*60, "/", "", TRUE, TRUE);
    $bot = "first";
  }else{
    $aclu = $_COOKIE["aclu"];
    setCookie("aclu",$aclu , time() + 365*24*60*60, "/", "", TRUE, TRUE);//延長
    $bot = "repeater";
  }
  //$bot = "user";
}
$get = print_r($_GET,true);
$get = str_replace(["\r","\n","\t"],"",$get);//改行・タブの削除
$log_param = [
  //!empty($_SERVER['REMOTE_ADDR'])?$_SERVER['REMOTE_ADDR']:""
  $_SERVER['REMOTE_ADDR']
  ,$bot
  ,$ua
  ,!empty($_SERVER['HTTP_REFERER'])?$_SERVER['HTTP_REFERER']:""
  ,!empty($_SERVER['PHP_SELF'])?$_SERVER['PHP_SELF']:""
  ,!empty($_GET['id'])?$_GET['id']:""
  ,$get
  ,$get_z
  ,$aclu
];
aclog_writer($log_param,$pdo_h);


?>