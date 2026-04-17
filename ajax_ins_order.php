<?php
//log_writer2(basename(__FILE__)."",$sql,"lv3");
require "php_header.php";
register_shutdown_function('shutdown_ajax',basename(__FILE__));

$msg = "";                          //ユーザー向け処理結果メッセージ
$alert_status = "alert-warning";    //bootstrap alert class
$reseve_status=false;               //処理結果セット済みフラグ。
$timeout=false;                     //セッション切れ。ログイン画面に飛ばすフラグ
$sqllog="";

//log_writer2("\$_POST",$_POST,"lv3");

$rtn = csrf_checker(["index.php",""],["P","C","S"]);
if($rtn !== true){
	$msg=$rtn;
	$alert_status = "alert-warning";
	$reseve_status = true;
}else{

	$owner = $db->SELECT("SELECT * from Users_online where `uid` = :uid",["uid" => $_POST["order_shop_id"]]);
	//log_writer2("\$owner",$owner,"lv3");
	if(count($owner)===0){
		$msg="ショップIDが存在してません。システム不具合のため、管理者に通知いたしました。オーダーは無効となります。";
		$alert_status = "alert-danger";
		$post_id = $_POST['order_shop_id'] ?? "NULL";
		log_writer2("","オーダー登録処理でエラー。ポストされたショップIDがUsers_onlineに存在しません。(\$_POST['order_shop_id'] = $post_id)","lv0");
		$reseve_status = true;
	}else{
		try{
			$db->begin_tran();
			$datetime = date('Y-m-d H:i:s');
			$params["uid"] = $_POST["order_shop_id"];
			$params["orderNO"] = substr("0000000".((string)rand(0,99999999)),-8);
			$params["name"] = $_POST["name"];
			$params["yubin"] = $_POST["yubin"];
			$params["jusho"] = $_POST["jusho"];
			$params["tel"] 		= $_POST["tel"] ?? "";
			$params["mail"]		= $_POST["mail"] ?? "";
			$params["bikou"]	= $_POST["bikou"] ?? "";
			$params["st_name"] = $_POST["st_name"];
			$params["st_yubin"] = $_POST["st_yubin"];
			$params["st_jusho"] = $_POST["st_jusho"];
			$params["st_tel"] = $_POST["st_tel"];
			$params["buy_trigger"] = $_POST["buy_trigger"];
			$params["mark_id"] = rot13decrypt2($_SESSION["mark_id"]);
			$params["juchuu_date"] = $datetime;
			
			//通知メール用
			$head_bikou = $params["bikou"];
			//受注ヘッダ登録

			//オーダー番号作成
			$maxTries = 10000;
			$i = 0;
			while(true){
				try{
					$db->INSERT("juchuu_head",$params);
					break;
				}catch(Exception $e){
					// 重複エラー(23000)ならリトライ、それ以外は外側に投げる
					if ($e->getCode() === '23000' && $i < $maxTries - 1) {
						$params["orderNO"] = substr("0000000".((string)rand(0,99999999)),-8);
						log_writer2("\$params['orderNO']",$params["orderNO"],"lv3");
						$i++;
						continue; 
					}
					if($i < $maxTries - 1){$msg = "オーダーNOの採番枠をオーバーしました。";}

					throw $e; // 重大なエラー（接続切れ等）やリトライ限界なら外側のcatchへ
				}
				break;
			}

			//明細登録
			$orderlist="【ご注文内容】\r\n";
			foreach($_POST["meisai"] as $row){
				//log_writer2("\$row",$row,"lv3");
				$params_meisai["orderNO"] = $params["orderNO"];
				$params_meisai["shouhinCD"] = $row["shouhinCD"];
				$params_meisai["shouhinNM"] = $row["shouhinNM"];
				$params_meisai["su"] = $row["su"];
				$params_meisai["tanka"] = $row["tanka"];
				$params_meisai["goukeitanka"] = $row["goukeitanka"];
				$params_meisai["zeikbn"] = $row["zeikbn"];
				$params_meisai["bikou"] = $row["bikou"];
				$params_meisai["upd_datetime"] = $datetime;

				$db->INSERT("juchuu_meisai",$params_meisai);
				
				$orderlist .= "◆".$params_meisai["shouhinNM"]."\r\n".$row["short_info"]."\r\n価格( ".return_num_disp($params_meisai["tanka"])." 円) x ".$params_meisai["su"]."(コ) = 合計 ".return_num_disp($params_meisai["goukeitanka"])." 円(税抜)\n\r備考：".$params_meisai["bikou"]."\r\n\r\n";
			}

			//消費税明細の登録
			$sqlstr = "INSERT into juchuu_meisai SELECT null as SEQ, orderNO,JM.zeikbn as shouhinCD,ZMS.hyoujimei,0 as su,0 as tanka,0 as goukeitanka,
				FLOOR(sum(goukeitanka) * ZMS.zeiritu / 100) as zei ,JM.zeikbn,'-' as bikou ,:upd_datetime as upd_datetime 
				from juchuu_meisai JM inner join ZeiMS ZMS on JM.zeikbn = ZMS.zeiKBN where orderNO = :orderNO group by orderNO,ZMS.hyoujimei,JM.zeikbn,'-' having zei <> 0";
			$db->UP_DEL_EXEC($sqlstr,["orderNO" => $params["orderNO"], "upd_datetime" => $datetime]);
			

			//メールの作成
			{
				$sql = "SELECT orderNO,CAST(sum(goukeitanka) as char) + 0 as soutanka,CAST(sum(zei) as char) + 0 as souzei,CAST(sum(goukeitanka + zei) as char) + 0 as zeikomisou from juchuu_meisai where orderNO = :orderNO group by orderNO";
				$orderlist2 = $db->SELECT($sql,["orderNO" => $params["orderNO"]]);
				
				//log_writer2("\$owner[mail]",$owner[0]["mail"],"lv3");
				//log_writer2("\$params[mail]",$params["mail"],"lv3");
				$orderNO = $params['orderNO'];
				$name = $params['name'];
				$yubin = $params['yubin'];
				$jusho = $params['jusho'];
				$tel = $params['tel'];
				$mail = $params['mail'];
				$st_name = $params['st_name'];
				$st_yubin = $params['st_yubin'];
				$st_jusho = $params['st_jusho'];
				$st_tel = $params['st_tel'];
				$goukeitanka = return_num_disp($orderlist2[0]["soutanka"]);
				$goukeizei = return_num_disp($orderlist2[0]["souzei"]);
				$sougaku = return_num_disp($orderlist2[0]["zeikomisou"]);

				//ショップオーナー向けメール
				$body = <<< "EOM"
					$name 様よりご注文いただきました。

					【ご注文内容】
					$orderlist

					ご注文総額：$sougaku  内税($goukeizei)

					【ご注文主】
					$name
					$yubin
					$jusho
					$tel
					$mail
					オーダー備考：
					$head_bikou

					【お届け先】(表示がない場合は同上)
					$st_name
					$st_yubin
					$st_jusho
					$st_tel
				EOM;
				
				if(U::exist($owner[0]["line_id"])){//LINEで通知
					$rtn = U::send_line($owner[0]["line_id"],"オーダー受注通知[No:".$orderNO."]\r\n".$body);
				}else if(U::exist($owner[0]["mail"])){
					$rtn = U::send_mail($owner[0]["mail"],"オーダー受注通知[No:".$orderNO."]",$body,APP_NAME." onLineShop",$owner[0]["mail"]);
				}
				if($rtn === false){
					log_writer2("U::send_mail \$rtn","ショップへの注文メールの送信に失敗しました。","lv3");
					throw new Exception("ショップへの注文メールの送信に失敗しました。");
				}

				//お客様向けメール 
				{
					$title = APP_NAME;
					
					$body = $owner[0]["mail_body_auto"];
					$body = str_replace("<購入者名>",$name,$body);
					$body = str_replace("<注文内容>",$orderlist."ご注文総額：".$sougaku."  内税(".$goukeizei.")",$body);
					$body = str_replace("<送料込の注文内容>",$orderlist,$body);
					$body = str_replace("<購入者情報>","【ご注文主】\r\nお名前：".$name."\r\n郵便番号：".$yubin."\r\n住所：".$jusho."\r\nTEL：".$tel."\r\nMAIL：".$mail."\r\nオーダー備考：\r\n".$head_bikou.'',$body);
					$body = str_replace("<届け先情報>","【お届け先】\r\nお名前：".$st_name."\r\n郵便番号：".$st_yubin."\r\n送付先住所：".$st_jusho."\r\nTEL：".$st_tel.'',$body);
					$body = str_replace("<自社名>",$owner[0]["yagou"],$body);
					$body = str_replace("<自社住所>",$owner[0]["jusho"],$body);
					$body = str_replace("<問合せ受付TEL>",$owner[0]["tel"],$body);
					$body = str_replace("<問合せ受付MAIL>",$owner[0]["mail"],$body);
					$body = str_replace("<問合担当者>",$owner[0]["name"],$body);
					$body = str_replace("<代表者>",$owner[0]["shacho"],$body);
					
					$rtn = U::send_mail($params["mail"],"注文内容ご確認（自動配信メール）[No:".$orderNO."]",$body,APP_NAME." onLineShop",$owner[0]["cc_mail"]);
					if($rtn === false){
						log_writer2("U::send_mail \$rtn",$rtn,"lv3");
						throw new Exception("注文者向けの注文内容確認メールの送信に失敗しました。");
					}
				}
			}

			$db->commit_tran();
	
			$msg = "登録が完了しました。";
			$alert_status = "alert-success";
			$reseve_status=true;

		}catch(Exception $e){
			$db->rollback_tran($e->getMessage());
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
	,"csrf_create" => $token
	,"timeout" => $timeout
	,"orderNO" => $params["orderNO"]
);
header('Content-type: application/json');
echo json_encode($return_sts, JSON_UNESCAPED_UNICODE);

exit();

?>