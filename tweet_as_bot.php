<?php
//１日５０ツイートまでかつ月１５００ツイートまで
date_default_timezone_set('Asia/Tokyo'); 
if (php_sapi_name() != 'cli') {
	exit('このスクリプトはCLIからのみ実行可能です。');
}
chdir(__DIR__);

//require "php_header.php";

define("VERSION","ver1.36.1");

require "./vendor/autoload.php";
require "functions.php";

//.envの取得
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();
define("ROOT_URL",$_ENV["HTTP"]);
define("EXEC_MODE",$_ENV["EXEC_MODE"]);
//システム通知
define("SYSTEM_NOTICE_MAIL",$_ENV["SYSTEM_NOTICE_MAIL"]);

if(EXEC_MODE<>"Product"){
  $time=date('Ymd-His');
  $id="demo";
  $pass="00000000";
}else{
  $time=VERSION;
  $id="";
  $pass="";
}


// DBとの接続
define("DNS","mysql:host=".$_ENV["SV"].";dbname=".$_ENV["DBNAME"].";charset=utf8");
define("USER_NAME", $_ENV["DBUSER"]);
define("PASSWORD", $_ENV["PASS"]);

//メール送信関連
define("HOST", $_ENV["HOST"]);
define("PORT", $_ENV["PORT"]);
define("FROM", $_ENV["FROM"]);
define("PROTOCOL", $_ENV["PROTOCOL"]);
define("POP_HOST", $_ENV["POP_HOST"]);
define("POP_USER", $_ENV["POP_USER"]);
define("POP_PASS", $_ENV["POP_PASS"]);

define("GEMINI",$_ENV["GOOGLE_API"]);

$pdo_h = new PDO(DNS, USER_NAME, PASSWORD, get_pdo_options());

register_shutdown_function('shutdown_ajax',basename(__FILE__));
//log_writer2("\$_POST",$_POST,"lv1");
use Abraham\TwitterOAuth\TwitterOAuth;
use GeminiAPI\Client;
use GeminiAPI\Resources\ModelName;
use GeminiAPI\Resources\Parts\TextPart;

$status="false";

//実行時間check
$stmt = $pdo_h->prepare("select * from online_shop_config");
$stmt->execute();
$online_shop_config = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
	$stmt = $pdo_h->prepare("select U.yagou,M.* from shouhinMS_online as M inner join Users_online as U on M.uid=U.uid where status='show' and IFNULL(auto_post_sns,'') not like '%X%' order by shouhinCD");
	$stmt->execute();
	$row = $stmt->fetchAll(PDO::FETCH_ASSOC);
	if(count($row)===0){//全件投稿完了・フラグリセット
		try{
			$pdo_h->beginTransaction();
			$stmt = $pdo_h->prepare("update shouhinMS_online set auto_post_sns=''");
			$stmt->execute();
			$pdo_h->commit();
			echo "投稿済みフラグをリセット\n";

			//商品再選定
			$stmt = $pdo_h->prepare("select U.yagou,M.* from shouhinMS_online as M inner join Users_online as U on M.uid=U.uid where status='show' and IFNULL(auto_post_sns,'') not like '%X%' order by shouhinCD");
			$stmt->execute();
			$row = $stmt->fetchAll(PDO::FETCH_ASSOC);
		}catch(Exception $e){
			$pdo_h->rollBack();
			log_writer2("\$e",$e,"lv0");
			echo "投稿済みフラグをリセットでエラー\n";
		}
	
	}

	$post_index = rand(0,(count($row)-1));
	
	echo "対象商品:".$row[$post_index]["shouhinNM"]."\n";
	//log_writer2("\$post_index",$post_index,"lv1");
	//log_writer2("\$row",$row[$post_index],"lv1");

	//つぶやき作成
	$sns_type = "X" ;
	$uid = $row[$post_index]["uid"];
	$shouhinCD = $row[$post_index]["shouhinCD"];
	$yagou = $row[$post_index]["yagou"];
	$hinmei = $row[$post_index]["shouhinNM"];
	$sort_info = $row[$post_index]["short_info"];
	$information = $row[$post_index]["infomation"];
	
	$discription = "URL：".ROOT_URL."product.php?id=".$uid."-".$shouhinCD."&z=".$sns_type." 販売元:".$yagou." 商品名：".$hinmei."。説明：".$sort_info." ".$information;
	
	
	$client = new Client(GEMINI);
	
	$response = $client->withV1BetaVersion()
			->generativeModel(ModelName::GEMINI_1_5_FLASH)
			->withSystemInstruction('凄腕インフルエンサーとして')
			->generateContent(
					new TextPart($sns_type.'で購買意欲を掻き立てる日本語の投稿例を10個出力。'.$sns_type.'にそのまま投稿できるようにＵＲＬとハッシュタグも含めて作成。phpのjson_decodeで処理できるように[{"post":投稿例},{"post":投稿例}]で出力。URLとハッシュタグを除いた文字数は100文字以下。JSONオブジェクトを、プレーンテキスト形式で出力してください'.$discription),
			);
			
	//print nl2br($response->text());
			
	$answer = $response->text();
	$answer = str_replace('```json','',$answer);
	$answer = str_replace('```','',$answer);
	$answer = str_replace('\n','',$answer);
	$answer = str_replace('\r','',$answer);
	$answer = str_replace('\r\n','',$answer);
	$answer = substr($answer,1);
	$answer = json_decode($answer,true);
	//print_r($answer);
	//log_writer2("\$answer",$answer,"lv1");
	
	
	$text = $answer[rand(0,9)]["post"];
	echo $text."\n";
	
	define("API_KEY",$_ENV["X_API_KEY"]);
	define("API_SECRET_KEY",$_ENV["X_API_SECRET_KEY"]);
	define("ACCESS_TOKEN",$_ENV["X_ACCESS_TOKEN"]);
	define("SECRET_ACCESS_TOKEN",$_ENV["X_SECRET_ACCESS_TOKEN"]);
	
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
			$stmt = $pdo_h->prepare("update shouhinMS_online set auto_post_sns='X' where uid=".$uid." and shouhinCD=".$shouhinCD);
			$stmt->execute();
	
		} else {
			$errorMessage = isset($result->errors) ?json_encode($result->errors, JSON_UNESCAPED_UNICODE) :'不明なエラー';
			echo "ツイートの送信に失敗しました。\n HTTPコード: $httpCode,\n エラーメッセージ: $errorMessage ";
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
		$pdo_h->beginTransaction();
		$next = rand($online_shop_config[0]["post_interval_F"],$online_shop_config[0]["post_interval_T"]);
		$stmt = $pdo_h->prepare("update online_shop_config set next_post_time=DATE_ADD(NOW(), INTERVAL ".$next." MINUTE)");
		$stmt->execute();
		$pdo_h->commit();
		echo "次は ".$next." 分後です";
	}catch(Exception $e){
		$pdo_h->rollBack();
		log_writer2("\$e",$e,"lv0");
		echo "次の投稿時間設定でエラー";
	}
	
}

exit();
?>