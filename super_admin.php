<?php
/*
自動投稿の間隔設定（最短最長の間でランダム）
自動投稿内容の履歴
新規出店者作成

*/
require "php_header.php";

$msg="";
if(!empty($_POST["new_yagou"])){//知り合いの新規出店者手動登録
	$stmt = $pdo_h->prepare("select uid from Users where uid = :uid FOR UPDATE");
	while(true){
		//乱数からユーザIDを発行し、重複してなければ使用する
		$new_id = rand(0,99999);
		log_writer2("\$uid",$new_id,"lv3");
		$stmt->bindValue("uid", $new_id, PDO::PARAM_INT);
		$stmt->execute();
		$row = $stmt->fetchAll(PDO::FETCH_ASSOC);
		if(empty($row["uid"])){
			break;
		}
	}
	//新規出店者登録SQL
	try{
		$pdo_h->beginTransaction();
		$stmt = $pdo_h->prepare("INSERT INTO Users_online(uid,logo,yagou) values('$new_id','upload/logo_sample.png','".$_POST["new_yagou"]."') ");
		$stmt->execute();
		$stmt = $pdo_h->prepare($sql="INSERT INTO Users(uid,mail,password,question,answer) values('$new_id','-','-','-','-') ");
		$stmt->execute();
		$pdo_h->commit();
		$msg='出店アカウントを作成しました。';
	}catch(Exception $e){
		$pdo_h->rollBack();
		var_dump($e);
		exit();
	}
}

if(!empty($_POST["sns_f"]) && !empty($_POST["sns_t"])){
	//自動ツイート間隔幅設定
	try{
		$pdo_h->beginTransaction();
		$stmt = $pdo_h->prepare("UPDATE online_shop_config SET post_interval_F = ".$_POST["sns_f"]." ,post_interval_T = ".$_POST["sns_t"]);
		$stmt->execute();
		$pdo_h->commit();
		$msg='自動ツイート間隔幅を設定しました。';
	}catch(Exception $e){
		$pdo_h->rollBack();
		var_dump($e);
		exit();
	}
}
$sql = "select * from Users_online";
$stmt = $pdo_h->prepare($sql);
$stmt->execute();

$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

$i=0;
foreach($data as $row){
	 //$row["yagou"]."　　　<a href=admin_menu.php?key=".rot13encrypt2($row["uid"]).">管理サイトへ</a><br>";
	 $data[$i] = ["uri" => "admin_menu.php?key=".rot13encrypt2($row["uid"]),"yagou" => $row["yagou"]];
	 $i++;
}

$stmt = $pdo_h->prepare("SELECT * from online_shop_config");
$stmt->execute();

$config = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
		<header class='common_header alice-regular p-3 mb-3' style='color:brown;'>
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
