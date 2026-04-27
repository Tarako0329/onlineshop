<?php
date_default_timezone_set('Asia/Tokyo'); 
// PHPがCLIで実行されていない場合のみセッションを開始する
if (php_sapi_name() !== 'cli') {
  ini_set('session.cookie_httponly', 1);
  ini_set('session.use_strict_mode', 1);
  session_name("PresentOnline_SESSION");
  session_start();
}
//ob_start();
define("VERSION","ver1.66.5");

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
define("APP_NAME",$_ENV["APP_NAME"]);
//システム通知
define("SYSTEM_NOTICE_MAIL",$_ENV["SYSTEM_NOTICE_MAIL"]);

//$rtn=session_set_cookie_params(24*60*60*24*3,'/',MAIN_DOMAIN,true,true);
//session_start();
//$_SESSION = [];

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
define("MERCHANT_ID",$_ENV["MERCHANT_ID"]);
//$MERCHANT_ID = $_ENV["MERCHANT_ID"];

spl_autoload_register(function ($className) {
  // 1. 名前空間のバックスラッシュ '\' を、OS標準のパス区切り文字（通常は '/'）に置換
  $path = str_replace('\\', DIRECTORY_SEPARATOR, $className);
  // 2. クラスファイルを探すフルパスを組み立て
  $file = __DIR__.DIRECTORY_SEPARATOR.$path.'.php';
  //log_writer2("Autoloading class", $className . " (Path: " . $file . ")", "lv3");
  // 3. ファイルが存在すれば読み込む
  if (file_exists($file)) {
    require_once $file;
    //log_writer2("Autoloading success", "Class: " . $className . " (Expected Path: " . $file . ")", "lv3");
  }else{
    log_writer2("Autoloading failed", "Class: " . $className . " (Expected Path: " . $file . ")", "lv3");
  }
});
$pdo_h = new PDO(DNS, USER_NAME, PASSWORD, get_pdo_options());

class_alias('classes\Utilities\Utilities','U');
use classes\Database\Database;
$db = new Database();

//require元PHPの取得
$request_php = basename($_SERVER['PHP_SELF']);
if(!str_starts_with($request_php, 'ajax_')){//リファイラの取得($request_phpがajax_から始まらない場合)
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
  }else if(!empty($_SERVER['HTTP_REFERER'])){
    if(strpos($_SERVER['HTTP_REFERER'], "instagram")!==false){
      $get_z = "instagram";
    }else if(strpos($_SERVER['HTTP_REFERER'], "facebook")!==false){
      $get_z = "facebook";
    }else{
      $get_z = "direct";
    }
  }else if(!empty($_GET["fbclid"])){
    $get_z = "facebook";
  }else{
    $get_z = "unknown";
  }

  // クライアントのユーザエージェントを取得
  $ua = !empty($_SERVER['HTTP_USER_AGENT'])?$_SERVER['HTTP_USER_AGENT']:"";
  $pattern_list_string = file_get_contents('bot_list.txt');
  $aclu="";

  // 作成したパターン文字列を使い正規表現によるマッチングを行うbot判定
  if(preg_match('/' . $pattern_list_string . '/', $ua) === 1 || $ua === "" || !empty($_GET["author"])){
    //大半のユーザはユーザエージェントある。ないのは非ユーザが大半で、あとは小数のセキュリティ意識高すぎ人なのでbotとして排除
    //$_GET["author"]はgooglebot
    $bot = "bot";
  }else{
    //訪問者のマーキング
    if(empty($_COOKIE["aclu"])){//アクセスログユーザの略
      //$aclu = rot13encrypt2(date('Y/m/d-H:i:s')."__".$_SERVER['REMOTE_ADDR']);
      $aclu = rot13encrypt2(date('Y/m/d-H:i:s')."__".session_id());
      setCookie("aclu",$aclu , time() + 365*24*60*60, "/", "", TRUE, TRUE);
      $bot = "first";
    }else{
      $aclu = $_COOKIE["aclu"];
      setCookie("aclu",$aclu , time() + 365*24*60*60, "/", "", TRUE, TRUE);//延長
      $bot = "repeater";
    }
    //$bot = "user";
  }
  $_SESSION["mark_id"] = $_SESSION["mark_id"] ?? $aclu;

  $get = print_r($_GET,true);
  $get = str_replace(["\r","\n","\t"],"",$get);//改行・タブの削除
  $log_param = [
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
  $_SESSION["log_param"] = $log_param;
  aclog_writer($log_param,$pdo_h);
}else{
  //log_writer2("","ajax：アクセスログスキップ","lv3");
}

?>