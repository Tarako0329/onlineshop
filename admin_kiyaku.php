<?php
require "php_header.php";
$user_hash = $_GET["key"] ;
$login = false;
?>
<!DOCTYPE html>
<HTML>
	<HEAD>
		<?php
			require "head_admin.php";
		?>
	</HEAD>
	<!--ログイン画面-->
	<TITLE><?php echo TITLE;?></TITLE>
	<BODY>
  <div id='app'>
  <?php include "header_tag_admin.php"  ?>
  <MAIN class='container common_main'>
    <div class='row'>
      <div class = "col-12 col-md-6 mb-5">
				<div style="font-size:1rem;"><h3>**利用規約**</h3></div>
			</div>
			<div class = "col-12 mb-3 p-3">
				<h4>**第1条（適用範囲）**</h4>
				<ol>
					<li>本規約は、[PlesentOnlineショップ]の出店管理用サイト（以下「管理サイト」といいます。）の利用者（以下「利用者」といいます。）と[Midori System]（以下「当社」といいます。）との間の管理サイトの利用に関する一切の関係に適用されます。</li>
					<li>当社が管理サイト上で掲載する個別規定や追加規定は、本規約の一部を構成します。</li>
					<li>本規約の内容と個別規定や追加規定の内容が異なる場合は、個別規定や追加規定の内容が優先して適用されます。</li>
				</ol>
			</div>
			<div class = "col-12 mb-3 p-3">
			<h4>**第2条（利用登録）**</h4>
			<p></p>
			<ol>
				<li>管理サイトの利用を希望する者は、本規約に同意の上、当社の定める方法によって利用登録を行う必要があります。</li>
			</ol>
			</div>
			<div class = "col-12 mb-3 p-3">
			<h4>**第3条（利用者の義務）**</h4>
			<p></p>
			<ol>
				<li>利用者は、本規約および当社の定める方法に従って管理サイトを利用するものとします。</li>
				<li>利用者は、管理サイトの利用にあたり、以下の行為を行ってはなりません。
					<ul>
						<li>当社のサーバーまたはネットワークの機能を破壊または妨害する行為</li>
						<li>当社のサービスの運営を妨害する行為</li>
						<li>他の利用者または第三者の知的財産権、肖像権、プライバシー、名誉、その他の権利または利益を侵害する行為</li>
						<li>その他、当社が不適切と判断する行為</li>
					</ul>
				</li>
			</ol>
			</div>
			<div class = "col-12 mb-3 p-3">
			<h4>**第4条（知的財産権）**</h4>
			<p></p>
			<ol>
				<li>管理サイトに関する知的財産権は、全て当社または当社にライセンスを許諾している者に帰属します。</li>
				<li>利用者は、管理サイトのコンテンツについて、著作権法その他の法令により認められる場合を除き、無断で使用することはできません。</li>
			</ol>
			</div>
			<div class = "col-12 mb-3 p-3">
			<h4>**第5条（免責事項）**</h4>
			<p></p>
			<ol>
				<li>当社は、利用者が管理サイトを利用したことにより生じた損害について、一切の責任を負いません。</li>
				<li>当社は、サービス提供サーバー業者(MixHost)の障害発生時等にサービスを中断することがあります。</li>
			</ol>
			</div>
			<div class = "col-12 mb-3 p-3">
			<h4>**第6条（利用規約の変更）**</h4>
			<p></p>
			<ol>
				<li>当社は、必要と判断した場合、利用者の承諾なく本規約を変更できるものとします。</li>
				<li>変更後の利用規約は、管理サイト上に掲載された時点で効力を生じるものとします。</li>
			</ol>
			</div>
			<div class = "col-12 mb-3 p-3">
			<h4>**第7条（準拠法および管轄裁判所）**</h4>
			<p></p>
			<ol>
				<li>本規約の準拠法は日本法とします。</li>
				<li>本規約に起因または関連する紛争については、[管轄裁判所名]を第一審の専属的合意管轄裁判所とします。</li>
			</ol>
			</div>
			<p>**[会社名:Midori System]**</p>
			<p></p>
			<p>**[制定日:2026/02/20]**</p>
			<!--</p><p>**[改定日:2025/03/11]**-->
			<div>
  </MAIN>
  <FOOTER class='container-fluid common_footer'>
  </FOOTER>
  </div>
  <script src="script/admin_menu.js?<?php echo $time; ?>"></script>
  <script>
    admin_menu('admin_kiyaku.php','','<?php echo $user_hash;?>').mount('#admin_menu');
  </script>
	</BODY>
</HTML>
