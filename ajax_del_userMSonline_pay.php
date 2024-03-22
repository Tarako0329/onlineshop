<?php
//log_writer2(basename(__FILE__)."",$sql,"lv3");
require "php_header.php";
register_shutdown_function('shutdown');

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
    //$rtn=check_session_userid_for_ajax($pdo_h);
    if($rtn===false){
        $reseve_status = true;
        $msg="長時間操作されていないため、自動ﾛｸﾞｱｳﾄしました。再度ログインし、もう一度xxxxxxして下さい。";
        $_SESSION["EMSG"]="長時間操作されていないため、自動ﾛｸﾞｱｳﾄしました。再度ログインし、もう一度xxxxxxして下さい。";
        $timeout=true;
    }else{
        //更新モード(実行)
        $sqlstr_h = "delete from Users_online_payinfo where payname = :payname and uid like :uid";

        $params["payname"] = $_POST["payname"];
        $params["uid"] = $_SESSION["user_id"];

        try{
            $pdo_h->beginTransaction();
            $sqllog .= rtn_sqllog("START TRANSACTION",[]);

            $stmt = $pdo_h->prepare( $sqlstr_h );
            //bind処理
            $stmt->bindValue("payname", $params["payname"], PDO::PARAM_STR);
            $stmt->bindValue("uid", $params["uid"], PDO::PARAM_INT);

            $sqllog .= rtn_sqllog($sqlstr_h,$params);

            $status = $stmt->execute();
            $sqllog .= rtn_sqllog("--execute():正常終了",[]);
            
            //$count = $stmt->rowCount();

            if($_POST["types"]==="QR"){
                if(unlink($_POST["source"])){
                    $pdo_h->commit();
                    $sqllog .= rtn_sqllog("commit",[]);
                    $msg .= "ファイルを削除しました。";
                    $alert_status = "success";
            
                }else{
                    $pdo_h->rollBack();
                    $sqllog .= rtn_sqllog("rollBack",[]);
                    $msg .= "ファイル削除が失敗しました。";
                    $alert_status = "warning";
                }
            }else{
                $pdo_h->commit();
                $sqllog .= rtn_sqllog("commit",[]);
                sqllogger($sqllog,0);
                $msg .= "登録が完了しました。";
                $alert_status = "success";
            }
	
			$reseve_status=true;

        }catch(Exception $e){
            $pdo_h->rollBack();
            $sqllog .= rtn_sqllog("rollBack",[]);
            sqllogger($sqllog,$e);
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
            ,"status" => "danger"
            ,"csrf_create" => $token
            ,"timeout" => false
        );
        header('Content-type: application/json');
        echo json_encode($return_sts, JSON_UNESCAPED_UNICODE);
      }
  }
  

?>