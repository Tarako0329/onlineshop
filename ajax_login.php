<?php
//log_writer2(basename(__FILE__)."",$sql,"lv3");
require "php_header_admin.php";

$msg = "";                          //ユーザー向け処理結果メッセージ
$status = false;								    //ログイン成否
$reseve_status=false;               //処理結果セット済みフラグ。
$timeout=false;                     //セッション切れ。ログイン画面に飛ばすフラグ
$sqllog="";
$uid_hash = "";
$token="";

log_writer2("\$_POST",$_POST,"lv3");
log_writer2("\$_SESSION['user_id']",$_SESSION["user_id"],"lv3");

$uid_hash = rot13encrypt2($_SESSION["user_id"]);

$rtn = csrf_checker(["admin_login.php",""],["P","C","S"]);
if($rtn!==true){
	$msg = $rtn;
}else if(!empty($_POST["mail"]) && !empty($_POST["pass"])){
	//ログイン処理
	
	$username = $_POST['mail'];
	$password = trim($_POST['pass'] ?? '');
	

	//ユーザIDの存在確認
	if($_POST["login_type"]==="signup_with"){//新規の場合はIDのみで抽出
		$sql = "SELECT * FROM Users WHERE `uid` = :uid ";
		$row = $db->SELECT($sql, [":uid" => $_SESSION["user_id"]]);
	}else{
		$sql = "SELECT * FROM Users WHERE `uid` = :uid AND mail = :mail";
		$row = $db->SELECT($sql, [":uid" => $_SESSION["user_id"],":mail" => $username]);
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

				$params["password"] = passEx($password);
				$params["mail"] = $username;
				$params["uid"] = $_SESSION["user_id"];
				$params["login_type"] = $_POST["shubetu"];

				$sql="UPDATE Users SET `password_onlineshop`=:password, `mail`=:mail, login_type = :login_type WHERE `uid` = :uid";
				$db->UP_DEL_EXEC($sql,[":password" => $params["password"],":mail" => $params["mail"],":uid" => $params["uid"],":login_type" => $params["login_type"]]);
				
				$db->commit_tran();
	
				$status = true;
				//log_writer2("\$pass_hashed",$params["password"],"lv3");
			}catch(Exception $e){
				$db->rollback_tran();
				log_writer2("\$e",$e,"lv0");
				$msg = "予期せぬエラーが発生しました。";
			}
		}else if($_POST["login_type"]==="signin_with" && verifyPassword($password, $row[0]['password_onlineshop']) && $row[0]['mail'] === $username){//サインイン
			//ログイン成功
			$status = true;
		
		}else{
			//ログイン失敗
			log_writer2("\$_POST['login_type']",$_POST["login_type"],"lv3");
			log_writer2("\$row[0]['mail']",$row[0]['mail'],"lv3");
			log_writer2("\$username",$username,"lv3");
			log_writer2("\$row[0]['password_onlineshop']",$row[0]['password_onlineshop'],"lv3");
			log_writer2("\$password",$password,"lv3");
			log_writer2("password_verify",verifyPassword($password, $row[0]['password_onlineshop']),"lv3");
			$msg = "ログインに失敗しました。メールアドレスまたはパスワードを確認してください。";
		}
	}
}

$_SESSION["e-msg"] = $msg;

if($status===true){
	//自動ログイン用の情報を登録
	$token = csrf_create();
	setCookie("remember_me", $_SESSION["user_id"].":".$token, time()+60*60*24*2, "/", "", TRUE, TRUE);

	// トークンをハッシュ化（DB保存用）
	$hashed_token = hash('sha256', $token);
	
	// 有効期限を設定（1週間後）
	$expiry_date = date('Y-m-d H:i:s', strtotime('+1 week'));
	log_writer2("\$_SESSION['user_id']",$_SESSION["user_id"],"lv3");
	
	// DBに保存(デリイン)
	$stmt = $pdo_h->prepare("DELETE FROM AUTO_LOGIN_SHOP WHERE UID = ?");
	$stmt->execute([$_SESSION["user_id"]]);

	$stmt = $pdo_h->prepare("INSERT INTO AUTO_LOGIN_SHOP (UID, TOKEN, YUKOU_KIGEN) VALUES (?, ?, ?)");
	$stmt->execute([$_SESSION["user_id"], $hashed_token, $expiry_date]);
}

if($_POST["shubetu"]==="google"){
	$return = array(
		"MSG" => $msg
		,"status" => ($status ? "success" : "false")
		,"csrf_create" => $token
	);
  header('Content-type: application/json');  
  echo json_encode($return, JSON_UNESCAPED_UNICODE);
	exit();
}else if($_POST["shubetu"]==="IPASS"){
	if($status===true){
		header('Location: admin_menu.php?key='.$uid_hash);
		exit();
	}else{
		header('Location: admin_login.php?key='.$uid_hash);
		exit();
	}
}

exit();
?>