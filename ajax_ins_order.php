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

$rtn = csrf_checker(["index.php",""],["P","C","S"]);
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

        $stmt = $pdo_h->prepare("select * from Users where uid = :uid");
        $stmt->bindValue("uid", $_SESSION["user_id"], PDO::PARAM_INT);
        $stmt->execute();
        $owner = $stmt->fetchAll(PDO::FETCH_ASSOC);

        //log_writer2("\$owner",$owner,"lv3");

        //更新モード(実行)
        $sqlstr_h = "insert into juchuu_head(uid,orderNO,name,yubin,jusho,tel,mail,bikou) values(:uid,:orderNO,:name,:yubin,:jusho,:tel,:mail,:bikou)";
        $sqlstr_m = "insert into juchuu_meisai(orderNO,shouhinCD,shouhinNM,su,tanka,goukeitanka,zeikbn,bikou) values(:orderNO,:shouhinCD,:shouhinNM,:su,:tanka,:goukeitanka,:zeikbn,:bikou)";

        $params["uid"] = $_SESSION["user_id"];
        //$params["orderNO"] = $_POST["orderNO"];
        $params["name"] = $_POST["name"];
        $params["yubin"] = $_POST["yubin"];
        $params["jusho"] = $_POST["jusho"];
        $params["tel"] = is_null($_POST["tel"])?"":$_POST["tel"];
        $params["mail"] = is_null($_POST["mail"])?"":$_POST["mail"];
        $params["bikou"] = is_null($_POST["bikou"])?"":$_POST["bikou"];

        try{
            $pdo_h->beginTransaction();
            $sqllog .= rtn_sqllog("START TRANSACTION",[]);

            //受注ヘッダ登録
            $stmt = $pdo_h->prepare("select max(orderNO) + 1 as new_orderNO from juchuu_head FOR UPDATE");
            $stmt->execute();
            //log_writer2("\$stmt",$stmt,"lv3");

            $row = $stmt->fetchAll(PDO::FETCH_ASSOC);
            //log_writer2("\$row",$row,"lv3");
            if(!empty($row[0]["new_orderNO"])){
                $params["orderNO"] = $row[0]["new_orderNO"];
            }else{
                $params["orderNO"] = 1;
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
            
            $sqllog .= rtn_sqllog($sqlstr_h,$params);

            $status = $stmt->execute();
            $sqllog .= rtn_sqllog("--execute():正常終了",[]);
            
            //明細登録
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
                
            }

            //消費税明細の登録
            $sqlstr = "insert into juchuu_meisai select orderNO,JM.zeikbn as shouhinCD,ZMS.hyoujimei,0 as su,0 as tanka,0 as goukeitanka,FLOOR(sum(goukeitanka) * ZMS.zeiritu / 100) as zei ,JM.zeikbn,'-' as bikou from juchuu_meisai JM inner join zeiMS ZMS on JM.zeikbn = ZMS.zeiKBN where orderNO = :orderNO group by orderNO,ZMS.hyoujimei,JM.zeikbn,'-' having zei <> 0";
            $stmt = $pdo_h->prepare($sqlstr);
            $stmt->bindValue("orderNO", $params["orderNO"], PDO::PARAM_INT);
            $sqllog .= rtn_sqllog($sqlstr,$params);
            $stmt->execute();
            $sqllog .= rtn_sqllog("--execute():正常終了",[]);

            $body = "test";
            log_writer2("\$owner[mail]",$owner[0]["mail"],"lv3");
            log_writer2("\$params[mail]",$params["mail"],"lv3");
            $rtn = send_mail($owner[0]["mail"],"オーダー受注通知",$body);
            $rtn = send_mail($params["mail"],"オーダー確認",$body);


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
  

?>