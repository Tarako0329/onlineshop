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
	$response_schema = json_decode($_POST["response_schema"],true) ?? NULL; 

	/*$response_schemaサンプル
	  $response_schema = [
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
                'required' => ['自動返信', '受付確認', '支払確認', '発送連絡', 'キャンセル受付']	//必須項目
            ]
        ],
        'required' => ['check_results']	//必須項目
    ];
	*/


	/*$response_schema = [
		'type' => 'object',
		'properties' => [
			'posts' => [
				'type' => 'object',
				'properties' => [
					'tags' => [
						'type' => 'array',
						'items' => [ // 配列の各要素の型を定義
								'type' => 'string',
								'description' => 'ハッシュタグ',
						],
						'description' => '投稿に紐づくハッシュタグの配列',
					],
					'texts' => [
						'type' => 'array',
						'items' => [ // 配列の各要素（投稿例オブジェクト）の型を定義
							'type' => 'object',
							'properties' => [
								'text' => ['type' => 'string', 'description' => 'SNS投稿例のテキスト'],
								'tags' => [ // 各投稿例に紐づくタグも配列にする
									'type' => 'array',
									'items' => [
										'type' => 'string',
										'description' => 'SNS投稿例に紐づくハッシュタグ',
									],
								],
							],
							'required' => ['text', 'tags'], // 各投稿例の必須項目
						],
						'description' => 'SNS投稿例の配列',
					],
					'url' =>['type' => 'string', 'description' => '商品url']
				]
				,'required' => ['tags', 'texts','url']	//必須項目
			],
			'required' => ['posts']	//必須項目
		]
	];*/
    
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