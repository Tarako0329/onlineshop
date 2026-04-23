<?php
//商品管理の販売ステータス変更を行うajaxファイル
//PGNAME; ajax_upd_shouhinMS_status.php
require "php_header.php";
register_shutdown_function('shutdown_ajax',basename(__FILE__));

$msg = "";                          //ユーザー向け処理結果メッセージ
$alert_status = "alert-warning";    //bootstrap alert class
$reseve_status=false;               //処理結果セット済みフラグ。
$timeout=false;                     //セッション切れ。ログイン画面に飛ばすフラグ
$sqllog="";
if(empty($_POST["hash"])){
    echo "アクセスが不正です。";
    exit();
}
$user_hash = $_POST["hash"] ;
$_SESSION["user_id"] = rot13decrypt2($user_hash);

log_writer2("\$_POST",$_POST,"lv3");

$rtn = csrf_checker(["shouhinMS.php"],["P","C","S"]);
if($rtn !== true){
	$msg=$rtn;
	$alert_status = "alert-warning";
	$reseve_status = true;
}else{
	try{
		$sql = "UPDATE shouhinMS_online set status = :status where uid = :uid and shouhinCD = :shouhinCD";

		$params["status"] = $_POST["status"];
		$params["uid"] = $_SESSION["user_id"];
		$params["shouhinCD"] = $_POST["shouhinCD"];
		$db->begin_tran();

		$db->UP_DEL_EXEC($sql,$params);
					
		$db->commit_tran();

		$msg .= ($_POST["status"]!=='del')?"販売ステータスを変更しました。":"商品を削除しました。";
		$alert_status = "alert-success";
		$reseve_status=true;

	}catch(\Throwable $e){
		$db->Exception_rollback($e);
		$msg .= "システムエラーによる更新失敗。管理者へ通知しました。";
		$alert_status = "alert-danger";
		$reseve_status=true;
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