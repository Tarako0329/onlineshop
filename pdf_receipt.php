<?php
{
	require "php_header.php";
	register_shutdown_function('shutdown_page');
	
	if(empty($_GET)){
		echo "想定外アクセス。";
		exit();
	}
	$to = empty($_GET["orderno"])?"3000-01-01":$_GET["to"];
}
use Dompdf\Dompdf;

if(empty($_GET)){
	echo "想定外アクセス。";
	exit();
}
$uid=rot13decrypt2($_GET["hash"]);
//$orderNO=rot13decrypt2($_GET["orderNO"]);
$orderNO=($_GET["val"]);
$type = ($_GET["tp"]==="1"?"領　収　書":"納　品　書");
$filename = ($_GET["tp"]==="1"?"Ryoushusho":"Nouhinsho");

$sysname="cafe present";

$sql="select * from Users_online where uid = ?";
$stmt = $pdo_h->prepare($sql);
$stmt->bindValue(1, $uid, PDO::PARAM_INT);
$stmt->execute();
$userinfo = $stmt->fetch(PDO::FETCH_ASSOC);
$from = $userinfo["yagou"];
$invoice = $userinfo["invoice"];
$add = $userinfo["jusho"];
$inquiry = $userinfo["tel"];

//売上明細の取得
{
$sql="select * from juchuu_head hd inner join juchuu_meisai ms on hd.orderNO = ms.orderNO where hd.orderNO = :orderNO and uid = :uid order by shouhinCD";
$stmt = $pdo_h->prepare($sql);
$stmt->bindValue("uid", $uid, PDO::PARAM_INT);
$stmt->bindValue("orderNO", $orderNO, PDO::PARAM_STR);
$stmt->execute();
$result = $stmt->fetchAll();

$i=0;
$Goukei=0;
$meisai="";
$meisai_postage="";
$ZeiKei="";

log_writer2("\$result",$result,"lv3");
foreach($result as $row){
	if($i===0){
		$insDT = (string)$row["juchuu_date"];
		$Atena = $row["name"];
		$meisai_postage .= "<tr><td style='text-align:left;'>".($row["postage_zeikbn"]=="0"?"(非課税)": "")."送料</td><td class='meisaival'>-";
		$meisai_postage .= "</td><td class='meisaival'>-</td><td class='meisaival'>".number_format($row["postage"]-$row["postage_zei"])."</td></tr>\n";
		$Goukei += $row["postage"]-$row["postage_zei"];
	}

	if($row["su"]<>0){
		$meisai .= "<tr><td style='text-align:left;'>".($row["zeikbn"]=="1001"?"※": ($row["zeikbn"]=="0"?"(非課税)":"")).$row["shouhinNM"]."</td><td class='meisaival'>".number_format($row["su"]);
		$meisai .= "</td><td class='meisaival'>".number_format($row["tanka"])."</td><td class='meisaival'>".number_format($row["goukeitanka"])."</td></tr>\n";
		$Goukei += $row["goukeitanka"];
		$i++;
	}
}
}

//税率ごとの合計
{
$sql="select orderNO,ZeiMS.hyoujimei as 税率,Uri.zeikbn, sum(売上金額) as 売上金額, sum(消費税額) as 消費税額 from 
(select orderNO,zeikbn, (goukeitanka) as 売上金額, (zei) as 消費税額 from juchuu_meisai 
union all
select orderNO,postage_zeikbn, (postage-postage_zei) as 売上金額, (postage_zei) as 消費税額 from juchuu_head ) as
Uri inner join ZeiMS on Uri.zeiKBN = ZeiMS.zeiKBN where orderNO = :orderNO group by orderNO,ZeiMS.hyoujimei,ZeiMS.zeiKBN order by ZeiMS.zeiKBN";
$stmt = $pdo_h->prepare($sql);
$stmt->bindValue("orderNO", $orderNO, PDO::PARAM_STR);
$stmt->execute();
$result = $stmt->fetchAll();
$ZeiGoukei = 0;
foreach($result as $row){
	if($row["売上金額"] <>0){
		$zeigaku = $row["消費税額"] ;
		$ZeiKei .= "<tr><td style='width:30%;'>".$row["税率"]."対象</td><td style='text-align:right;width:30%;'>￥".number_format($row["売上金額"])."-</td><td style='width:20%;'>消費税</td><td style='text-align:right;width:20%;'>	￥".number_format($zeigaku)."-</td></tr>\n";
		$ZeiGoukei += $zeigaku;
	}
}
$Goukei = $Goukei+$ZeiGoukei;
$ZeiGoukei = number_format($ZeiGoukei);
$Goukei = number_format($Goukei);
}
$message="";

