<?php
	require "php_header.php";
	$user_hash = $_GET["key"] ;
  $_SESSION["user_id"] = rot13decrypt2($user_hash);

	$date = new DateTime(date('Y-m-d'));
	//$date = new DateTime('2024-03-01');
	//echo $date->format('Ym')."<br>";
	$getudo=$date->format('Ym');

	$date->modify('+1 month');
	//echo $date->format('Y-m-d');
	//echo $date->format('Ym');
	$yokugetu=$date->format('Ym');

	//echo $getudo."<br>".$yokugetu;
	//$getudo="202403";
	//$yokugetu="202403";

	$sql = "select 
		jisseki.uid
		,jisseki.nendo as 月度
		,ifnull(seikyu.zenkuri,0) as 前月繰越
		,jisseki.juchu_jisseki
		,if(ifnull(seikyu.zenkuri,0)=0,`get_seikyuu_ritu`(jisseki.juchu_jisseki),'０％') as 率
		,if(ifnull(seikyu.zenkuri,0)=0,`get_seikyuu`(jisseki.juchu_jisseki),0) as seikyu
		,if(ifnull(seikyu.zenkuri,0)=0,0,ifnull(seikyu.zenkuri,0)+jisseki.juchu_jisseki) as 累積
		,if(ifnull(seikyu.zenkuri,0)<>0 && ifnull(seikyu.zenkuri,0)+jisseki.juchu_jisseki>=100000,'１％','０％') as 累積率
		,if(ifnull(seikyu.zenkuri,0)<>0 && ifnull(seikyu.zenkuri,0)+jisseki.juchu_jisseki>=100000,1000,0) as seikyu2
		,if(ifnull(seikyu.zenkuri,0)<>0 && ifnull(seikyu.zenkuri,0)+jisseki.juchu_jisseki-100000>0,ifnull(seikyu.zenkuri,0)+jisseki.juchu_jisseki-100000,0) as 繰越後対象額
		,if(ifnull(seikyu.zenkuri,0)<>0 && ifnull(seikyu.zenkuri,0)+jisseki.juchu_jisseki-100000>=100000,`get_seikyuu_ritu`(ifnull(seikyu.zenkuri,0)+jisseki.juchu_jisseki-100000),'０％') as 繰越後率
		,if(ifnull(seikyu.zenkuri,0)<>0 && ifnull(seikyu.zenkuri,0)+jisseki.juchu_jisseki-100000>=100000,`get_seikyuu`(ifnull(seikyu.zenkuri,0)+jisseki.juchu_jisseki-100000),0) as seikyu3
		,case 
			when ifnull(seikyu.zenkuri,0) = 0 and juchu_jisseki >= 100000 then 0
			when ifnull(seikyu.zenkuri,0) + juchu_jisseki < 100000 then ifnull(seikyu.zenkuri,0) + juchu_jisseki
			when ifnull(seikyu.zenkuri,0) + juchu_jisseki >= 100000 and ifnull(seikyu.zenkuri,0) + juchu_jisseki - 100000 < 100000 then ifnull(seikyu.zenkuri,0) + juchu_jisseki - 100000
			else 0
		end as kurikoshi
		from
		(
			SELECT ym.uid,:getudo as nendo,DATE_FORMAT(juchuu_date, '%Y%m') as getudo,sum(ifnull(jm.goukeitanka,0)) as juchu_jisseki 
			FROM 
			Users_online ym
			left join `juchuu_head` jh 
			on ym.uid = jh.uid
			and cancel=0
			and DATE_FORMAT(juchuu_date, '%Y%m') = :getudo2
			left join juchuu_meisai jm 
			on jh.orderNO = jm.orderNO 
			group by ym.uid,DATE_FORMAT(juchuu_date, '%Y%m')
		) as jisseki
		left join online_seikyu seikyu
		on jisseki.uid = seikyu.uid
		and seikyu.getudo = :getudo3
		where jisseki.uid = :uid
		order by jisseki.uid,jisseki.getudo;";

	$stmt = $pdo_h->prepare($sql);
  $stmt->bindValue("uid", $_SESSION["user_id"], PDO::PARAM_STR);
  $stmt->bindValue("getudo", $getudo, PDO::PARAM_STR);
  $stmt->bindValue("getudo2", $getudo, PDO::PARAM_STR);
  $stmt->bindValue("getudo3", $getudo, PDO::PARAM_STR);
  $stmt->execute();
  $data = $stmt->fetchAll(PDO::FETCH_ASSOC);


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
	<TITLE>ご利用料金</TITLE>
</head>
<BODY>
	<?php include "header_tag_admin.php"  ?>
	<div id='app'>
	<MAIN class='container common_main' data-bs-spy="scroll">
		<div class='row mb-5'>
				<div class='col-12 '>
					<div class='text-center'> <h1>システム利用料金</h1></div>
					<div class='p-5'>
						<div class='mb-3'>
							<table class='table table-bordered'>
								<thead class='table-info'>
									<tr>
										<th></th>
										<th class='text-center'>受注実績(税抜)</th>
										<th class='text-center'>手数料率</th>
										<th class='text-center'>ご請求予定額</th>
									</tr>
								</thead>
								<tbody class="table-group-divider">
									<tr scope="row"><td>前月繰越</td><td class='text-end'>{{Number(seikyu[0].前月繰越).toLocaleString()}} 円</td><td class='text-center'>-</td><td>-</td></tr>
									<tr scope="row"><th>当月実績</th><td class='text-end'>{{Number(seikyu[0].juchu_jisseki).toLocaleString()}} 円</td><td class='text-center'>{{seikyu[0].率}}</td><td class='text-end'>{{Number(seikyu[0].seikyu).toLocaleString()}} 円</td></tr>
									<tr scope="row"><th>繰越精算</th><td class='text-end'>{{Number(seikyu[0].累積).toLocaleString()}} 円</td><td class='text-center'>{{seikyu[0].累積率}}</td><td class='text-end'>{{Number(seikyu[0].seikyu2).toLocaleString()}} 円</td></tr>
									<tr scope="row"><th>繰越精算後<br>請求対象</th><td class='text-end'>{{Number(seikyu[0].繰越後対象額).toLocaleString()}} 円</td><td class='text-center'>{{seikyu[0].累積率}}</td><td class='text-end'>{{Number(seikyu[0].seikyu3).toLocaleString()}} 円</td></tr>
									<tr scope="row"><th>翌月繰越</th><td class='text-end'>{{Number(seikyu[0].kurikoshi).toLocaleString()}} 円</td><td class='text-center'>-</td><td>-</td></tr>
									
								</tbody>
								<tfoot class='table-info'>
									<tr><th colspan="3">合計</th><td class='text-end'>{{Number(Number(seikyu[0].seikyu) + Number(seikyu[0].seikyu2) + Number(seikyu[0].seikyu3)).toLocaleString()}}  円</td></tr>
								</tfoot>
							</table>
						</div>
					</div>
				</div>
		</div>


	</MAIN>
	<FOOTER class='container-fluid common_footer'>
	</FOOTER>
	</div><!--app-->
	<script src="script/vue3.js?<?php echo $time; ?>"></script>
	<script>
		admin_menu('seikyu_yotei.php','','<?php echo $user_hash;?>').mount('#admin_menu');
	</script>
	<script>
		createApp({
			setup(){
				const seikyu = ref(<?php echo json_encode($data, JSON_UNESCAPED_UNICODE);?>)

				return{
					seikyu,
				}
			}
		}).mount('#app');
	</script>
</BODY>
</html>