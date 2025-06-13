<?php
require "php_header.php";
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

//log_writer2("\$_POST",$_POST,"lv3");

$rtn = csrf_checker(["configration.php"],["P","C","S"]);
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
        $logfilename="sid_".$_SESSION['user_id'].".log";

        $DELsql = "delete from Users_online where uid = :uid ";

        $INSsql = "insert into Users_online (uid,yagou,name,shacho,jusho,tel,mail,mail_body,mail_body_auto,mail_body_sent,mail_body_paid,mail_body_cancel,site_name,logo,site_pr,cc_mail,line_id,fb_id,x_id,chk_recept,chk_sent,chk_paid,lock_sts,cancel_rule,invoice,headcolor,bodycolor,h_font_color)";
        $INSsql .= "values(:uid,:yagou,:name,:shacho,:jusho,:tel,:mail,:mail_body,:mail_body_auto,:mail_body_sent,:mail_body_paid,:mail_body_cancel,:site_name,:logo,:site_pr,:cc_mail,:line_id,:fb_id,:x_id,:chk_recept,:chk_sent,:chk_paid,:lock_sts,:cancel_rule,:invoice,:headcolor,:bodycolor,:h_font_color)";

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
        $params["mail_body_cancel"] = $_POST["mail_body_cancel"];
        //$params["site_name"] = $_POST["site_name"];
        $params["site_name"] = $_POST["yagou"]; //とりあえず今は屋号を転記
        $params["logo"] = !empty($_POST["logo"])?$_POST["logo"]:"";
        $params["cc_mail"] = $_POST["cc_mail"];
        $params["line_id"] = !empty($_POST["line_id"])?$_POST["line_id"]:NULL;
        $params["fb_id"] = $_POST["fb_id"];
        $params["x_id"] = $_POST["x_id"];
        $params["site_pr"] = $_POST["site_pr"];
        $params["chk_recept"] = $_POST["chk_recept"];
        $params["chk_sent"] = $_POST["chk_sent"];
        $params["chk_paid"] = $_POST["chk_paid"];
        $params["lock_sts"] = $_POST["lock_sts"];
        $params["cancel_rule"] = $_POST["cancel_rule"];
        $params["headcolor"] = $_POST["headcolor"];
        $params["bodycolor"] = $_POST["bodycolor"];
        $params["h_font_color"] = $_POST["h_font_color"];
        $params["invoice"] = $_POST["invoice"];
        $ask_json = <<<EOM
            mailbodys:{
                自動返信:"$params[mail_body_auto]",
                受付確認:"$params[mail_body]",
                支払確認:"$params[mail_body_paid]",
                発送連絡:"$params[mail_body_sent]",
                キャンセル受付:"$params[mail_body_cancel]",
            }
            <>で囲まれている部分はプログラムで他の単語に置き換えられる部分です。
            各値の文章に誤字脱字、タイプミス、正しくない日本語がないか、確認してください。
            確認結果はシンプルな文章にして、JSONで返してください。
            問題ない項目は"OK"を返してください。
            下記のJSONスキーマに厳密に従ってJSONを出力してください。
        EOM;

        $mail_check_schema = [
            'type' => 'object',
            'properties' => [
                'check_results' => [
                    'type' => 'object',
                    'properties' => [
                        '自動返信' => ['type' => 'string', 'description' => '自動返信メールのチェック結果'],
                        '受付確認' => ['type' => 'string', 'description' => '受付確認メールのチェック結果'],
                        '支払確認' => ['type' => 'string', 'description' => '支払確認メールのチェック結果'],
                        '発送連絡' => ['type' => 'string', 'description' => '発送連絡メールのチェック結果'],
                        'キャンセル受付' => ['type' => 'string', 'description' => 'キャンセル受付メールのチェック結果'],
                    ],
                    'required' => ['自動返信', '受付確認', '支払確認', '発送連絡', 'キャンセル受付']
                ]
            ],
            'required' => ['check_results']
        ];

        $value_check_result = null; // Initialize        
        if(EXEC_MODE<>"Local"){
            //$value_check = gemini_api($ask_json,"json");
            $value_check_response = gemini_api($ask_json, "json", $mail_check_schema);
            if (!empty($value_check_response['emsg'])) {
                // Handle error, e.g., log it, display a message to user or set a default
                log_writer2("Gemini API error or JSON decode error in ajax_delins_userMSonline: ", $value_check_response['emsg'], "lv1");
                $msg .= "メール本文のAIチェック中にエラーが発生しました: " . $value_check_response['emsg'];
                // $value_check_result will remain null or you can set a default error structure
            } else {
                $value_check_result = $value_check_response["result"];
            }
        } else {
            // Local mode, set a dummy response if needed for testing
            $value_check_result = [
                "check_results" => [
                    "自動返信" => "OK (Local)",
                    "受付確認" => "OK (Local)",
                    "支払確認" => "OK (Local)",
                    "発送連絡" => "OK (Local)",
                    "キャンセル受付" => "OK (Local)",
                ]
            ];        }
        
        //log_writer2("\$value_check",$value_check["result"],"lv3");
        
        //$value_check["result"] = str_replace("\n","",$value_check["result"]);
        //$value_check["result"] = str_replace("\r","",$value_check["result"]);
        //$value_check["result"] = str_replace(" ","",$value_check["result"]);
        
        //log_writer2("\$value_check",$value_check["result"],"lv3");

        try{
            if (is_file($params["logo"])) {//fileの移動
                if ( rename($params["logo"] , str_replace("temp/","",$params["logo"]))) {
                    $params["logo"] = str_replace("temp/","",$params["logo"] );
                } else {
                    $msg = "ファイル移動失敗";
                }
            } else {
                $msg = "ファイル保存失敗 or ファイル未設定・NOFILE";
            }

            $pdo_h->beginTransaction();
            $sqllog .= rtn_sqllog("START TRANSACTION",[]);

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
            $stmt->bindValue("mail_body_cancel", $params["mail_body_cancel"], PDO::PARAM_STR);
            $stmt->bindValue("site_name", $params["site_name"], PDO::PARAM_STR);
            $stmt->bindValue("logo", $params["logo"], PDO::PARAM_STR);
            $stmt->bindValue("cc_mail", $params["cc_mail"], PDO::PARAM_STR);
            $stmt->bindValue("line_id", $params["line_id"], PDO::PARAM_STR);
            $stmt->bindValue("fb_id", $params["fb_id"], PDO::PARAM_STR);
            $stmt->bindValue("x_id", $params["x_id"], PDO::PARAM_STR);
            $stmt->bindValue("site_pr", $params["site_pr"], PDO::PARAM_STR);
            $stmt->bindValue("chk_recept", $params["chk_recept"], PDO::PARAM_INT);
            $stmt->bindValue("chk_sent", $params["chk_sent"], PDO::PARAM_INT);
            $stmt->bindValue("chk_paid", $params["chk_paid"], PDO::PARAM_INT);
            $stmt->bindValue("lock_sts", $params["lock_sts"], PDO::PARAM_STR);
            $stmt->bindValue("cancel_rule", $params["cancel_rule"], PDO::PARAM_STR);
            $stmt->bindValue("headcolor", $params["headcolor"], PDO::PARAM_STR);
            $stmt->bindValue("bodycolor", $params["bodycolor"], PDO::PARAM_STR);
            $stmt->bindValue("h_font_color", $params["h_font_color"], PDO::PARAM_STR);
            $stmt->bindValue("invoice", $params["invoice"], PDO::PARAM_STR);
            
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
    //,"value_check" => $value_check["result"]
    ,"value_check" => $value_check_result
);
header('Content-type: application/json');
echo json_encode($return_sts, JSON_UNESCAPED_UNICODE);

exit();

?>