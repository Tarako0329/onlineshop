<?php
  require "php_header_admin.php";

  $user_hash = $_GET["key"] ;
  $_SESSION["user_id"] = rot13decrypt2($user_hash);
//  if(empty($_GET["key"])){
  if(empty(rot13decrypt2($user_hash))){
    //echo "参照用のURLが異なります。";
    //exit();
  }
	$g_login = "signin_with";
	$login = false;

	//Users_onlineテーブルのuidと$_SESSION["user_id"]が等しい１レコードを取得し、$Yagou に yagouの項目値をセットする。
	$sql = "SELECT UO.*,US.mail,US.password FROM Users_online UO INNER JOIN Users US ON UO.uid = US.uid WHERE UO.uid =:uid";
	$stmt = $pdo_h->prepare($sql);
	$stmt->bindValue("uid", $_SESSION["user_id"], PDO::PARAM_STR);
	$stmt->execute();
	$row = $stmt->fetchAll(PDO::FETCH_ASSOC);
	log_writer2("admin_menu.php start","","lv3");
	//log_writer2("\$row",$row,"lv3");
	log_writer2("\$_SESSION['user_id']",$_SESSION["user_id"],"lv3");
	
	$Yagou = $row[0]["yagou"];
	$mail = $row[0]["mail"];
	$password = $row[0]["password"];

	if($mail==="-" && $password==="-"){
		$g_login = "signup_with";
		$btn_text = "新規登録";
	}else{
		$g_login = "signin_with";
		$btn_text = "ログイン";
	}

	$token = csrf_create();
	$errmsg = $_SESSION["e-msg"];
	$_SESSION["e-msg"] = "";

?>
<!DOCTYPE html>
<html lang='ja'>
<head>
    <?php 
    //共通部分、bootstrap設定、フォントCND、ファビコン等
    include "head_admin.php" 
    ?>
    <TITLE><?php echo TITLE;?></TITLE>
		<style>
			.card-container.card {
			    max-width: 350px;
			    padding: 40px 40px;
			}
			.card {
			    background-color: #F7F7F7;
			    /* just in case there no content*/
			    padding: 20px 25px 30px;
			    margin: 0 auto 25px;
			    margin-top: 50px;
			    /* shadows and rounded borders */
			    -moz-border-radius: 2px;
			    -webkit-border-radius: 2px;
			    border-radius: 2px;
			    -moz-box-shadow: 0px 2px 2px rgba(0, 0, 0, 0.3);
			    -webkit-box-shadow: 0px 2px 2px rgba(0, 0, 0, 0.3);
			    box-shadow: 0px 2px 2px rgba(0, 0, 0, 0.3);
			}
		</style>
