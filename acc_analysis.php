<?php
	require "php_header.php";
	$token = csrf_create();
	if(empty($_GET["key"])){
		echo "参照用のURLが異なります。";
		exit();
	}
	$user_hash = $_GET["key"] ;
	$_SESSION["user_id"] = rot13decrypt2($user_hash);

	//同一時刻同一IP別セッションのbot疑惑ログをbot?に更新(とりあえず保留)
	$sql = "UPDATE access_log AS AL INNER JOIN ( SELECT datetime,ip,count(*) FROM `access_log` where bot<>'bot' group by datetime,ip HAVING count(*) > 1 ) AS tmp ON AL.datetime = tmp.datetime AND AL.ip = tmp.ip SET bot = 'bot?'";
?>
<!DOCTYPE html>
<html lang='ja'>
<head>
		<?php 
		//共通部分、bootstrap設定、フォントCND、ファビコン等
		include "head_bs5.php" 
		?>
		<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
		<style>
			.btn{
				min-width: 30px;
			}
		</style>
		<meta name="robots" content="noindex,nofollow"><!--googleクローラ不要-->
		<TITLE><?php echo TITLE;?></TITLE>
</head>
<BODY>
	<?php include "header_tag_admin.php"  ?>
	<div id='app' style='height: 100%'>
	<MAIN class='container common_main'>
		<div class='row'>
			<div class='col-12 d-flex'>
			<div style='width:80px;'>
					<label for='an_type'>集計タイプ</label>
					<select class='form-select' id='an_type' v-model='an_type'>
						<option value=1>新規／再訪</option>
						<option value=2>訪問経路</option>
						<option value=3>宣伝効果</option>
					</select>
				</div>
				<div style='width: 80px;'>
					<label for='tani'>集計単位</label>
					<select class='form-select' id='tani' v-model='tani'>
						<option value='d'>日</option>
						<option value='m'>月</option>
						<option value='y'>年</option>
					</select>
				</div>
				<div style='width:200px;' class='d-flex ms-3'>
					<div style='width:80px;'>
						<label for='from'>集計期間</label>
						<select class='form-select' id='from' v-model='from'>
							<template v-for='ym in list' :key='ym.年月'>
								<option :value='ym.年月'>{{ym.年月}}</option>
							</template>
						</select>
					</div>
					<div style='width:80px;' >
						<label for='to'>から</label>
						<select class='form-select' id='to' v-model='to'>
							<template v-for='ym in list' :key='ym.年月'>
								<option :value='ym.年月'>{{ym.年月}}</option>
							</template>
						</select>
					</div>
				</div>
			</div>
		</div>
		<div class='row' style='min-height:240px;'>
			<div class='col-xl-12' style='height:100%;display:flex;justify-content: center;'>
				<div style="position:relative;max-width:900px;width:90%;height:100%;">
					<canvas id="myChart"></canvas>
				</div>
			</div>
		</div>
	</MAIN>
	<FOOTER class='container-fluid common_footer'>
	</FOOTER>
	<!--<div class="loader-wrap" v-show='loader'>
		<div class="loader">Loading...</div>
	</div>-->
	</div><!--app-->

	<script src="script/vue3.js?<?php echo $time; ?>"></script>
	<script>
		acc_analysis('acc_analysis.php','<?php echo $token; ?>').mount('#app');
		admin_menu('acc_analysis.php','','<?php echo $user_hash;?>').mount('#admin_menu');
	</script>
	<script>// Enterキーが押された時にSubmitされるのを抑制する
			window.onload = function() {
				document.getElementById("app").onkeypress = (e) => {
					// form1に入力されたキーを取得
					const key = e.keyCode || e.charCode || 0;
					if (key == 13) {// 13はEnterキーのキーコード
						//e.preventDefault();// アクションを行わない
					}
				}    
			};    
	</script>
</BODY>
</html>