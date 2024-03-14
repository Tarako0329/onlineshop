<?php
//QRからアクセスされた場合は領収書発行済みとして処理する。
//プレビューの場合、発行済みとするチェックの有無で判断する。
{
	require "php_header.php";
	if(empty($_GET)){
		echo "想定外アクセス。";
		exit();
	}
	$to = empty($_GET["to"])?"3000-01-01":$_GET["to"];
	$from = empty($_GET["from"])?"2000-01-01":$_GET["from"];
	log_writer2("\$to",$to,"lv3");
	log_writer2("\$_SESSION[user_id]",$_SESSION["user_id"],"lv3");
}
use Dompdf\Dompdf;

$sysname="WEBREZ+";
$filename = "unshipped_list";

//売上明細の取得
{
	//未発送商品一覧
	$sql="select BD.shouhinNM,BD.tanka,sum(BD.su) as goukei from juchuu_head HD inner join juchuu_meisai BD on HD.orderNO = BD.orderNO 
	where zei = 0 and sent = 0 and cancel = 0 
	and uid = :uid 
	and CAST(HD.juchuu_date AS DATE) between :from and :to
	group by BD.shouhinNM,BD.tanka order by juchuu_date,shouhinNM";
	$stmt = $pdo_h->prepare($sql);
	$stmt->bindValue("uid", $_SESSION["user_id"], PDO::PARAM_INT);
	$stmt->bindValue("from", $from, PDO::PARAM_STR);
	$stmt->bindValue("to", $to, PDO::PARAM_STR);
	$stmt->execute();
	$result = $stmt->fetchAll(PDO::FETCH_ASSOC);

	log_writer2("\$result",$result,"lv3");

	//受注日別未発送商品一覧
	$sql="select CAST(HD.juchuu_date AS DATE) as order_dt,HD.name,HD.orderNO,HD.sent_flg,HD.yubin,HD.jusho,HD.tel,HD.st_name,HD.st_yubin,HD.st_jusho,HD.st_tel,
	BD.shouhinNM,BD.tanka,(BD.su) as goukei 
	from juchuu_head HD 
	inner join juchuu_meisai BD 
	on HD.orderNO = BD.orderNO 
	where zei = 0 and sent = 0 and cancel = 0 and uid = :uid 
	and CAST(HD.juchuu_date AS DATE) between :from and :to
	order by juchuu_date,HD.name,HD.orderNO,shouhinNM";
	$stmt = $pdo_h->prepare($sql);
	$stmt->bindValue("uid", $_SESSION["user_id"], PDO::PARAM_INT);
	$stmt->bindValue("from", $from, PDO::PARAM_STR);
	$stmt->bindValue("to", $to, PDO::PARAM_STR);
	$stmt->execute();
	$result2 = $stmt->fetchAll(PDO::FETCH_ASSOC);

	log_writer2("\$result2",$result2,"lv3");

	$i=0;
	$meisai="";
	$meisai2="";
	$ZeiKei="";

	foreach($result as $row){
		$meisai .= "<tr><td>".$row["shouhinNM"]."</td><td class='meisaival'>".number_format($row["tanka"])."</td><td class='meisaival'>".number_format($row["goukei"])."</td><td></td></tr>\n";
	}

	foreach($result2 as $row){
		if($i===0){
			$meisai2 .= "<tr><td style='text-align:left;padding-top:5px;' colspan='5'>".$row["order_dt"]."<br>".$row["name"]." 様 受付番号：".$row["orderNO"]."</td></tr>";
		}else if($result2[$i-1]["order_dt"].$result2[$i-1]["name"] <> $row["order_dt"].$row["name"]){
			$meisai2 .= "<tr><td style='text-align:left;padding-top:5px;' colspan='5'>".$row["order_dt"]."<br>".$row["name"]." 様 受付番号：".$row["orderNO"]."</td></tr>";
		}

		$meisai2 .= "<tr><td></td><td>".$row["shouhinNM"]."</td><td class='meisaival'>".number_format($row["tanka"])."</td><td class='meisaival'>".number_format($row["goukei"])."</td><td></td></tr>\n";

		$i++;
	}
}

$print_time=date('Y-m-d H:i:s');
$message="";

// PDFにする内容をHTMLで記述
$html = <<< EOM
<html>
	<head>
		<meta charset='utf-8'>
		<style>
			html{
				font-family:ipagp;
			}
			div{
				border:0;
				width:100%;
				text-align: center;
				padding:5px auto;
			}
			p{
				margin-top:5px;
				margin-bottom:0px;
			}
			table{
				margin: 0 auto;
				border:solid;
				border-collapse: collapse;
			}
			th{
				border-bottom:solid;
				border-right:solid 0.5px;
				border-collapse: collapse;
				padding:auto 5px;
			}
			td{
				border:solid 0.5px;
				border-collapse: collapse;
				padding:auto 5px;
			}
		</style>
	</head>
	<body>
		<div style='text-align:left;'>
			powered by <span style='font-family:Kranky;font-weight: bolder;'>$sysname</span><br>
			$print_time<br>
			対象期間　『受注日：$from ～ $to 』
		</div>
		<div style='margin-top:15px;'>
			<span style='font-size:20px;'>【 未発送商品 】</span>
			<table style='width:100%;'>
				<thead>
					<tr>
					<th>商品名</th>
					<th>単価</th>
					<th>数</th>
					<th>チェック</th>
					</tr>
				</thead>
				<tbody>
				$meisai
				</tbody>
			</table>
		</div>
		<div style='margin-top:15px;'>
			<span style='font-size:20px;'>【 未発送商品内訳 】</span>
			<table style='width:100%;'>
				<thead>
					<tr>
					<th></th>
					<th>商品名</th>
					<th>単価</th>
					<th>数</th>
					<th>チェック</th>
					</tr>
				</thead>
				<tbody>
				$meisai2
				</tbody>
			</table>
		</div>
		
	</body>
</html>
EOM;
$html = str_replace(["\r","\n","\t"],"",$html);//改行・タブの削除
try{
	// PDFの設定～出力
	output($html,$filename);
	
}catch(Exception $e){
	$pdo_h->rollBack();
	$sqllog .= rtn_sqllog("rollBack",[]);
	sqllogger($sqllog,$e);
	echo "システム不具合が発生したため、領収書が発行できませんでした。<br>";
	echo "システム管理者に不具合発生を通知いたしました。<br>";
	echo "ご迷惑をおかけいたしますが、復旧までお待ちください。<br>";
	echo "<button onclick='window.close()'>戻る</button>\n";
}

function output($html,$filename){
	$dompdf = new Dompdf();
	$dompdf->loadHtml($html);
	$options = $dompdf->getOptions();
	$options->set(array('isRemoteEnabled' => false));
	$dompdf->setOptions($options);
	$dompdf->setPaper('A4', 'portrait');
	$dompdf->render();
	$dompdf->stream($filename, array('Attachment' => 0));
}
?>