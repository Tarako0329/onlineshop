<?php
	if (php_sapi_name() != 'cli') {
		exit('このスクリプトはCLIからのみ実行可能です。');
	}
	$mypath = dirname(__DIR__);
	chdir($mypath);
	require "php_header_admin.php";

	//botリストの作成
	// jsonファイルを落としてくる
	//$mypath = dirname(__DIR__);
	

	$URL = 'https://raw.githubusercontent.com/monperrus/crawler-user-agents/master/crawler-user-agents.json';
	$json = file_get_contents($URL);
	// 落とすのに失敗したら終了
	if ($json === false) {
		log_writer2("","create_botlist.phpでボットリストの作成ができませんでした。参照するURLを確認してください。","lv0");
		return;
	}

	// jsonを配列に変換
	$arr = json_decode($json, true);
	// 変換できなかったら終了
	if ($arr === null) {
		log_writer2("","create_botlist.phpでボットリストの作成ができませんでした。","lv0");
		return;
	}

	// 正規表現のパターンをまとめた配列を作る
	$pattern_list = [];
	foreach ($arr as $key => $value) {
		$pattern_list[] = $value['pattern'];
	}

	// パターン配列を「|」で連結した文字列に変換
	$pattern_list_string = implode('|', $pattern_list);

	file_put_contents($mypath."/bot_list.txt",$pattern_list_string);
	exit();
?>