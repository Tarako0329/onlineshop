<?php
// =========================================================
// オリジナルログ出力(error_log)
// =========================================================
function log_writer($pgname,$msg){
    $log = print_r($msg,true);
    file_put_contents("error_log","[".date("Y/m/d H:i:s")."] ORG_LOG from [".$_SERVER["PHP_SELF"]." -> ".$pgname."] => ".$log."\n",FILE_APPEND);
}
function log_writer2($pgname,$msg,$kankyo){
    //$kankyo:lv0=全環境+メール通知 lv1=全環境 lv2=本番以外 lv3=テスト・ローカル環境のみ
    
    if($kankyo==="lv0"){
        log_writer($pgname,$msg);
        $log = print_r($msg,true);
        
        send_mail(SYSTEM_NOTICE_MAIL,"【重要】".TITLE."でシステムエラー発生",$log,"","");
    }else if($kankyo==="lv1"){
        log_writer($pgname,$msg);
    }else if($kankyo==="lv2" && EXEC_MODE!=="Product"){
        log_writer($pgname,$msg);
    }else if($kankyo==="lv3" && (EXEC_MODE==="Test" || EXEC_MODE==="Local")){
        log_writer($pgname,$msg);
    }else{
        return;
    }
}
// =========================================================
// オリジナルログ出力(access_log)
// =========================================================
function aclog_writer($pgname,$msg){
    if(!($_SERVER["PHP_SELF"]==="/index.php" || $_SERVER["PHP_SELF"]==="/product.php")){
        //file_put_contents("access_log.txt","[".date("Y/m/d H:i:s")."] => 対象外:".$_SERVER["PHP_SELF"]."\n",FILE_APPEND);
        return 0;
    }
    $log = print_r($msg,true);
    file_put_contents("access_log.txt","[".date("Y/m/d H:i:s")."] => [".$_SERVER["PHP_SELF"]." -> ".$pgname."] => ".$log."\n",FILE_APPEND);
}

// =========================================================
// トークンを作成
// =========================================================
function get_token() {
    $TOKEN_LENGTH = 16;//16*2=32桁
    $bytes = openssl_random_pseudo_bytes($TOKEN_LENGTH);
    return bin2hex($bytes);
}
// =========================================================
// トークンの削除(指定のトークン もしくは　期限切れのトークンを一括削除)
// =========================================================
function delete_old_token($token, $pdo) {
    
    $date = new DateTime("- 7 days");
    
    $sql = "DELETE FROM AUTO_LOGIN WHERE TOKEN = ? or REGISTRATED_TIME < ?;";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(1, $token, PDO::PARAM_STR);
    $stmt->bindValue(2, $date->format('Y-m-d H:i:s'), PDO::PARAM_STR);
    $stmt->execute();
}

// =========================================================
// 自動ログイン処理
// =========================================================
function check_auto_login($cookie_token, $pdo) {
    if($_COOKIE["login_type"]==="normal"){//自動ログインしない
        return "一定の期間、操作が行われなかったため、自動ログオフしました。";
    }
    $sql = "SELECT * FROM AUTO_LOGIN WHERE TOKEN = ? AND REGISTRATED_TIME >= ?;";
    $date = new DateTime("- 7 days");   //2週間前の日付を取得
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(1, $cookie_token, PDO::PARAM_STR);
    $stmt->bindValue(2, $date->format('Y-m-d H:i:s'), PDO::PARAM_STR);
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($rows) == 1) {//自動ログイン成功
    	$_SESSION['user_id'] = $rows[0]['USER_ID'];
    	return true;
        //return "test msg::自動ログインの有効期限が切れてます。";
    } else {//自動ログイン失敗
    	setCookie("webrez_token", '', -1, "/", "", TRUE, TRUE); // secure, httponly
        setCookie("login_type", "normal", time()+999*999*999, "/", "", TRUE, TRUE); // secure, httponly
    	delete_old_token($cookie_token, $pdo);  //古くなったトークンを削除
        
    	return "自動ログインの有効期限が切れてます。";
    }
}

