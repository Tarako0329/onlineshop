<?php
    require "php_header.php";
    
    $json_string = file_get_contents('php://input');
    $json_obj = json_decode($json_string);

    log_writer2("\$json_obj",$json_obj,"lv3");

    // webhookイベントタイプ
    $type = $json_obj->events[0]->type;
    // ユーザID
    $user_id = $json_obj->events[0]->source->userId . "\n";

    if ($type == 'message') {
        $msg = $json_obj->events[0]->message->text ;
        if($msg == "userid"){
            // 送信するメッセージ
            $post_values = array(
                [
                    "type" => "text",
                    "text" => "ユーザIDは"
                ],
                [
                    "type" => "text",
                    "text" => $user_id
                ],
                [
                    "type" => "text",
                    "text" => "になります。\r\nサイト設定画面の「LINE ID」に設定してください。"
                ]
                
            );
        }else{
            $post_values = array(
                [
                    "type" => "text",
                    "text" => "教えてあげることは何もないよ～"
                ]
            );

        }
    } else {
        $post_values = array(
            [
                "type" => "text",
                "text" => "フォローありがとう！\r\n「userid」とメッセージを送ると、サイト設定画面に入力するLINE ID が返信されるよ。"
            ]
        );
    }


    // LINE Messaging API プッシュメッセージを送る
    $LINE_PUSH_URL = "https://api.line.me/v2/bot/message/push";

    $LINE_CHANNEL_ACCESS_TOKEN = $_ENV["LINE_CHANNEL_ACCESS_TOKEN"];

    // Your user ID
    // Messaging API 管理画面で確認 これくらいながいやつ
    //$LINE_USER_ID = $_ENV["LINE_USER_ID"];
    $LINE_USER_ID = $user_id;


    // リクエストヘッダ
    $header = [
        'Authorization: Bearer ' . $LINE_CHANNEL_ACCESS_TOKEN,
        'Content-Type: application/json'
    ];

    // 送信するメッセージの下準備

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
        curl_close($curl);
    }else{
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
    //$file = 'tmp/result.txt';
    //file_put_contents($file, $result, FILE_APPEND);
    //file_put_contents($file, PHP_EOL.PHP_EOL, FILE_APPEND);
?>