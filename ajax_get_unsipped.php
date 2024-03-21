<?php
  require "php_header.php";
	//log_writer2("\$_GET",$_GET,"lv3");
	if(empty($_GET["hash"])){
    echo "アクセスが不正です。";
    exit();
}
$user_hash = $_GET["hash"] ;
$_SESSION["user_id"] = rot13decrypt2($user_hash);

	$rtn = true;//csrf_checker(["xxx.php","xxx.php"],["P","C","S"]); 
	if($rtn !== true){
	  $msg=$rtn;
	  $alert_status = "alert-warning";
	  $reseve_status = true;
	}else{
		//未発送商品一覧
		$to = empty($_GET["to"])?"3000-01-01":$_GET["to"];
		$from = empty($_GET["from"])?"2000-01-01":$_GET["from"];
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

		$alert_status = "success";
		
		$return = array(
	    "alert" => $alert_status,
	    "result" => $result,
	    "result2" => $result2
	  );
	}
  header('Content-type: application/json');  
  echo json_encode($return, JSON_UNESCAPED_UNICODE);
  exit();
?>
