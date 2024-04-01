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
        $DELsql = "delete from shouhinMS_online where uid = :uid and shouhinCD = :shouhinCD";
        $DELsql2 = "delete from shouhinMS_online_pic where uid = :uid and shouhinCD = :shouhinCD";

        $INSsql = "insert into shouhinMS_online(uid,shouhinCD,shouhinNM,status,short_info,infomation,customer_bikou,tanka,zeikbn,shouhizei) ";
        $INSsql .= "values(:uid,:shouhinCD,:shouhinNM,:status,:short_info,:infomation,:customer_bikou,:tanka,:zeikbn,:shouhizei)";
        $INSsql2 = "insert into shouhinMS_online_pic(uid,shouhinCD,sort,pic) values(:uid,:shouhinCD,:sort,:pic)";

        $params["uid"] = $_SESSION["user_id"];
        $params["shouhinCD"] = $_POST["shouhinCD"];
        $params["shouhinNM"] = $_POST["shouhinNM"];
        $params["status"] = $_POST["status"];
        $params["short_info"] = $_POST["short_info"];
        $params["infomation"] = $_POST["infomation"];
        $params["customer_bikou"] = $_POST["customer_bikou"];
        $params["tanka"] = $_POST["tanka"];
        $params["zeikbn"] = $_POST["zeikbn"];
        $params["shouhizei"] = $_POST["shouhizei"];

        try{
            $pdo_h->beginTransaction();
            $sqllog .= rtn_sqllog("START TRANSACTION",[]);
            //sqllogger("START TRANSACTION",[],basename(__FILE__),"ok");

            $stmt = $pdo_h->prepare( $DELsql );
            $stmt->bindValue("uid", $params["uid"], PDO::PARAM_INT);
            $stmt->bindValue("shouhinCD", $params["shouhinCD"], PDO::PARAM_STR);
            
            $sqllog .= rtn_sqllog($DELsql,$params);

            $status = $stmt->execute();
            $sqllog .= rtn_sqllog("--execute():正常終了",[]);
            
            $stmt = $pdo_h->prepare( $DELsql2 );
            $stmt->bindValue("uid", $params["uid"], PDO::PARAM_INT);
            $stmt->bindValue("shouhinCD", $params["shouhinCD"], PDO::PARAM_STR);
            
            $sqllog .= rtn_sqllog($DELsql2,$params);

            $status = $stmt->execute();
            $sqllog .= rtn_sqllog("--execute():正常終了",[]);
            
            $stmt = $pdo_h->prepare( $INSsql );
            $stmt->bindValue("uid", $params["uid"], PDO::PARAM_INT);
            $stmt->bindValue("shouhinCD", $params["shouhinCD"], PDO::PARAM_STR);
            $stmt->bindValue("shouhinNM", $params["shouhinNM"], PDO::PARAM_STR);
            $stmt->bindValue("status", $params["status"], PDO::PARAM_STR);
            $stmt->bindValue("short_info", $params["short_info"], PDO::PARAM_STR);
            $stmt->bindValue("infomation", $params["infomation"], PDO::PARAM_STR);
            $stmt->bindValue("customer_bikou", $params["customer_bikou"], PDO::PARAM_STR);
            $stmt->bindValue("tanka", $params["tanka"], PDO::PARAM_INT);
            $stmt->bindValue("zeikbn", $params["zeikbn"], PDO::PARAM_INT);
            $stmt->bindValue("shouhizei", $params["shouhizei"], PDO::PARAM_INT);
            
            $sqllog .= rtn_sqllog($INSsql,$params);

            $status = $stmt->execute();
            $sqllog .= rtn_sqllog("--execute():正常終了",[]);
            
            foreach($_POST["user_file_name"] as $row){
                //log_writer2("\$row[filename]",$row["filename"],"lv3");
                
                if (is_file($row["filename"])) {//fileの移動
                    if ( rename($row["filename"] , str_replace("temp/","",$row["filename"]))) {
                        $params["pic"] = str_replace("temp/","",$row["filename"] );
                        $params["sort"] = $row["sort"];
                    } else {
                        $msg = "ファイル移動失敗";
                    }
                } else {
                    $msg = "ファイル保存失敗・NOFILE";
                }

                $stmt = $pdo_h->prepare( $INSsql2 );
                $stmt->bindValue("uid", $params["uid"], PDO::PARAM_INT);
                $stmt->bindValue("shouhinCD", $params["shouhinCD"], PDO::PARAM_STR);
                $stmt->bindValue("sort", $params["sort"], PDO::PARAM_STR);
                $stmt->bindValue("pic", $params["pic"], PDO::PARAM_STR);
                $sqllog .= rtn_sqllog($INSsql2,$params);

                $status = $stmt->execute();
                $sqllog .= rtn_sqllog("--execute():正常終了",[]);
            }

			$pdo_h->commit();
			$sqllog .= rtn_sqllog("commit",[]);
			sqllogger($sqllog,0);
	
			$msg .= "登録が完了しました。";
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