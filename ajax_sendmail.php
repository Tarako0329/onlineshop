<?php
//log_writer2(basename(__FILE__)."",$sql,"lv3");
//PGNAME:ajax_sendmail.php
//受注管理からステータス変更をした際に、顧客へメールで通知するためのAJAX処理。ステータス変更内容はDBにも保存される。
require "php_header_admin.php";
register_shutdown_function('shutdown_ajax',basename(__FILE__));

$msg = "";                          //ユーザー向け処理結果メッセージ
$alert_status = "alert-warning";    //bootstrap alert class
$reseve_status=false;               //処理結果セット済みフラグ。
$timeout=false;                     //セッション切れ。ログイン画面に飛ばすフラグ
$sqllog="";
sleep(1);//連続アクセス対策
//log_writer2("\$_POST",$_POST,"lv3");
if(empty($_POST["hash"])){
	echo "アクセスが不正です。";
	exit();
}
$user_hash = $_POST["hash"] ;
$_SESSION["user_id"] = rot13decrypt2($user_hash);

$rtn = csrf_checker(["order_management.php",""],["P","C","S"]);
if($rtn !== true){
	$msg=$rtn;
	$alert_status = "alert-warning";
	$reseve_status = true;
}else{
	try{
		$rtn = U::send_mail($_POST["mailto"],$_POST["subject"],$_POST["mailbody"],APP_NAME,$_POST["mailtoCC"]);
		if($rtn===true){
			$msg = "送信完了";
			$alert_status = "alert-success";
		}else{
			$msg = "送信失敗";
			$alert_status = "alert-warning";
		}
		$reseve_status=true;
	}catch(\Throwable $e){
		$msg = "システムエラーによる更新失敗。管理者へ通知しました。";
		$alert_status = "alert-danger";
		$reseve_status=true;
		U::log("\$e",$e,1);
	}
}

$token = csrf_create();

$return_sts = array(
	"MSG" => $msg
	,"status" => $alert_status
	,"csrf_create" => $token
	,"timeout" => $timeout
);
header('Content-type: application/json');
echo json_encode($return_sts, JSON_UNESCAPED_UNICODE);

exit();
?>