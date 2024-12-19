<?php

//botリストの作成
// jsonファイルを落としてくる
$json = file_get_contents('https://raw.githubusercontent.com/monperrus/crawler-user-agents/master/crawler-user-agents.json');
// 落とすのに失敗したら終了
if ($json === false) {
    return;
}

// jsonを配列に変換
$arr = json_decode($json, true);
// 変換できなかったら終了
if ($arr === null) {
    return;
}

// 正規表現のパターンをまとめた配列を作る
$pattern_list = [];
foreach ($arr as $key => $value) {
    $pattern_list[] = $value['pattern'];
}

// パターン配列を「|」で連結した文字列に変換
$pattern_list_string = implode('|', $pattern_list);

file_put_contents("bot_list.txt",$pattern_list_string);
exit();
?>