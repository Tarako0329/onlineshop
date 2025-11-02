<?php
	require "php_header.php";
	if(empty($_GET["key"])){
		exit();
	}
	$user_hash = $_GET["key"] ;
  $_SESSION["user_id"] = rot13decrypt2($user_hash);
	
	$date2 = new DateTime(date('Y-m-d'));
	$tougetu = $date2->format('Ym');

	if(empty($_GET["ymd"])){
		$date = new DateTime(date('Y-m-d'));
	}else{
		$date = new DateTime(date($_GET["ymd"]));
	}
	$getudo=$date->format('Ym');
	
	//$date->modify('-1 month');
	//$zengetu=$date->format('Ym');



	$sql = "SELECT 
		jisseki.uid
		,jisseki.getudo as 月度
		,ifnull(seikyu.zenkuri,0) as 前月繰越
		,jisseki.juchu_jisseki1
		,jisseki.cancel_jisseki
		,jisseki.juchu_jisseki
		,if(ifnull(seikyu.zenkuri,0)=0,`get_seikyuu_ritu`(jisseki.juchu_jisseki),'０％') as 率
		,if(ifnull(seikyu.zenkuri,0)=0,`get_seikyuu`(jisseki.juchu_jisseki),0) as seikyu
		,if(ifnull(seikyu.zenkuri,0)=0,0,ifnull(seikyu.zenkuri,0)+jisseki.juchu_jisseki) as 累積
		,if(ifnull(seikyu.zenkuri,0)=0,0,if(ifnull(seikyu.zenkuri,0)+jisseki.juchu_jisseki>=100000,100000,0)) as 繰越精算対象額
		,if(ifnull(seikyu.zenkuri,0)<>0 && ifnull(seikyu.zenkuri,0)+jisseki.juchu_jisseki>=100000,'１％','０％') as 累積率
		,if(ifnull(seikyu.zenkuri,0)<>0 && ifnull(seikyu.zenkuri,0)+jisseki.juchu_jisseki>=100000,1000,0) as seikyu2
		,if(ifnull(seikyu.zenkuri,0)<>0 && ifnull(seikyu.zenkuri,0)+jisseki.juchu_jisseki-100000>=100000,ifnull(seikyu.zenkuri,0)+jisseki.juchu_jisseki-100000,0) as 繰越後対象額
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
			SELECT 
				ym.uid,ym.getudo
				,sum(if(DATE_FORMAT(juchuu_date, '%Y%m') = ym.getudo,ifnull(jm.goukeitanka,0),0)) as juchu_jisseki1
				,sum(if(DATE_FORMAT(cancel, '%Y%m') = ym.getudo,(ifnull(jm.goukeitanka,0) * (-1)),0)) as cancel_jisseki 
				,sum(if(DATE_FORMAT(juchuu_date, '%Y%m') = ym.getudo,ifnull(jm.goukeitanka,0),0) + if(DATE_FORMAT(cancel, '%Y%m') = ym.getudo,(ifnull(jm.goukeitanka,0) * (-1)),0)) as juchu_jisseki
			FROM 
			(select *,:getudo as getudo from Users_online) ym
			left join `juchuu_head` jh 
			on ym.uid = jh.uid
			and (
				DATE_FORMAT(juchuu_date, '%Y%m') = ym.getudo
				or
				DATE_FORMAT(cancel, '%Y%m') = ym.getudo
				)
			left join juchuu_meisai jm 
			on jh.orderNO = jm.orderNO 
			group by ym.uid,DATE_FORMAT(juchuu_date, '%Y%m')
		) as jisseki
		left join online_seikyu seikyu
		on jisseki.uid = seikyu.uid
		and jisseki.getudo = seikyu.getudo
		where jisseki.uid = :uid
		order by jisseki.uid,jisseki.getudo;";
		
	$stmt = $pdo_h->prepare($sql);
  $stmt->bindValue("uid", $_SESSION["user_id"], PDO::PARAM_STR);
  $stmt->bindValue("getudo", $getudo, PDO::PARAM_STR);
  $stmt->execute();
  $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

	$sql = "SELECT 
		*
		,if(zenkuri>0 
			,if(zenkuri+jisseki<200000
				,if(zenkuri+jisseki<100000,0,100000)
				,zenkuri+jisseki)
			,if(jisseki<100000,0,jisseki)
		) as taishou
		,LAST_DAY(concat(left(getudo,4),'-',right(getudo,2),'-01')) + INTERVAL 1 DAY as gessho
		,seikyu+seikyu2+seikyu3 as goukei from online_seikyu where uid = :uid and getudo < :getudo order by getudo desc";
	$stmt = $pdo_h->prepare($sql);
  $stmt->bindValue("uid", $_SESSION["user_id"], PDO::PARAM_STR);
	$stmt->bindValue("getudo", $tougetu, PDO::PARAM_STR);
  $stmt->execute();
  $data2 = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
			<div class='col-lg-2 col-0'></div>
			<div class='col-lg-8 col-12'>
				<div class='text-center'> <h1>システム利用料金</h1></div>
				<div class='text-center'> <h2>{{getudo}} 月度ご利用分（翌月１日請求）</h2></div>
				<div class='p-3'>
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
								<tr scope="row"><td>前月繰越</td><td class='text-end'>{{Number(seikyu[0].前月繰越).toLocaleString()}} 円</td><td class='text-center'>-</td><td class='text-end'>-</td></tr>
								<tr scope="row"><th>当月受注</th><td class='text-end'>{{Number(seikyu[0].juchu_jisseki1).toLocaleString()}} 円</td><td class='text-center'>-</td><td class='text-end'>-</td></tr>
								<tr scope="row"><th>当月キャンセル</th><td class='text-end'>{{Number(seikyu[0].cancel_jisseki).toLocaleString()}} 円</td><td class='text-center'>-</td><td class='text-end'>-</td></tr>
								<tr scope="row"><th>当月実績</th><td class='text-end'>{{Number(seikyu[0].juchu_jisseki).toLocaleString()}} 円</td><td class='text-center'>{{seikyu[0].率}}</td><td class='text-end'>{{Number(seikyu[0].seikyu).toLocaleString()}} 円</td></tr>
								<tr v-if='seikyu[0].前月繰越!=0' scope="row"><th>繰越+当月</th><td class='text-end'>{{Number(Number(seikyu[0].juchu_jisseki)+Number(seikyu[0].前月繰越)).toLocaleString()}} 円</td><td class='text-center'>-</td><td class='text-end'>-</td></tr>
								<tr v-if='seikyu[0].前月繰越!=0' scope="row"><th>繰越精算対象額</th><td class='text-end'>{{Number(seikyu[0].繰越精算対象額).toLocaleString()}} 円</td><td class='text-center'>{{seikyu[0].累積率}}</td><td class='text-end'>{{Number(seikyu[0].seikyu2).toLocaleString()}} 円</td></tr>
								<tr v-if='seikyu[0].前月繰越!=0' scope="row"><th>繰越精算後<br>請求対象</th><td class='text-end'>{{Number(seikyu[0].繰越後対象額).toLocaleString()}} 円</td><td class='text-center'>{{seikyu[0].繰越後率}}</td><td class='text-end'>{{Number(seikyu[0].seikyu3).toLocaleString()}} 円</td></tr>
								<tr scope="row"><th>翌月繰越</th><td class='text-end'>{{Number(seikyu[0].kurikoshi).toLocaleString()}} 円</td><td class='text-center'>-</td><td class='text-end'>-</td></tr>
								
							</tbody>
							<tfoot class='table-info'>
								<tr><th colspan="3">合計</th><td class='text-end'>{{Number(Number(seikyu[0].seikyu) + Number(seikyu[0].seikyu2) + Number(seikyu[0].seikyu3)).toLocaleString()}}  円</td></tr>
							</tfoot>
						</table>
					</div>
				</div>
			</div>
			<div class='col-lg-2 col-0'></div>
		</div>
		<div class='row'>
			<div class='col-lg-1 col-0'></div>
		
			<div class='col-lg-10 col-12'>
				<div class='text-center'> <h3> - ご請求済み一覧 -</h3></div>
				<table class='table table-bordered'>
					<thead>
						<tr>
							<th class='text-center'>ご利用月</th>
							<th class='text-center'>前月繰越</th>
							<th class='text-center'>当月受注</th>
							<th class='text-center'>請求対象受注額</th>
							<th class='text-center'>ご利用請求額</th>
							<th class='text-center'>請求日</th>
							<th class='text-center'>次月繰越分</th>
						</tr>
					</thead>
					<tbody>
						<template v-for='(list,index) in seikyu_rireki' :key='list.getudo+list.uid'>
							<tr>
								<td class='text-center'><a :href='`seikyu_yotei.php?key=${hash}&ymd=${list.gessho}`'> {{list.getudo}}</a></td>
								<td class='text-end'>{{Number(list.zenkuri).toLocaleString()}} 円</td>
								<td class='text-end'>{{Number(list.jisseki).toLocaleString()}} 円</td>
								<td class='text-end'>{{Number(list.taishou).toLocaleString()}} 円</td>
								<td class='text-end'>{{Number(list.goukei).toLocaleString()}} 円</td>
								<td v-if='list.goukei > 0' class='text-center'>{{list.gessho}}</td>
								<td v-else class='text-center'>請求なし</td>
								<td class='text-end'>{{Number(list.kurikoshi).toLocaleString()}} 円</td>

							</tr>
						</template>
					</tbody>
				</table>
			</div>
			<div class='col-lg-1 col-0'></div>
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
				const seikyu_rireki = ref(<?php echo json_encode($data2, JSON_UNESCAPED_UNICODE);?>)
				const today = ref(new Date().toLocaleDateString('sv-SE'))
				const getudo = ref('<?php echo $getudo;?>')
				const hash = ref('<?php echo $user_hash;?>')

				return{
					seikyu,
					seikyu_rireki,
					today,
					getudo,
					hash,
				}
			}
		}).mount('#app');
	</script>
</BODY>
</html>