<?php
//log_writer2(basename(__FILE__)."",$sql,"lv3");
require "php_header.php";
register_shutdown_function('shutdown');

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

$rtn = csrf_checker(["order_management.php",""],["P","C","S"]);
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
        //$logfilename="sid_".$_SESSION['user_id'].".log";
        /*
        $stmt = $pdo_h->prepare("select * from Users where uid = :uid");
        $stmt->bindValue("uid", $_SESSION["user_id"], PDO::PARAM_INT);
        $stmt->execute();
        $owner = $stmt->fetchAll(PDO::FETCH_ASSOC);
        */
        //log_writer2("\$owner",$owner,"lv3");

        //更新モード(実行)
        $colum = $_POST["colum"];   //更新対象項目名
        $value = $_POST["value"];
        $orderNO = $_POST["orderNO"];

        $sqlstr_h = "update juchuu_head set ".$colum." = :".$colum." where orderNO = :orderNO and uid like :uid";

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