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
	//$rtn=check_session_userid_for_ajax($pdo_h);
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

		$sqlstr_h = "UPDATE juchuu_head set ".$colum." = :".$colum." where orderNO = :orderNO and uid like :uid";

		$params["uid"] = $_SESSION["user_id"];
		$params[$colum] = $_POST["value"];
		$params["orderNO"] = $_POST["orderNO"];

		try{
			$pdo_h->beginTransaction();
			$sqllog .= rtn_sqllog("START TRANSACTION",[]);

			//受注ヘッダ登録

			$stmt = $pdo_h->prepare( $sqlstr_h );
			//bind処理
			$stmt->bindValue($colum, $params[$colum], PDO::PARAM_STR);
			$stmt->bindValue("orderNO", $params["orderNO"], PDO::PARAM_STR);
			$stmt->bindValue("uid", $params["uid"], PDO::PARAM_INT);

			$sqllog .= rtn_sqllog($sqlstr_h,$params);

			$status = $stmt->execute();
			$sqllog .= rtn_sqllog("--execute():正常終了",[]);

			if($colum ==="postage" || $colum === "postage_zeikbn"){
				$sqlstr_h = "UPDATE juchuu_head set postage_zei = postage - CEILING (postage / if(postage_zeikbn = 0,0,1.1)) where orderNO = :orderNO and uid like :uid";
				$stmt = $pdo_h->prepare( $sqlstr_h );
				//bind処理
				$stmt->bindValue("orderNO", $params["orderNO"], PDO::PARAM_STR);
				$stmt->bindValue("uid", $params["uid"], PDO::PARAM_INT);
				
				$sqllog .= rtn_sqllog($sqlstr_h,$params);
				
				$status = $stmt->execute();
				$sqllog .= rtn_sqllog("--execute():正常終了",[]);
			}
			
			//レジアプリユーザの売上連携
			$sqlstr_h ="SELECT * from Users where uid = :uid";
			$stmt = $pdo_h->prepare( $sqlstr_h );
			$stmt->bindValue("uid", $params["uid"], PDO::PARAM_INT);
			$stmt->execute();
			$user_ms = $stmt->fetchAll(PDO::FETCH_ASSOC);

			if($user_ms[0]["webrez"]==="use" && $colum==="payment" && $value==1){
				$sql = "SELECT m.orderNO,m.zeikbn,sum(m.goukeitanka) as urikin,sum(m.zei) as zei ,h.name
							from juchuu_meisai m
							inner join juchuu_head h
							on m.orderNO = h.orderNO
							where h.uid = :uid and m.orderNO = :orderNO group by h.uid,m.orderNO,zeikbn";
				$stmt = $pdo_h->prepare( $sql );
				$stmt->bindValue("uid", $params["uid"], PDO::PARAM_INT);
				$stmt->bindValue("orderNO", $params["orderNO"], PDO::PARAM_STR);
				$stmt->execute();
				$Uriage = $stmt->fetchAll(PDO::FETCH_ASSOC);

				$sql = "SELECT MAX(UriageNO) as MAX_URINO from UriageData where uid = :uid";
				$stmt = $pdo_h->prepare( $sql );
				$stmt->bindValue("uid", $params["uid"], PDO::PARAM_INT);
				$stmt->execute();
				$UriageNO = $stmt->fetch(PDO::FETCH_ASSOC);
				log_writer2("\$UriageNO",$UriageNO,"lv3");

				$sql = "INSERT into UriageData(uid,UriageNO,UriDate,TokuisakiNM,ShouhinCD,ShouhinNM,su,tanka,UriageKin,zei,zeiKBN)";
				$sql .= "	values(:uid,:UriageNO,CURDATE(),:TokuisakiNM,:ShouhinCD,:ShouhinNM,1,:tanka,:UriageKin,:zeigaku,:zeiKBN)";
				foreach($Uriage as $row){
					$NextUriNO = $UriageNO["MAX_URINO"] + 1;
					//$params["uid"]="";
					$params["UriageNO"]=$NextUriNO;
					$params["TokuisakiNM"]=$row["name"]."様";
					$params["ShouhinCD"]=99999;
					$params["ShouhinNM"]="OnLine受注NO [".$row["orderNO"]."]";
					$params["tanka"]=$row["urikin"];
					$params["UriageKin"]=$row["urikin"];
					$params["zeigaku"]=$row["zei"];
					$params["zeiKBN"]=$row["zeikbn"];
					$stmt = $pdo_h->prepare( $sql );
					$stmt->bindValue("uid", $params["uid"], PDO::PARAM_INT);
					$stmt->bindValue("UriageNO", $params["UriageNO"], PDO::PARAM_INT);
					$stmt->bindValue("TokuisakiNM", $params["TokuisakiNM"], PDO::PARAM_STR);
					$stmt->bindValue("ShouhinCD", $params["ShouhinCD"], PDO::PARAM_INT);
					$stmt->bindValue("ShouhinNM", $params["ShouhinNM"], PDO::PARAM_STR);
					$stmt->bindValue("tanka", $params["tanka"], PDO::PARAM_INT);
					$stmt->bindValue("UriageKin", $params["UriageKin"], PDO::PARAM_INT);
					$stmt->bindValue("zeigaku", $params["zeigaku"], PDO::PARAM_INT);
					$stmt->bindValue("zeiKBN", $params["zeiKBN"], PDO::PARAM_INT);
					
					$sqllog .= rtn_sqllog($sql,$params);
					$stmt->execute();
					$sqllog .= rtn_sqllog("--execute():正常終了",[]);
				}
			}else if($user_ms[0]["webrez"]==="use" && $colum==="payment" && $value==0){
				$sql ="DELETE from UriageData where uid=:uid and ShouhinNM Like :ShouhinNM";
				$stmt = $pdo_h->prepare($sql);
				$params["ShouhinNM"]="OnLine受注NO [".$params["orderNO"]."]";
				$stmt->bindValue("uid", $params["uid"], PDO::PARAM_INT);
				$stmt->bindValue("ShouhinNM", $params["ShouhinNM"], PDO::PARAM_STR);
				$sqllog .= rtn_sqllog($sql,$params);
				$stmt->execute();
				$sqllog .= rtn_sqllog("--execute():正常終了",[]);

			}
			
			//$count = $stmt->rowCount();
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