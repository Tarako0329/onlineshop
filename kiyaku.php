<?php
	require "php_header.php";
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
	<meta name="robots" content="noindex,nofollow"><!--googleクローラ不要-->
	<TITLE><?php echo TITLE;?> 規約</TITLE>
</head>
<BODY>
	<?php include "header_tag_admin.php"  ?>
	<div id='app'>
	<MAIN class='container common_main' data-bs-spy="scroll">
		<div class='row mb-5'>
				<div class='col-12 '>
					<div class='text-center'> <h1>規約</h1></div>
					<div class='p-5'>
						<div class='mb-3'>
							<h4>１ 当サイトがご提供する機能について </h4>
							<p  class='ps-4'>当サイトはインターネット販売の機会を提供する目的で運営されてます。</p>
							<p  class='ps-4'>商品の発送については事業者様にて行って頂きます。</p>
							<p  class='ps-4'>お客様のお支払い方法は事業者様にてご指定頂きます。当サイトでは事業者様・お客様間での入出金仲介は行いません。</p>
							<p  class='ps-4'>例外として、当サイトが提供しますクレジット支払機能をお客様がご利用いただいた場合、当サイトより事業者様への入金通知を行う機能を有してます。</p>
							<p  class='ps-4' style='color:red;'>クレジット決済機能については、Stripe社が提供する機能を利用しており、次項のシステム利用料金とは別にクレジット決済手数料3.6%が発生します。</p>
							<p  class='ps-4' style='color:red;'>お客様のクレジット情報については当サイト及び事業者様には一切触れることはございません。</p>
							<p  class='ps-4' >Stripe社について詳しく知りたい方はＨＰを参照してください。(<a href='https://stripe.com/jp' target="_blank" rel="noopener noreferrer">https://stripe.com/jp</a>)</p>
						</div>
						<div class='mb-3'>
							<h4>２ システム利用料金 </h4>
							<p  class='ps-4'>毎月１日より月末日までの税抜の総受注額をもとに、以下テーブルにのっとりご利用料金を決定いたします。 </p>
							<p  class='ps-4'>なお、月の受注額が10万円に満たない場合、前月の受注額はそのまま繰越され、10万円を超えた時点で1%(1000円)のシステム利用料をご請求となり、繰越はリセットされます。</p>
							<p  class='ps-4' style='color:red;'>※ご請求額は受注金額をもとに算出されます。キャンセル対応を行った場合、必ずお客様にキャンセル入力をして頂いてください。</p>
							<p  class='ps-4'>例：10月の総受注額が7万円 ⇒ 10月利用料0円（翌月は受注額7万円からスタート）</p>
							<p  class='ps-4'>　　11月15日で総受注額が10万2千円 ⇒ 15日時点で1000円の利用料が確定。</p>
							<p  class='ps-4'>　　11月分は2000円スタートで15日から末日までの総受注額をもとに御請求となります。</p>
							<table class='table'>
								<thead>
									<tr>
										<th>受注金額(以上～未満)</th>
										<th>適用料率</th>
										<th>金額</th>
									</tr>
								</thead>
								<tbody>
									<tr>
										<td>0 ～ 10万未満</td><td>0%</td><td>0円</td>
									</tr>
									<tr>
										<td>10万 ～ 15万未満</td><td>1%</td><td>0円</td>
									</tr>
									<tr>
										<td>15万 ～ 20万未満</td><td>1.5%</td><td>0円</td>
									</tr>
									<tr>
										<td>20万 ～ 25万未満</td><td>2%</td><td>0円</td>
									</tr>
									<tr>
										<td>25万 ～ 30万未満</td><td>2.5%</td><td>0円</td>
									</tr>
									<tr>
										<td>30万 ～ 35万未満</td><td>3%</td><td>0円</td>
									</tr>
									<tr>
										<td>35万 ～ 40万未満</td><td>3.5%</td><td>0円</td>
									</tr>
									<tr>
										<td>40万 ～ 45万未満</td><td>4%</td><td>0円</td>
									</tr>
									<tr>
										<td>45万 ～ 50万未満</td><td>4.5%</td><td>0円</td>
									</tr>
									<tr>
										<td>50万 ～ 55万未満</td><td>5%</td><td>0円</td>
									</tr>
									<tr>
										<td>55万 ～ 60万未満</td><td>5.5%</td><td>0円</td>
									</tr>
									<tr>
										<td>60万 ～ 65万未満</td><td>6%</td><td>0円</td>
									</tr>
									<tr>
										<td>65万 ～ 70万未満</td><td>6.5%</td><td>0円</td>
									</tr>
									<tr>
										<td>70万 ～ 75万未満</td><td>7%</td><td>0円</td>
									</tr>
									<tr>
										<td>75万 ～ 80万未満</td><td>7.5%</td><td>0円</td>
									</tr>
									<tr>
										<td>80万 ～ 85万未満</td><td>8%</td><td>0円</td>
									</tr>
									<tr>
										<td>85万 ～ 90万未満</td><td>8.5%</td><td>0円</td>
									</tr>
									<tr>
										<td>90万 ～ 95万未満</td><td>9%</td><td>0円</td>
									</tr>
									<tr>
										<td>95万 ～ 100万未満</td><td>9.5%</td><td>0円</td>
									</tr>
									<td>100万 ～ </td><td>10%</td><td>0円</td>
								</tr>
									</tr>
								</tbody>
							</table>
						</div>
						<div class='mb-3'>
							<h4>３ ご請求について </h4>
							<p class='ps-4'>ご利用料金については<a href='#'>コチラ</a>にてリアルタイムでご確認いただけます。 </p>
							<p class='ps-4'>月末締め後、翌10日前後までにメールにてご請求をいたしますので末日までのお支払いをお願いいたします。 </p>
						</div>
						<div class='mb-3'>
							<h4>４ 返品対応について  </h4>
							<p  class='ps-4'>お客様に納品した商品に不備(不良品)があった場合、必ず返品対応をするようにお願いいたします。（破損・期限切れ等）</p>
							<p  class='ps-4'>その他、返品交換などについては各事業者様のご判断にてお願いいたします。</p>
							<p  class='ps-4'>また、サイト設定より必ずキャンセル規定を記載するようにしてください。 </p>
							<p  class='ps-4'>特段記載なき場合は、法律により『商品の引渡し（特定権利の移転）が完了した日から数えて８日以内』まではキャンセルを受け付ける義務が生じます。 </p>
						</div>
						<div class='mb-3'>
							<h4>５ その他 </h4>
							<p  class='ps-4'>プライバシーポリシー、特定商取引法に基づく表記  を順守するようお願いします。</p>
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
	<script src="script/vue3.js?<?php echo $time; ?>"></script>
	<script defer>
		admin_menu('kiyaku.php','','<?php echo $user_hash;?>').mount('#admin_menu');
	</script>
</BODY>
</html>