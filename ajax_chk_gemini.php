<?php
require "php_header.php";
register_shutdown_function('shutdown_ajax',basename(__FILE__));

$msg = "";                          //ユーザー向け処理結果メッセージ
log_writer2("\$_POST",$_POST,"lv3");


$rtn = true;//csrf_checker(["review_post.php"],["P","S"]);
if($rtn !== true){
	$msg=$rtn;
	$alert_status = "alert-warning";
	$reseve_status = true;
}else{
	$user_input = $_POST["Article"] ?? '';
	$type = $_POST["type"] ?? 'kaiwa';   //連続会話(kaiwa) or 一問一答(one)
	$answer_type = $_POST["answer_type"] ?? 'plain';   //json or plain(そのまま)
	$subject = $_POST["subject"] ?? ''; //会話のテーマ($_SESSION[$subject]に会話履歴を保存)
	//$type = "one";
	$response_schema = json_decode($_POST["response_schema"],true) ?? NULL; //会話のテーマ($_SESSION[$subject]に会話履歴を保存)
	
    
	if($type==="kaiwa"){
		$msg = gemini_api_kaiwa($user_input,$answer_type,$subject);
	}else if($type==="one"){
		$msg = gemini_api($user_input,$answer_type,$response_schema);
	}

}
//log_writer2("\$msg",$msg,"lv3");
//$token = csrf_create();

header('Content-type: application/json');
echo json_encode($msg, JSON_UNESCAPED_UNICODE);
//echo $msg;

exit();

?>