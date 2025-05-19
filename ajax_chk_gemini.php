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
    //$type = "one";
    
	if($type==="kaiwa"){
		$msg = gemini_api_kaiwa($user_input,"plain","test");
	}else if($type==="one"){
		$msg = gemini_api($user_input,"plain");
	}

}

//$token = csrf_create();

header('Content-type: application/json');
echo json_encode($msg, JSON_UNESCAPED_UNICODE);

exit();

?>