<?php
	if (php_sapi_name() != 'cli') {
		//exit('このスクリプトはCLIからのみ実行可能です。');
	}
	$mypath = dirname(__DIR__);
	chdir($mypath);
	require "php_header_admin.php";

	// 送信先のURL
	$url = ROOT_URL.'ajax_get_analysis.php';

	

	for ($i=2; $i <= 4; $i++) { 
		// 送信したいデータ（連想配列）
		$data = [
		    'an_type' => (string)$i,
		    'from' => '2026-05',
		    'to' => '2026-05',
		    'tani' => 'm',
		    'taishou_all' => 'true',
			'user_id' => '1234'
		];

		// データを URLエンコード形式（key1=val1&key2=val2...）に変換
		$options = [
			'http' => [
				'method'  => 'POST',
				'header'  => 'Content-type: application/x-www-form-urlencoded',
				'content' => http_build_query($data)
			]
		];

		// コンテキストを作成
		$context = stream_context_create($options);

		// リクエスト送信とレスポンスの取得
		$response[$i] = file_get_contents($url, false, $context);

		// 結果を表示
		echo "受信側の応答: " . $response[$i] . "\n";
	}

?>