</head>
<BODY>
  <div id='admin_menu'>
		<?php include "header_tag_admin.php"  ?>
  <!--<HEADER class='common_header'>
    <nav class="navbar navbar-expand-xl bg-body-tertiary fixed-top" style='padding:0;'>
      <div class="container common_header">
        <img src="img/android-chrome-48x48.png" alt="Logo" width="48" height="48" class="d-inline-block align-text-top">
        <a class="navbar-brand alice-regular" href="admin_menu.php?key=<?php //echo $user_hash;?>"><h1>管理メニュー【ログイン】</h1></a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
          <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse ms-5" id="navbarNav" >
          <ul class="navbar-nav alice-regular">
          </ul>
        </div>
      </div>
    </nav>
  </HEADER>-->

  <MAIN class='container common_main'>
	<?php
		if (!empty($errmsg)) {
			echo "<div class='alert alert-danger text-center'>".$errmsg."</div>";
		}
	?>
	  <div class="card card-container">
			<form class="form-signin" id="form1" method="post" action="ajax_login.php">
				<div class="row mb-3">
					<div class='col-12 text-center' style='height:50px;border-radius: 50%;'>
						<div style='height:48px;width:48px;border-radius: 50%;background-color:red;margin:auto;'>
							<img style='border-radius: 50%;' src='img/android-chrome-48x48.png'></img>
						</div>
					</div>
				</div> 
				<div class="row mb-2">
					<div class="col-12">
						<p>ようこそ　『<?php echo $Yagou;?>』　様</p>
					</div>
				</div>
				<div class="input-group mb-1">
  				<input type="text" class="form-control" name="mail" placeholder="Input your E-mail" aria-label="Input your E-mail" aria-describedby="button-addon2" required>
				</div>
				<div class="input-group mb-1">
  				<input type="text" class="form-control" name="pass" placeholder="Input your Password" aria-label="Input your Password" aria-describedby="button-addon2" required>
  				<button class="btn btn-outline-primary" type="submit" id="button-addon2" name="login_type" value="<?php echo $g_login;?>"><?php echo $btn_text;?></button>
				</div>
				<a href="forget_pass_sendurl.php" class="forgot-password">ﾊﾟｽﾜｰﾄﾞを忘れたらｸﾘｯｸ</a>

				<input type="hidden" name="csrf_token" value="<?php echo $token;?>">
				<input type="hidden" name="shubetu" value="IPASS">
			</form><!-- /form -->

				<p> OR </p>

				<div class="g_id_signin " style='width:268px;margin:auto;'
				data-type="standard"
				data-size="large"
				data-theme="outline"
				data-text="<?php echo $g_login;?>"
				data-shape="rectangular"
				data-logo_alignment="left">
			</div>
			<div id="g_id_onload"
				data-client_id="<?php echo GOOGLE_AUTH;?>"
				data-callback="handleCredentialResponse"
				data-auto_prompt="false">
			</div>
			<hr>
			<div class='row'>
				<div class='col-12 ps-3 pe-3 text-center fs-5'>
					<div class='text-center mb-2 fs-5'>
						<a href="admin_pbPolicy.php?key=<?php echo $user_hash;?>">＜プライバシーポリシー＞</a>
					</div>
					<div class='text-center mb-2 fs-5'>
						<a href="admin_kiyaku.php?key=<?php echo $user_hash;?>">＜利用規約＞</a>
					</div>
				</div>
			</div>

			<form style='display:none;' id="form2" method="post" action="logincheck.php">
				<input type='hidden' name='login_type' value='google'>
				<input type='hidden' id='sub_id' name='sub_id'>
				<input type='hidden' id='token' name='token'>
				<input type='hidden' id='uid' name='uid'>
				<input type='hidden' id='AUTOLOGIN2' name='AUTOLOGIN'>
				<input type='submit' id='form2_submit'>
			</form>

		</div><!-- /card-container -->
  </MAIN>
  <FOOTER class='container-fluid common_footer'>
  </FOOTER>
  </div>
  <script src="script/admin_menu.js?<?php echo $time; ?>"></script>
  <script>
    admin_menu('admin_login.php','','<?php echo $user_hash;?>').mount('#admin_menu');
  </script>
  <script>
    	function handleCredentialResponse(response) {//googleログインでパスワードが認証されたときのみ、コールされる
  			// decodeJwtResponse() is a custom function defined by you
  			// to decode the credential response.
  			const responsePayload = decodeJwtResponse(response.credential);
				
  			console_log(response);
  			console_log(responsePayload);
				
				const form = new FormData()
				form.append("pass",responsePayload.sub)	//googleのユーザID
				form.append("mail",responsePayload.email)
				form.append("csrf_token","<?php echo $token;?>")
				form.append("login_type","<?php echo $g_login;?>")
				form.append("shubetu","google")
				axios.post('ajax_login.php',form, {headers: {'Content-Type': 'multipart/form-data'}})
				.then((response)=>{
					console_log(response.data)
					
					if (response.data.status === 'success') {
 					   // ログイン画面を履歴に残さないように移動
    				window.location.replace('admin_menu.php?key=<?php echo $user_hash;?>');
					} else {
    				alert('ログインに失敗しました');
					}
				})
				.catch((error)=>{
					alert(error)
				})
				.finally(()=>{
				
				})
				
  		}
			function decodeJwtResponse(token) {
        var base64Url = token.split(".")[1];
        var base64 = base64Url.replace(/-/g, "+").replace(/_/g, "/");
        var jsonPayload = decodeURIComponent(
          atob(base64)
            .split("")
            .map(function (c) {
              return "%" + ("00" + c.charCodeAt(0).toString(16)).slice(-2);
            })
            .join("")
        );

        return JSON.parse(jsonPayload);
      }

  </script>
</BODY>
</html>

<?php
exit();
?>

