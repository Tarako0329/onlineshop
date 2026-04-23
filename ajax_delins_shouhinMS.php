<?php
//log_writer2(basename(__FILE__)."",$sql,"lv3");
require "php_header.php";
//register_shutdown_function('shutdown');
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

U::log("\$_POST",$_POST,4);

$rtn = csrf_checker(["shouhinMS.php"],["P","C","S"]);
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
		$params["uid"] = $_SESSION["user_id"];
		$params["shouhinCD"] = $_POST["shouhinCD"];
		$params["shouhinNM"] = $_POST["shouhinNM"];
		$params["status"] = $_POST["status"];
		$params["short_info"] = $_POST["short_info"];
		$params["infomation"] = $_POST["infomation"];
		$params["haisou"] = $_POST["haisou"];
		$params["customer_bikou"] = $_POST["customer_bikou"];
		$params["tanka"] = $_POST["tanka"];
		$params["zeikbn"] = $_POST["zeikbn"];
		$params["shouhizei"] = $_POST["shouhizei"];
		$params["hash_tag"] = $_POST["hash_tag"];
		$params["limited_cd"] = $_POST["limited_cd"];
		$params["ins_datetime"] = (U::exist($_POST["ins_datetime"])) ? $_POST["ins_datetime"] : date("Y-m-d H:i:s");
		$params["upd_datetime"] = date("Y-m-d H:i:s");

		try{
			$db->begin_tran();
		
			$db->UP_DEL_EXEC("DELETE from shouhinMS_online where uid = :uid and shouhinCD = :shouhinCD",[
				"uid" => $params["uid"],
				"shouhinCD" => $params["shouhinCD"]
			]);
			$db->UP_DEL_EXEC("DELETE from shouhinMS_online_pic where uid = :uid and shouhinCD = :shouhinCD",[
				"uid" => $params["uid"],
				"shouhinCD" => $params["shouhinCD"]
			]);

			$db->INSERT("shouhinMS_online",[
				"uid" => $params["uid"],
				"shouhinCD" => $params["shouhinCD"],
				"shouhinNM" => $params["shouhinNM"],
				"status" => $params["status"],
				"short_info" => $params["short_info"],
				"infomation" => $params["infomation"],
				"haisou" => $params["haisou"],
				"customer_bikou" => $params["customer_bikou"],
				"tanka" => $params["tanka"],
				"zeikbn" => $params["zeikbn"],
				"shouhizei" => $params["shouhizei"],
				"hash_tag" => $params["hash_tag"],
				"limited_cd" => $params["limited_cd"],
				"ins_datetime" => $params["ins_datetime"],
				"upd_datetime" => $params["upd_datetime"]
			]);

			//画像ファイル処理
			$i=1;	//sortの初期値
			foreach($_POST["user_file_name"] as $row){
				if($row["delete_flg"]==='true'){
					//削除対象画像は処理をスキップ（事前に削除されているため）
					//実ファイルの削除処理は夜間バッチで処理する
					log_writer2("\$row['delete_flg']",$row["delete_flg"],"lv3");
					continue;
				}
				if (is_file($row["filename"])) {//fileの移動
					if ( rename($row["filename"] , str_replace("temp/","",$row["filename"]))) {
						$params["pic"] = str_replace("temp/","",$row["filename"] );
						//$params["sort"] = $row["sort"];
						$params["sort"] = $i;

						$i = $i+1;
					} else {
						$msg = "ファイル移動失敗";
						continue;
					}
				} else {
					$msg = "ファイル保存失敗・NOFILE";
					continue;
				}
				$db->INSERT("shouhinMS_online_pic",[
					"uid" => $params["uid"],
					"shouhinCD" => $params["shouhinCD"],
					"sort" => $params["sort"],
					"pic" => $params["pic"]		
				]);
			}
			$db->commit_tran();
			$msg .= "登録が完了しました。";
			$alert_status = "alert-success";
			$reseve_status=true;

		}catch(\Throwable $e){
			$db->Exception_rollback($e);
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

?>