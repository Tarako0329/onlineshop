<?php
//log_writer2(basename(__FILE__)."",$sql,"lv3");
require "php_header.php";
register_shutdown_function('shutdown');

$msg = "";                          //ユーザー向け処理結果メッセージ
$alert_status = "alert-warning";    //bootstrap alert class
$reseve_status=false;               //処理結果セット済みフラグ。
$timeout=false;                     //セッション切れ。ログイン画面に飛ばすフラグ
$sqllog="";

//log_writer2("\$_POST",$_POST,"lv3");

$rtn = csrf_checker(["configration.php"],["P","C","S"]);
if($rtn !== true){
    $msg=$rtn;
    $alert_status = "alert-warning";
    $reseve_status = true;
}else{
    $rtn=check_session_userid_for_ajax($pdo_h);
    if($rtn===false){
        $reseve_status = true;
        $msg="長時間操作されていないため、自動ﾛｸﾞｱｳﾄしました。再度ログインし、もう一度xxxxxxして下さい。";
        $_SESSION["EMSG"]="長時間操作されていないため、自動ﾛｸﾞｱｳﾄしました。再度ログインし、もう一度xxxxxxして下さい。";
        $timeout=true;
    }else{
        $logfilename="sid_".$_SESSION['user_id'].".log";

        $DELsql = "delete from Users_online where uid = :uid ";

        $INSsql = "insert into Users_online (uid,yagou,name,shacho,jusho,tel,mail,mail_body,mail_body_auto,mail_body_sent,mail_body_paid,site_name,logo,site_pr,cc_mail)";
        $INSsql .= "values(:uid,:yagou,:name,:shacho,:jusho,:tel,:mail,:mail_body,:mail_body_auto,:mail_body_sent,:mail_body_paid,:site_name,:logo,:site_pr,:cc_mail)";

        $params["uid"] = $_SESSION["user_id"];
        $params["yagou"] = $_POST["yagou"];
        $params["name"] = $_POST["name"];
        $params["shacho"] = $_POST["shacho"];
        $params["jusho"] = $_POST["jusho"];
        $params["tel"] = $_POST["tel"];
        $params["mail"] = $_POST["mail"];
        $params["mail_body"] = $_POST["mail_body"];
        $params["mail_body_auto"] = $_POST["mail_body_auto"];
        $params["mail_body_paid"] = $_POST["mail_body_paid"];
        $params["mail_body_sent"] = $_POST["mail_body_sent"];
        $params["site_name"] = $_POST["site_name"];
        $params["logo"] = $_POST["logo"];
        $params["cc_mail"] = $_POST["cc_mail"];
        $params["site_pr"] = $_POST["site_pr"];

        try{
            $pdo_h->beginTransaction();
            $sqllog .= rtn_sqllog("START TRANSACTION",[]);
            //sqllogger("START TRANSACTION",[],basename(__FILE__),"ok");

            $stmt = $pdo_h->prepare( $DELsql );
            $stmt->bindValue("uid", $params["uid"], PDO::PARAM_INT);
            
            $sqllog .= rtn_sqllog($DELsql,$params);

            $status = $stmt->execute();
            $sqllog .= rtn_sqllog("--execute():正常終了",[]);
            
            $stmt = $pdo_h->prepare( $INSsql );
            $stmt->bindValue("uid", $params["uid"], PDO::PARAM_INT);
            $stmt->bindValue("yagou", $params["yagou"], PDO::PARAM_STR);
            $stmt->bindValue("name", $params["name"], PDO::PARAM_STR);
            $stmt->bindValue("shacho", $params["shacho"], PDO::PARAM_STR);
            $stmt->bindValue("jusho", $params["jusho"], PDO::PARAM_STR);
            $stmt->bindValue("tel", $params["tel"], PDO::PARAM_STR);
            $stmt->bindValue("mail", $params["mail"], PDO::PARAM_STR);
            $stmt->bindValue("mail_body", $params["mail_body"], PDO::PARAM_STR);
            $stmt->bindValue("mail_body_auto", $params["mail_body_auto"], PDO::PARAM_STR);
            $stmt->bindValue("mail_body_paid", $params["mail_body_paid"], PDO::PARAM_STR);
            $stmt->bindValue("mail_body_sent", $params["mail_body_sent"], PDO::PARAM_STR);
            $stmt->bindValue("site_name", $params["site_name"], PDO::PARAM_INT);
            $stmt->bindValue("logo", $params["logo"], PDO::PARAM_INT);
            $stmt->bindValue("cc_mail", $params["cc_mail"], PDO::PARAM_INT);
            $stmt->bindValue("site_pr", $params["site_pr"], PDO::PARAM_INT);
            
            $sqllog .= rtn_sqllog($INSsql,$params);

            $status = $stmt->execute();
            $sqllog .= rtn_sqllog("--execute():正常終了",[]);
            
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
  

?>