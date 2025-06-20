<?php
require "php_header.php";
register_shutdown_function('shutdown_ajax',basename(__FILE__));

$msg = "";                          //ユーザー向け処理結果メッセージ
$alert_status = "alert-warning";    //bootstrap alert class
$reseve_status=false;               //処理結果セット済みフラグ。
$timeout=false;                     //セッション切れ。ログイン画面に飛ばすフラグ
$sqllog="";
log_writer2("\$_POST",$_POST,"lv3");

$rtn = csrf_checker(["order_management.php","index.php","Q_and_A.php","order_rireki.php",""],["P","C","S"]);
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

        $CusMailAdd = $_POST["mailto"];
        try{
            //uidからusers_onlineの情報を取得
            $sql = "select * from Users_online where uid = :uid";
            $stmt = $pdo_h->prepare($sql);
            $stmt->bindValue("uid", $_POST["shop_id"], PDO::PARAM_STR);
            $stmt->execute();
            $user = $stmt->fetchAll(PDO::FETCH_ASSOC);
            //$_POST["mailtoBCC"] = $user[0]["mail"];
            $ShopMailAdd = $user[0]["mail"];
            $yagou = $user[0]["yagou"];
            //$_POST["lineid"] = (!empty($user[0]["line_id"])?$user[0]["line_id"]:"none");
            $lineID = (!empty($user[0]["line_id"])?$user[0]["line_id"]:"none");
            
            $pdo_h->beginTransaction();
            $sqllog .= rtn_sqllog("START TRANSACTION",[]);

            //QA管理画面から来た場合はSESSIONに値を持つ
            $firstQ = false;    //初回質問フラグ
            if($_POST["sts"]==="session"){
                $sts = rot13decrypt2($_SESSION["sts"]); //Q or A
                $askNO = rot13decrypt2($_SESSION["askNO"]);
            }else{
                $sts = $_POST["sts"];
                //問合せNoの取得（同一ユーザが同じ対象に問合せした場合に同じNoを利用する.shopID,返信先メアド,Subjectで判断）
                $stmt = $pdo_h->prepare("select IFNULL(askNO,'') as askNO from online_q_and_a where shop_id = :shop_id and customer = :customer and shouhinNM = :shouhinNM");
                $stmt->bindValue("shop_id", $_POST["shop_id"], PDO::PARAM_STR);
                $stmt->bindValue("customer", $CusMailAdd, PDO::PARAM_STR);
                $stmt->bindValue("shouhinNM", $_POST["qa_head"], PDO::PARAM_STR);
                $stmt->execute();
                $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
                if($stmt->rowCount() > 0){
                    $askNO = $rows[0]["askNO"];
                }else{
                    //初回質問。問合せ番号を新規発行
                    $firstQ = true;
                    $stmt = $pdo_h->prepare("select max(askNO) + 1 as nextNO from online_q_and_a");
                    $stmt->execute();
                    if($stmt->rowCount() == 0){
                        $askNO = 1;
                    }else{
                        $tmp = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        $askNO = $tmp[0]["nextNO"];
                    }
                }
            }

            {//db登録
                $sql = "insert into online_q_and_a(shop_id,askNO,customer,name,shouhinNM,sts,body) values(:shop_id,:askNO,:customer,:name,:shouhinNM,:sts,:body)";
                $stmt = $pdo_h->prepare( $sql );

                $params["shop_id"] = $_POST["shop_id"];
                $params["askNO"] = $askNO;
                $params["customer"] = $CusMailAdd;
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
            }

            //CtoBで始まるやり取り
            $Q_URL = ROOT_URL."Q_and_A.php?askNO=".rot13encrypt2($askNO)."&QA=".rot13encrypt2("Q");
            $A_URL = ROOT_URL."Q_and_A.php?askNO=".rot13encrypt2($askNO)."&QA=".rot13encrypt2("A")."&key=".rot13encrypt2($_POST["shop_id"]);
            //BtoCで始まるやり取り
            $BQ_URL = ROOT_URL."Q_and_A.php?askNO=".rot13encrypt2($askNO)."&QA=".rot13encrypt2("BQ");
            $CA_URL = ROOT_URL."Q_and_A.php?askNO=".rot13encrypt2($askNO)."&QA=".rot13encrypt2("CA");
            $rtn="success";

            /*
            if($sts==="Q"){//通販画面QAのQuestion
                if($firstQ){//新規問い合わせ
                    //新規問い合わせは受付メールを客にも送る
                    $head = "お客様よりお問い合わせがありました。\r\n".$A_URL."\r\nより回答をお願いします\r\n\r\nお客様ヘ以下の内容で受付メールを自動返信しました。\r\n\r\n";
                    if($lineID <> "none"){
                        $ShopMailAdd = "";
                        send_line($lineID,$head.$_POST["subject"]."\r\n".$_POST["mailbody"]);//出店者へお知らせLINE
                    }else{
                        $rtn = send_mail($ShopMailAdd,$_POST["subject"],$head.$_POST["mailbody"],TITLE,"");//出店者へお知らせメール
                    }
                    log_writer2("to出店者 - send_mail() \$rtn","[".$ShopMailAdd."] send ".$rtn,"lv3");

                    if($rtn==="success"){
                        $rtn = send_mail($CusMailAdd,$_POST["subject"],$_POST["mailbody"],TITLE,"");//客向け受付メール
                        $_SESSION["subject"] = $_POST["subject"];
                        log_writer2("toお客さん - send_mail() \$rtn","[".$CusMailAdd."] send ".$rtn,"lv3");
                    }else{
                        //出店者への通知メールが失敗した場合は受付メールを送らない(rollback)
                    }
                }else{//継続問合せ
                    //その後のやり取りはトーク風画面なので回答通知を出店者のみに送る
                    $head = "お客様より返信がありました。\r\n".$A_URL."\r\nより回答をお願いします\r\n\r\n";
                    if($lineID <> "none"){
                        $ShopMailAdd = "";
                        send_line($lineID,$head.$_POST["subject"]."\r\n".$_POST["mailbody"]);//出店者へお知らせLINE
                    }else{
                        $rtn = send_mail($ShopMailAdd,$_POST["subject"],$head.$_POST["mailbody"],TITLE,"");//出店者へお知らせメール
                    }
                }
            }else if($sts==="A"){//通販画面QAのanswer
                $head = "出店者より回答がありました。追加でご確認したいことがございましたら\r\n".$Q_URL."\r\nよりメッセージを入力して下さい。\r\n\r\n";
                $rtn = send_mail($CusMailAdd,$_POST["subject"],$head.$_POST["mailbody"],TITLE,"");//客向け回答メール
            }else if($sts==="BQ"){//受注管理画面のお客様向け質問
                //出店者からお客様へ
                $rtn = send_mail($CusMailAdd,$_POST["subject"],$head.$_POST["mailbody"],TITLE,"");//客向け回答メール
                
            }else if($sts==="CA"){//受注管理画面のお客様からの返信
            }else{
                exit();//想定外
            }
            */
            $cc_address = "";//メールの送信者へのCC
            if($sts==="Q"){//通販画面QAのQuestion（客⇒店）
                $head = (($firstQ)
                        ?"お客様よりお問い合わせがありました。\r\n"
                        :"お客様より返信がありました。\r\n")
                        ."".$A_URL."\r\nより回答をお願いします\r\n\r\n====以下、お客様より====\r\n\r\n";

                if($lineID <> "none"){
                    $ShopMailAdd = "LINE";
                    send_line($lineID,$head."【".$_POST["subject"]."】\r\n".$_POST["mailbody"]);//出店者へお知らせLINE
                }else{
                    $rtn = send_mail($ShopMailAdd,$_POST["subject"],$head.$_POST["mailbody"],TITLE,"");//出店者へお知らせメール
                }
                log_writer2("to出店者 - send_mail() \$rtn","[".$ShopMailAdd."] send ".$rtn,"lv3");
                $cc_address = $CusMailAdd;
            }else if($sts==="A"){//通販画面QAのanswer（店⇒客）
                $head = $yagou." より回答がありました。追加でご確認したいことがございましたら\r\n".$Q_URL."\r\nよりメッセージを入力して下さい。\r\n\r\n";
                $rtn = send_mail($CusMailAdd,$_POST["subject"],$head.$_POST["mailbody"],TITLE,"");//客向け回答メール
                $cc_address = "shop";


            }else if($sts==="BQ"){//受注管理画面のお客様向け質問（店⇒客）
                $head = (($firstQ)
                        ?$yagou." よりお問い合わせがありました。\r\n"
                        :$yagou." より返信がありました。\r\n")
                        ."ご回答いただく場合は\r\n".$CA_URL."\r\nよりお願いします\r\n\r\n====以下、".$yagou." より====\r\n\r\n";
                $rtn = send_mail($CusMailAdd,$_POST["subject"],$head.$_POST["mailbody"],TITLE,"");//客向け回答メール
                $cc_address = "shop";
            }else if($sts==="CA"){//受注管理画面のお客様からの返信（客⇒店）
                $head = $_POST["qa_name"]." より回答がありました。追加でご確認したいことがございましたら\r\n".$BQ_URL."\r\nよりメッセージを入力して下さい。\r\n\r\n";
                if($lineID <> "none"){
                    $ShopMailAdd = "LINE";
                    send_line($lineID,$head."【".$_POST["subject"]."】\r\n".$_POST["mailbody"]);//出店者へお知らせLINE
                }else{
                    $rtn = send_mail($ShopMailAdd,$_POST["subject"],$head.$_POST["mailbody"],TITLE,"");//出店者へお知らせメール
                }
                log_writer2("to出店者 - send_mail() \$rtn","[".$ShopMailAdd."] send ".$rtn,"lv3");
                $cc_address = $CusMailAdd;
            }else{
                exit();//想定外
            }

            //送信者へのCC
            $head = "下記内容にてメールを送信しました。\r\n========\r\n";
            if($cc_address==="shop"){
                if($lineID <> "none"){
                    $ShopMailAdd = "LINE";
                    send_line($lineID,$head."【".$_POST["subject"]."】\r\n".$_POST["mailbody"]);//出店者へお知らせLINE
                }else{
                    $rtn = send_mail($ShopMailAdd,$_POST["subject"],$head.$_POST["mailbody"],TITLE,"");//出店者へお知らせメール
                }

            }else{
                $rtn = send_mail($cc_address,$_POST["subject"],$head.$_POST["mailbody"],TITLE,"");//客向け回答メール

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