<?php
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
define("TITLE",$_ENV["TITLE"]);
//システム通知
define("SYSTEM_NOTICE_MAIL",$_ENV["SYSTEM_NOTICE_MAIL"]);

session_start();
log_writer2("\$time",$time,"lv3");

// DBとの接続
define("DNS","mysql:host=".$_ENV["SV"].";dbname=".$_ENV["DBNAME"].";charset=utf8");
define("USER_NAME", $_ENV["DBUSER"]);
define("PASSWORD", $_ENV["PASS"]);
$pdo_h = new PDO(DNS, USER_NAME, PASSWORD, get_pdo_options());

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
	}catch(Exception $e){
		log_writer2("\$e",$e,"lv0");
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