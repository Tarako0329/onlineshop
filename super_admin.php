<?php
/*
自動投稿の間隔設定（最短最長の間でランダム）
自動投稿内容の履歴
新規出店者作成

*/
require "php_header_admin.php";

$msg="";
if(!empty($_POST["new_yagou"])){//知り合いの新規出店者手動登録
	while(true){
		//乱数からユーザIDを発行し、重複してなければ使用する
		$new_id = rand(0,99999);
		log_writer2("\$uid",$new_id,"lv3");
		$row = $db->SELECT("SELECT `uid` from Users where `uid` = :uid FOR UPDATE", [":uid" => $new_id]);
		if(empty($row["uid"])){
			break;
		}
	}
	//新規出店者登録SQL
	try{
		$db->begin_tran();
		$db->INSERT("Users_online",["uid"=>$new_id,"logo"=>"upload/logo_sample.webp","yagou"=>$_POST["new_yagou"]]);
		$db->INSERT("Users",["uid"=>$new_id,"mail"=>"-","password"=>"-","question"=>"-","answer"=>"-","onlineshop"=>"use"]);
		$db->commit_tran();
		$msg='出店アカウントを作成しました。';
	}catch(Exception $e){
		$db->rollback_tran();
		var_dump($e);
		exit();
	}
}

if(!empty($_POST["sns_f"]) && !empty($_POST["sns_t"])){
	//自動ツイート間隔幅設定
	try{
		$db->begin_tran();
		$db->UP_DEL_EXEC(
			"UPDATE online_shop_config SET post_interval_F = :sns_f ,post_interval_T = :sns_t"
			, [":sns_f" => $_POST["sns_f"], ":sns_t" => $_POST["sns_t"]]);
		$db->commit_tran();
		$msg='自動ツイート間隔幅を設定しました。';
	}catch(Exception $e){
		$db->rollback_tran();
		var_dump($e);
		exit();
	}
}
$sql = "SELECT * from Users_online";
$data = $db->SELECT($sql);

$i=0;
foreach($data as $row){
	 //$row["yagou"]."　　　<a href=admin_menu.php?key=".rot13encrypt2($row["uid"]).">管理サイトへ</a><br>";
	 $data[$i] = ["uri" => "admin_menu.php?key=".rot13encrypt2($row["uid"]),"yagou" => $row["yagou"]];
	 $i++;
}

$config = $db->SELECT("SELECT * from online_shop_config");

?>
<!DOCTYPE html>
<html lang='ja'>
<head prefix="og: http://ogp.me/ns#">
		<?php 
		//共通部分、bootstrap設定、フォントCND、ファビコン等
		include "head_admin.php" 
		?>
		<style>
			.btn{
				min-width: 50px;
			}
		</style>
		<TITLE><?php echo TITLE;?>管理画面</TITLE>
</head>
<BODY >
	<div id='app' class='container'>
		<header class='common_header alice-regular p-3 mb-3'>
			<h3>システム管理画面</h3>
		</header>
		<main>
			<div v-if='msg!==""' class='alert alert-success text-center'>{{msg}}</div>
			<div class='row'>
				<div class='col-md-6'>
					<form method="post" action='super_admin.php'>
						<p>X.com</p>
						<label for='sns_f' class='form-label'>つぶやき最短(分)</label>
						<input type='text' v-model='sns_f' name='sns_f' class='form-control mb-1' required placeholder="つぶやき最短分" id='sns_f'>
						<label for='sns_t' class='form-label'>つぶやき最長(分)</label>
						<input type='text' v-model='sns_t' name='sns_t' class='form-control mb-3' required placeholder="つぶやき最長分" id='sns_t'>
						<button type='submit' class='btn btn-primary'>設定</button>
					</form>
					<hr>
				</div>
				<div class='col-md-6'>
					<h4 class='alice-regular'>新規出店登録</h4>
					<form method="post" action='super_admin.php'>
						<div class='row ps-3 pe-3'>
							<input type='text' name='new_yagou' class='form-control mb-3' placeholder="屋号">
							<button type='submit' class='btn btn-primary'>登録</button>
						</div>
					</form>
					<hr>
					<h4 class='alice-regular'>出店者管理</h4>
					<template v-for='(list,index) in menu' :key='list.yagou'>
						<a :href='list.uri' class='btn btn-info text-truncate m-2' style='width:100px;'>{{list.yagou}}</a>
					</template>
					<hr>
				</div>
			</div>
		</main>
		<footer></footer>
	</div>
	<script><!--vue-->
		const { createApp, ref, onMounted, onBeforeMount, computed,watch } = Vue;
		createApp({//管理者メニュー
		 setup() {
		   const menu = ref(
		     <?php echo json_encode($data, JSON_UNESCAPED_UNICODE); ?>
		   )
			 const sns_f = ref(<?php echo $config[0]["post_interval_F"]; ?>)
			 const sns_t = ref(<?php echo $config[0]["post_interval_T"]; ?>)
			 const msg = ref('<?php echo $msg;?>')
				
		   onMounted(()=>{
		     console_log(`onMounted`)
				 setTimeout(function() {
  				msg.value = ''
				}, 3000);
		   })
			
		   return{
		     menu,
				 sns_f,
				 sns_t,
				 msg,
		   }
		 }
		}).mount('#app');
	</script>
</BODY>
</html>
