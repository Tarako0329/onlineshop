<?php
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
	//$rtn=check_session_userid_for_ajax($pdo_h);
	if($rtn===false){
			$reseve_status = true;
			$msg="長時間操作されていないため、自動ﾛｸﾞｱｳﾄしました。再度ログインし、もう一度xxxxxxして下さい。";
			$_SESSION["EMSG"]="長時間操作されていないため、自動ﾛｸﾞｱｳﾄしました。再度ログインし、もう一度xxxxxxして下さい。";
			$timeout=true;
	}else{
			$sql = "update shouhinMS_online set status = :status where uid = :uid and shouhinCD = :shouhinCD";

			$params["status"] = $_POST["status"];
			$params["uid"] = $_SESSION["user_id"];
			$params["shouhinCD"] = $_POST["shouhinCD"];

			try{
					$pdo_h->beginTransaction();
					$sqllog .= rtn_sqllog("START TRANSACTION",[]);

					$stmt = $pdo_h->prepare( $sql );
					$stmt->bindValue("status", $params["status"], PDO::PARAM_INT);
					$stmt->bindValue("uid", $params["uid"], PDO::PARAM_INT);
					$stmt->bindValue("shouhinCD", $params["shouhinCD"], PDO::PARAM_STR);
					
					$sqllog .= rtn_sqllog($sql,$params);

					$status = $stmt->execute();
					$sqllog .= rtn_sqllog("-- execute():正常終了",[]);
					
					$pdo_h->commit();
					$sqllog .= rtn_sqllog("commit",[]);
					sqllogger($sqllog,0);

					$msg .= ($_POST["status"]!=='del')?"販売ステータスを変更しました。":"商品を削除しました。";
					$alert_status = "alert-success";
					$reseve_status=true;

			}catch(Exception $e){
					$pdo_h->rollBack();
					$sqllog .= rtn_sqllog("rollBack",[]);
					sqllogger($sqllog,$e);
					log_writer2("\$e",$e,"lv0");
					$msg .= "システムエラーによる更新失敗。管理者へ通知しました。";
					$alert_status = "alert-danger";
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

/*
function shutdown(){
	// シャットダウン関数
	// スクリプトの処理が完了する前に
	// ここで何らかの操作をすることができます
	// トランザクション中のエラー停止時は自動rollbackされる。
		$lastError = error_get_last();
		
		//直前でエラーあり、かつ、catch処理出来ていない場合に実行
		if($lastError!==null && $GLOBALS["reseve_status"] === false){
			log_writer2(basename(__FILE__),"shutdown","lv3");
			log_writer2(basename(__FILE__),$lastError,"lv1");
				
			$emsg = "uid::".$_SESSION['user_id']." ERROR_MESSAGE::予期せぬエラー".$lastError['message'];
			if(EXEC_MODE!=="Local"){
					send_mail(SYSTEM_NOTICE_MAIL,"【".TITLE." - WARNING】".basename(__FILE__)."でシステム停止",$emsg,"");
			}
			log_writer2(basename(__FILE__)." [Exception \$lastError] =>",$lastError,"lv0");
	
			$token = csrf_create();
			$return_sts = array(
					"MSG" => "システムエラーによる更新失敗。管理者へ通知しました。"
					,"status" => "alert-danger"
					,"csrf_create" => $token
					,"timeout" => false
			);
			header('Content-type: application/json');
			echo json_encode($return_sts, JSON_UNESCAPED_UNICODE);
		}
}
*/
?>