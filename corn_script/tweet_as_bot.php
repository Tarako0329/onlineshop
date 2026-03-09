<?php
//１日５０ツイートまでかつ月１５００ツイートまで
//date_default_timezone_set('Asia/Tokyo'); 
if (php_sapi_name() != 'cli') {
	exit('このスクリプトはCLIからのみ実行可能です。');
}
$mypath = dirname(__DIR__);
chdir($mypath);
require "php_header_admin.php";

define("API_KEY",$_ENV["X_API_KEY"]);
define("API_SECRET_KEY",$_ENV["X_API_SECRET_KEY"]);
define("ACCESS_TOKEN",$_ENV["X_ACCESS_TOKEN"]);
define("SECRET_ACCESS_TOKEN",$_ENV["X_SECRET_ACCESS_TOKEN"]);

exit('サービスは停止してます');	//サービス一時停止


register_shutdown_function('shutdown_ajax',basename(__FILE__));
use Abraham\TwitterOAuth\TwitterOAuth;

$status="false";

//実行時間check
$online_shop_config = $db->SELECT("SELECT * from online_shop_config");

if($online_shop_config[0]["next_post_time"] >= date('Y-m-d H:i:s')){
	//実行時間前
	//echo "次回送信は ".$online_shop_config[0]["next_post_time"]."です。\n処理を終了します";
	exit();
}

echo "処理を開始します。\n";

if(EXEC_MODE==="Local"){
		echo "ツイートが送信されました！";
		$status = "success";
}else{
	//ポスト内容取得
	//商品選定
	$row = $db->SELECT("SELECT U.yagou,M.* from shouhinMS_online as M inner join Users_online as U on M.uid=U.uid where status in ('show','soon') and IFNULL(auto_post_sns,'') not like '%X%' order by shouhinCD");
	if(count($row)===0){//全件投稿完了・フラグリセット
		try{
			$db->begin_tran();
			$db->UP_DEL_EXEC("UPDATE shouhinMS_online set auto_post_sns=''");
			$db->commit_tran();
			echo "投稿済みフラグをリセット\n";

			//商品再選定
			$row = $db->SELECT("SELECT U.yagou,M.* from shouhinMS_online as M inner join Users_online as U on M.uid=U.uid where status='show' and IFNULL(auto_post_sns,'') not like '%X%' order by shouhinCD");
		}catch(Exception $e){
			$db->rollback_tran($e->getMessage());
			log_writer2("\$e",$e,"lv0");
			echo "投稿済みフラグをリセットでエラー\n";
		}
	}

	$post_index = rand(0,(count($row)-1));
	
	echo "対象商品:".$row[$post_index]["shouhinNM"]."\n";
	//log_writer2("\$post_index",$post_index,"lv1");
	//log_writer2("\$row",$row[$post_index],"lv1");

	//つぶやき作成
	$sns_type = "X.com" ;
	$uid = $row[$post_index]["uid"];
	$shouhinCD = $row[$post_index]["shouhinCD"];
	$yagou = $row[$post_index]["yagou"];
	$hinmei = $row[$post_index]["shouhinNM"];
	$sort_info =  (($row[$post_index]["status"]==="soon")?"近日公開！！　":"").$row[$post_index]["short_info"];
	$information = $row[$post_index]["infomation"];
	
	$discription = "URL：".ROOT_URL."product.php?id=".$uid."-".$shouhinCD."&z=".$sns_type." 販売元:".$yagou." 商品名：".$hinmei."。説明：".$sort_info." ".$information;
	
	$ask = '凄腕インフルエンサーとして'.$sns_type.'で購買意欲を掻き立てる日本語の投稿例を10個出力。'
		.$sns_type.'にそのまま投稿できるようにＵＲＬとハッシュタグも含めて作成。phpのjson_decodeで処理できるように下記のJSONスキーマに厳密に従ってJSONを出力してください。
		URLとハッシュタグを除いた文字数は100文字以下。JSONオブジェクトを、プレーンテキスト形式で出力してください。商品情報『'.$discription.'』';

	$response_schema = [
            'type' => 'array',
            'items' => [
                'type' => 'object',
                'properties' => [
                    'post' => ['type' => 'string', 'description' => '投稿例']
                ],
                'required' => ['post']
            ]
        ];
        
	$chk_result = gemini_api($ask, "json", $response_schema);
	log_writer2("\$chk_result",$chk_result,"lv3");
	
	if(!empty($chk_result[0]["emsg"])){
		//$msg =  'Gemini呼び出しに失敗しました。再度投稿してみてください';
		log_writer2("\$chk_result",$chk_result,"lv3");
		exit();
	}else{
		$answer = $chk_result["result"];
	}

	$text = $answer[rand(0,9)]["post"];
	echo $text."\n";

	if(EXEC_MODE<>"Product"){
		echo "本番環境以外ではツイートしないで終了\n";
		exit();
	}
	
	try{
		$connection = new TwitterOAuth(
			API_KEY,
			API_SECRET_KEY,
			ACCESS_TOKEN,
			SECRET_ACCESS_TOKEN
		);
		
		$connection->setApiVersion('2');
		
		//URLが半角23文字扱い。ハッシュタグは含まない
		$result = $connection->post("tweets", ["text"=>$text], ['jsonPayload'=>true]);
		$httpCode = $connection->getLastHttpCode();
		//$httpCode = 201;	//テスト運用・ツイートはしない
		
		
		if ($httpCode == 201) { // 201は作成成功を示すステータスコード
			//$this->info("ツイートが送信されました！");
			echo "ツイートが送信されました！\n";
			$status = "success";
			$db->UP_DEL_EXEC(
				"UPDATE shouhinMS_online set auto_post_sns='X' where uid=:uid and shouhinCD=:shouhinCD",
				['uid'=>$uid, 'shouhinCD'=>$shouhinCD]
			);
			
		}else if($httpCode == 429){
			echo "1日の送信可能数を超過しました。\n";
			log_writer2("X-bot-ErrorHeader",$connection->getLastXHeaders(),"lv1");
			$next = (24-17)*60;
			$db->UP_DEL_EXEC("UPDATE online_shop_config set next_post_time=DATE_ADD(NOW(), INTERVAL :next MINUTE)", ['next'=>$next]);
			echo "次は ".$next." 分後です";
		}else{
			$errorMessage = isset($result->errors) ?json_encode($result->errors, JSON_UNESCAPED_UNICODE) :'不明なエラー';
			echo "ツイートの送信に失敗しました。\n HTTPコード: $httpCode,\n エラーメッセージ: $errorMessage \n";
			log_writer2("X-bot-ErrorHeader",$connection->getLastXHeaders(),"lv1");
			//log_writer2("\$msg",$msg,"lv1");
		}
	}catch(Exception $e){
		//print_r($e,true);
		echo "catch(Exception \$e)";
		log_writer2("\$e",$e,"lv0");
		exit();
	}
}

if($status==="success"){//次回の実行時間をセット
	try{
		$db->begin_tran();
		$next = rand($online_shop_config[0]["post_interval_F"],$online_shop_config[0]["post_interval_T"]);
		$db->UP_DEL_EXEC("UPDATE online_shop_config set next_post_time=DATE_ADD(NOW(), INTERVAL :next MINUTE)", ['next'=>$next]);
		$db->commit_tran();
		echo "次は ".$next." 分後です";
	}catch(Exception $e){
		$db->rollback_tran($e->getMessage());
		log_writer2("\$e",$e,"lv0");
		echo "次の投稿時間設定でエラー";
	}
}

exit();
?>