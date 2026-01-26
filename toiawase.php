<?php
	require "php_header.php";
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
	<meta name="robots" content="noindex,nofollow"><!--googleクローラ不要-->
	<TITLE><?php echo TITLE;?> 特定商取引法に基づく表記</TITLE>
</head>
<BODY>
	<?php include "header_tag.php"  ?>
	<div id='app'>
	<MAIN class='container common_main' data-bs-spy="scroll">
		<div class='row mb-5'>
				<div class='col-12 '>
					<div class='text-center'> <h1>特定商取引法に基づく表記</h1></div>
					<!--<div class='p-5'>
						<div class='mb-3'>
							<h4>１ サイト運営元・基本ポリシー</h4>
							<p  class='ps-4'>当サイトは[ Midori System ]が運営してます。</p>

							<div class='ms-4' style='width:100px;height:100px;'><img src='img/msystem_logo.png' class='img-fluid'></div>
							<p  class='ps-4'><a href='https://site.greeen-sys.com/'>https://site.greeen-sys.com/</a></p>

							<p  class='ps-4'>当サイトのシステム的な不備・不具合については当社。販売される商品については、当サイトを通じて販売する出店者(以降、出店者と呼ぶ)が責任をもって管理するものとする。</p>
							<p  class='ps-4'>商品サービス販売における商品・金銭の授受に関しては項２以降に基づき、出店者とお客様の間で責任をもって実施するものとする。</p>
							<p  class='ps-4'>なお、商品未発送、未支払いについては法にのっとり当社が対処するものとする。</p>
						</div>
						<div class='mb-3'>
							<h4>２  返品について  </h4>
							<p  class='ps-4'>お届けした商品が不良品だった場合、返品を受け付け致します。 </p>
							<p  class='ps-4'>上記以外の理由における返品・交換については出店者の判断により、対応いたします。  </p>
						</div>
						<div class='mb-3'>
							<h4>３ キャンセルポリシー  </h4>
							<p  class='ps-4'>各商品ページの記載にのっとります。</p>
							<p  class='ps-4'>なお、特段記載なき場合は、法律により『商品の引渡し（特定権利の移転）が完了した日から数えて８日以内』まではキャンセルを受け付けます。</p>
						</div>
						<div class='mb-3'>
							<h4>４ 支払について </h4>
							<p  class='ps-4'>各商品ページの記載にのっとります。</p>
						</div>
						<div class='mb-3'>
							<h4>５ お問い合わせ窓口 </h4>
							<p  class='ps-4'>商品についての質問・配送状況などについては、<a href='index.php'>商品販売のページ</a>より問合せをお願いいたします。</p>
							<p  class='ps-4'>出店者（商品販売元）へのお問い合わせについては、<a href='shops.php'>Shopsページ</a>より問合せをお願いいたします。</p>
							<p  class='ps-4'>当サイトに関するお問い合わせは<a href='https://site.greeen-sys.com/contact-page/'>https://site.greeen-sys.com/contact-page/</a>よりお願いいたします。</p>
						</div>
						
						<div class='mb-3'>
							※当社：当サイト運営者。　出店者：当サイトを通じて商品を販売する業者。
						</div>
						<div class='mb-3'>
							【2025年05月22日改定】
							【2024年11月22日制定】
						</div>
					</div>-->
					<div class='p-5'>
						<div class='mb-3'>
							<h4>１ プラットフォーム運営事業者</h4>
							<div class='ms-4' style='width:100px;height:100px;'><img src='img/msystem_logo.png' class='img-fluid'></div>
							<p  class='ps-4'><a href='https://site.greeen-sys.com/'>https://site.greeen-sys.com/</a></p>
							<p  class='ps-4'>[ Midori System ]</p>
							<p  class='ps-4'>運営責任者：田村良太</p>
							<p  class='ps-4'>所在地：〒263-0016 千葉県千葉市稲毛区天台3-5-7</p>
							<p  class='ps-4'>TEL：090-6415-6202</p>
							<p  class='ps-4'>サポート対応時間：平日１０：００～２０：００（祝日・年末年始を除く）</p>
						</div>
						<div class='mb-3'>
							<h4>２  お問い合わせ窓口  </h4>
							<p  class='ps-4'>・当サイト・システムに関するお問い合わせ： <a href='https://site.greeen-sys.com/contact-page/'> https://site.greeen-sys.com/contact-page/ </a>（メール：<a href="mailto:support@site.greeen-sys.com">support@site.greeen-sys.com</a>）</p>
							<p  class='ps-4'>・商品・配送・出店者に関するお問い合わせ： 各商品販売ページ、または<a href='shops.php'>Shopsページ</a>より対象の出店者へ直接ご連絡ください。</p>
						</div>
						<div class='mb-3'>
							<h4>３ 販売業者（出店者）  </h4>
							<p  class='ps-4'>当サイトはプラットフォームであり、販売主体は各出店者となります。出店者の氏名（法人名）、住所、電話番号等の詳細は、各商品ページまたは出店者個別ページ（<a href='shops.php'>Shopsページ</a>）をご確認ください。</p>
						</div>
						<div class='mb-3'>
							<h4>４ 販売価格</h4>
							<p  class='ps-4'>各商品ページに表示された価格に基づきます（表示価格は消費税込み）。</p>
						</div>
						<div class='mb-3'>
							<h4>５ 商品代金以外の必要料金 </h4>
							<p  class='ps-4'>配送料：各商品ページに記載の金額</p>
							<p  class='ps-4'>銀行振込手数料：銀行振込決済をご利用の場合</p>
						</div>

						<div class='mb-3'>
							<h4>６ お支払方法</h4>
							<p  class='ps-4'>銀行振込、クレジットカード決済（Visa, Mastercard, American Express, JCB, Diners Club, Discover）、およびその他各商品ページに記載の決済手段。</p>
						</div>
						<div class='mb-3'>
							<h4>７ 代金の支払時期</h4>
							<p  class='ps-4'>出店者よりご請求のメールがお客様へ送信されます。</p>
							<p  class='ps-4'>メール内に支払方法をご案内するURLより決済したタイミングで支払が確定いたします。</p>
							<p  class='ps-4'><a href='https://onlineshop-test.greeen-sys.com/payment.php?key=493256316948735441456d61324a3058496f704968673d3d&val=5980&no=01234567'>決済ページのサンプルはコチラ</a></p>
						</div>
						<div class='mb-3'>
							<h4>８ 商品の引渡時期</h4>
							<p  class='ps-4'>ご注文確定（決済完了）後、各商品ページに記載の発送目安（通常3〜5営業日以内）に従い、出店者より発送いたします。</p>
						</div>
						<div class='mb-3'>
							<h4>９ 返品について</h4>
							<p  class='ps-4'>・不良品・誤配送の場合： 商品到着後8日以内に、販売元の出店者または当社までご連絡のうえ、送料着払いにて出店者までご返送ください。商品到着後、良品と交換または返金いたします。</p>
							<p  class='ps-4'>・お客様都合による返品・交換： 商品発送後の返品・返金・キャンセルには原則として応じかねます。発送前のキャンセルについては、各商品ページの規定に従います。</p>
						</div>
						<div class='mb-3'>
							<h4>１０ キャンセルポリシー</h4>
							<p  class='ps-4'>各商品ページの記載に従います。特段の記載がない場合は、法律に基づき、商品の引渡し（特定権利の移転）が完了した日から数えて8日以内まではキャンセルを受け付けます。ただし、開封済み・使用済みの商品についてはご容赦ください。</p>
						</div>
						<div class='mb-3'>
							<h4>１１ 基本ポリシーと責任範囲</h4>
							<p  class='ps-4'>当サイトのシステム的な不備・不具合については当社が責任を負います。販売される商品については出店者が管理責任を負い、商品・金銭の授受は出店者とお客様の間で実施するものとします。ただし、商品未発送および未支払いに関するトラブルについては、当社が法にのっとり適切に対処いたします。</p>
						</div>
						
						<div class='mb-3'>
							※当社：当サイト運営者。　出店者：当サイトを通じて商品を販売する業者。
						</div>
						<div class='mb-3'>
							【2026年01月23日改定】
							【2024年11月22日制定】
						</div>
					</div>				</div>
		</div>


	</MAIN>
	<FOOTER class='container-fluid common_footer'>
	</FOOTER>
	</div><!--app-->
	<script defer>
		document.getElementById("menu_toiawase").classList.add("active");
	</script>
</BODY>
</html>