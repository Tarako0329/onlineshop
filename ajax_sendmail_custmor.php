<?php
require "php_header.php";
register_shutdown_function('shutdown_ajax',basename(__FILE__));

$msg = "";                          //ユーザー向け処理結果メッセージ
$alert_status = "alert-warning";    //bootstrap alert class
$reseve_status=false;               //処理結果セット済みフラグ。
$timeout=false;                     //セッション切れ。ログイン画面に飛ばすフラグ
$sqllog="";
log_writer2("\$_POST",$_POST,"lv3");

$rtn = csrf_checker(["order_management.php","index.php","Q_and_A.php",""],["P","C","S"]);
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
            $pdo_h->beginTransaction();
            $sqllog .= rtn_sqllog("START TRANSACTION",[]);

            if(empty($_SESSION["askNO"])){
                $stmt = $pdo_h->prepare("select max(askNO) + 1 as nextNO from online_q_and_a");
                $stmt->execute();
                if($stmt->rowCount() == 0){
                    $askNO = 1;
                }else{
                    $tmp = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    $askNO = $tmp[0]["nextNO"];
                }
            }else{
                $askNO = rot13decrypt2($_SESSION["askNO"]);
            }

            if($_POST["sts"]==="session"){
                $sts = rot13decrypt2($_SESSION["sts"]);
            }else{
                $sts = $_POST["sts"];
            }

            {//db登録
                $sql = "insert into online_q_and_a(shop_id,askNO,customer,name,shouhinNM,sts,body) values(:shop_id,:askNO,:customer,:name,:shouhinNM,:sts,:body)";
                $stmt = $pdo_h->prepare( $sql );

                $params["shop_id"] = $_POST["shop_id"];
                $params["askNO"] = $askNO;
                $params["customer"] = $_POST["mailto"];
                $params["name"] = $_POST["qa_name"];
                $params["shouhinNM"] = $_POST["qa_head"];
                $params["sts"] = $sts;
                $params["body"] = $_POST["qa_text"];

                $stmt->bindValue("shop_id", $params["shop_id"], PDO::PARAM_STR);
                $stmt->bindValue("askNO", $params["askNO"], PDO::PARAM_STR);
                $stmt->bindValue("customer", $params["customer"], PDO::PARAM_STR);
                $stmt->bindValue("name", $params["name"], PDO::PARAM_STR);
                $stmt->bindValue("shouhinNM", $params["shouhinNM"], PDO::PARAM_STR);
                $stmt->bindValue("sts", $params["sts"], PDO::PARAM_STR);
                $stmt->bindValue("body", $params["body"], PDO::PARAM_STR);
                
                $sqllog .= rtn_sqllog($sql,$params);

                $status = $stmt->execute();
                $sqllog .= rtn_sqllog("--execute():正常終了",[]);
                
                /*メール送信が正常終了したらコミットする
                    $pdo_h->commit();
                    $sqllog .= rtn_sqllog("commit",[]);
                    sqllogger($sqllog,0);
                */
            }

            $Q_URL = ROOT_URL."Q_and_A.php?askNO=".rot13encrypt2($askNO)."&QA=".rot13encrypt2("Q");
            $A_URL = ROOT_URL."Q_and_A.php?askNO=".rot13encrypt2($askNO)."&QA=".rot13encrypt2("A");
            $rtn="success";
            if($sts==="Q"){
                if(empty($_SESSION["askNO"])){//新規問い合わせ
                    //新規問い合わせは受付メールを客にも送る
                    $head = "お客様よりお問い合わせがありました。\r\n".$A_URL."\r\nより回答をお願いします\r\n\r\nお客様ヘ以下の内容で受付メールを自動返信しました。\r\n\r\n";
                    if(!empty($_POST["lineid"]) && $_POST["lineid"] <> "null"){
                        $bcc = "";
                        send_line($_POST["lineid"],$head.$_POST["subject"]."\r\n".$_POST["mailbody"]);//出店者へお知らせLINE
                    }else{
                        $bcc = $_POST["mailtoBCC"];
                        $rtn = send_mail($bcc,$_POST["subject"],$head.$_POST["mailbody"],TITLE,$bcc);//出店者へお知らせメール
                    }
                    if($rtn==="success"){
                        $rtn = send_mail($_POST["mailto"],$_POST["subject"],$_POST["mailbody"],TITLE,"");//客向け受付メール
                    }else{
                        //出店者への通知メールが失敗した場合は受付メールを送らない(rollback)
                    }
                }else{//継続問合せ
                    //その後のやり取りはトーク風画面なので回答通知を出店者のみに送る
                    $head = "お客様より返信がありました。\r\n".$A_URL."\r\nより回答をお願いします\r\n\r\n";
                    if(!empty($_POST["lineid"])){
                        $bcc = "";
                        send_line($_POST["lineid"],$head.$_POST["subject"]."\r\n".$_POST["mailbody"]);//出店者へお知らせLINE
                    }else{
                        $bcc = $_POST["mailtoBCC"];
                        $rtn = send_mail($bcc,$_POST["subject"],$head.$_POST["mailbody"],TITLE,$bcc);//出店者へお知らせメール
                    }
                }
            }else if($sts==="A"){
                $head = "出店者より回答がありました。追加でご確認したいことがございましたら\r\n".$Q_URL."\r\nよりメッセージを入力して下さい。\r\n\r\n";
                $rtn = send_mail($_POST["mailto"],$_POST["subject"],$head.$_POST["mailbody"],TITLE,"");//客向け回答メール
            }else{
                exit();//想定外
            }

            if($rtn==="success"){
                $msg = "送信完了";
                $alert_status = "alert-success";

                $pdo_h->commit();
                $sqllog .= rtn_sqllog("commit",[]);
                sqllogger($sqllog,0);
            }else{
                $msg = "送信　失敗";
                $alert_status = "alert-warning";
    
                $pdo_h->rollBack();
                $sqllog .= rtn_sqllog("rollBack",[]);
                sqllogger($sqllog,$e);
            }

			//$msg = "登録が完了しました。";
			//$alert_status = "alert-success";
			$reseve_status=true;

        }catch(Exception $e){
            $pdo_h->rollBack();
            $sqllog .= rtn_sqllog("rollBack",[]);
            sqllogger($sqllog,$e);

            $msg = "システムエラーによる更新失敗。管理者へ通知しました。";
            $alert_status = "alert-danger";
            $reseve_status=true;
            log_writer2("\$e",$e,"lv0");
        }
    }
}
//$_SESSION["askNO"]="";
//$_SESSION["sts"]="";
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

