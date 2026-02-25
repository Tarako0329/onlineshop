<?php
	require "php_header_admin.php";
	$token = csrf_create();
	if(empty($_GET["key"])){
		echo "参照用のURLが異なります。";
		exit();
	}
	$user_hash = $_GET["key"] ;
	$key_user = rot13decrypt2($user_hash);
	require_once "auth.php";
	//$_SESSION["user_id"] = rot13decrypt2($user_hash);

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
			@media screen and (min-width:0px) {
			    /*md以上*/
			    #chart_area{
			        height: 750px;
			    }
			}
			@media screen and (min-width:1000px) {
			    /*md以上 768*/
			    #chart_area{
			        height:600px;
			    }
			}
		</style>
		<meta name="robots" content="noindex,nofollow"><!--googleクローラ不要-->
		<TITLE><?php echo TITLE;?></TITLE>
</head>
<BODY>
	<?php include "header_tag_admin.php"  ?>
	<div id='app' style='height: 100%'>
	<MAIN class='container common_main'>
		<div class='row'><!--serch-->
			<div class='col-12 d-flex'>
				<div style='width:80px;'>
					<label for='an_type'>集計タイプ</label>
					<select class='form-select' id='an_type' v-model='an_type'>
						<option value='1'>新規／再訪</option>
						<option value='2'>訪問経路</option>
						<option value='3'>ページ別訪問人数</option>
						<option value='4'>ご購入者の訪問履歴</option>
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
			<div class='col-12'>
				<div class="form-check">
				  <input class="form-check-input" type="checkbox" v-model='taishou_all' id="flexCheckDefault">
				  <label class="form-check-label" for="flexCheckDefault">
				    販売中以外のページを除く
				  </label>
				</div>
			</div>
		</div>
		<div v-if='an_type != 4' class='row' style=''><!--graph/table-->
			<div class='col-xl-7 col-12' style=''><!--graph-->
				<div id='chart_area' style="width: 95%;">
					<canvas id="myChart"></canvas>
				</div>
			</div>
			<div class='col-xl-5 col-12' style=''><!--table-->
				<table class='table'>
					<thead>
						<tr>
							<th>日付</th>
							<th v-if='an_type==3'>ページ</th>
							<th>アクセス</th>
						</tr>
					</thead>
					<tbody>
						<template v-for='(list,index) in analysis_data' :key='list.date+list.shouhinNM'>
							<tr>
								<td>{{list.date}}</td>
								<td v-if='an_type==3'>{{list.shouhinNM}}</td>
								<td>{{list.訪問者数}}</td>
							</tr>
						</template>
					</tbody>
				</table>
			</div>
		</div>
		<div v-else class='row' style=''><!--table-->
			<table class='table'>
				<thead>
					<tr>
						<th>日時</th>
						<th>名前</th>
						<th>訪問先</th>
						<th>どこから</th>
					</tr>
				</thead>
				<tbody>
					<template v-for='(list,index) in analysis_data' :key='list.SEQ'>
						<tr>
							<td>{{list.datetime}}</td>
							<td>{{list.name}}</td>
							<td>{{list.PAGE_NAME}}</td>
							<td>{{list.koukoku_sns}}</td>
						</tr>
					</template>
				</tbody>
			</table>
		</div>
	</MAIN>
	<FOOTER class='container-fluid common_footer'>
	</FOOTER>
	<!--<div class="loader-wrap" v-show='loader'>
		<div class="loader">Loading...</div>
	</div>-->
	</div><!--app-->

	<script src="script/admin_menu.js?<?php echo $time; ?>"></script>
	<script src="script/acc_analysis_vue3.js?<?php echo $time; ?>"></script>
	<script>
		acc_analysis('acc_analysis.php','<?php echo $token; ?>').mount('#app');
		admin_menu('acc_analysis.php','','<?php echo $user_hash;?>').mount('#admin_menu');
	</script>
	<script>// Enterキーが押された時にSubmitされるのを抑制する
			/*window.onload = function() {
				document.getElementById("app").onkeypress = (e) => {
					// form1に入力されたキーを取得
					const key = e.keyCode || e.charCode || 0;
					if (key == 13) {// 13はEnterキーのキーコード
						//e.preventDefault();// アクションを行わない
					}
				}    
			};    */
	</script>
</BODY>
</html>