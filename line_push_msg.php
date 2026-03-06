<?php
require "php_header.php";
//log_writer2("\$_POST",$_POST,"lv3");

// LINE Messaging API プッシュメッセージを送る
$LINE_PUSH_URL = "https://api.line.me/v2/bot/message/push";

$LINE_CHANNEL_ACCESS_TOKEN = $_ENV["LINE_CHANNEL_ACCESS_TOKEN"];

// Your user ID
// Messaging API 管理画面で確認 これくらいながいやつ
$LINE_USER_ID = $_POST["LINE_USER_ID"];

// リクエストヘッダ
$header = [
    'Authorization: Bearer ' . $LINE_CHANNEL_ACCESS_TOKEN,
    'Content-Type: application/json'
];

// 送信するメッセージの下準備
$post_values = array(
    [
    "type" => "text",
    "text" => $_POST["MSG"]
    ]
);

// 送信するデータ
$post_data = [
    "to" => $LINE_USER_ID,
    "messages" => $post_values
    ];

// デバグ確認用のログ：送信データ
//$file = 'tmp/post_data.txt';
//file_put_contents($file, json_encode($post_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), FILE_APPEND);
//file_put_contents($file, PHP_EOL.PHP_EOL, FILE_APPEND);

// cURLを使った送信処理の時は true
// file_get_contentsを使った送信処理の時は false
$USE_CURL = true;

if ($USE_CURL) {
    // cURLを使った送信処理
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $LINE_PUSH_URL);
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
    curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($post_data));
    $result = curl_exec($curl);
    // HTTPステータスコードを取得
    $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);
}

$response_data = json_decode($result, true);

if ($http_code === 200) {
    $status = "success";
} else {
    $status = "failed";
}
$result = array(
    "status" => $status,
    "response" => $response_data,
    "http_code" => $http_code ?? null
);
$result = json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
header('Content-Type: application/json'); // レスポンスがJSONであることを明示
echo $result;
?>