<?php
//クレジットカードの利用フラグを更新
//PGNAME:ajax_upd_userMSonline_credit.php
require "php_header.php";
register_shutdown_function('shutdown_ajax',basename(__FILE__));

$msg = "";                          //ユーザー向け処理結果メッセージ
$alert_status = "warning";    //bootstrap alert class
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

$rtn = csrf_checker(["settlement.php",""],["P","C","S"]);
if($rtn !== true){
    $msg=$rtn;
    $alert_status = "warning";
    $reseve_status = true;
}else{
    if($rtn===false){
        $reseve_status = true;
        $msg="長時間操作されていないため、自動ﾛｸﾞｱｳﾄしました。再度ログインし、もう一度xxxxxxして下さい。";
        $_SESSION["EMSG"]="長時間操作されていないため、自動ﾛｸﾞｱｳﾄしました。再度ログインし、もう一度xxxxxxして下さい。";
        $timeout=true;
    }else{
        try{
            //更新モード(実行)
            $sqlstr_h = "UPDATE Users_online set credit = :credit where `uid` like :uid";

            $params["credit"] = $_POST["credit"];
            $params["uid"] = $_SESSION["user_id"];

            $db->begin_tran();
            $db->UP_DEL_EXEC($sqlstr_h,$params);
            
            $db->commit_tran();
	
			$msg = "登録が完了しました。";
			$alert_status = "success";
			$reseve_status=true;

        }catch(\Throwable $e){
		    $db->Exception_rollback($e);
            $msg = "システムエラーによる更新失敗。管理者へ通知しました。";
            $alert_status = "danger";
            $reseve_status=true;
        }
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