<?php
//log_writer2(basename(__FILE__)."",$sql,"lv3");
require "php_header.php";
//register_shutdown_function('shutdown');
register_shutdown_function('shutdown_ajax',basename(__FILE__));

//log_writer2("\$_POST",$_POST,"lv3");

$msg = "";                          //ユーザー向け処理結果メッセージ
$alert_status = "alert-warning";    //bootstrap alert class
$reseve_status=false;               //処理結果セット済みフラグ。
$timeout=false;                     //セッション切れ。ログイン画面に飛ばすフラグ
$sqllog="";
if($_POST["colum"]==="cancel"){
	//キャンセルは顧客からだからHASH不要
	$_SESSION["user_id"] = "%";
}else if(empty($_POST["hash"])){
	echo "アクセスが不正です。";
	exit();
}else{
	$user_hash = $_POST["hash"] ;
	$_SESSION["user_id"] = rot13decrypt2($user_hash);
}


$rtn = csrf_checker(["order_management.php","order_rireki.php"],["P","C","S"]);
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
		$colum = $_POST["colum"];   //更新対象項目名
		$value = $_POST["value"];
		$orderNO = $_POST["orderNO"];

		//利用カラムのチェックリスト
		$allowed_columns = [
    	'first_answer', // 受付連絡有無
    	'payment',        // 支払い有無
    	'sent',   // 発送有無
    	'post_corp',   // 配送会社
    	'postage',   // 送料
    	'postage_zeikbn',   // 送料税区分
    	'postage_url',   // 配送確認URL
    	'postage_no',   // 配送NO
    	'name',   // 注文者
    	'yubin',   // 郵便
    	'jusho',   // 住所
    	'tel',   // 
    	'mail',   // 
    	'sent_flg',   // 送付先指定
    	'st_name',   // 送付先めい
    	'st_yubin',   // 送付先郵便
    	'st_jusho',   // 送付先住所
    	'st_tel',   // 送付先TEL
    	'cancel',   // キャンセルフラグ
		];
		// 入力されたカラム名がホワイトリストに含まれているかチェック
		if (!in_array($colum, $allowed_columns, true)) {
    	// 許可されていないカラム名の場合は処理を中断し、エラーを出力
			$token = csrf_create();

			$return_sts = array(
				"MSG" => "エラー: 不正なカラム名が指定されました。"
				,"status" => "alert-danger"
				,"csrf_create" => $token
				,"timeout" => $timeout
			);
			header('Content-type: application/json');
			echo json_encode($return_sts, JSON_UNESCAPED_UNICODE);
			exit();	
    	//die("エラー: 不正なカラム名が指定されました。");
		}

		//$columと$sent_ymdはインジェクション対策済み
		if($colum==="sent"){
			$sent_ymd = ($value==1) ? date("Y-m-d") : "''";
			$sqlstr_h = "UPDATE juchuu_head set `sent` = :$colum,`sent_ymd` = '$sent_ymd' where `orderNO` = :orderNO and `uid` like :uid";
		}else{
			$sqlstr_h = "UPDATE juchuu_head set `$colum` = :$colum where `orderNO` = :orderNO and `uid` like :uid";
		}

		$params["uid"] = $_SESSION["user_id"];
		$params[$colum] = $_POST["value"];
		$params["orderNO"] = $_POST["orderNO"];

		try{
			$db->begin_tran();

			//受注ヘッダ登録
			$db->UP_DEL_EXEC($sqlstr_h,$params);

			if($colum ==="postage" || $colum === "postage_zeikbn"){
				$sqlstr_h = "UPDATE `juchuu_head` set `postage_zei` = postage - CEILING (postage / if(postage_zeikbn = 0,0,1.1)) where `orderNO` = :orderNO and `uid` like :uid";
				$db->UP_DEL_EXEC($sqlstr_h,$params);
			}
			
			//レジアプリユーザの売上連携
			$sqlstr_h ="SELECT * from Users where `uid` = :uid";
			$user_ms = $db->SELECT($sqlstr_h, [":uid" => $params["uid"]]);

			if($user_ms[0]["webrez"]==="use" && $colum==="payment" && $value==1){
				$sql = "SELECT m.orderNO,m.zeikbn,sum(m.goukeitanka) as urikin,sum(m.zei) as zei ,h.name
							from juchuu_meisai m
							inner join juchuu_head h
							on m.orderNO = h.orderNO
							where h.uid = :uid and m.orderNO = :orderNO group by h.uid,m.orderNO,zeikbn";
				$Uriage = $db->SELECT($sql, [":uid" => $params["uid"], ":orderNO" => $params["orderNO"]]);

				$sql = "SELECT MAX(UriageNO) as MAX_URINO from UriageData where `uid` = :uid";
				$UriageNO = $db->SELECT($sql, [":uid" => $params["uid"]]);
				log_writer2("\$UriageNO",$UriageNO,"lv3");

				$params = [];
				foreach($Uriage as $row){
					$NextUriNO = $UriageNO["MAX_URINO"] + 1;
					$params["uid"] = $_SESSION["user_id"];
					$params["UriageNO"]=$NextUriNO;
					$params["UriDate"]=date("Y-m-d");
					$params["TokuisakiNM"]=$row["name"]."様";
					$params["ShouhinCD"]=99999;
					$params["ShouhinNM"]="OnLine受注NO [".$row["orderNO"]."]";
					$params["tanka"]=$row["urikin"];
					$params["UriageKin"]=$row["urikin"];
					$params["zei"]=$row["zei"];
					$params["zeiKBN"]=$row["zeikbn"];
					$db->INSERT("UriageData",$params);
				}
			}else if($user_ms[0]["webrez"]==="use" && $colum==="payment" && $value==0){
				$sql ="DELETE from UriageData where `uid`=:uid and `ShouhinNM` Like :ShouhinNM";
				$db->UP_DEL_EXEC($sql,[":uid" => $params["uid"], ":ShouhinNM" => "OnLine受注NO [".$params["orderNO"]."]"]);
			}
			$db->commit_tran();

			$msg = "登録が完了しました。";
			$alert_status = "alert-success";
			$reseve_status=true;

		}catch(\Throwable $e){
			$db->Exception_rollback($e);
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
);
header('Content-type: application/json');
echo json_encode($return_sts, JSON_UNESCAPED_UNICODE);

exit();
?>