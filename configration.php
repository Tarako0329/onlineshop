<?php
	require "php_header_admin.php";
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
			@keyframes blink-red-border-animation {
    		0%, 100% { border-color: red; }
    		50% { border-color: transparent; }
			}
		</style>

		<TITLE>サイト設定</TITLE>
</head>
<BODY>
	<?php include "header_tag_admin.php"  ?>
	<div id='app'>
	<MAIN class='container common_main' data-bs-spy="scroll" >
		<div class='row mb-3 pt-3'>
			<div class='col-md-6 col-12'>
				<label for='yagou' class="form-label">屋号・ショップ名</label>
				<input type='text' class='form-control' id='yagou' v-model='yagou'>
			</div>
		</div>
		<div class='row mb-3'>
			<div class='col-md-6 col-12'>
				<label for='invoice' class="form-label">インボイス登録番号</label>
				<input type='text' class='form-control' id='invoice' v-model='invoice'>
			</div>
		</div>
		<div class='row mb-3'>
			<div class='col-md-6 col-12'>
				<label for='site_pr' class="form-label">ショップＰＲ</label><small>（htmlタグが使用できます）</small>
				<textarea type='memo' class='form-control' id='site_pr' rows="7" v-model='site_pr'></textarea>
				<small style='color:red;'>{{site_pr_chk}}</small>
				<button type='button' class='btn btn-warning m-2' @click='chk_bunshou(`
					『${site_pr}』鍵括弧でくくられた部分はWEBショップ出店者のPR文章です。校閲者として、次の観点でこの文章をチェックしてください。
					１．誤字脱字,正しくない日本語\n
					２．より魅力的にするには\n\n
					pタグを使い、チェック結果のみをシンプルに教えてください。`,"one","AI_answer1",$event)'>
					<span style='display:none;' class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>AIでチェック
				</button>
				<small id='AI_answer1'></small>
			</div>

		</div>
		<div class='row mb-1 pt-3'>
			<div class='col-md-6 col-12'>
				<div class='img-div' style='position:relative;width:50px;'>
					<img :src="logo" class="d-block img-item-sm">
				</div>
			</div>
		</div>
		<div class='row mb-3 pt-1'>
			<div class='col-md-6 col-12'>
				<label for='logo' class="form-label">ショップロゴ</label>
				<div class="input-group mb-3">
					<input type="file" name='filename' class="form-control" id='logo' @change='uploadfile("logo")'>
				</div>
				<small>(50 x 50)px で表示されます</small>
			</div>
		</div>
		<!--<div class='row mb-3' style=''>
			<div class='col-md-6 col-12'>
				<label for='site_name' class="form-label">ショップ名</label>
				<input type='text' class='form-control' id='site_name' v-model='site_name'>
			</div>
		</div>-->


		<div class='row mb-3 pt-3'>
			<div class='col-md-6 col-12'>
				<label for='shacho' class="form-label">代表者</label>
				<input type='text' class='form-control' id='shacho' v-model='shacho'>
			</div>
		</div>
		<div class='row mb-3 pt-3'>
			<div class='col-md-6 col-12'>
				<label for='jusho' class="form-label">屋号所在地</label>
				<input type='text' class='form-control' id='jusho' v-model='jusho'>
			</div>
		</div>
		<div class='row mb-3 pt-3'>
			<div class='col-md-6 col-12'>
				<label for='tantou' class="form-label">問い合せ担当者</label>
				<input type='text' class='form-control' id='tantou' v-model='tantou'>
			</div>
		</div>
		<div class='row mb-3 pt-3'>
			<div class='col-md-6 col-12'>
				<label for='tel' class="form-label">問い合せ窓口（TEL）</label>
				<input type='text' class='form-control' id='tel' v-model='tel'>
			</div>
		</div>
		<div class='row mb-3 pt-3'>
			<div class='col-md-6 col-12'>
				<label for='mail' class="form-label">問い合せ窓口（MAIL）</label>
				<input type='mail' class='form-control' id='mail' v-model='mail'>
				<small>システムからの通知MAILも送信されます。</small>
			</div>
		</div>
		<div class='row mb-3 pt-3'>
			<div class='col-md-6 col-12'>
				<label for='cc_mail' class="form-label">CC Mail</label>
				<input type='mail' class='form-control' id='cc_mail' v-model='cc_mail'>
				<small>お客様宛に送信したメールをBCCで、ご自身にも送りたい場合は設定してください。</small>
			</div>
		</div>
		<div class='row mb-5 pt-3'>
			<div class='col-md-6 col-12'>
				<label for='line_id' class="form-label">LINE ID</label>
				<div class="input-group mb-3">
					<input type="text" class="form-control" id='line_id' v-model='line_id'>
					<button class="btn btn-outline-success" type="button" id="button-addon2" @click='line_test()'>LINEテスト</button>
				</div>
				<small>
					<p>設定すると、注文の通知がLINEに届きます。</p>
					<p>LINE ID はお友達とLINE交換などで使用しているＩＤとは異なります</p>
					<p><a href="https://lin.ee/bIvwCmb"> 通知用のLINEチャンネル</a>に登録して取得してください。</p>
				</small>
			</div>
		</div>

		<div class='row mb-1 pt-1'>
			<!--shop URLを表示し、その横にコピーボタンを配置-->
			<div class='col-md-6 col-12'>
				<label for='shop_url' class="form-label">あなたの商品のみが表示される”専用ショップURL”</label>
				<div class="input-group mb-3">
					<input type="text" class="form-control" id='shop_url' value='<?php echo ROOT_URL."index.php?key=".$user_hash;?>' readonly>
					<button class="btn btn-outline-secondary" type="button" id="button-addon2" @click='copy_url("shop_url")'>Copy</button>
				</div>
			</div>
		</div>

		<div class='row mb-5 pt-1'>
			<div class='col-md-6 col-12'>
				<div class="accordion accordion-flush" id="accordionFlushExample">
				  <div class="accordion-item">
				    <h2 class="accordion-header">
				      <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapseOne" aria-expanded="false" aria-controls="flush-collapseOne">
								<span class="rainbow-text">専用ショップURLのカラー設定</span>
				      </button>
				    </h2>
				    <div id="flush-collapseOne" class="accordion-collapse collapse" data-bs-parent="#accordionFlushExample">
				      <div class="accordion-body">
								<div class='row mb-3 pt-1'>
									<!--<p>専用ショップURLのカラー設定</p>-->
									<div class='col-6'>
										<label for='logo' class="form-label">メニューバーの色</label>
										<div class="input-group mb-1">
											<input type="color" name='headcolor' class="form-control" v-model='headcolor'>
										</div>
										<small></small>
									</div>
									<div class='col-6'>
										<label for='logo' class="form-label">メニューバーの文字色</label>
										<div class="input-group mb-1">
											<input type="color" name='headcolor' class="form-control" v-model='h_font_color'>
										</div>
										<small></small>
									</div>
									<div class='col-6'>
										<label for='logo' class="form-label">商品販売部分の色</label>
										<div class="input-group mb-1">
											<input type="color" name='bodycolor' class="form-control" v-model='bodycolor'>
										</div>
										<small></small>
									</div>
								</div>
								<div class='row mb-3 p-3 pt-3 pb-3' style='background-color:#fff;'>
									<p>配色イメージ</p>
									<div class='row'>
										<div class='col-12 p-3' :style='`background-color:${headcolor};`'>
											<h3 class='alice-regular' :style='`color:${h_font_color};`'>Present Selection - {{site_name}} -</h3>
										</div>
									</div>
									<div class='row'>
										<div class='col-12 p-3 ps-3' :style='`background-color:${bodycolor};`'>
											<div class="col-12"><!--外枠-->
												<div class="container-fluid">
													<div class="row pb-1">
														<div class="col-6" style="position: relative;"><!--写真-->
															<div id="carouselExample_2" class="carousel slide">
																	<div class="carousel-inner">
																	<div>
																		<div class="carousel-item active" style="text-align: center;">
																			<img src="img/sample_apple_cake.jpg" class="d-block img-item">
																		</div>
																	</div>
																</div>
															</div>
															<button class="carousel-control-prev" type="button" data-bs-target="#carouselExample_2" data-bs-slide="prev">
																<span class="carousel-control-prev-icon" aria-hidden="true"></span><span class="visually-hidden">Previous</span>
															</button>
															<button class="carousel-control-next" type="button" data-bs-target="#carouselExample_2" data-bs-slide="next">
																<span class="carousel-control-next-icon" aria-hidden="true"></span><span class="visually-hidden">Next</span>
															</button>
														</div>
														<div class="col-6"><!--見出-->
															<div class="row">
																<div class="col-12 d-flex align-items-end mb-3">
																	<div class="me-3" style="width: 30px; height: 30px; padding: 0px;">
																		<img class="img_icon" :src="logo" >
																	</div>
																	<div>
																		<small>{{yagou}}</small>
																	</div>
																</div>
															</div>
															<h3>『りんごのパウンドケーキ』(サンプル)</h3>
															<div class="pb-3"><p>税込価格：<span class="kakaku">3,024 円</span></p><p>内税：<span class="zei">224</span></p></div>
															<p>甘酸っぱいリンゴがゴロゴロ。甘みと酸味が絶妙なパウンドケーキです</p>
														</div><!--見出-->
													</div><!--写真-->
													<div class="row"><!--問合せ・シェアボタン-->
														<div class="col-6 mt-2 mb-2 ps-3">
															<button type="button" class="btn btn-primary fs-5">問合せ<i class="bi bi-envelope-at-fill ms-2"></i></button>
															<!--review.phpへジャンプ-->
															<a href="#" class="btn btn-secondary fs-5">レビュー<i class="bi bi-chat-left-text-fill ms-2"></i><span class="ms-2">0</span></a>
														</div>
														<div class="col-6 mt-2 mb-2 ps-3">
															<div class="">
																<!--LINE--><a href="#" target="_blank" rel="noopener noreferrer"><i class="bi bi-line line-green fs-1"></i></a>
																<!--FACEBOOK--><a href="#" target="_blank" rel="noopener noreferrer"><i class="bi bi-facebook facebook-blue fs-1 p-3"></i></a>
																<!--TWITTER--><a href="#" rel="nofollow noopener noreferrer" target="_blank"><i class="bi bi-twitter-x twitter-black fs-1"></i></a> 紹介する 
															</div>
														</div>
													</div>
													<div class="row">
															<div class="col-12"><!--詳細-->
																<div class="accordion" id="accordion_2">
																	<div class="accordion-item">
																		<h2 class="accordion-header">
																			<button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne_2" aria-expanded="true" aria-controls="collapseOne" style="font-size: 15px; font-weight: 400;"> 
																				商品詳細・ご注文はコチラ
																			</button>
																		</h2>
																		<div id="collapseOne_2" class="accordion-collapse collapse" data-bs-parent="#accordion_2">
																			<div class="accordion-body">
																				<div class="pb-1">
																					<p>【商品詳細】</p>
																					<p>【原材料】無農薬リンゴ、米粉、</p>
																				</div>
																				<div class="pb-1">
																					<p>【送料・配送・納期などについて】</p>
																					<p>入金確認後、３営業日以内の発送。</p>
																					<p>ヤマトクール便で発送。基本は１０８０円。</p>
																				</div>
																				<div style="">
																					<div>ご注文数：<span class="order">3</span></div>
																					<div class="pb-3">
																			<input type="radio" class="btn-check" autocomplete="off" id="show_2"><label class="btn btn-primary" for="show_2" style="border-radius: 0px;">＋</label>
																			<input type="radio" class="btn-check" autocomplete="off" id="stop_2"><label class="btn btn-secondary" for="stop_2" style="border-radius: 0px;">－</label>
																					</div>
																				</div>
																				<div>
																					<label for="floating_2">お客様備考記入欄</label>
																					<textarea class="form-control" placeholder="Leave a comment here" id="floating_2" style="height: 100px;"></textarea>
																				</div>
																			</div>
																		</div>
																	</div>
																</div>
															</div><!--詳細-->
													</div>
												</div>
												<hr>
											</div>
										</div>
									</div>
								</div>
							</div>
				    </div>
				  </div>
				</div>
			</div>
		</div>




		<div class='row mb-3 pt-3'>
			<div class='col-md-6 col-12'>
				<label for='cc_mail' class="form-label">SNSアカウント（商品URLをシェアするときに効果的です）</label>
				
				<div class="input-group mb-3">
					<span class="input-group-text" id="basic-addon1"><i class="bi bi-facebook"></i></span>
					<input type='text' class='form-control' id='fb_id' v-model='fb_id'>
				</div>
				<div class="input-group mb-3">
					<span class="input-group-text" id="basic-addon2"><i class="bi bi-twitter-x"></i></span>
					<input type='text' class='form-control' id='x_id' v-model='x_id' placeholder="@から始まるIDの ＠ 以降を入力 @tarako -> tarako">
				</div>
			</div>
		</div>
		<div class='row mb-3'>
			<div class='col-md-6 col-12'>
				<label for='cancel_rule' class="form-label">キャンセル規定</label>
				<textarea type='memo' class='form-control' id='cancel_rule' rows="5" v-model='cancel_rule'></textarea>
				<small>注文確認時に表示します。<br>キャンセル規定を定めない場合、法律で『商品の引渡し（特定権利の移転）が完了した日から数えて８日以内』まではキャンセルを受け付ける義務があります。</small>
			</div>
		</div>

		<hr>
		<h4>受注管理画面の設定</h4>
		<div class='row mb-5 pt-3'>
			<div class='col-md-6 col-12'>
				<label for='lock_sts' class="form-label" id='AI_MAIL_CHK'>オーダーキャンセルロック</label>
				<select class='form-select' id='lock_sts' v-model='lock_sts'>
					<option value="recept">注文受付</option>
					<option value="sent">発送済み</option>
					<option value="paid">入金済み</option>
					<option value="nolock">ロックしない</option>
				</select>
				<small>上記の状態になるとお客様からのキャンセル操作をロックします。</small><br>
				<small>以降のキャンセルについてはお客様と直接ご相談いただき、お店側で判断することになります。</small>
			</div>
		</div>

		<div class='row mb-3'>
			<div class='col-md-6 col-12'>
				<p>各種メールのテンプレート作成</p>
				<p style='background-color:#fff;'><small v-html='AI_MAIL_CHK'></small></p>
				<nav>
					<div class="nav nav-tabs" id="nav-tab" role="tablist" style='width:100%;'>
						<button class="nav-link active" id="nav-mail_body_auto	-tab" data-bs-toggle="tab" data-bs-target="#nav-mail_body_auto	" type="button" role="tab" aria-controls="nav-mail_body_auto" aria-selected="true">自動返信</button>
						<button class="nav-link" id="nav-mail_body-tab" data-bs-toggle="tab" data-bs-target="#nav-mail_body" type="button" role="tab" aria-controls="nav-mail_body" aria-selected="false">受付確認</button>
						<button class="nav-link" id="nav-mail_body_sent-tab" data-bs-toggle="tab" data-bs-target="#nav-mail_body_sent" type="button" role="tab" aria-controls="nav-mail_body_sent" aria-selected="false">発送連絡</button>
						<button class="nav-link" id="nav-mail_body_paid-tab" data-bs-toggle="tab" data-bs-target="#nav-mail_body_paid" type="button" role="tab" aria-controls="nav-mail_body_paid" aria-selected="false">支払確認</button>
						<button class="nav-link" id="nav-mail_body_cancel-tab" data-bs-toggle="tab" data-bs-target="#nav-mail_body_cancel" type="button" role="tab" aria-controls="nav-mail_body_cancel" aria-selected="false">ｷｬﾝｾﾙ受付</button>
					</div>
				</nav>
				<div class="tab-content" id="nav-tabContent" style='width:100%;'>
					<div class="tab-pane fade show active" id="nav-mail_body_auto" role="tabpanel" aria-labelledby="nav-mail_body_auto-tab" tabindex="0">
						<button type='button' class='btn btn-warning m-2' style='width:70px;min-width:50px;' @click='()=>{mail_body_auto=""}'>クリア</button>
						<textarea type='memo' class='form-control' id='mail_body_auto' rows="20" v-model='mail_body_auto'></textarea>
						<div class='row mb-3 mt-2'>
							<div class='col-12'>
								定型値(例：購入者ボタンを押すと、メール本文で「購入者」に変換されます。)
							</div>
						</div>
						<div class='row mb-3'>
							<div class='col-12'>
								<button type='button' class='btn btn-info m-2' @click='mail_temp_ins("mail_body_auto","<購入者名>")' style='width:80px;min-width:50px;'>購入者名</button>
								<button type='button' class='btn btn-info m-2' @click='mail_temp_ins("mail_body_auto","<注文内容>")' style='width:80px;min-width:50px;'>注文内容</button>
								<button type='button' class='btn btn-info m-2' @click='mail_temp_ins("mail_body_auto","<送料込の注文内容>")' style='width:80px;min-width:50px;'>注文+送料</button>
								<button type='button' class='btn btn-info m-2' @click='mail_temp_ins("mail_body_auto","<購入者情報>")' style='width:80px;min-width:50px;'>購入者情報</button>
								<button type='button' class='btn btn-info m-2' @click='mail_temp_ins("mail_body_auto","<届け先情報>")' style='width:80px;min-width:50px;'>届け先情報</button>
								<button type='button' class='btn btn-info m-2' @click='mail_temp_ins("mail_body_auto","<問合担当者>")' style='width:80px;min-width:50px;'>問合担当者</button>
								<button type='button' class='btn btn-info m-2' @click='mail_temp_ins("mail_body_auto","<問合せ受付TEL>")' style='width:80px;min-width:50px;'>問合TEL</button>
								<button type='button' class='btn btn-info m-2' @click='mail_temp_ins("mail_body_auto","<問合せ受付MAIL>")' style='width:80px;min-width:50px;'>問合MAIL</button>
								<button type='button' class='btn btn-info m-2' @click='mail_temp_ins("mail_body_auto","<自社名>")' style='width:80px;min-width:50px;'>自社名</button>
								<button type='button' class='btn btn-info m-2' @click='mail_temp_ins("mail_body_auto","<代表者>")' style='width:80px;min-width:50px;'>代表者</button>
								<button type='button' class='btn btn-info m-2' @click='mail_temp_ins("mail_body_auto","<自社住所>")' style='width:80px;min-width:50px;'>自社住所</button>
							</div>
						</div>
						<small>メールサンプル</small>
						<div class='p-2' style='white-space: pre-wrap;border:1px solid black;' v-text='mail_body_auto_sample'></div>

					</div>
					<div class="tab-pane fade" id="nav-mail_body" role="tabpanel" aria-labelledby="nav-mail_body-tab" tabindex="0">
						<button type='button' class='btn btn-warning m-2' style='width:70px;min-width:50px;' @click='()=>{mail_body=""}'>クリア</button>
						<textarea type='memo' class='form-control' id='mail_body' rows="20" v-model='mail_body'></textarea>
						<div class='row mb-3 mt-2'>
							<div class='col-12'>
								定型値(例：購入者ボタンを押すと、メール本文で「購入者」に変換されます。)
							</div>
						</div>
						<div class='row mb-3'>
							<div class='col-12'>
								<button type='button' class='btn btn-info m-2' @click='mail_temp_ins("mail_body","<購入者名>")' style='width:80px;min-width:50px;'>購入者名</button>
								<button type='button' class='btn btn-info m-2' @click='mail_temp_ins("mail_body","<注文内容>")' style='width:80px;min-width:50px;'>注文内容</button>
								<button type='button' class='btn btn-info m-2' @click='mail_temp_ins("mail_body","<送料込の注文内容>")' style='width:80px;min-width:50px;'>注文+送料</button>
								<button type='button' class='btn btn-info m-2' @click='mail_temp_ins("mail_body","<購入者情報>")' style='width:80px;min-width:50px;'>購入者情報</button>
								<button type='button' class='btn btn-info m-2' @click='mail_temp_ins("mail_body","<届け先情報>")' style='width:80px;min-width:50px;'>届け先情報</button>
								<button type='button' class='btn btn-info m-2' @click='mail_temp_ins("mail_body","<支払方法>")' style='width:80px;min-width:50px;'>支払方法</button>
								<button type='button' class='btn btn-info m-2' @click='mail_temp_ins("mail_body","<問合担当者>")' style='width:80px;min-width:50px;'>問合担当者</button>
								<button type='button' class='btn btn-info m-2' @click='mail_temp_ins("mail_body","<問合せ受付TEL>")' style='width:80px;min-width:50px;'>問合TEL</button>
								<button type='button' class='btn btn-info m-2' @click='mail_temp_ins("mail_body","<問合せ受付MAIL>")' style='width:80px;min-width:50px;'>問合MAIL</button>
								<button type='button' class='btn btn-info m-2' @click='mail_temp_ins("mail_body","<自社名>")' style='width:80px;min-width:50px;'>自社名</button>
								<button type='button' class='btn btn-info m-2' @click='mail_temp_ins("mail_body","<代表者>")' style='width:80px;min-width:50px;'>代表者</button>
								<button type='button' class='btn btn-info m-2' @click='mail_temp_ins("mail_body","<自社住所>")' style='width:80px;min-width:50px;'>自社住所</button>
							</div>
						</div>
						<small>メールサンプル</small>
						<div class='p-2' style='white-space: pre-wrap;border:1px solid black;' v-text='mail_body_sample'></div>
					</div>
					<div class="tab-pane fade" id="nav-mail_body_sent" role="tabpanel" aria-labelledby="nav-mail_body_sent-tab" tabindex="0">
						<button type='button' class='btn btn-warning m-2' style='width:70px;min-width:50px;' @click='()=>{mail_body_sent=""}'>クリア</button>
						<textarea type='memo' class='form-control' id='mail_body_sent' rows="20" v-model='mail_body_sent'></textarea>
						<div class='row mb-3 mt-2'>
							<div class='col-12'>
								定型値(例：購入者ボタンを押すと、メール本文で「購入者」に変換されます。)
							</div>
						</div>
							<div class='row mb-3'>
								<div class='col-12'>
									<button type='button' class='btn btn-info m-2' @click='mail_temp_ins("mail_body_sent","<購入者名>")' style='width:80px;min-width:50px;'>購入者名</button>
									<button type='button' class='btn btn-info m-2' @click='mail_temp_ins("mail_body_sent","<注文内容>")' style='width:80px;min-width:50px;'>注文内容</button>
									<button type='button' class='btn btn-info m-2' @click='mail_temp_ins("mail_body_sent","<送料込の注文内容>")' style='width:80px;min-width:50px;'>注文+送料</button>
									<button type='button' class='btn btn-info m-2' @click='mail_temp_ins("mail_body_sent","<購入者情報>")' style='width:80px;min-width:50px;'>購入者情報</button>
									<button type='button' class='btn btn-info m-2' @click='mail_temp_ins("mail_body_sent","<届け先情報>")' style='width:80px;min-width:50px;'>届け先情報</button>
									<button type='button' class='btn btn-info m-2' @click='mail_temp_ins("mail_body_sent","<問合担当者>")' style='width:80px;min-width:50px;'>問合担当者</button>
									<button type='button' class='btn btn-info m-2' @click='mail_temp_ins("mail_body_sent","<問合せ受付TEL>")' style='width:80px;min-width:50px;'>問合TEL</button>
									<button type='button' class='btn btn-info m-2' @click='mail_temp_ins("mail_body_sent","<問合せ受付MAIL>")' style='width:80px;min-width:50px;'>問合MAIL</button>
									<button type='button' class='btn btn-info m-2' @click='mail_temp_ins("mail_body_sent","<自社名>")' style='width:80px;min-width:50px;'>自社名</button>
									<button type='button' class='btn btn-info m-2' @click='mail_temp_ins("mail_body_sent","<代表者>")' style='width:80px;min-width:50px;'>代表者</button>
									<button type='button' class='btn btn-info m-2' @click='mail_temp_ins("mail_body_sent","<自社住所>")' style='width:80px;min-width:50px;'>自社住所</button>
									<button type='button' class='btn btn-info m-2' @click='mail_temp_ins("mail_body_sent","<配送状況>")' style='width:80px;min-width:50px;'>配送状況</button>
								</div>
						</div>
						<small>メールサンプル</small>
						<div class='p-2' style='white-space: pre-wrap;border:1px solid black;' v-text='mail_body_sent_sample'></div>

					</div>
					<div class="tab-pane fade" id="nav-mail_body_paid" role="tabpanel" aria-labelledby="nav-mail_body_paid-tab" tabindex="0">
						<button type='button' class='btn btn-warning m-2' style='width:70px;min-width:50px;' @click='()=>{mail_body_paid=""}'>クリア</button>
						<textarea type='memo' class='form-control' id='mail_body_paid' rows="20" v-model='mail_body_paid'></textarea>
						<div class='row mb-3 mt-2'>
							<div class='col-12'>
								定型値(例：購入者ボタンを押すと、メール本文で「購入者」に変換されます。)
							</div>
						</div>
						<div class='row mb-3'>
							<div class='col-12'>
								<button type='button' class='btn btn-info m-2' @click='mail_temp_ins("mail_body_paid","<購入者名>")' style='width:80px;min-width:50px;'>購入者名</button>
								<button type='button' class='btn btn-info m-2' @click='mail_temp_ins("mail_body_paid","<注文内容>")' style='width:80px;min-width:50px;'>注文内容</button>
								<button type='button' class='btn btn-info m-2' @click='mail_temp_ins("mail_body_paid","<送料込の注文内容>")' style='width:80px;min-width:50px;'>注文+送料</button>
								<button type='button' class='btn btn-info m-2' @click='mail_temp_ins("mail_body_paid","<購入者情報>")' style='width:80px;min-width:50px;'>購入者情報</button>
								<button type='button' class='btn btn-info m-2' @click='mail_temp_ins("mail_body_paid","<届け先情報>")' style='width:80px;min-width:50px;'>届け先情報</button>
								<button type='button' class='btn btn-info m-2' @click='mail_temp_ins("mail_body_paid","<問合担当者>")' style='width:80px;min-width:50px;'>問合担当者</button>
								<button type='button' class='btn btn-info m-2' @click='mail_temp_ins("mail_body_paid","<問合せ受付TEL>")' style='width:80px;min-width:50px;'>問合TEL</button>
								<button type='button' class='btn btn-info m-2' @click='mail_temp_ins("mail_body_paid","<問合せ受付MAIL>")' style='width:80px;min-width:50px;'>問合MAIL</button>
								<button type='button' class='btn btn-info m-2' @click='mail_temp_ins("mail_body_paid","<自社名>")' style='width:80px;min-width:50px;'>自社名</button>
								<button type='button' class='btn btn-info m-2' @click='mail_temp_ins("mail_body_paid","<代表者>")' style='width:80px;min-width:50px;'>代表者</button>
								<button type='button' class='btn btn-info m-2' @click='mail_temp_ins("mail_body_paid","<自社住所>")' style='width:80px;min-width:50px;'>自社住所</button>
								<button type='button' class='btn btn-info m-2' @click='mail_temp_ins("mail_body_paid","<領収書LINK>")' style='width:80px;min-width:50px;'>領収書LINK</button>
								</div>
						</div>
						<small>メールサンプル</small>
						<div class='p-2' style='white-space: pre-wrap;border:1px solid black;' v-text='mail_body_paid_sample'></div>

					</div>
					<div class="tab-pane fade" id="nav-mail_body_cancel" role="tabpanel" aria-labelledby="nav-mail_body_cancel-tab" tabindex="0">
						<button type='button' class='btn btn-warning m-2' style='width:70px;min-width:50px;' @click='()=>{mail_body_cancel=""}'>クリア</button>
						<textarea type='memo' class='form-control' id='mail_body_cancel' rows="20" v-model='mail_body_cancel'></textarea>
						<div class='row mb-3 mt-2'>
							<div class='col-12'>
								定型値(例：購入者ボタンを押すと、メール本文で「購入者」に変換されます。)
							</div>
						</div>
						<div class='row mb-3'>
							<div class='col-12'>
								<button type='button' class='btn btn-info m-2' @click='mail_temp_ins("mail_body_cancel","<購入者名>")' style='width:80px;min-width:50px;'>購入者名</button>
								<button type='button' class='btn btn-info m-2' @click='mail_temp_ins("mail_body_cancel","<注文内容>")' style='width:80px;min-width:50px;'>注文内容</button>
								<button type='button' class='btn btn-info m-2' @click='mail_temp_ins("mail_body_cancel","<送料込の注文内容>")' style='width:80px;min-width:50px;'>注文+送料</button>
								<button type='button' class='btn btn-info m-2' @click='mail_temp_ins("mail_body_cancel","<購入者情報>")' style='width:80px;min-width:50px;'>購入者情報</button>
								<button type='button' class='btn btn-info m-2' @click='mail_temp_ins("mail_body_cancel","<届け先情報>")' style='width:80px;min-width:50px;'>届け先情報</button>
								<button type='button' class='btn btn-info m-2' @click='mail_temp_ins("mail_body_cancel","<問合担当者>")' style='width:80px;min-width:50px;'>問合担当者</button>
								<button type='button' class='btn btn-info m-2' @click='mail_temp_ins("mail_body_cancel","<問合せ受付TEL>")' style='width:80px;min-width:50px;'>問合TEL</button>
								<button type='button' class='btn btn-info m-2' @click='mail_temp_ins("mail_body_cancel","<問合せ受付MAIL>")' style='width:80px;min-width:50px;'>問合MAIL</button>
								<button type='button' class='btn btn-info m-2' @click='mail_temp_ins("mail_body_cancel","<自社名>")' style='width:80px;min-width:50px;'>自社名</button>
								<button type='button' class='btn btn-info m-2' @click='mail_temp_ins("mail_body_cancel","<代表者>")' style='width:80px;min-width:50px;'>代表者</button>
								<button type='button' class='btn btn-info m-2' @click='mail_temp_ins("mail_body_cancel","<自社住所>")' style='width:80px;min-width:50px;'>自社住所</button>
							</div>
						</div>
						<small>メールサンプル</small>
						<div class='p-2' style='white-space: pre-wrap;border:1px solid black;' v-text='mail_body_cancel_sample'></div>

					</div>
				</div>
			</div>
		</div>



		<div class='row mb-3' style='position:fixed;bottom:0;'>
			<div class='col-md-6 col-12'>
				<button v-if='security_lock!==true' type='button' class='btn btn-primary m-2 btn-lg fs-1 ps-5 pe-5' @click='set_user' style='width:150px;'>登録</button>
			</div>
		</div>
	</MAIN>
	<FOOTER class='container-fluid common_footer'>
	</FOOTER>
	<div class="loader-wrap" v-show='loader'>
		<div class="loader">Loading...</div>
	</div>

	</div>
	<script src="script/admin_menu.js?<?php echo $time; ?>"></script>
	<script src="script/configration_vue3.js?<?php echo $time; ?>"></script>
	<script>
		configration('configration.php','<?php echo $token; ?>','<?php echo $user_hash;?>').mount('#app');
		admin_menu('configration.php','','<?php echo $user_hash;?>').mount('#admin_menu');
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