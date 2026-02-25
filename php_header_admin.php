<?php
session_start();
//ob_start();
date_default_timezone_set('Asia/Tokyo'); 
define("VERSION","ver1.62.0-1");

//ini_set('max_execution_time', -1);
//ini_set('max_input_time', -1);
require_once "./vendor/autoload.php";
require_once "functions.php";
require_once "classes/database.php";


//.envの取得
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();
define("MAIN_DOMAIN",$_ENV["MAIN_DOMAIN"]);
define("ROOT_URL",$_ENV["HTTP"]);
define("EXEC_MODE",$_ENV["EXEC_MODE"]);
define("TITLE",$_ENV["TITLE"]);
//システム通知
define("SYSTEM_NOTICE_MAIL",$_ENV["SYSTEM_NOTICE_MAIL"]);

// DBとの接続
define("DNS","mysql:host=".$_ENV["SV"].";dbname=".$_ENV["DBNAME"].";charset=utf8");
define("USER_NAME", $_ENV["DBUSER"]);
define("PASSWORD", $_ENV["PASS"]);

define("DB_HOST", $_ENV["SV"]);
define("DB_NAME", $_ENV["DBNAME"]);

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
define("WEBHOOK_SKEY",$_ENV["WEBHOOK_SKEY"]);

define("GEMINI",$_ENV["GOOGLE_API"]);
define("GEMINI_URL",$_ENV["GEMINI_URL"]);
$MERCHANT_ID = $_ENV["MERCHANT_ID"];

define("GOOGLE_AUTH",$_ENV["GOOGLE_AUTH"]);
define("KEY",$_ENV["KEY"]);


$pdo_h = new PDO(DNS, USER_NAME, PASSWORD, get_pdo_options());
$db = new Database();

if(EXEC_MODE<>"Product"){
  $time=date('Ymd-His');
  $id="demo";
  $pass="00000000";
}else{
  $time=VERSION;
  $id="";
  $pass="";
}

?>