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
					<div class='p-5'>
						<div class='mb-3'>
							<h4>１ サイト運営元・基本ポリシー</h4>
							<p  class='ps-4'>Midori System </p>
							<p  class='ps-4'><a href='https://site.greeen-sys.com/'>https://site.greeen-sys.com/</a></p>
							<p  class='ps-4'>商品サービス販売における商品・金銭の授受に関しては項２以降に基づき、当事者間で責任をもって実施するものとする。</p>
							<p  class='ps-4'>なお、商品未発送、未支払いについては法にのっとり対処するものとする。</p>
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
							<p  class='ps-4'>商品・出店者についての質問・配送状況などについては、商品販売のページより問合せをお願いいたします。</p>
							<p  class='ps-4'>その他、当サイトに関するお問い合わせは<a href='https://site.greeen-sys.com/contact-page/'>https://site.greeen-sys.com/contact-page/</a>よりお願いいたします。</p>
						</div>
						
						<div class='mb-3'>
							※当社とは当サイト運営者、及び当サイトを通じて商品を販売する業者を含みます。
						</div>
						<div class='mb-3'>
						 【2024年11月22日制定】
						</div>
					</div>
				</div>
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