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
							<h4>０ 当サイトのご利用にあたり</h4>
							<p  class='ps-4 mb-2'>当サイトを利用する事業者は<a href='privacy_policy.php' target="_blank" rel="noopener noreferrer">プライバシーポリシー</a>、<a href='https://www.no-trouble.caa.go.jp/what/mailorder/' target="_blank" rel="noopener noreferrer">特定商取引法に基づく表記</a>を理解し、順守することに同意するものとします。</p>
							<p  class='ps-4 mb-2'>違反が発覚した場合は即販売停止とします。</p>
						</div>
						<div class='mb-3'>
							<h4>１ 当サイトがご提供する機能について </h4>
							<p  class='ps-4 mb-2'>当サイトはインターネット販売の機会を提供する目的で運営されてます。</p>
							<p  class='ps-4 mb-3'>商品の発送については事業者様にて行って頂きます。</p>
							<p  class='ps-4 mb-2'>当サイトでは事業者様・お客様間での入出金仲介は行いません。</p>
							<p  class='ps-4 mb-2'>お客様が利用できるお支払い方法は、事業者様にてご指定頂きます。(振込・代引き・QR払など)</p>
							<p  class='ps-4'>なお、当サイトが提供するクレジット払いをご利用いただく場合、クレジット払い分のみ、Strip社がお客様と事業主様の間に入り</p>
							<p  class='ps-4'>Strip社 経由で売上の入出金を管理することになります。</p>



							<p  class='ps-4' style='color:red;'>※次項のシステム利用料金とは別に、クレジット決済手数料として税込決済額の3.6%がStrip社より徴収されます。</p>
							<p  class='ps-4' style='color:red;'>(売上1000円の場合、手数料36円が天引され964円の入金)</p>
							<p  class='ps-4' style='color:red;'>お客様のクレジット情報についてはStrip社のシステムで安全に管理されます。</p>
							<p  class='ps-4' >Stripe社について詳しく知りたい方はＨＰを参照してください。(<a href='https://stripe.com/jp' target="_blank" rel="noopener noreferrer">https://stripe.com/jp</a>)</p>
						</div>
						<div class='mb-3'>
							<h4>２ システム利用料金 </h4>
							<p  class='ps-4'>毎月１日より月末日までの税抜の総受注額をもとに、以下テーブルにのっとりご利用料金を決定いたします。 </p>
							<p  class='ps-4' style='color:red;'>※ご請求額は税抜の受注金額をもとに算出されます。キャンセル対応を行った場合、必ずお客様にキャンセル入力をして頂いてください。</p>
							<div class='col-12 col-lg-5 ps-4'>
							<table class='table'>
								<thead>
									<tr>
										<th>受注金額(以上～未満)</th>
										<th>適用料率</th>
									</tr>
								</thead>
								<tbody>
									<tr>
										<td>0 ～ 10万未満</td><td>0%</td>
									</tr>
									<tr>
										<td>10万 ～ 15万未満</td><td>1%</td>
									</tr>
									<tr>
										<td>15万 ～ 20万未満</td><td>1.5%</td>
									</tr>
									<tr>
										<td>20万 ～ 25万未満</td><td>2%</td>
									</tr>
									<tr>
										<td>25万 ～ 30万未満</td><td>2.5%</td>
									</tr>
									<tr>
										<td>30万 ～ 35万未満</td><td>3%</td>
									</tr>
									<tr>
										<td>35万 ～ 40万未満</td><td>3.5%</td>
									</tr>
									<tr>
										<td>40万 ～ 45万未満</td><td>4%</td>
									</tr>
									<tr>
										<td>45万 ～ 50万未満</td><td>4.5%</td>
									</tr>
									<tr>
										<td>50万 ～ 55万未満</td><td>5%</td>
									</tr>
									<tr>
										<td>55万 ～ 60万未満</td><td>5.5%</td>
									</tr>
									<tr>
										<td>60万 ～ 65万未満</td><td>6%</td>
									</tr>
									<tr>
										<td>65万 ～ 70万未満</td><td>6.5%</td>
									</tr>
									<tr>
										<td>70万 ～ 75万未満</td><td>7%</td>
									</tr>
									<tr>
										<td>75万 ～ 80万未満</td><td>7.5%</td>
									</tr>
									<tr>
										<td>80万 ～ 85万未満</td><td>8%</td>
									</tr>
									<tr>
										<td>85万 ～ 90万未満</td><td>8.5%</td>
									</tr>
									<tr>
										<td>90万 ～ 95万未満</td><td>9%</td>
									</tr>
									<tr>
										<td>95万 ～ 100万未満</td><td>9.5%</td>
									</tr>
									<td>100万 ～ </td><td>10%</td>
								</tr>
									</tr>
								</tbody>
							</table>
							</div>
							<p  class='ps-4'>例外パターン１：月の受注額が10万円に満たない場合</p>
							<p  class='ps-4'>受注実績額はそのまま翌月に繰越され、10万円を超えた時点で1%(1000円)のシステム利用料をご請求となります。繰越は一旦リセットされます。</p>
							<p  class='ps-4'>繰越リセット後、同月の追加売上が10万円未満だった場合、再度翌月に繰り越されます。</p>
							<div class='col-12 ps-4'>
								<img src='img/kurikoshi-1.png' style="width:100%;">
							</div>
							<p  class='ps-4'>繰越リセット後、同月の追加売上が10万円以上だった場合、上のテーブルに基づき、追加売上分の利用料金をご請求いたします。</p>
							<div class='col-12 ps-4'>
								<img src='img/kurikoshi-2.png' style="width:100%;">
							</div>
						</div>
						<div class='mb-3'>
							<h4>３ ご請求について </h4>
							<p class='ps-4'>ご利用料金については<a href='seikyu_yotei.php?key=<?php echo $user_hash;?>'>コチラ</a>にてリアルタイムでご確認いただけます。 </p>
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