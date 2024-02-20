<?php
require "php_header.php";

if ($login_type==="auto") {
    header("HTTP/1.1 301 Moved Permanently");
    header("Location: logincheck.php");
}

if(isset($_SESSION["EMSG"])){
    $errmsg="<div style='color:red'>".$_SESSION["EMSG"]."</div>";
    //一度エラーを表示したらクリアする
    $_SESSION["EMSG"]="";
}
$csrf = csrf_create();
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <?php 
    //共通部分、bootstrap設定、フォントCND、ファビコン等
    include "head_bs5.php" 
    ?>
    <!--ページ専用CSS-->
    <link rel="stylesheet" href="css/style_index.css?<?php echo $time; ?>" >
    <TITLE><?php echo TITLE;?></TITLE>
</head>
<header  class="header-color common_header" style="flex-wrap:wrap">
    <div class="title" style="width: 100%;"><a href="index.php" ><?php echo TITLE;?></a></div>
</header>
<body class='common_body'>
    <div class="container">
        <div class="card card-container">
            <?php echo $errmsg; ?>
            <form class="form-signin" id="form1" method="post" action="logincheck.php">
                <span id="reauth-email" class="reauth-email"></span>
                <input type="email" id="inputEmail" class="form-control" placeholder="Email address" name="LOGIN_EMAIL" required autofocus value=''>
                <input type="password" id="inputPassword" class="form-control" name="LOGIN_PASS" placeholder="Password" required value=''>
                <div id="remember" class="checkbox">
                    <label>
                        <input type="checkbox" name="AUTOLOGIN" checked> AUTOLOGIN 
                    </label>
                </div>

                <button class="btn btn-lg btn-primary btn-block btn-signin" type="submit"  >ロ グ イ ン</button>
                <input type="hidden" name="csrf_token" value="<?php echo $csrf; ?>">
            </form><!-- /form -->
            <a href="forget_pass_sendurl.php" class="forgot-password">
                ﾊﾟｽﾜｰﾄﾞを忘れたらｸﾘｯｸ
            </a>
            <hr>
            <a href="pre_account.php" class="btn btn-lg btn-primary btn-block btn-signin" style="padding-top:8px" >新 規 登 録</a>
        </div><!-- /card-container -->
    </div><!-- /container -->    
    <script>
        window.onload = function() {
            // Enterキーが押された時にSubmitされるのを抑制する
            document.getElementById("form1").onkeypress = (e) => {
                // form1に入力されたキーを取得
                const key = e.keyCode || e.charCode || 0;
                // 13はEnterキーのキーコード
                if (key == 13) {
                    // アクションを行わない
                    e.preventDefault();
                }
            }    

        };    
    </script>
</body>
</html>