$RyoushuuNO = $orderNO;
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
		.meisaival{
			width:80px;
			min-width:50px;
			text-align: right;
			padding:auto 5px;
		}
		.title{
			font-size:30px;
			font-weight: bolder;
			border-top: 4px solid;
			border-bottom: 4px solid;
		}
		.Seikyu{
			font-size:25px;
			font-weight: bolder;
		}
	</style>
</head>
<body>
	<div style='text-align:left;'>
		<span style='font-weight: bolder;'>$sysname</span>
	</div>
	<div style='height:70px;'>
		<span class='title'> - $type - </span>
		$message
	</div>
	<div style='text-align:left;font-size:25px;'>
		<span style='border-bottom:solid;'>$Atena 様</span>
	</div>
	<div style='text-align:right;'>
		<p style='font-size:25px;'>$from</p>
		<p>$invoice</p>
		<p>$add</p>
		<p>取引日時[$insDT]</p>
		<p>伝票番号[$RyoushuuNO]</p>
	</div>
	<div class='Seikyu' style='display:flex;'>
		<table style='width:100%;'>
			<tr>
			<td style='width:30%;'>税込合計金額</td><td style='text-align:right;width:30%;'>￥$Goukei-</td>
			<td style='width:20%;'>内消費税</td><td style='text-align:right;width:20%;'>￥$ZeiGoukei-</td>
			</tr>
		</table>
	</div>
		<table style='width:100%;'>
			$ZeiKei
		</table>
	<div style='margin-top:15px;'>
		<span style='font-size:20px;'>【 内　訳 】</span>
		<table style='width:100%;'>
			<thead>
				<tr>
				<th>商品名</th>
				<th>数</th>
				<th>単価</th>
				<th>金額</th>
				</tr>
			</thead>
			<tbody>
			$meisai
			$meisai_postage
			</tbody>
		</table>
	</div>
	<div style='text-align:left;'>
		※：軽減税率８％対象
	</div>
	
</body>
</html>
EOM;
$html = str_replace(["\r","\n","\t"],"",$html);//改行・タブの削除
try{
$sqllog="";
//if($saiban==="on"){
	$pdo_h->beginTransaction();
	$sqllog .= rtn_sqllog("START TRANSACTION",[]);
	$sql = "insert into ryoushu(uid,R_NO,Atena,html) values(:uid,:R_NO,:Atena,:html)  ON DUPLICATE KEY UPDATE Atena = :Atena2,html=:html2";
	$stmt = $pdo_h->prepare($sql);
	$stmt->bindValue("uid", $id, PDO::PARAM_INT);
	$stmt->bindValue("R_NO", $RyoushuuNO, PDO::PARAM_INT);
	$stmt->bindValue("Atena", $Atena, PDO::PARAM_STR);
	$stmt->bindValue("html", $html, PDO::PARAM_STR);
	$stmt->bindValue("Atena2", $Atena, PDO::PARAM_STR);
	$stmt->bindValue("html2", $html, PDO::PARAM_STR);

	$status = $stmt->execute();
	$sqllog .= rtn_sqllog($sql,[$id,$RyoushuuNO,$UriNo,$Atena,$html,$Atena,$html]);

	$pdo_h->commit();
	$sqllog .= rtn_sqllog("commit",[]);
	sqllogger($sqllog,0);
//}
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