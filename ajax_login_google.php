<?php
declare(strict_types=1);
require "php_header_admin.php";

$msg = "";                          //ユーザー向け処理結果メッセージ
$status = false;								    //ログイン成否
$reseve_status=false;               //処理結果セット済みフラグ。
$timeout=false;                     //セッション切れ。ログイン画面に飛ばすフラグ
$sqllog="";
$uid_hash = "";
$token="";

log_writer2("\$_POST",$_POST,"lv3");

$uid_hash = (string)$_POST['user_hash'];
$gmail = (string)$_POST['mail'];
$password = (string)trim($_POST['pass'] ?? '');
$uid = rot13decrypt2($uid_hash);
$idToken = (string)trim($_POST['id_token'] ?? '');
$subid = (string)trim($_POST['subid'] ?? '');

$rtn = csrf_checker(["admin_login.php",""],["P","C","S"]);
$token = csrf_create();
if($rtn!==true){
	$msg = $rtn;
}else if(!empty($gmail) && !empty($subid)){//ログイン処理
	// 設定値
	//$clientId = 'YOUR_GOOGLE_CLIENT_ID.apps.googleusercontent.com';
	if (!$idToken) {
	  $msg = '認証失敗：GoogleのIDトークンがありません';
	}
	$client = new Google_Client(['client_id' => GOOGLE_AUTH]);
	$payload = $client->verifyIdToken($idToken);
	if ($payload) {
	}else{
		$msg = '認証失敗：アクセスが不正です';
		$return = array(
			"MSG" => $msg
			,"status" => "false"
			,"csrf_create" => $token
		);
		header('Content-type: application/json');  
		echo json_encode($return, JSON_UNESCAPED_UNICODE);
		exit();
	}
	
	//ユーザIDの存在確認
	if($_POST["login_type"]==="signup_with"){//新規の場合はIDのみで抽出
		$sql = "SELECT * FROM Users WHERE `uid` = :uid ";
		$row = $db->SELECT($sql, [":uid" => $uid]);
	}else{
		$sql = "SELECT * FROM Users WHERE `uid` = :uid AND mail = :mail";
		$row = $db->SELECT($sql, [":uid" => $uid,":mail" => $gmail]);
	}

	if(empty($row) ){
		//ログイン失敗
		$msg = "ログインに失敗しました。お客様のログインページのURLが不正です。";
	}else{
		//IDあり
		if($_POST["login_type"]==="signup_with"){//新規登録
			//Usersテーブルのパスワードを更新（メアドとパスワードを登録）
			try{
				$db->begin_tran();

				$sql="UPDATE Users SET `password`=:password, `mail`=:mail, `login_type` = :login_type WHERE `uid` = :uid";
				$db->UP_DEL_EXEC($sql,[":password" => $subid,":mail" => $gmail,":uid" => $uid,":login_type" => "google"]);
				
				$db->commit_tran();
	
				$status = true;
			}catch(Exception $e){
				$db->rollback_tran();
				log_writer2("\$e",$e,"lv0");
				$msg = "予期せぬエラーが発生しました。";
			}
		}else if($_POST["login_type"]==="signin_with" && hash_equals($row[0]['password'],$subid)){//サインインかつグーグル識別子IDが一致
			//ログイン成功
			$status = true;
		}else{
			//ログイン失敗
			log_writer2("\$row[0]['mail']",$row[0]['mail'],"lv3");
			log_writer2("\$gmail",$gmail,"lv3");
			log_writer2("\$row[0]['password']",$row[0]['password'],"lv3");
			$msg = "ログインに失敗しました。Googleアカウントとログイン画面ユーザーが不一致です。";
		}
	}
}else{
	$msg = "ログインに失敗しました。";
}

if($status===true){
	//自動ログイン用の情報を登録
	
	setCookie("remember_me", $uid.":".$token, time()+60*60*24*2, "/", "", TRUE, TRUE);

	// トークンをハッシュ化（DB保存用）
	$hashed_token = hash('sha256', $token);
	
	// 有効期限を設定（1週間後）
	$expiry_date = date('Y-m-d H:i:s', strtotime('+1 week'));
	log_writer2("\$_SESSION['user_id']",$uid,"lv3");
	
	// 有効期限切れのレコードを削除
	$stmt = $pdo_h->prepare("DELETE FROM AUTO_LOGIN_SHOP WHERE YUKOU_KIGEN < NOW() ");
	$stmt->execute();
	// DBに保存
	$stmt = $pdo_h->prepare("INSERT INTO AUTO_LOGIN_SHOP (UID, TOKEN, YUKOU_KIGEN) VALUES (?, ?, ?)");
	$stmt->execute([$uid, $hashed_token, $expiry_date]);
}

$return = array(
	"MSG" => $msg
	,"status" => ($status ? "success" : "false")
	,"csrf_create" => $token
);
header('Content-type: application/json');  
echo json_encode($return, JSON_UNESCAPED_UNICODE);
exit();
?>