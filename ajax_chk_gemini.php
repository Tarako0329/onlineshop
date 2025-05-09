<?php
//review_onlineテーブルにデータをデリインで登録する。
//テーブル項目はshop_id,shouhinCD,review,score,Contributor,NoName,orderNO
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
    //Geminiで誹謗中傷check
    $url = GEMINI_URL.GEMINI;
    $data = [
        'contents' => [
            [
                'parts' => [
                    ['text' => '次の文章について誤字脱字等のチェックをお願いします。対象の文章「'.$_POST["Article"].'」']
                ]
            ]
        ]
    ];
    
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
        
        log_writer2("\$result",$result,"lv3"); 
    
        if (isset($msg)) {
        } else {
            $msg = 'Gemini呼び出しに失敗しました。時間をおいて、再度実行してみてください。ER2';
        }
    }
}

//$token = csrf_create();

header('Content-type: application/json');
echo json_encode($msg, JSON_UNESCAPED_UNICODE);

exit();

?>