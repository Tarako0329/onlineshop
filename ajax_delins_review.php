<?php
//review_onlineテーブルにデータをデリインで登録する。
//テーブル項目はshop_id,shouhinCD,review,score,Contributor,NoName,orderNO
require "php_header.php";
register_shutdown_function('shutdown_ajax',basename(__FILE__));

$msg = "";                          //ユーザー向け処理結果メッセージ
$alert_status = "alert-warning";    //bootstrap alert class
$reseve_status=false;               //処理結果セット済みフラグ。
$timeout=false;                     //セッション切れ。ログイン画面に飛ばすフラグ
$sqllog="";

log_writer2("\$_POST",$_POST,"lv3");

//User_onlineよりデータ取得
$sql = "select * from Users_online where uid = :uid";
$stmt = $pdo_h->prepare($sql);
$stmt->bindValue("uid", $_POST["shop_id"], PDO::PARAM_STR);
$stmt->execute();
$user = $stmt->fetchAll(PDO::FETCH_ASSOC);
$lineID = (!empty($user[0]["line_id"])?$user[0]["line_id"]:"none");
$mail = $user[0]["mail"];
$head = " 様より商品レビューが投稿されました。";


$rtn = csrf_checker(["review_post.php"],["P","S"]);
if($rtn !== true){
    $msg=$rtn;
    $alert_status = "alert-warning";
    $reseve_status = true;
}else{
    if($rtn===false){
        $reseve_status = true;
        $msg="長時間操作されていないため、自動ﾛｸﾞｱｳﾄしました。再度ログインし、もう一度xxxxxxして下さい。";
        $_SESSION["EMSG"]="長時間操作されていないため、自動ﾛｸﾞｱｳﾄしました。再度ログインし、もう一度xxxxxxして下さい。";
        $timeout=true;
    }else{

        $DELsql2 = "delete from review_online where shop_id = :shop_id and shouhinCD = :shouhinCD and Contributor = :Contributor and orderNO = :orderNO";
        $INsql2 = "insert into review_online (shop_id,shouhinCD,review,Contributor,NoName,orderNO,score) values(:shop_id,:shouhinCD,:review,:Contributor,:NoName,:orderNO,:score)";
        
        
        $params["shop_id"] = $_POST["shop_id"];
        $params["Contributor"] = $_POST["Contributor"];
        $params["shouhinCD"] = $_POST["shouhinCD"];
        $params["review"] = $_POST["review"];
        $params["NoName"] = $_POST["NoName"];
        $params["orderNO"] = $_POST["orderNO"];
        $params["score"] = $_POST["score"];

        //Geminiで誹謗中傷check
        $ask = '次のレビューが過度な誹謗中傷にあたるかどうかを判断してください。判断結果はPHPで利用するため、下記のJSONスキーマに厳密に従ってJSONを出力してください。
            誹謗中傷と判断された場合"NG"。問題ない場合は"OK"を。NGの場合は理由も出力。出力形式は[{"判定":結果,"理由":"理由"}]です。
            対象のレビュー「'.$params["review"].'」';
        
        $response_schema = [
            'type' => 'array',
            'items' => [
                'type' => 'object',
                'properties' => [
                    '判定' => ['type' => 'string', 'description' => '誹謗中傷判定結果 ("OK" or "NG")'],
                    '理由' => ['type' => 'string', 'description' => 'NGの場合の理由']
                ],
                'required' => ['判定', '理由']
            ]
        ];
        
        $chk_result = gemini_api($ask, "json", $response_schema);
        log_writer2("\$chk_result",$chk_result,"lv3");
        
        if(!empty($chk_result[0]["emsg"])){
            $msg =  '誹謗中傷checkのためのGemini呼び出しに失敗しました。再度投稿してみてください';
            $check_ng = "NG";
            $alert_status = "alert-danger";
            log_writer2("\$chk_result",$chk_result,"lv3");
        }else{
            $check_ng = $chk_result["result"][0]["判定"];
            $msg = $chk_result["result"][0]["理由"];
        }

        if($check_ng==="NG"){
            $msg = "AIに誹謗中傷と判断されたため、登録できませんでした。\n理由：".$msg;
            $alert_status = "alert-danger";
            $reseve_status=true;
            $token = csrf_create();
            $return_sts = array(
                "MSG" => $msg
                ,"status" => $alert_status
                ,"token" => $token
                ,"timeout" => $timeout
            );
            header('Content-type: application/json');
            echo json_encode($return_sts, JSON_UNESCAPED_UNICODE);
            exit();
        }else{
            $msg="";
        }

        try{
            $pdo_h->beginTransaction();
            $sqllog .= rtn_sqllog("START TRANSACTION",[]);

            $stmt = $pdo_h->prepare( $DELsql2 );
            $stmt->bindValue("shop_id", $params["shop_id"], PDO::PARAM_INT);
            $stmt->bindValue("shouhinCD", $params["shouhinCD"], PDO::PARAM_STR);
            $stmt->bindValue("Contributor", $params["Contributor"], PDO::PARAM_STR);
            $stmt->bindValue("orderNO", $params["orderNO"], PDO::PARAM_STR);
            
            $sqllog .= rtn_sqllog($DELsql2,$params);

            $status = $stmt->execute();
            $sqllog .= rtn_sqllog("--execute():正常終了",[]);

            $stmt = $pdo_h->prepare( $INsql2 );
            $stmt->bindValue("shop_id", $params["shop_id"], PDO::PARAM_INT);
            $stmt->bindValue("shouhinCD", $params["shouhinCD"], PDO::PARAM_STR);
            $stmt->bindValue("Contributor", $params["Contributor"], PDO::PARAM_STR);
            $stmt->bindValue("review", $params["review"], PDO::PARAM_STR);
            $stmt->bindValue("NoName", $params["NoName"], PDO::PARAM_STR);
            $stmt->bindValue("orderNO", $params["orderNO"], PDO::PARAM_STR);
            $stmt->bindValue("score", $params["score"], PDO::PARAM_INT);
            
            $sqllog .= rtn_sqllog($INsql2,$params);

            $status = $stmt->execute();
            $sqllog .= rtn_sqllog("--execute():正常終了",[]);
            
            $pdo_h->commit();
            $sqllog .= rtn_sqllog("commit",[]);
            sqllogger($sqllog,0);
    
            $msg .= "登録が完了しました。";
            $alert_status = "alert-success";
            $reseve_status=true;

            //出店者URL
            $url = ROOT_URL."review_management.php?key=".rot13encrypt2($params["shop_id"]);
            $body = "商品名：".$_POST["shouhinNM"]."\r\nレビュー：".$_POST["review"]."\r\n返信したい場合は、下記URLよりご確認ください。\r\n".$url;
            

            if($lineID <> "none"){
                $rtn = send_line($lineID,"【".$params["Contributor"].$head."】\r\n".$body);//出店者へお知らせLINE
            }else{
                $rtn = send_mail($mail,$params["Contributor"].$head,$body,TITLE,"");//出店者へお知らせメール
            }
            log_writer2("\$rtn",$rtn,"lv3");

        }catch(Exception $e){
            $pdo_h->rollBack();
            $sqllog .= rtn_sqllog("rollBack",[]);
            sqllogger($sqllog,$e);
            log_writer2("\$e",$e,"lv0");
            $msg .= "システムエラーによる更新失敗。管理者へ通知しました。";
            $alert_status = "alert-danger";
            $reseve_status=true;
        }
    }
}

$token = csrf_create();

$return_sts = array(
    "MSG" => $msg
    ,"status" => $alert_status
    ,"token" => $token
    ,"timeout" => $timeout
);
header('Content-type: application/json');
echo json_encode($return_sts, JSON_UNESCAPED_UNICODE);

exit();

?>