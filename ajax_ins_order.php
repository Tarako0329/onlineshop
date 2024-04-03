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

//log_writer2("\$_POST",$_POST,"lv3");

$rtn = csrf_checker(["index.php",""],["P","C","S"]);
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

        $stmt = $pdo_h->prepare("select * from Users_online where uid = :uid");
        //$stmt->bindValue("uid", $_SESSION["user_id"], PDO::PARAM_INT);
        $stmt->bindValue("uid", $_POST["order_shop_id"], PDO::PARAM_INT);
        $stmt->execute();
        $owner = $stmt->fetchAll(PDO::FETCH_ASSOC);

        //log_writer2("\$owner",$owner,"lv3");

        //更新モード(実行)
        $sqlstr_h = "insert into juchuu_head(uid,orderNO,name,yubin,jusho,tel,mail,bikou,st_name,st_yubin,st_jusho,st_tel) values(:uid,:orderNO,:name,:yubin,:jusho,:tel,:mail,:bikou,:st_name,:st_yubin,:st_jusho,:st_tel)";
        $sqlstr_m = "insert into juchuu_meisai(orderNO,shouhinCD,shouhinNM,su,tanka,goukeitanka,zeikbn,bikou) values(:orderNO,:shouhinCD,:shouhinNM,:su,:tanka,:goukeitanka,:zeikbn,:bikou)";

        //$params["uid"] = $_SESSION["user_id"];
        $params["uid"] = $_POST["order_shop_id"];
        $params["name"] = $_POST["name"];
        $params["yubin"] = $_POST["yubin"];
        $params["jusho"] = $_POST["jusho"];
        $params["tel"] = is_null($_POST["tel"])?"":$_POST["tel"];
        $params["mail"] = is_null($_POST["mail"])?"":$_POST["mail"];
        $params["bikou"] = is_null($_POST["bikou"])?"":$_POST["bikou"];
        $params["st_name"] = $_POST["st_name"];
        $params["st_yubin"] = $_POST["st_yubin"];
        $params["st_jusho"] = $_POST["st_jusho"];
        $params["st_tel"] = $_POST["st_tel"];

        //通知メール用
        $head_bikou = $params["bikou"];

        try{
            $pdo_h->beginTransaction();
            $sqllog .= rtn_sqllog("START TRANSACTION",[]);

            //受注ヘッダ登録

            //オーダー番号作成
            $stmt = $pdo_h->prepare("select orderNO from juchuu_head where orderNO = :orderNO FOR UPDATE");
            while(true){
                $params["orderNO"] = substr("0000000".((string)rand(0,99999999)),-8);
                $stmt->bindValue("orderNO", $params["orderNO"], PDO::PARAM_STR);
                $stmt->execute();
                $row = $stmt->fetchAll(PDO::FETCH_ASSOC);
                if(empty($row[0]["orderNO"])){
                    break;
                }
            }

            $stmt = $pdo_h->prepare( $sqlstr_h );
            //bind処理
            $stmt->bindValue("uid", $params["uid"], PDO::PARAM_INT);
            $stmt->bindValue("orderNO", $params["orderNO"], PDO::PARAM_INT);
            $stmt->bindValue("name", $params["name"], PDO::PARAM_STR);
            $stmt->bindValue("yubin", $params["yubin"], PDO::PARAM_STR);
            $stmt->bindValue("jusho", $params["jusho"], PDO::PARAM_STR);
            $stmt->bindValue("tel", $params["tel"], PDO::PARAM_STR);
            $stmt->bindValue("mail", $params["mail"], PDO::PARAM_STR);
            $stmt->bindValue("bikou", $params["bikou"], PDO::PARAM_STR);
            
            $stmt->bindValue("st_name", $params["st_name"], PDO::PARAM_STR);
            $stmt->bindValue("st_yubin", $params["st_yubin"], PDO::PARAM_STR);
            $stmt->bindValue("st_jusho", $params["st_jusho"], PDO::PARAM_STR);
            $stmt->bindValue("st_tel", $params["st_tel"], PDO::PARAM_STR);

            $sqllog .= rtn_sqllog($sqlstr_h,$params);

            $status = $stmt->execute();
            $sqllog .= rtn_sqllog("--execute():正常終了",[]);
            
            //明細登録
            $orderlist="【ご注文内容】\r\n";
            foreach($_POST["meisai"] as $row){
                //log_writer2("\$row",$row,"lv3");
                
                $params["shouhinCD"] = $row["shouhinCD"];
                $params["shouhinNM"] = $row["shouhinNM"];
                $params["su"] = $row["su"];
                $params["tanka"] = $row["tanka"];
                $params["goukeitanka"] = $row["goukeitanka"];
                $params["zeikbn"] = $row["zeikbn"];
                $params["bikou"] = $row["bikou"];

                $stmt = $pdo_h->prepare( $sqlstr_m );
                $stmt->bindValue("orderNO", $params["orderNO"], PDO::PARAM_INT);
                $stmt->bindValue("shouhinCD", $params["shouhinCD"], PDO::PARAM_INT);
                $stmt->bindValue("shouhinNM", $params["shouhinNM"], PDO::PARAM_STR);
                $stmt->bindValue("su", $params["su"], PDO::PARAM_INT);
                $stmt->bindValue("tanka", $params["tanka"], PDO::PARAM_INT);
                $stmt->bindValue("goukeitanka", $params["goukeitanka"], PDO::PARAM_INT);
                $stmt->bindValue("zeikbn", $params["zeikbn"], PDO::PARAM_INT);
                $stmt->bindValue("bikou", $params["bikou"], PDO::PARAM_STR);
                $sqllog .= rtn_sqllog($sqlstr_m,$params);

                $status = $stmt->execute();
                $sqllog .= rtn_sqllog("--execute():正常終了",[]);
                
                $orderlist .= "◆".$params["shouhinNM"]."\r\n".$row["short_info"]."\r\n価格( ".return_num_disp($params["tanka"])." 円) x ".$params["su"]."(コ) = 合計 ".return_num_disp($params["goukeitanka"])." 円(税抜)\n\r備考：".$params["bikou"]."\r\n\r\n";
            }

            //消費税明細の登録
            $sqlstr = "insert into juchuu_meisai select orderNO,JM.zeikbn as shouhinCD,ZMS.hyoujimei,0 as su,0 as tanka,0 as goukeitanka,FLOOR(sum(goukeitanka) * ZMS.zeiritu / 100) as zei ,JM.zeikbn,'-' as bikou from juchuu_meisai JM inner join ZeiMS ZMS on JM.zeikbn = ZMS.zeiKBN where orderNO = :orderNO group by orderNO,ZMS.hyoujimei,JM.zeikbn,'-' having zei <> 0";
            $stmt = $pdo_h->prepare($sqlstr);
            $stmt->bindValue("orderNO", $params["orderNO"], PDO::PARAM_INT);
            $sqllog .= rtn_sqllog($sqlstr,$params);
            $stmt->execute();
            $sqllog .= rtn_sqllog("--execute():正常終了",[]);

            //メールの作成
            {
                $stmt = $pdo_h->prepare("select orderNO,CAST(sum(goukeitanka) as char) + 0 as soutanka,CAST(sum(zei) as char) + 0 as souzei,CAST(sum(goukeitanka + zei) as char) + 0 as zeikomisou from juchuu_meisai where orderNO = :orderNO group by orderNO");
                $stmt->bindValue("orderNO", $params["orderNO"], PDO::PARAM_INT);
                $stmt->execute();
                $orderlist2 = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                //log_writer2("\$owner[mail]",$owner[0]["mail"],"lv3");
                //log_writer2("\$params[mail]",$params["mail"],"lv3");
                $orderNO = $params['orderNO'];
                $name = $params['name'];
                $yubin = $params['yubin'];
                $jusho = $params['jusho'];
                $tel = $params['tel'];
                $mail = $params['mail'];
                //$bikou = $params["bikou"];    $head_bikouを使う
                $st_name = $params['st_name'];
                $st_yubin = $params['st_yubin'];
                $st_jusho = $params['st_jusho'];
                $st_tel = $params['st_tel'];
                $goukeitanka = return_num_disp($orderlist2[0]["soutanka"]);
                $goukeizei = return_num_disp($orderlist2[0]["souzei"]);
                $sougaku = return_num_disp($orderlist2[0]["zeikomisou"]);

                //ショップオーナー向けメール
                $body = <<< "EOM"
                $name 様よりご注文いただきました。
                
                【ご注文内容】
                $orderlist
                
                ご注文総額：$sougaku  内税($goukeizei)
                
                【ご注文主】
                $name
                $yubin
                $jusho
                $tel
                $mail
                オーダー備考：
                $head_bikou
                
                【お届け先】(表示がない場合は同上)
                $st_name
                $st_yubin
                $st_jusho
                $st_tel
                EOM;
                
                if(!empty($owner[0]["line_id"]) && EXEC_MODE <> "Local"){//LINEで通知
                    $url = ROOT_URL.'line_push_msg.php';

                    $data = array(
                        'LINE_USER_ID' => $owner[0]["line_id"],
                        'MSG' => "オーダー受注通知[No:".$orderNO."]\r\n".$body,
                    );
                
                    $context = array(
                        'http' => array(
                            'method'  => 'POST',
                            'header'  => implode("\r\n", array('Content-Type: application/x-www-form-urlencoded',)),
                            'content' => http_build_query($data)
                        )
                    );
                
                    $html = file_get_contents($url, false, stream_context_create($context));
                
                    //var_dump($http_response_header);
                
                    //echo $html;
                }else if(!empty($owner[0]["mail"])){
                    $rtn = send_mail($owner[0]["mail"],"オーダー受注通知[No:".$orderNO."]",$body,TITLE." onLineShop",$owner[0]["mail"]);
                }

                //お客様向けメール
                {
                $title = TITLE;

                $body = $owner[0]["mail_body_auto"];

                $body = str_replace("<購入者名>",$name,$body);
                $body = str_replace("<注文内容>",$orderlist."ご注文総額：".$sougaku."  内税(".$goukeizei.")",$body);
                $body = str_replace("<送料込の注文内容>",$orderlist,$body);
                //$body = str_replace("<購入者情報>","【ご注文主】\r\nお名前：".$name."\r\n郵便番号：".$yubin."\r\n住所：".$jusho."\r\nTEL：".$tel."\r\nMAIL：".$mail."\r\nオーダー備考：\r\n".$bikou.'',$body);
                $body = str_replace("<購入者情報>","【ご注文主】\r\nお名前：".$name."\r\n郵便番号：".$yubin."\r\n住所：".$jusho."\r\nTEL：".$tel."\r\nMAIL：".$mail."\r\nオーダー備考：\r\n".$head_bikou.'',$body);
                $body = str_replace("<届け先情報>","【お届け先】\r\nお名前：".$st_name."\r\n郵便番号：".$st_yubin."\r\n送付先住所：".$st_jusho."\r\nTEL：".$st_tel.'',$body);
                $body = str_replace("<自社名>",$owner[0]["yagou"],$body);
                $body = str_replace("<自社住所>",$owner[0]["jusho"],$body);
                $body = str_replace("<問合せ受付TEL>",$owner[0]["tel"],$body);
                $body = str_replace("<問合せ受付MAIL>",$owner[0]["mail"],$body);
                $body = str_replace("<問合担当者>",$owner[0]["name"],$body);
                $body = str_replace("<代表者>",$owner[0]["shacho"],$body);
          
                $rtn = send_mail($params["mail"],"注文内容ご確認（自動配信メール）[No:".$orderNO."]",$body,TITLE." onLineShop",$owner[0]["CC_mail"]);
                }
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
    ,"orderNO" => $params["orderNO"]
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
            send_mail(SYSTEM_NOTICE_MAIL,"【".TITLE." - WARNING】".basename(__FILE__)."でシステム停止",$emsg);
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