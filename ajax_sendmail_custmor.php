<?php
require "php_header.php";
register_shutdown_function('shutdown_ajax',basename(__FILE__));

$msg = "";                          //ユーザー向け処理結果メッセージ
$alert_status = "alert-warning";    //bootstrap alert class
$reseve_status=false;               //処理結果セット済みフラグ。
$timeout=false;                     //セッション切れ。ログイン画面に飛ばすフラグ
$sqllog="";
log_writer2("\$_POST",$_POST,"lv3");

$rtn = csrf_checker(["order_management.php","index.php"],["P","C","S"]);
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

        try{
            $head = "お客様よりお問い合わせがありました。\r\n".ROOT_URL."\r\nより回答をお願いします\r\n\r\nお客様ヘ以下の内容で受付メールを自動返信しました。\r\n\r\n";

            if(!empty($_POST["lineid"])){
                $bcc = "";
                send_line($_POST["lineid"],$head.$_POST["subject"]."\r\n".$_POST["mailbody"]);
            }else{
                $bcc = $_POST["mailtoBCC"];
            }
            $rtn = send_mail($_POST["mailto"],$_POST["subject"],$_POST["mailbody"],TITLE,$bcc);//客向け受付メール

            $rtn = send_mail($bcc,$_POST["subject"],$head.$_POST["mailbody"],TITLE,$bcc);//出店者へお知らせメール


            $pdo_h->beginTransaction();
            $sqllog .= rtn_sqllog("START TRANSACTION",[]);

            $sql = "insert into online_q_and_a(shop_id,customer,name,shouhinNM,sts,body) values(:shop_id,:customer,:name,:shouhinNM,:sts,:body)";
            $stmt = $pdo_h->prepare( $sql );

            $params["shop_id"] = $_POST["shop_id"];
            $params["customer"] = $_POST["mailto"];
            $params["name"] = $_POST["qa_name"];
            $params["shouhinNM"] = $_POST["qa_head"];
            $params["sts"] = $_POST["sts"];
            $params["body"] = $_POST["qa_text"];

            $stmt->bindValue("shop_id", $params["shop_id"], PDO::PARAM_STR);
            $stmt->bindValue("customer", $params["customer"], PDO::PARAM_STR);
            $stmt->bindValue("name", $params["name"], PDO::PARAM_STR);
            $stmt->bindValue("shouhinNM", $params["shouhinNM"], PDO::PARAM_STR);
            $stmt->bindValue("sts", $params["sts"], PDO::PARAM_STR);
            $stmt->bindValue("body", $params["body"], PDO::PARAM_STR);
            
            $sqllog .= rtn_sqllog($sql,$params);

            $status = $stmt->execute();
            $sqllog .= rtn_sqllog("--execute():正常終了",[]);
            
            $pdo_h->commit();
            $sqllog .= rtn_sqllog("commit",[]);
            sqllogger($sqllog,0);


			$msg = "登録が完了しました。";
			$alert_status = "alert-success";
			$reseve_status=true;

        }catch(Exception $e){
            //$pdo_h->rollBack();
            //$sqllog .= rtn_sqllog("rollBack",[]);
            //sqllogger($sqllog,$e);
            $msg = "システムエラーによる更新失敗。管理者へ通知しました。";
            $alert_status = "alert-danger";
            $reseve_status=true;
            log_writer2("\$e",$e,"lv0");
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

