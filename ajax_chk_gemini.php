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
	$url = GEMINI_URL.GEMINI;
	$user_input = $_POST["Article"] ?? '';
	$type = $_POST["type"] ?? '';   //連続会話(kaiwa) or 一問一答(one)

	if($type==="kaiwa"){
		// 現在のユーザー入力を会話履歴に追加
		$_SESSION['chat_history'][] = [
			'role' => 'user',
			'parts' => [
				['text' => $user_input]
			]
		];
		$data = [
			'contents' => $_SESSION['chat_history']
			// 必要に応じて safety_settings や generation_config もここに追加
		];
	}else if($type==="one"){
		$data =  [
			'contents' => [
				[
					'parts' => [
						//['text' => '次の文章について誤字脱字等のチェックをお願いします。対象の文章「'.$user_input.'」']
						['text' => $_POST["Article"]]
					]
				]
			]
		];
	}

	$options = [
		'http' => [
			'method' => 'POST',
			'header' => [
				'Content-Type: application/json',
			],
			'content' => json_encode($data),
		],
	];
	
	$context = stream_context_create($options);
	$response = file_get_contents($url, false, $context);
	
	log_writer2("\$response",$response,"lv3");
	
	if ($response === false) {
		$msg =  'Gemini呼び出しに失敗しました。時間をおいて、再度実行してみてください。ER1';
	} else {
		$result = json_decode($response, true);
		$msg = $result['candidates'][0]['content']['parts'][0]['text'];
		//log_writer2("\$result",$result,"lv3"); 
		if($type==="kaiwa"){
			// Geminiの応答を会話履歴に追加
			$_SESSION['chat_history'][] = [
				'role' => 'model',
				'parts' => [
					['text' => $msg]
				]
			];
		}
	}
}

//$token = csrf_create();

header('Content-type: application/json');
echo json_encode($msg, JSON_UNESCAPED_UNICODE);

exit();

?>