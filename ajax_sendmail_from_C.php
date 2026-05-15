<?php
//PGNAME:ajax_sendmail_from_C.php
//概要：通販サイトの問合せ、もしくはショップからの確認メールの回答をQA画面からメールで通知するためのAJAX処理。質問内容はDBにも保存される。
//パターンは2パターンあり
// 1. 受注管理画面のメール送信画面から出店者が質問をするパターン（BtoC）。
// 2. 通販サイトの問合せ画面からの質問に返信（CtoB）。
require "php_header.php";
register_shutdown_function('shutdown_ajax',basename(__FILE__));

$msg = "";                          //ユーザー向け処理結果メッセージ
$alert_status = "alert-warning";    //bootstrap alert class
$reseve_status=false;               //処理結果セット済みフラグ。
$timeout=false;                     //セッション切れ。ログイン画面に飛ばすフラグ
$sqllog="";
U::log("\$_POST",$_POST,4);

$rtn = csrf_checker(["index.php","","Q_and_A.php","",""],["P","C","S"]);
if($rtn !== true){
	$msg=$rtn;
	$alert_status = "alert-warning";
	$reseve_status = true;
}else{
		$CusMailAdd = $_POST["mailto"];
		$subject = $_POST["subject"];
		$uid = $_POST["shop_id"];
		$action = $_POST["action"];
		try{
			$db->begin_tran();
			//uidからusers_onlineの情報を取得
			$user = $db->SELECT("SELECT * from Users_online where `uid` = :uid", [":uid" => $uid]);
			$ShopMailAdd = $user[0]["mail"];
			$yagou = $user[0]["yagou"];
			$lineID = $user[0]["line_id"];

			//U::log("\$user", $user, 4);

			//問合せ番号の取得
			$sender = "customer";
			if($action==="toiawase"){
				//問合せNoの取得（同じ商品、またはショップへメールを出してないか確認。出していた場合は同じ問合せ番号を利用する）
				$rows = $db->SELECT("SELECT IFNULL(askNO,'') as askNO from online_q_and_a where shop_id = :shop_id and customer = :customer and shouhinNM = :shouhinNM"
					, [":shop_id" => $uid, ":customer" => $CusMailAdd, ":shouhinNM" => $subject]);
				if(!empty($rows)){
					$askNO = $rows[0]["askNO"];
				}else{
					//初回質問。問合せ番号を新規発行
					$askNO_rows = $db->SELECT("SELECT max(askNO) + 1 as nextNO from online_q_and_a", []);
					if(empty($askNO_rows) || $askNO_rows[0]["nextNO"] === null){
						$askNO = 1;
					}else{
						$askNO = $askNO_rows[0]["nextNO"];
					}
				}
			}else if($action==="kaitou"){
				$askNO = rot13decrypt2($_SESSION["askNO"]);
			}else{
				U::log("\$action", "elseやて", 4);
			}
			U::log("\$askNO", $askNO, 4);

			{//db登録
				$params["shop_id"] = $uid;
				$params["askNO"] = $askNO;
				$params["customer"] = $CusMailAdd;
				$params["name"] = $_POST["qa_name"];
				$params["shouhinNM"] = $subject;
				$params["sender"] = $sender;
				$params["body"] = $_POST["mailbody"];
				$params["insdate"] = date("Y-m-d H:i:s");
				$db->INSERT("online_q_and_a",$params);
			}

			//shop回答用のURL
			$URL = ROOT_URL."Q_and_A.php?askNO=".rot13encrypt2($askNO)."&sender=".rot13encrypt2("shop")."&key=".rot13encrypt2($uid);
			
			//メール送信
			$send_rtn = false;

			if($action==="toiawase"){//ショッピング画面からの問合せ（客⇒店）
				$head = $params["name"]." 様 よりお問い合わせがあります。\r\n"
						."\r\n".$URL."\r\nより回答してください。\r\n\r\n====以下、".$params["name"]." 様 より====\r\n\r\n";
			}else if($action==="kaitou"){//ショップからの確認に対する回答、に対する回答（店⇒客/2回目以降）
				$head = $params["name"]." 様 より回答がありました。追加でご確認したいことがございましたら\r\n".$URL."\r\nよりメッセージを入力して下さい。\r\n\r\n";
			}else{
				exit();//想定外
			}
			$send_rtn = U::send_mail($ShopMailAdd,$subject, $head.$params["body"]."\n\n※このメールは送信専用です。返信はメール上部のURLよりお願いします。※\n", APP_NAME,"");

			//お客へのCC
			$head = "下記内容にてメールを送信しました。\r\n========\r\n";
			$send_cc_rtn = U::send_mail($CusMailAdd,$subject,$head.$params["body"],APP_NAME,"");//お客へお知らせメール

			if($send_rtn===true && $send_cc_rtn === true){
				$msg = "送信完了";
				$alert_status = "alert-success";
				$db->commit_tran();
			}else{
				$msg = "送信失敗";
				$alert_status = "alert-warning";
				$db->rollback_tran("送信失敗のためロールバック");
			}

			$reseve_status=true;

		}catch(\Throwable $e){
			$db->Exception_rollback($e,"ショップから顧客への問合せメール送信で例外発生");

			$msg = "システムエラーによる更新失敗。管理者へ通知しました。";
			$alert_status = "alert-danger";
			$reseve_status=true;
			//U::log("\$e",$e,"lv0");
		}
	}

$token = csrf_create();

$return_sts = array(
	"MSG" => $msg
	,"status" => $alert_status
	,"csrf_create" => $token
	,"timeout" => $timeout
);
header('Content-type: application/json');
echo json_encode($return_sts, JSON_UNESCAPED_UNICODE);

exit();
?>