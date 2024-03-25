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
	<TITLE><?php echo TITLE;?> 商品管理</TITLE>
</head>
<BODY>
	<?php include "header_tag.php"  ?>
	<div id='app'>
	<MAIN class='container common_main' data-bs-spy="scroll">
		<div class='row mb-5'>
				<div class='col-12 '>
					<div class='text-center'> <h1>プライバシーポリシー</h1></div>
					<div class='p-5'>
						<div class='mb-3'>
						<h4>１ 個人情報の定義 </h4>
						<p  class='ps-4'>本プライバシーポリシーにおける、「個人情報」とは、個人情報の保護に関する法律規定される「個人情報」を指します。 </p>
						</div>
						<div class='mb-3'>
							<h4>２ 個人情報の取得 </h4>
							<p class='ps-4'>当社は、利用目的の達成に必要な範囲で、個人情報を適正に取得し、不正の手段により取得することはありません。 </p>
						</div>
						<div class='mb-3'>
							<h4>２ 個人情報の取得 </h4>
							<p  class='ps-4'>当社は、利用目的の達成に必要な範囲で、個人情報を適正に取得し、不正の手段により取得することはありません。 </p>
						</div>
						<div class='mb-3'>
							<h4>３ 利用目的  </h4>
							<p  class='ps-4'>当社は、取得した個人情報を以下の目的で利用します。  </p>
							<p  class='ps-4'>①当社サービスのおける商品の発送、関連するアフターサービス、新商品サービスに関する情報の通知  </p>
							<p  class='ps-4'>②当社サービスに関するお問い合わせ等への対応   </p>
							<p  class='ps-4'>③当社サービスに関する規約等の変更等の通知   </p>
						</div>
						<div class='mb-3'>
							<h4>４ 第三者提供 </h4>
							<p  class='ps-4'>当社は、法令に定められた場合を除き、あらかじめ利用者の同意を得ないで、第三者（日本国外にあるも者を含みます。）に個人情報を提供しません。  </p>
						</div>
						<div class='mb-3'>
							<h4>５ 開示、訂正、利用停止、削除 </h4>
							<p  class='ps-4'>当社は、利用者から個人情報の開示、訂正、利用停止、削除を求められたときは、法令に定められた場合を除き、本人確認の上で、遅滞なく開示を行います。  </p>
						</div>
						<div class='mb-3'>
							<h4>６ お問い合わせ窓口 </h4>
							<p  class='ps-4'>個人情報の取り扱いに関するお問い合わせは、下記の窓口までお願いいたします。 </p>
						</div>
						<div class='mb-3'>
							<h4>７ プライバシーポリシー </h4>
							<p  class='ps-4'>当社は、必要に応じて、本プライバシーポリシーを変更いたします。 </p>
							<p  class='ps-4'>なお、本プライバシーポリシーを変更する場合は、その内容を当社のウェブサイト上で表示いたします。  </p>
						</div>
						
						<div class='mb-3'>
							※当社とは当サイト運営者、及び当サイトを通じて商品を販売する業者を含みます。
						</div>
						<div class='mb-3'>
						 【2024年3月1日制定】
						</div>
					</div>
				</div>
		</div>


	</MAIN>
	<FOOTER class='container-fluid common_footer'>
	</FOOTER>
	</div><!--app-->
	<script defer>
		document.getElementById("menu_privacy").classList.add("active");
	</script>
</BODY>
</html>