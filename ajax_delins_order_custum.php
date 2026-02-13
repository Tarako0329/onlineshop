<?php
require "php_header.php";
register_shutdown_function('shutdown_ajax',basename(__FILE__));

$msg = "";                          //ユーザー向け処理結果メッセージ
$alert_status = "alert-warning";    //bootstrap alert class
$reseve_status=false;               //処理結果セット済みフラグ。
$timeout=false;                     //セッション切れ。ログイン画面に飛ばすフラグ
$sqllog="";

log_writer2("\$_POST",$_POST,"lv3");

$rtn = csrf_checker(["order_management.php"],["P","C","S"]);
if($rtn !== true){
	$msg=$rtn;
	$alert_status = "alert-warning";
	$reseve_status = true;
}else{
	if($rtn===false){
		$reseve_status = true;
		$msg="長時間操作されていないため、自動ﾛｸﾞｱｳﾄしました。再度ログインし、もう一度xxxxxxして下さい。";
		$_SESSION["EMSG"]="長時間操作されていないため、自動ﾛｸﾞｱｳﾄしました。再度ログインし、もう一度xxxxxxして下さい。";
		$timeout=true;
	}else{

		//更新モード(実行)
		$sqlstr_m = "INSERT into juchuu_meisai(orderNO,shouhinCD,shouhinNM,su,tanka,goukeitanka,zeikbn,bikou) values(:orderNO,:shouhinCD,:shouhinNM,:su,:tanka,:goukeitanka,:zeikbn,:bikou)";

		try{
			$pdo_h->beginTransaction();
			$sqllog .= rtn_sqllog("START TRANSACTION",[]);

			//明細登録
			
			$params["SEQ"] = $_POST["SEQ"];
			$params["orderNO"] = $_POST["orderNO"];
			$params["shouhinCD"] = $_POST["shouhinCD"];
			$params["shouhinNM"] = $_POST["shouhinNM"];
			$params["su"] = $_POST["su"];
			$params["tanka"] = $_POST["tanka"];
			$params["goukeitanka"] = $_POST["su"] * $_POST["tanka"];
			$params["zeikbn"] = $_POST["zeikbn"];
			$params["bikou"] = $_POST["bikou"];

			//（削除＆登録）
			IF(!empty($_POST["SEQ"])){
				$sqlstr = "DELETE FROM juchuu_meisai WHERE SEQ = :SEQ";
				$stmt = $pdo_h->prepare($sqlstr);
				$stmt->bindValue("SEQ", $params["SEQ"], PDO::PARAM_INT);
				$sqllog .= rtn_sqllog($sqlstr,$params);
				$stmt->execute();
				$sqllog .= rtn_sqllog("-- execute():正常終了",[]);
			}

			$stmt = $pdo_h->prepare( $sqlstr_m );
			$stmt->bindValue("orderNO", $params["orderNO"], PDO::PARAM_STR);
			$stmt->bindValue("shouhinCD", $params["shouhinCD"], PDO::PARAM_INT);
			$stmt->bindValue("shouhinNM", $params["shouhinNM"], PDO::PARAM_STR);
			$stmt->bindValue("su", $params["su"], PDO::PARAM_INT);
			$stmt->bindValue("tanka", $params["tanka"], PDO::PARAM_INT);
			$stmt->bindValue("goukeitanka", $params["goukeitanka"], PDO::PARAM_INT);
			$stmt->bindValue("zeikbn", $params["zeikbn"], PDO::PARAM_INT);
			$stmt->bindValue("bikou", $params["bikou"], PDO::PARAM_STR);
			$sqllog .= rtn_sqllog($sqlstr_m,$params);

			$status = $stmt->execute();
			$sqllog .= rtn_sqllog("-- execute():正常終了",[]);

			//消費税明細の再計算（削除＆登録）
			$sqlstr = "DELETE FROM juchuu_meisai WHERE orderNO = :orderNO AND zei <> 0";
			$stmt = $pdo_h->prepare($sqlstr);
			$stmt->bindValue("orderNO", $params["orderNO"], PDO::PARAM_STR);
			$sqllog .= rtn_sqllog($sqlstr,$params);
			$stmt->execute();
			$sqllog .= rtn_sqllog("-- execute():正常終了",[]);
			
			$sqlstr = "INSERT into juchuu_meisai select null as SEQ, orderNO,JM.zeikbn as shouhinCD,ZMS.hyoujimei,0 as su,0 as tanka,0 as goukeitanka,FLOOR(sum(goukeitanka) * ZMS.zeiritu / 100) as zei ,JM.zeikbn,'-' as bikou ,NOW() as upd_datetime from juchuu_meisai JM inner join ZeiMS ZMS on JM.zeikbn = ZMS.zeiKBN where orderNO = :orderNO group by orderNO,ZMS.hyoujimei,JM.zeikbn,'-' having zei <> 0";
			$stmt = $pdo_h->prepare($sqlstr);
			$stmt->bindValue("orderNO", $params["orderNO"], PDO::PARAM_STR);
			$sqllog .= rtn_sqllog($sqlstr,$params);
			$stmt->execute();
			$sqllog .= rtn_sqllog("-- execute():正常終了",[]);

			$pdo_h->commit();
			$sqllog .= rtn_sqllog("commit",[]);
			sqllogger($sqllog,0);
	
			$msg = "登録が完了しました。";
			$alert_status = "alert-success";
			$reseve_status=true;

		}catch(Exception $e){
			$pdo_h->rollBack();
			$sqllog .= rtn_sqllog("rollBack",[]);
			sqllogger($sqllog,$e);
			log_writer2("\$e",$e,"lv0");
			$msg = "システムエラーによる更新失敗。管理者へ通知しました。";
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
	,"orderNO" => $params["orderNO"]
);
header('Content-type: application/json');
echo json_encode($return_sts, JSON_UNESCAPED_UNICODE);

exit();

?>