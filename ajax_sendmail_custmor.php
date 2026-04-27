<?php
require "php_header.php";
register_shutdown_function('shutdown_ajax',basename(__FILE__));

$msg = "";                          //ユーザー向け処理結果メッセージ
$alert_status = "alert-warning";    //bootstrap alert class
$reseve_status=false;               //処理結果セット済みフラグ。
$timeout=false;                     //セッション切れ。ログイン画面に飛ばすフラグ
$sqllog="";
log_writer2("\$_POST",$_POST,"lv3");

$rtn = csrf_checker(["order_management.php","index.php","Q_and_A.php","order_rireki.php",""],["P","C","S"]);
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

		$CusMailAdd = $_POST["mailto"];
		try{
			$db->begin_tran();
			//uidからusers_onlineの情報を取得
			$user = $db->SELECT("SELECT * from Users_online where `uid` = :uid", [":uid" => $_POST["shop_id"]]);
			$ShopMailAdd = $user[0]["mail"];
			$yagou = $user[0]["yagou"];
			$lineID = $user[0]["line_id"];
			
			//QA管理画面から来た場合はSESSIONに値を持つ
			$firstQ = false;    //初回質問フラグ
			if($_POST["sts"]==="session"){
				$sts = rot13decrypt2($_SESSION["sts"]); //Q or A
				$askNO = rot13decrypt2($_SESSION["askNO"]);
			}else{
				$sts = $_POST["sts"];
				//問合せNoの取得（同一ユーザが同じ対象に問合せした場合に同じNoを利用する.shopID,返信先メアド,Subjectで判断）
				$rows = $db->SELECT("SELECT IFNULL(askNO,'') as askNO from online_q_and_a where shop_id = :shop_id and customer = :customer and shouhinNM = :shouhinNM", [":shop_id" => $_POST["shop_id"], ":customer" => $CusMailAdd, ":shouhinNM" => $_POST["qa_head"]]);
				if(!empty($rows)){
					$askNO = $rows[0]["askNO"];
				}else{
					//初回質問。問合せ番号を新規発行
					$firstQ = true;
					$askNO_rows = $db->SELECT("SELECT max(askNO) + 1 as nextNO from online_q_and_a", []);
					if(empty($askNO_rows) || $askNO_rows[0]["nextNO"] === null){
						$askNO = 1;
					}else{
						$askNO = $askNO_rows[0]["nextNO"];
					}
				}
			}

			{//db登録
				$params["shop_id"] = $_POST["shop_id"];
				$params["askNO"] = $askNO;
				$params["customer"] = $CusMailAdd;
				$params["name"] = $_POST["qa_name"];
				$params["shouhinNM"] = $_POST["qa_head"];
				$params["sts"] = $sts;
				$params["body"] = $_POST["qa_text"];
				$params["insdate"] = date("Y-m-d H:i:s");
				$db->INSERT("online_q_and_a",$params);
			}

			//CtoBで始まるやり取り
			$Q_URL = ROOT_URL."Q_and_A.php?askNO=".rot13encrypt2($askNO)."&QA=".rot13encrypt2("Q");
			$A_URL = ROOT_URL."Q_and_A.php?askNO=".rot13encrypt2($askNO)."&QA=".rot13encrypt2("A")."&key=".rot13encrypt2($_POST["shop_id"]);
			//BtoCで始まるやり取り
			$BQ_URL = ROOT_URL."Q_and_A.php?askNO=".rot13encrypt2($askNO)."&QA=".rot13encrypt2("BQ")."&key=".rot13encrypt2($_POST["shop_id"]);;
			$CA_URL = ROOT_URL."Q_and_A.php?askNO=".rot13encrypt2($askNO)."&QA=".rot13encrypt2("CA");
			
			//メール送信
			$send_rtn = false;

			$cc_address = "";//メールの送信者へのCC
			if($sts==="Q"){//通販画面QAのQuestion（客⇒店）
				$head = (($firstQ)
						?"お客様よりお問い合わせがありました。\r\n"
						:"お客様より返信がありました。\r\n")
						."".$A_URL."\r\nより回答をお願いします\r\n\r\n====以下、お客様より====\r\n\r\n";

				if(U::exist($lineID)){
					$ShopMailAdd = "LINE";
					$send_rtn = U::send_line($lineID,$head."【".$_POST["subject"]."】\r\n".$_POST["mailbody"]);//出店者へお知らせLINE
				}else{
					$send_rtn = U::send_mail($ShopMailAdd,$_POST["subject"],$head.$_POST["mailbody"],APP_NAME,"");//出店者へお知らせメール
				}
				log_writer2("to出店者 - U::send_mail() \$send_rtn","[".$ShopMailAdd."] Q send ".$send_rtn,"lv3");
				$cc_address = $CusMailAdd;
			}else if($sts==="A"){//通販画面QAのanswer（店⇒客）
				$head = $yagou." より回答がありました。追加でご確認したいことがございましたら\r\n".$Q_URL."\r\nよりメッセージを入力して下さい。\r\n\r\n";
				$send_rtn = U::send_mail($CusMailAdd,$_POST["subject"],$head.$_POST["mailbody"],APP_NAME,"");//客向け回答メール
				$cc_address = "shop";


			}else if($sts==="BQ"){//受注管理画面のお客様向け質問（店⇒客）
				$head = (($firstQ)
						?$yagou." よりお問い合わせがありました。\r\n"
						:$yagou." より返信がありました。\r\n")
						."ご回答いただく場合は\r\n".$CA_URL."\r\nよりお願いします\r\n\r\n====以下、".$yagou." より====\r\n\r\n";
				$send_rtn = U::send_mail($CusMailAdd,$_POST["subject"],$head.$_POST["mailbody"],APP_NAME,"");//客向け回答メール
				$cc_address = "shop";
			}else if($sts==="CA"){//受注管理画面のお客様からの返信（客⇒店）
				$head = $_POST["qa_name"]." より回答がありました。追加でご確認したいことがございましたら\r\n".$BQ_URL."\r\nよりメッセージを入力して下さい。\r\n\r\n";
				if(U::exist($lineID)){
					$ShopMailAdd = "LINE";
					$send_rtn = U::send_line($lineID,$head."【".$_POST["subject"]."】\r\n".$_POST["mailbody"]);//出店者へお知らせLINE
				}else{
					$send_rtn = U::send_mail($ShopMailAdd,$_POST["subject"],$head.$_POST["mailbody"],APP_NAME,"");//出店者へお知らせメール
				}
				log_writer2("to出店者 - U::send_mail() \$send_rtn","[".$ShopMailAdd."] CA send ".$send_rtn,"lv3");
				$cc_address = $CusMailAdd;
			}else{
				exit();//想定外
			}

			//送信者へのCC
			$head = "下記内容にてメールを送信しました。\r\n========\r\n";
			if($cc_address==="shop"){
				if(U::exist($lineID)){
					$ShopMailAdd = "LINE";
					$send_cc_rtn = U::send_line($lineID,$head."【".$_POST["subject"]."】\r\n".$_POST["mailbody"]);//出店者へお知らせLINE
				}else{
					$send_cc_rtn = U::send_mail($ShopMailAdd,$_POST["subject"],$head.$_POST["mailbody"],APP_NAME,"");//出店者へお知らせメール
				}
			}else{
				$send_cc_rtn = U::send_mail($cc_address,$_POST["subject"],$head.$_POST["mailbody"],APP_NAME,"");//客向け回答メール
			}

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
			$db->Exception_rollback($e);

			$msg = "システムエラーによる更新失敗。管理者へ通知しました。";
			$alert_status = "alert-danger";
			$reseve_status=true;
			//log_writer2("\$e",$e,"lv0");
		}
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