<?php
//お客さんが商品詳細を開いたときにLOGを取得するためのajax
//PGNAME:ajax_ins_access_log.php
date_default_timezone_set('Asia/Tokyo'); 
define("VERSION","ver1.35.0");

require "./vendor/autoload.php";
require "functions.php";
$time = date('Ymd-His');

//.envの取得
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();
define("MAIN_DOMAIN",$_ENV["MAIN_DOMAIN"]);
define("ROOT_URL",$_ENV["HTTP"]);
define("EXEC_MODE",$_ENV["EXEC_MODE"]);
define("APP_NAME",$_ENV["APP_NAME"]);
//システム通知
define("SYSTEM_NOTICE_MAIL",$_ENV["SYSTEM_NOTICE_MAIL"]);

session_start();
//log_writer2("\$time",$time,"lv3");

// DBとの接続
define("DNS","mysql:host=".$_ENV["SV"].";dbname=".$_ENV["DBNAME"].";charset=utf8");
define("USER_NAME", $_ENV["DBUSER"]);
define("PASSWORD", $_ENV["PASS"]);
define("DB_HOST", $_ENV["SV"]);
define("DB_NAME", $_ENV["DBNAME"]);

//$pdo_h = new PDO(DNS, USER_NAME, PASSWORD, get_pdo_options());
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
use classes\Database\Database;
$db = new Database();


//メール送信関連
define("HOST", $_ENV["HOST"]);
define("PORT", $_ENV["PORT"]);
define("FROM", $_ENV["FROM"]);
define("PROTOCOL", $_ENV["PROTOCOL"]);
define("POP_HOST", $_ENV["POP_HOST"]);
define("POP_USER", $_ENV["POP_USER"]);
define("POP_PASS", $_ENV["POP_PASS"]);


register_shutdown_function('shutdown_ajax',basename(__FILE__));

$msg = "";                          //ユーザー向け処理結果メッセージ
$reseve_status=false;               //処理結果セット済みフラグ。



if(empty($_SESSION["log_param"])){
	$msg="ログ情報がありません";
	$alert_status = "warning";
	$reseve_status = true;
}else{
	try{
		$_SESSION["log_param"][4] = "/product.php";
		$_SESSION["log_param"][5] = $_POST["shouhinCD"];	//X-X
		$_SESSION["log_param"][6] = "open";
		log_writer2("\$_SESSION[log_param]",$_SESSION["log_param"],"lv3");

		aclog_writer($_SESSION["log_param"],$pdo_h);

		$msg="ログ書き込み成功";
		$reseve_status=true;
	}catch(\Throwable $e){
		$db->Exception_rollback($e);
		$msg .= "アクセスログ登録エラー。管理者へ通知しました。";
		$reseve_status=true;
	}
}

$return_sts = array(
	"MSG" => $msg
);
header('Content-type: application/json');
echo json_encode($return_sts, JSON_UNESCAPED_UNICODE);

exit();
?>