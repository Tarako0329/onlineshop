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
			.btn{
				min-width: 120px;
			}
		</style>
		<TITLE>商品管理</TITLE>
</head>
<BODY>
	<?php include "header_tag_admin.php"  ?>
	<div id='app'>
	<MAIN class='container common_main' data-bs-spy="scroll">
		<transition>
			<div v-show="msg!==''" class="alert alert-warning" role="alert">
				{{msg}}
			</div>
		</transition>
		<div class='row mb-5 pt-3'>
			<div class='col-md-8 col-12 mb-0'>
				<div class="btn-group" role="group" aria-label="Basic outlined example">
					<button type='button' class='btn btn-light btn-sm' @click='cg_mode("new")'>新規登録</button>
					<button type='button' class='btn btn-light btn-sm' @click='cg_mode("upd")'>修正</button>
				</div>
			</div>
			<div class='col-md-8 col-12 mt-0' style='position:relative;'>
				<div class="btn-group" role="group" aria-label="Basic outlined example" style='position:absolute ;top:2px;'>
					<input type='radio' class='btn-check' name='mode' value='new' autocomplete='off' v-model='mode' id='new' disabled>
					<label class='btn btn-outline-success ' for='new' style='border-radius:0;' ></label>
					<input type='radio' class='btn-check' name='mode' value='upd' autocomplete='off' v-model='mode' id='upd' disabled>
					<label class='btn btn-outline-success ' for='upd' style='border-radius:0;' ></label>
				</div>
			</div>
		</div>
		<div v-show='mode==="upd"' class='row' style=''>
			<div class='col-12' style='color:red;font-size:10px;'>
				<p>※注！：商品とお客様のレビューは商品CDで紐づいてます。</p>
				<p>※修正は商品名の変更、少額の価格修正、PR文等の修正にご利用ください</p>
				<p>※異なる商品への変更、極端な価格変更、内容量変更等は新規商品として登録してください。</p>
				<p>※（例：ドーナツ⇒食パン×　ドーナツ1個⇒ドーナツ10個×　1000円⇒1500円×）</p>
			</div>
			<div class='col-md-8 col-12 overflow-y-scroll p-1 mb-1' :style='shouhin_table'>
				<table class='table table-sm mb-1'>
					<tbody>
						<template v-for='(list,index) in shouhinMS' :key='list.shouhinCD+list.uid'>
							<tr>
								<!--<td>{{list.shouhinNM}}</td>-->
								<td v-if='list.status==="show"' style='color:blue'>商品CD：{{list.shouhinCD}}「{{list.shouhinNM}}」</td>
								<td v-if='list.status==="limited"' style='color:green'>商品CD：{{list.shouhinCD}}「{{list.shouhinNM}}」</td>
								<td v-if='list.status==="soldout"' style='color:darkorange'>商品CD：{{list.shouhinCD}}「{{list.shouhinNM}}」</td>
								<td v-if='list.status==="stop"' style='color:gray'>商品CD：{{list.shouhinCD}}「{{list.shouhinNM}}」</td>

								<td style='width: 30px' class='pt-2' role='button' @click='open_product_page(`${list.uid}-${list.shouhinCD}`,list.shouhinNM)'>
									<i class="bi bi-window-plus"></i>
								</td>
								<td style='width: 30px' class='pt-2' role='button' @click='copy_target(`${list.uid}-${list.shouhinCD}`,list.shouhinNM)'>
									<i class="bi bi-share"></i>
								</td>
								<td :id="`${list.uid}-${list.shouhinCD}`" style='display:none;'>{{RTURL}}product.php?id={{list.uid}}-{{list.shouhinCD}}</td>
								<td style='width: 80px'>
									<select style='width: 80px;' class='form-select' v-model='list.status' @change='upd_status(list.status,list.shouhinCD)'>
										<option value='show'>販売中</option>
										<option value='limited' disabled>限定販売</option>
										<option value='soldout'>受付停止</option>
										<option value='stop'>販売停止</option>
									</select>
								</td>
								<td style='width: 50px'><button type='button' style='min-width: 40px' class='btn btn-primary' @click='set_shouhinNM(list.shouhinNM)'>編集</button></td>
							</tr>
						</template>
					</tbody>
				</table>
			</div>
			<div class='col-12'><i class="bi bi-share me-2 ms-2"></i><small>商品販売ページのURLをコピー。SNS投稿等に利用できます。</small></div>
			<div class='col-12'><small>限定販売への変更は編集ページからのみ設定できます。</small></div>
		</div>
		<div v-show='disp!=="none"'>
			<hr>
			<div class='row mb-3'>
				<div class='col-md-8 col-12'>
					<label for='hinmei' class="form-label">商品名（商品CD：{{shouhinCD}}）</label>
					<input type='text' v-model='shouhinNM' class='form-control' id='hinmei'>
				</div>
			</div>
			<div class='row mb-1'>
				<div class='col-md-8 col-12'>
					<input type='radio' class='btn-check' name='status' value='show' autocomplete='off' v-model='status' id='show'>
					<label class='btn btn-outline-primary ' for='show' style='border-radius:0;min-width: 90px;'>販売中</label>
					<input type='radio' class='btn-check' name='status' value='limited' autocomplete='off' v-model='status' id='limited'>
					<label class='btn btn-outline-success ' for='limited' style='border-radius:0;min-width: 90px;'>限定販売</label>
					<input type='radio' class='btn-check' name='status' value='soldout' autocomplete='off' v-model='status' id='soldout'>
					<label class='btn btn-outline-warning ' for='soldout' style='border-radius:0;min-width: 90px;'>受付停止</label>
					<input type='radio' class='btn-check' name='status' value='stop' autocomplete='off' v-model='status' id='stop'>
					<label class='btn btn-outline-danger ' for='stop' style='border-radius:0;min-width: 90px;'>販売停止</label>
				</div>
			</div>
			<div class='row mb-3'>
				<small>限定販売：注文時に出店者が設定した特別コードが必要</small>
				<small>受付停止：品切れ中で表示（お勧め）</small>
				<small>販売停止：商品は非表示（今後販売予定がない場合はコチラ）</small>
			</div>
			<div class='row mb-3'>
				<div v-show='status==="limited"' class='col-md-8 col-12'>
					<label for='limited_cd' class='form-label'>特別コード(最大10文字)</label>
					<input type='text' v-model='limited_cd' id='limited_cd' class='form-control' maxlength="10">
				</div>
			</div>
			<div class='row mb-3'>
				<div class='col-md-8 col-12'>
					<label for='tanka' class="form-label">単価</label>
					<input type='number' class='form-control' id='tanka' v-model='tanka'>
					<label for='zei' class="form-label">税区分</label>
					<select class='form-select' id='zei' v-model='zei'>
						<option value="0">非課税</option>
						<option value="1001">8%</option>
						<option value="1101">10%</option>
					</select>
					<p>税込価格：{{zeikomi.toLocaleString()}} ({{shouhizei.toLocaleString()}})</p>
				</div>
			</div>
			<div class='row mb-5'>
				<div class='col-md-8 col-12'>
					<label for='midasi' class="form-label">商品説明(見出し)</label>
					<div class='row'>
						<div class='col-9'>
							<textarea @focus='set_elm_hi("midasi","20vh")' @blur='set_elm_hi("midasi","110px")' style='height:110px' type='memo' class='form-control' id='midasi'  v-model='midasi' placeholder="商品のアピールポイントを記入。AIを使う場合は必要最低限のアピール文を記入。（AIは商品名、商品説明詳細も加味してPR文を作成します。）"></textarea>
							<p class='m-0'><small>Googleの検索結果や商品一覧画面に表示。</small></p>
							<p class='m-0'><small>商品のPR文になります。(推奨80～100文字)</small></p>
						</div>
						<div class='col-3 ps-0'>
							<button class='btn btn-sm btn-info' style='min-width:90px' @click='get_AI_seo()' id='gemini_seo_btn'>
								<template v-if='loader2===false'><p>Google AI</p><p>で魅力的に編集</p></template>
								<template v-else><p><span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Google AI</p><p>考え中...</p></template>
							</button>
						</div>
					</div>
				</div>
			</div>
			<div class='row mb-3'>
				<div class='col-md-8 col-12'>
					<label for='setumei' class="form-label">商品説明(詳細)</label>
					<textarea @focus='set_elm_hi("setumei","30vh")' @blur='set_elm_hi("setumei","100px")' style='height:100px' type='memo' class='form-control' id='setumei' v-model='info' placeholder='商品の仕様・原材料名　等、商品に関する詳細を記入'></textarea>
				</div>
			</div>
			<div class='row mb-3'>
				<div class='col-md-8 col-12'>
					<label for='setumei' class="form-label">配送・送料等について</label>
					<textarea @focus='set_elm_hi("haisou","30vh")' @blur='set_elm_hi("haisou","100px")' style='height:100px' type='memo' class='form-control' id='haisou' v-model='haisou' placeholder='配送方法、送料、納期などについて'></textarea>
				</div>
			</div>
			<div class='row mb-3'>
				<div class='col-md-8 col-12'>
					<label for='customer_bikou' class="form-label">お客様の備考</label>
					<textarea @focus='set_elm_hi("customer_bikou","30vh")' @blur='set_elm_hi("customer_bikou","65px")' style='height:65px' type='memo' class='form-control' id='customer_bikou' rows="3" v-model='customer_bikou' aria-labelledby="customer_bikou_help"></textarea>
					<div id="customer_bikou_help" class="form-text">
						お客様に記入いただくエリアの初期表示です。<br>
						例：セット商品の場合 => A～Eの商品から３種類を入力してください。
					</div>
				</div>
			</div>
			<div class='row mb-3'>
				<div class='col-md-8 col-12'>
					<label for='hash_tag' class="form-label">ハッシュタグ</label>
					<small>X(twitter)のシェアボタン投稿時に自動で入ります</small>
					<div class='row'>
						<div class='col-9'>
							<textarea type='memo' class='form-control' id='hash_tag' rows="2" v-model='hash_tag' placeholder="#おいしい,#お菓子,#おすすめ"></textarea>
						</div>
						<div class='col-3 ps-0'>
							<button class='btn btn-sm btn-info' style='min-width:90px' @click='get_AI_post()' id='gemini_btn'>
								<template v-if='loader2===false'><p>Google AI</p><p>が提案</p></template>
								<template v-else><p><span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Google AI</p><p>考え中...</p></template>
							</button>
						</div>
					</div>
				</div>
			</div>
			<hr>
			<div class='row mb-3'><!--写真アップロード-->
				<div class='col-md-8 col-12'>
					<button type='button' class='btn btn-info' @click='input_file_btn("pic_file")'>写真アップロード</button>
					<input type='file' name='filename' style='display:none;' id='pic_file' @change='uploadfile("pic_file")' multiple accept="image/*">
					<p><small>正方形推奨。</small></p>
				</div>
			</div>
			<div class='row mb-3'>
				<div class='col-md-8 col-12'>
					<small>写真の『削除』は『登録』ボタンに関係なく、即反映されます。</small>
					<small>ファイル名は半角英数。日本語と"＆"マークは厳禁です。</small>
					<div class='row'>
					<template v-for='(list,index) in pic_list' :key='list.filename'>
						<div class='col-md-4 col-6' style='padding:10px;'>
							<div style='width:100%;'><button type='button' class='btn btn-info mb-1' @click='resort(index)' style='min-width: 50px;'>表示順：{{list.sort}}</button></div>
							<div class='img-div' style='position:relative;'>
								<button type="button" class='btn btn-danger' style='position:absolute;top:0;right:0;min-width: 40px;' @click='pic_delete(list.filename)'>削除</button>
								<img :src="list.filename" class="d-block img-item-sm">
							</div>
						</div>
					</template>
					</div>
				</div>
			</div>

			 <div class='row mb-3'>
				<div class='col-md-8 col-12'>
					<button type='button' class='btn btn-primary' @click='ins_shouhinMS'>登録</button>
					<button type='button' class='btn btn-warning ms-3' @click='set_shouhinNM("")'>キャンセル</button>
					<button v-if='mode==="upd"' type='button' class='btn btn-danger ms-3' style='min-width:90px;' @click='upd_status("del",shouhinCD)'>削除</button>
				</div>
			</div>
		</div>

	</MAIN>
	<FOOTER class='container-fluid common_footer'>
	</FOOTER>
	<!-- Modal TAGS-->
	<button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#exampleModal" style="display: none;" id="modalon"></button>
	<div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
		<div class="modal-dialog modal-dialog-centered">
			<div class="modal-content">
				<div class="modal-header">
					お気に入りのタグを選択してください。（5個ぐらいまでが良いようです）
				</div>
				<div class="modal-body fs-2">
					<template v-for='(tag,index) in AI_answer.posts.tags'>
							<div class='ms-3' role='button'>
								<input class="form-check-input" type="checkbox" :value="`${tag}`" :id="`tag_${tag}`" @click='tags_add(`${tag}`)' >
								<label class="form-check-label" :for="`tag_${tag}`">
									{{tag}}
								</label>
							</div>
					</template>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-bs-dismiss="modal" id='mail_modal_close'>Close</button>
				</div>
			</div>
		</div>
	</div>

	<!-- Modal SEO-->
	<button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#exampleModal_seo" style="display: none;" id="modalon_seo"></button>
	<div class="modal fade" id="exampleModal_seo" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
		<div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
			<div class="modal-content">
				<div class="modal-header">
					お気に入りの紹介文を選択してください。（後から編集できます）
				</div>
				<div class="modal-body fs-2">
					<div class="btn-group-vertical" role="group" aria-label="Vertical radio toggle button group">
						<template v-for='(list,index) in AI_answer_seo.introductions'>
								<input class="btn-check" type="radio" name='gemini_seo' :id="`tag_${index}`" @click='set_midasi(list.rei)' >
								<label class="btn btn-outline-primary text-start mb-2" style='border-radius:2px;' :for="`tag_${index}`">
									{{list.rei}}
								</label>
						</template>
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-bs-dismiss="modal" id='mail_modal_close'>Close</button>
				</div>
			</div>
		</div>
	</div>

	<div class="loader-wrap" v-show='loader'>
		<div class="loader">Loading...</div>
	</div>
	</div><!--app-->
	<script src="script/vue3.js?<?php echo $time; ?>"></script>
	<script src="script/shouhinMS_vue3.js?<?php echo $time; ?>"></script>
	<script>
		admin_menu('shouhinMS.php','','<?php echo $user_hash;?>').mount('#admin_menu');
		shouhinMS('shouhinMS.php','<?php echo $token; ?>','<?php echo $user_hash;?>').mount('#app');
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