// =========================================================
// $_SESSION[user_id]の存在チェック
// =========================================================
function check_session_userid($pdo_h){
    if(substr(EXEC_MODE,0,5)==="Trial"){
        if(empty($_COOKIE["user_id"]) && empty($_SESSION["user_id"])){
            //セッション・クッキーのどちらにもIDが無い場合、ID発行を行う
            header("HTTP/1.1 301 Moved Permanently");
            header("Location: TrialDataCreate.php");
            exit(); 
        }else if((!empty($_SESSION["user_id"]) && empty($_COOKIE["user_id"])) || (!empty($_SESSION["user_id"]) && $_COOKIE["user_id"] != $_SESSION["user_id"])){
            //クッキーが空　もしくは　セッションありかつセッション＜＞クッキーの場合
            //クッキーにセッションの値をセットする
            setCookie("user_id", $_SESSION["user_id"], time()+60*60*24, "/", "", TRUE, TRUE);
        }else if(!empty($_COOKIE["user_id"]) && empty($_SESSION["user_id"])){
            //セッションが空の場合、クッキーからIDを取得する
            $_SESSION["user_id"]=$_COOKIE["user_id"];
        }
        
        //取得できたIDがDBに存在するか確認
        $sqlstr="select * from Users where uid=?";
        $stmt = $pdo_h->prepare($sqlstr);
        $stmt->bindValue(1, $_SESSION["user_id"], PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
        if (count($rows) == 0) {
            //IDは取得できたがDB側にデータが無い場合もID再発行
            header("HTTP/1.1 301 Moved Permanently");
            header("Location: TrialDataCreate.php");
            exit();
        }
        
    }else{
        //log_writer2("function.php[func:check_session_userid] $_SESSION values ",$_SESSION,"lv3");
        if(empty($_SESSION["user_id"])){
            //セッションのIDがクリアされた場合の再取得処理。
            if(empty($_COOKIE['webrez_token'])){
                log_writer2("func:check_session_userid","cookieのwebrez_tokenが存在してない。useridの取得手段がないのでログイン画面へ","lv3");
                redirect_to_login("セッションが切れてます。");
                exit();
            }
            $rtn=check_auto_login($_COOKIE['webrez_token'],$pdo_h);
            if($rtn!==true){
                redirect_to_login($rtn);
                exit();
            }
        }
        if(!($_SESSION["user_id"]<>"")){
            //念のための最終チェック
            redirect_to_login("ユーザーＩＤの再取得に失敗しました。[error:1]");
            exit();
        }
        //取得できたUIDがDBに存在するか確認
        $sqlstr="select * from Users where uid=?";
        $stmt = $pdo_h->prepare($sqlstr);
        $stmt->bindValue(1, $_SESSION["user_id"], PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
        if (count($rows) == 0) {
            //IDは取得できたがDB側にデータが無い場合もID再発行
            redirect_to_login("ユーザーＩＤの再取得に失敗しました。[error:2]");
            exit();
        }
    }
    return true;
}

// =========================================================
// $_SESSION[user_id]の存在チェック for ajax
// =========================================================
function check_session_userid_for_ajax($pdo_h){
    $rtn_val = true;
    //$_SESSION["user_id"]=null;
    if(empty($_SESSION["user_id"])){//セッションのIDがクリアされた場合の再取得処理。
        
        if(empty($_COOKIE['webrez_token'])){
            log_writer2("func:check_session_userid_for_ajax","cookie[webrez_token] is nothing、useridの取得手段なし。[login type:".$_COOKIE["login_type"]."]","lv3");
            $rtn_val = false;
        }else{
            $rtn=check_auto_login($_COOKIE['webrez_token'],$pdo_h);
            log_writer2("func:check_session_userid_for_ajax [check_auto_login return value]",$rtn,"lv3");
            if($rtn!==true){
                $rtn_val = false;
            }else{
                if(!($_SESSION["user_id"]<>"")){//念のための最終チェック
                    log_writer2("func:check_session_userid_for_ajax","ユーザーＩＤの再取得に失敗しました。[error:1]","lv3");
                    $rtn_val = false;
                }else{//取得できたUIDがDBに存在するか確認
                    $sqlstr="select * from Users where uid=?";
                    $stmt = $pdo_h->prepare($sqlstr);
                    $stmt->bindValue(1, $_SESSION["user_id"], PDO::PARAM_INT);
                    $stmt->execute();
                    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
                    if (count($rows) === 0) {
                        //IDは取得できたがDB側にデータが無い場合もID再発行
                        log_writer2("func:check_session_userid_for_ajax","ユーザーＩＤの再取得に失敗しました。[error:2]","lv3");
                        $rtn_val = false;
                    }else{
                        $rtn_val = true;
                    }
                }
            }
        }
    }

    return $rtn_val;
}

// =========================================================
// データ更新時のセキュリティ対応（セッション・クッキー・ポストのチェック）
//　一元化 (リファイラ[xxx.php,xxx.php],[S:session,C:cookie,G:get,P:post])
// =========================================================
function csrf_checker($from,$chkpoint){
    //リファイラーチェック
    $chkflg=false;
    foreach($from as $row){
        if(false !== strpos($_SERVER['HTTP_REFERER'],ROOT_URL.$row)){
            $chkflg=true;
            log_writer2("func:csrf_checker","HTTP_REFERER success \$_SERVER[".$_SERVER['HTTP_REFERER']."]","lv3");
            log_writer2("func:csrf_checker","HTTP_REFERER success ParamUrl[".ROOT_URL.$row."]","lv3");
            break;
        }
    }
    if($chkflg===true){
        $i=0;
        $csrf="";
        $checked="";
        foreach($chkpoint as $row){
            if($row==="S"){
                $csrf_ck = (!empty($_SESSION["csrf_token"])?$_SESSION["csrf_token"]:"\$_SESSION empty");
                $checked=$checked."S";
                unset($_SESSION['csrf_token']) ; // セッション側のトークンを削除し再利用を防止
            }else if($row==="C"){
                $csrf_ck = (!empty($_COOKIE["csrf_token"])?$_COOKIE["csrf_token"]:"\$_COOKIE empty");
                $checked=$checked."C";
                setCookie("csrf_token", '', -1, "/", "", TRUE, TRUE); // secure, httponly// クッキー側のトークンを削除し再利用を防止
            }if($row==="G"){
                $csrf_ck = (!empty($_GET["csrf_token"])?$_GET["csrf_token"]:"\$_GET empty");
                $checked=$checked."G";
            }if($row==="P"){
                $csrf_ck = (!empty($_POST["csrf_token"])?$_POST["csrf_token"]:"\$_POST empty");
                $checked=$checked."P";
            }
            if($i!==0){
                if($csrf !== $csrf_ck){
                    $chkflg=false;
                    log_writer2("func:csrf_checker","CSRF failed [".$checked."]","lv3");
                    log_writer2("func:csrf_checker","CSRF failed [".$csrf."]","lv3");
                    log_writer2("func:csrf_checker","CSRF failed [".$csrf_ck."]","lv3");
                    $chkflg = "セッションが正しくありません";
                    break;
                }else{
                    log_writer2("func:csrf_checker","CSRF success [".$checked."]","lv3");
                    log_writer2("func:csrf_checker","CSRF success [".$csrf."]","lv3");
                    log_writer2("func:csrf_checker","CSRF success [".$csrf_ck."]","lv3");
                }
            }
            $csrf=$csrf_ck;
            $i++;
        }
    }else{
        log_writer2("func:csrf_checker","HTTP_REFERER failed \$_SERVER[".$_SERVER['HTTP_REFERER']."]","lv3");
        log_writer2("func:csrf_checker","HTTP_REFERER failed ParamUrl[".ROOT_URL.$row."]","lv3");
        $chkflg = "アクセス元が不正です";
    }
    
    return $chkflg;
}


function csrf_create(){
    //INPUT HIDDEN で呼ぶ
    $token = get_token();
    $_SESSION['csrf_token'] = $token;

	//自動ログインのトークンを１週間の有効期限でCookieにセット
    setCookie("csrf_token", $token, time()+60*60*24*2, "/", "", TRUE, TRUE);
    
    return $token;
}

// =========================================================
// 不可逆暗号化
// =========================================================
function passEx($str,$uid,$key){
	if(strlen($str)>0 and !empty($uid)){
		$rtn = crypt($str,$key);
		for($i = 0; $i < 1000; $i++){
			$rtn = substr(crypt($rtn.$uid,$key),2);
		}
	}else{
		$rtn = $str;
	}
	return $rtn;
}
// =========================================================
// 可逆暗号(日本語文字化け対策)
// 22.05.11 商品名の暗号化運用を止めるため、既存関数を無効化。以降、暗号化したい場合はver2を使用する
// =========================================================
function rot13encrypt2 ($str) {
	//暗号化
    return bin2hex(openssl_encrypt($str, "AES-128-ECB", "1"));
}
function rot13decrypt2 ($str) {
	//暗号化解除
    return openssl_decrypt(hex2bin($str), "AES-128-ECB", "1");
}

// =========================================================
// PDO の接続オプション取得
// =========================================================
function get_pdo_options() {
  return array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
               PDO::MYSQL_ATTR_MULTI_STATEMENTS => false,   //sqlの複文禁止 "select * from hoge;delete from hoge"みたいなの
               PDO::ATTR_EMULATE_PREPARES => false);        //同上
}


// =========================================================
// メール送信 
// =========================================================
function send_mail($to,$subject,$body,$fromname,$bcc){
	//$to		: 送信先アドレス
	//$subject	: 件名
	//$body		: 本文
    //$fromname : 送信者

	//SMTP送信
    $return_flag = 'false';
    if(EXEC_MODE==="Local"){
        log_writer2("send_mail - \$to",$to,"lv3");
        log_writer2("send_mail - \$bcc",$bcc,"lv3");
        log_writer2("send_mail - \$body",$body,"lv3");
        return "success";
    }

    require_once('qdmail.php');
    require_once('qdsmtp.php');

    try{
        $mail = new Qdmail();
        $mail -> smtp(true);
        $param = array(
            'host'=> HOST,
            'port'=> PORT ,
            'from'=> FROM,
            'protocol'=>PROTOCOL,
            'pop_host'=>POP_HOST,
            'pop_user'=>POP_USER,
            'pop_pass'=>POP_PASS,
        );
        $mail->smtpServer($param);
        $mail->charsetBody('UTF-8','base64');
        $mail->kana(true);
        $mail->errorDisplay(false);
        //$mail->errorDisplay(true);
        $mail->smtpObject()->error_display = false;
        //$mail->smtpObject()->error_display = true;
        $mail->logLevel(1);//0:ログを出力しない（デフォルト）/1:シンプルタイプ（送信ログ）/2:ヘッダー情報も含むログ/3:メール本文も含めたログ
        
        $mail->errorlogLevel( 1 );//0:エラーログを出力しない（デフォルト）/1:シンプルタイプ（エラーメッセージのみ）/2:ヘッダー情報も含むエラーログ/3:メール本文も含めたエラーログ
        //$mail -> smtpLoglevelLink( true );//QdmailとQdsmtpを併用している場合、Qdmailのログのレベルを以下のメソッドで、Qdsmtpに渡して、同レベルのログをとるよう、Qdsmtpに指示することができます。
        //$mail->logPath('./log/');
        //$mail->logFilename('anpi.log');
        //$smtp ->timeOut(10);
        $mail->smtpObject()->timeOut(10);
        
        $mail ->to($to);
        if(!empty($bcc)){$mail ->bcc($bcc);}
        $mail ->from(FROM , $fromname);
        $mail ->subject($subject);
        $mail ->text($body);
    
        //送信
        $mail ->send();
        $rtn = $mail -> errorStatment();
        if(empty($rtn)){
            $return_flag = 'success';
        }else{
            array_unshift($rtn,$to." / ".$bcc." へのメール送信に失敗しました");
            log_writer2("\$mail_send_rtn",$rtn,"lv0");
            $return_flag = 'false';
        }
    }catch(Exception $e){
        log_writer2("send_mail [Exception] \$e",$e,"lv0");
    }
    log_writer2("send_mail \$return_flag","[".$to." / ".$bcc."] send ".$return_flag,"lv3");
    return $return_flag;
}

function send_line($to,$body){
    if(EXEC_MODE==="Local"){
        log_writer2("send_line - \$body",$body,"lv3");
        return "success";
    }

    $url = ROOT_URL.'line_push_msg.php';

    $data = array(
        'LINE_USER_ID' => $to,
        'MSG' => $body,
    );

    $context = array(
        'http' => array(
            'method'  => 'POST',
            'header'  => implode("\r\n", array('Content-Type: application/x-www-form-urlencoded',)),
            'content' => http_build_query($data)
        )
    );

    return file_get_contents($url, false, stream_context_create($context));
}
// =========================================================
// GUID取得
// =========================================================
function getGUID(){
    if (function_exists('com_create_guid')){
        return com_create_guid();
    }else {
        mt_srand((int)microtime()*10000);//optional for php 4.2.0 and up.
        $charid = strtoupper(md5(uniqid(rand(), true)));
        $hyphen = chr(45);// "-"
        $uuid = chr(123)// "{"
            .substr($charid, 0, 8).$hyphen
            .substr($charid, 8, 4).$hyphen
            .substr($charid,12, 4).$hyphen
            .substr($charid,16, 4).$hyphen
            .substr($charid,20,12)
            .chr(125);// "}"
        return $uuid;
    }
}

// =========================================================
// CSV出力
// =========================================================
function output_csv($data,$kikan){
    $date = date("Ymd");
    header("Content-Type: application/octet-stream");
    header("Content-Disposition: attachment; filename=売上実績_{$date}_{$kikan}.csv");
    
    // データ行の文字コード変換・加工
    foreach ($data as $data_key => $line) {
        foreach ($line as $line_key => $value) {
            $data[$data_key][$line_key] = mb_convert_encoding($value, "SJIS", "UTF-8");
        }
    }

    foreach ($data as $key => $line) {
        echo implode(",", $line ) . "\r\n";
    }
    exit;
}

// =========================================================
// 日付未指定時にルールに沿ってYMDを返す
// =========================================================
function rtn_date($date,$mode){
    //rtn_date(empty($date),$mode)
    //$date:チェックする日付　$mode:日付が空白の場合　today=今日　min=0000-00-00 max=2999-12-31 を返す
    
    if($date==false){
        //何かしら入ってる
        $rtn_date = (string)$date;
    }elseif($mode=="today"){
        $rtn_date = (string)date("Y-m-d");
    }elseif($mode=="min"){
        $rtn_date = "0000-00-00";
    }elseif($mode=="max"){
        $rtn_date = "2999-12-31";
    }else{
        $rtn_date = "";
    }
    
    return $rtn_date;
}

// =========================================================
// 検索ワード未指定時にワイルドカード(%)を返す
// =========================================================
function rtn_wildcard($word){
    //rtn_wildcard(empty($word))で使用する
    if($word==true){
        //空白の場合
        return "%";
    }else{
        return $word;
    }
}


// =========================================================
// 指定年月の月末を返す
// =========================================================
function get_getsumatsu($ym){
    if(strlen($ym)<>6){
        return $ym;
    }
    $yyyymm = substr($ym,0,4)."-".substr($ym,4,2);
    
    return date('Y-m-d',strtotime($yyyymm.' last day of this month'));
}


// =========================================================
// ログイン画面へ飛ばす
// =========================================================
function redirect_to_login($message) {
	$_SESSION = array();
	session_destroy();
	session_start();
    if(EXEC_MODE!=="Local"){
        //session_regenerate_id(true);
    }
    setCookie("login_type", "", -1, "/", "", TRUE, TRUE);
    setCookie("webrez_token", "", -1, "/", "", TRUE, TRUE);
    setCookie("csrf_token", "", -1, "/", "", TRUE, TRUE);

    $_SESSION["EMSG"] = $message;
    log_writer2("function.php[func:redirect_to_login] \$_SESSION values ",$_SESSION,"lv3");

    header("HTTP/1.1 301 Moved Permanently");
    header("Location: index.php");
    exit();
}

function sqllogger($logsql,$e){//(sqlログ,Exception $e:$eセット時はメール通知あり)
    //SQL文はトランザクション単位で共通ログファイルに書き込みを行う。
    //エラーをキャッチした場合、ユーザーID別のログファイルにも書き込みを行う。
    $logfilename="esql_sid_".$_SESSION['user_id'].".log";
    $userid = (!empty($_SESSION['user_id'])?$_SESSION['user_id']:"-");
    $callphp = debug_backtrace();
    $phpname = substr($callphp[0]["file"], (strrpos($callphp[0]["file"],"\\") +1));

    if(!empty($logsql)){
        file_put_contents("sql_log/".date("Y-m-d").".log", $logsql,FILE_APPEND);
    }
    if(!empty($e)){//主にロールバック時
        $elog = print_r($e,true);
        $eMsg = date("Y-m-d H:i:s")."\t".$userid."\t".$phpname."\t"."/*".$e->getMessage()."*/\n";
        file_put_contents("sql_log/".date("Y-m-d").".log", $eMsg, FILE_APPEND);

        file_put_contents("sql_log/".$logfilename,$logsql,FILE_APPEND);
        file_put_contents("sql_log/".$logfilename,"/*".$elog."*/\n",FILE_APPEND);
        log_writer2($phpname." [Exception \$e] =>",$e,"lv0");
    }
    
}

function rtn_sqllog($sql,$params){//(sql,パラメータ[],phpファイル名)w:書き込み r:整形SQLリターン
    //log_writer2("[\$sql] =>",$sql,"lv3");
    //log_writer2("[\$params] =>",$params,"lv3");
    $logsql=$sql.";";
    $i=0;
    $userid = (!empty($_SESSION['user_id'])?$_SESSION['user_id']:"-");
    $callphp = debug_backtrace();
    $phpname = substr($callphp[0]["file"], (strrpos($callphp[0]["file"],"\\") +1));

    if(strstr($logsql,"?")!==false){
        while(strstr($logsql,"?")!==false){
            $logsql = strstr($logsql,"?",true).(!is_null($params[$i])?"\"".$params[$i]."\"":"null").substr(strstr($logsql,"?"), ((strlen(strstr($logsql,"?"))-1)*(-1))) ;
            $i++;
        }
    }else{
        foreach(array_keys($params) as $row){
            $logsql = str_replace(":".$row,(!is_null($params[$row])?"\"".$params[$row]."\"":"null"),$logsql);
        }
    }
    return date("Y-m-d H:i:s")."\t".$userid."\t".$phpname."\t".$logsql."\n";
}

// =========================================================
// 数字を3桁カンマ区切りで返す(整数のみ対応)
// =========================================================
function return_num_disp($number) {
    //$return_number = "";
    //$zan_mojisu = 0;
    $return_number = null;
    if(preg_match('/[^0-9]/',$number)==0){//0～9以外が存在して無い場合、数値として処理
        $shori_moji_su = mb_strlen($number) - 3;
        $zan_mojisu = null;
        
        while($shori_moji_su > 0){
            $return_number = $return_number.",".mb_substr($number,$shori_moji_su,3);
            $zan_mojisu = $shori_moji_su;
            $shori_moji_su = $shori_moji_su - 3;
        }
        
        $return_number = mb_substr($number,0,$zan_mojisu).$return_number;
    }else{
        $return_number = $number;
    }
    return $return_number;
}
// =========================================================
// fatal error　実行関数
// =========================================================
function shutdown_ajax($filename){
    // シャットダウン関数
    // スクリプトの処理が完了する前に
    // ここで何らかの操作をすることができます
    // トランザクション中のエラー停止時は自動rollbackされる。
      $lastError = error_get_last();
      
      //直前でエラーあり、かつ、catch処理出来ていない場合に実行
      if($lastError!==null && $GLOBALS["reseve_status"] === false){
        log_writer2($filename,"shutdown","lv3");
        log_writer2($filename,$lastError,"lv1");
          
        $emsg = "uid::".$_SESSION['user_id']." ERROR_MESSAGE::予期せぬエラー".$lastError['message'];
        if(EXEC_MODE!=="Local"){
            send_mail(SYSTEM_NOTICE_MAIL,"【".TITLE." - WARNING】".$filename."でシステム停止",$emsg,"","");
        }
        log_writer2($filename." [Exception \$lastError] =>",$lastError,"lv0");
    
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
  function shutdown_page(){
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
					send_mail(SYSTEM_NOTICE_MAIL,"【".TITLE." - WARNING】".basename(__FILE__)."でシステム停止",$emsg,"","");
			}
			log_writer2(basename(__FILE__)." [Exception \$lastError] =>",$lastError,"lv0");
			echo "予期せぬエラーが発生しました。<br>エラー内容は管理者へ自動通報されます。<br>ご迷惑をおかけしますが、復旧までしばらくお待ち下さい。";
	}
}

  
?>