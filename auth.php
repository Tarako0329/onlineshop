<?php
require_once "classes/Database.php";
$db = $db ?? new Database();

// 1. すでにセッションがあり、GetKeyのUIDと等しい場合は何もしない
if (isset($_SESSION['user_id']) && $_SESSION['user_id'] === $key_user) {
    // ログイン済みなので処理終了
    log_writer2("","セッションあるよ！","lv3");
    return;
}

log_writer2("","セッション切れた。","lv3");

// 2. Cookieがあるか確認
$cookie = $_COOKIE['remember_me'] ?? null;

if ($cookie) {
    $parts = explode(':', $cookie);
    if (count($parts) === 2) {
        [$selector, $validator] = $parts;
        
        log_writer2("\$selector",$selector,"lv3");
        log_writer2("rot13decrypt2(\$user_hash)",rot13decrypt2($user_hash),"lv3");

        if($selector === rot13decrypt2($user_hash)){//URLのkeyとクッキーのkeyが一致するか確認
            // DBからselectorで検索
            $sql = "SELECT * FROM AUTO_LOGIN_SHOP WHERE UID = :uid AND YUKOU_KIGEN > NOW()";
            $tokenRecords = $db->SELECT($sql, [":uid" => $selector]);

            $login_success = false;
            foreach ($tokenRecords as $tokenRecord) {
                // Cookie(validator)が、DB内のどれか1つとでも合致するか？
                if (hash_equals($tokenRecord['TOKEN'], hash('sha256', $validator))) {
                    $login_success = true;
                    $active_seq = $tokenRecord['SEQ']; // どの端末のレコードか特定
                    log_writer2("","トークン発見","lv3");
                    break;
                }else{
                    log_writer2("","トークンない","lv3");
                    log_writer2("\$tokenRecord['TOKEN']",$tokenRecord['TOKEN'],"lv3");
                    log_writer2("hash('sha256', \$validator)",hash('sha256', $validator),"lv3");
                }
            }

            if ($login_success) {
                // ログイン成功
                $_SESSION['user_id'] = $selector;
                log_writer2("","ログイン成功","lv3");
                log_writer2("\$_SESSION['user_id']",$selector,"lv3");
                
                // トークンのリフレッシュ（セキュリティ向上のため、使用するたびに新しいトークンを発行）
                $newToken = get_token();
                setCookie("remember_me", $selector.":".$newToken, time()+60*60*24*7, "/", "", TRUE, TRUE);
                log_writer2("","トークンリフレッシュ","lv3");

                $hashedToken = hash('sha256', $newToken);
                $expiryDate = date('Y-m-d H:i:s', strtotime('+1 week'));

                $sql = "UPDATE AUTO_LOGIN_SHOP SET TOKEN = :token, YUKOU_KIGEN = :kigen WHERE SEQ = :seq";
                $db->UP_DEL_EXEC($sql, [
                    ':token' => $hashedToken,
                    ':kigen' => $expiryDate,
                    ':seq'   => $active_seq
                ]);

                return; // ログイン成功、呼び出し元へ戻る
            } else {
                // 無効なCookieの場合は削除
                setCookie("remember_me", "", -1, "/");
                log_writer2("","一致するクッキーない","lv3");
                log_writer2("\$tokenRecord['TOKEN']",$tokenRecord['TOKEN'],"lv3");
                log_writer2("hash('sha256', \$validator)",hash('sha256', $validator),"lv3");
            }
        }else{
            log_writer2("","URLのkeyとクッキーのkeyが一致しない","lv3");
            log_writer2("\$selector['TOKEN']",$selector,"lv3");
            log_writer2("rot13decrypt2(\$user_hash)",rot13decrypt2($user_hash),"lv3");
        }
    }
}else{
    log_writer2("","クッキーない","lv3");
}

// 4. セッションも有効なCookieもない場合はログイン画面へ
$_SESSION['e-msg'] = '自動ログインの有効期限切れです。再ログインしてください';
header("Location: admin_login.php?key=".$user_hash);
exit;