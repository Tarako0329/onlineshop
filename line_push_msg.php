<?php
// LINE Messaging API プッシュメッセージを送る
$LINE_PUSH_URL = "https://api.line.me/v2/bot/message/push";


$LINE_CHANNEL_ACCESS_TOKEN = 'tMgHcjdEccRfOqeUnGb27VDF98ywD7HatWhLJC6tB0olVWon2rAa5cU+lCv3+MJWp/9K3KpJrqkipQsNwe5+HKX7RU5SW6kcRhr7vT8kZCldCK4WGnO1Y006Plr143WgDuOoVW8O43aib+NjVlmNZgdB04t89/1O/w1cDnyilFU='; 

// Your user ID
// Messaging API 管理画面で確認 これくらいながいやつ
$LINE_USER_ID = "U56023f698175e5d2fc8bd649fc4b378b";

// 送信するメッセージ
$message_1 = "こんにちは API";
$message_2 = "PHPからPUSH送信\r\n改行して２行目";

// リクエストヘッダ
$header = [
    'Authorization: Bearer ' . $LINE_CHANNEL_ACCESS_TOKEN,
    'Content-Type: application/json'
];

// 送信するメッセージの下準備
$post_values = array(
    [
    "type" => "text",
    "text" => $message_1
    ],
    [
    "type" => "text",
    "text" => $message_2
    ]
);

// 送信するデータ
$post_data = [
    "to" => $LINE_USER_ID,
    "messages" => $post_values
    ];

// デバグ確認用のログ：送信データ
$file = 'tmp/post_data.txt';
file_put_contents($file, json_encode($post_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), FILE_APPEND);
file_put_contents($file, PHP_EOL.PHP_EOL, FILE_APPEND);

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
    curl_close($curl);
}
else
{
    // file_get_contentsを使った送信処理
    $context = stream_context_create(array(
        'http' => array(
            'method' => 'POST',
            'header' => implode("\r\n", $header),
            'content'=>  json_encode($post_data),
            'ignore_errors' => true
        )
    ));

    $result = file_get_contents(
        $LINE_PUSH_URL,
        false,
        $context
        );
}

// デバグ確認用のログ：受信レスポンス
$file = 'tmp/result.txt';
file_put_contents($file, $result, FILE_APPEND);
file_put_contents($file, PHP_EOL.PHP_EOL, FILE_APPEND);
?>