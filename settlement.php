<?php
	require "php_header.php";
	$token = csrf_create();
	if(empty($_GET["key"])){
		echo "参照用のURLが異なります。";
		exit();
	}
	$user_hash = $_GET["key"] ;
	$_SESSION["user_id"] = rot13decrypt2($user_hash);
?>
<!DOCTYPE html>
<html lang='ja'>
<head>
		<?php 
		//共通部分、bootstrap設定、フォントCND、ファビコン等
		include "head_admin.php" 
		?>
		<style>

		</style>
		<TITLE><?php echo TITLE;?> 商品管理</TITLE>
</head>
<BODY>
	<?php include "header_tag_admin.php"  ?>
	<div id='app'>
	<MAIN class='container common_main' data-bs-spy="scroll">
		<div class='row mb-3'>
			<div class='col-md-5 col-7'>
				<label for='hinmei' class="form-label">決済名</label>
				<input type='text' class='form-control' id='hinmei' v-model='new_type.payname' placeholder="〇〇銀行、〇〇ペイなど">
			</div>
			<div class='col-md-3 col-5'>
				<label for='zei' class="form-label">種類</label>
				<select class='form-select' id='zei' v-model='new_type.types'>
					<option value="bank">銀行振込</option>
					<option value="QR">QR決済</option>
					<option value="link">決済URK</option>
					<option value="other">その他</option>
				</select>
			</div>
		</div>
		<div v-if='new_type.types==="bank"' class='row mb-3'>
			<div class='col-md-8 col-12'>
				<label for='source' class="form-label">振込先</label>
				<textarea type='memo' class='form-control' id='source' rows="2" v-model='new_type.source'></textarea>
			</div>
		</div>
		<div v-if='new_type.types==="QR"' class='row mb-3'>
			<div class='col-md-8 col-12'>
				<label for='source' class="form-label">QRコード選択</label>
				<input type="file" name='filename' class="form-control" id='source' @change='fileupload("source","PAY_QR_CODE")'>
			</div>
		</div>
		<div v-if='new_type.types==="link"' class='row mb-3'>
			<div class='col-md-8 col-12'>
				<label for='source' class="form-label">振込用ＵＲＬ</label>
				<input type='text' class='form-control' id='source' v-model='new_type.source' placeholder="URLを記載してください">
			</div>
		</div>
		<div v-if='new_type.types==="other"' class='row mb-3'>
			<div class='col-md-8 col-12'>
				<label for='source' class="form-label">その他</label>
				<input type='text' class='form-control' id='source' v-model='new_type.source' placeholder="支払方法を記載してください">
			</div>
		</div>
		<div class='row mb-3'>
			<div class='col-md-8 col-12'>
				<label for='hinmei' class="form-label">補足</label>
				<input type='text' class='form-control' id='hinmei' v-model='new_type.hosoku' placeholder="支払方法の補足・やり方など">
			</div>
		</div>
		<div class='row mb-3'>
			<div class='col-md-8 col-12'>
				<button type='button' class='btn btn-primary btn-lg' @click='submit_payinfo()'>追加</button>
			</div>
		</div>
		<div class='row mb-3'>
			<div class='col-md-8 col-12 '>
				<div class='table-responsive'>
				<table class="table">
					<thead>
						<tr>
							<th scope="col">決済名</th>
							<th scope="col">種類</th>
							<th scope="col">情報</th>
							<th scope="col">補足</th>
							<th scope="col">有効</th>
							<th scope="col">削除</th>
						</tr>
					</thead>
					<tbody>
						<template v-for='(list,index) in pay_lists' :key='list.source'>
							<tr>
								<td>{{list.payname}}</td>
								<td>{{list.types}}</td>
								<td v-if='list.types==="QR"'><div class='img-div' style="height:50px;width:50px;"><img :src="list.source" class='img-item-sm'></div></td>
								<td v-else style='white-space: pre-wrap;'>{{list.source}}</td>
								<td>{{list.hosoku}}</td>
								<td><input type='checkbox' class="form-check-input" v-model='list.flg' @change='upd_flg(index)'></td>
								<td role='button' @click='del_payinfo(index)'><i class="bi bi-trash3"></i></td>
							</tr>
						</template>
					</tbody>
				</table>
				</div>
			</div>
		</div>


	</MAIN>
	<FOOTER class='container-fluid common_footer'>
	</FOOTER>
	<div class="loader-wrap" v-show='loader'>
		<div class="loader">Loading...</div>
	</div>
	</div><!--app-->
	<script src="script/vue3.js?<?php echo $time; ?>"></script>
	<script src="script/shouhinMS_vue3.js?<?php echo $time; ?>"></script>
	<script>
		admin_menu('settlement.php','','<?php echo $user_hash;?>').mount('#admin_menu');
		settlement('settlement.php','<?php echo $token; ?>','<?php echo $user_hash;?>').mount('#app');
	</script>
	<script>// Enterキーが押された時にSubmitされるのを抑制する
			window.onload = function() {
				//document.getElementById("menu_01").classList.add("active");
				//console_log(document.getElementById("menu_01").classList)
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