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
					<div class="col-12 mb-3 p-3">
						<div style="font-size:1rem;">
							<h3>**プライバシーポリシー**</h3>
						</div>
					</div>
					<div class="col-12 mb-3 p-3">
						<h4>**1. 取得する情報**</h4>
						<p>当社は、本サービスの提供にあたり、以下の情報を取得・利用します。</p>
						<ul>
							<li>出店者基本情報: 氏名、屋号、住所、電話番号、銀行口座情報（利用目的：出店審査、売上の精算、本人確認、連絡のため）</li>
						</ul>
						<p>Googleアカウントから取得する情報: Google認証を利用してログインする場合、当社はGoogle APIsを利用して以下の情報を取得します。</p>
						<ul>
							<li>メールアドレス、氏名、プロフィール画像、Google ID（内部識別子）（利用目的：本サービスへのログイン認証、アカウントの紐付け、ユーザー識別のため）</li>
						</ul>
					</div>
					<div class="col-12 mb-3 p-3">
						<h4>**2. Googleデータの取扱いと保護**</h4>
						<p>当社によるGoogleから取得した情報の利用は、Google APIサービスのユーザーデータに関するポリシー（限定的公開要件を含む）に従います。</p>
						<p>取得したGoogleユーザーデータを、ログイン認証以外の目的（広告配信や第三者への売却等）で利用することはありません。</p>
						<p>Googleから取得した情報は、安全なサーバーに適切に保存され、不正アクセス防止措置を講じます。</p>
					</div>
					<div class="col-12 mb-3 p-3">
						<h4>**3. 利用目的**</h4>
						<p>当社は、取得した情報を以下の目的で利用します。</p>
						<ul>
							<li>本サービスの提供・運営（出店審査、店舗管理画面の提供）</li>
							<li>本サービスに関する通知、お問い合わせへの対応</li>
							<li>不正利用の防止およびセキュリティ維持</li>
							<li>当社の新機能、広告、アンケート等の案内</li>
						</ul>
					</div>
					<div class="col-12 mb-3 p-3">
						<h4>**4. 第三者提供**</h4>
						<p>当社は、法令に基づく場合を除き、出店者の同意なく個人情報を第三者に提供することはありません。ただし、以下の場合は除きます。</p>
						<ul>
							<li>決済代行会社への提供: 売上金振込等のため、金融機関や決済代行会社に情報を提供する場合</li>
							<li>配送業者への提供: 配送伝票発行システム等を利用する場合</li>
						</ul>
					</div>
					<div class="col-12 mb-3 p-3">
						<h4>**5. 委託**</h4>
						<p>当社は、利用目的の達成に必要な範囲内で、個人情報の取扱いの全部または一部を外部に委託することがあります（サーバー運営会社、カスタマーサポート代行等）。この際、委託先に対して適切な監督を行います。</p>
					</div>
					<div class="col-12 mb-3 p-3">
						<h4>**6. 個人情報の安全管理**</h4>
						<p>当社は、取得した個人情報の漏洩、滅失または毀損を防止するため、適切な安全管理措置を講じます。</p>
					</div>
					<div class="col-12 mb-3 p-3">
						<h4>**7. Cookie（クッキー）について**</h4>
						<p>当サイトでは、Cookieを使用することがあります。Cookieとは、ウェブサイト訪問者のブラウザに送信される小さなテキストファイルであり、訪問者のコンピュータに保存されます。Cookieを利用することで、訪問者の閲覧履歴や入力情報を記録し、利便性を向上させることができます。訪問者は、ブラウザの設定を変更することで、Cookieの受け入れを拒否することができます。</p>
					</div>
					<div class="col-12 mb-3 p-3">
						<h4>**8. 個人情報の開示・訂正・削除**</h4>
						<p>ご本人からの個人情報の開示・訂正・削除のご請求については、法令に基づき適切に対応いたします。</p>
					</div>
					<div class="col-12 mb-3 p-3">
						<h4>**9. プライバシーポリシーの変更**</h4>
						<p>当社は、必要に応じて本プライバシーポリシーを変更することがあります。変更後のプライバシーポリシーは、当サイトに掲載された時点で効力を生じるものとします。</p>
					</div>
					<div class="col-12 mb-3 p-3">
						<h4>**10. お問い合わせ窓口**</h4>
						<p>個人情報の取り扱いに関するお問い合わせは、以下の窓口までご連絡ください。</p>
					</div>
					<div class="col-12 mb-3 p-3">
						<ul>
							<li>green.green.midori@greeen-sys.com</li>
						</ul>

						<p>**[会社名:Midori System]**</p>
						<p></p>
						<p>**[制定日:2026/02/20]**</p>
						<!--</p><p>**[改定日:2025/03/11]**-->
					</div>
				</div>
			</MAIN>
			<FOOTER class='container-fluid common_footer'>
			</FOOTER>
		</div>
		<script src="script/admin_menu.js?<?php echo $time; ?>"></script>
		<script>
			admin_menu('admin_pbPolicy.php', '', '<?php echo $user_hash;?>').mount('#admin_menu');
		</script>
	</BODY>
</HTML>
