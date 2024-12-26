<?php
  require "php_header.php";
	/*
	パラメータ
	集計単位⇒年/月/日
	集計期間
	集計タイプ⇒１：新規ORリピーター　２：どこ経由　３：広告宣伝効果　４：商品別
	*/
	$rtn = csrf_checker(["shouhinMS.php"],["P","C","S"]);
	log_writer2("\$POST",$_POST,"lv3");

	$an_type = $_POST["an_type"];
	$from = $_POST["from"];
	$to = $_POST["to"];
	$tani = $_POST["tani"];

	if($tani==='y'){
		$from = $from."-01-01";
		$to = $to."-12-31";
		$word1 = "YEAR";
		$word2 = "YEAR(AL.date)";
		$word3 = "DATE_FORMAT(cal.date, '%Y')";
	}else{//m or d
		$from = $from."-01";
		$to = get_getsumatsu(str_replace("-","",$to));

		if($tani==='m'){
			$word1 = "MONTH";
			$word2 = "CONCAT(YEAR(AL.date), '-', MONTH(AL.date))";
			$word3 = "DATE_FORMAT(cal.date, '%Y-%m')";
		}else{//d
			$word1 = "DAY";
			$word2 = "(AL.date)";
			$word3 = "cal.date";
		}
	}

	if(1<>1){
	  $msg=$rtn;
	  $alert_status = "alert-warning";
	  $reseve_status = true;
	}else{
	  //log_writer('\$_SESSION["uid"]',++$a);
		$askNO = rot13decrypt2($_SESSION["askNO"]);
		
		if($an_type == 1){
			$sql = "WITH RECURSIVE cal AS (
								SELECT :from1 AS date 
								UNION ALL
								SELECT DATE_ADD(cal.date, INTERVAL 1 ".$word1.")
								FROM cal
								WHERE cal.date < :to1
						)
						SELECT 
						".$word3." as date
							,IFNULL(jisseki.訪問者数,0) as 訪問者数
							,IFNULL(jisseki.初訪問,0) as 初訪問
							,IFNULL(jisseki.再訪問,0) as 再訪問
						FROM cal
						LEFT JOIN
						(SELECT 
							".$word2." as date
							,count(*) as 訪問者数 
							,sum(if(bot='first',1,0)) as 初訪問 
							,sum(if(bot='repeater',1,0)) as 再訪問 
						FROM access_log AL 
						inner join ( SELECT date,mark_id,min(SEQ) as minseq FROM `access_log` where bot <> 'bot' and date between :from2 and :to2 group by date,mark_id ) as MIN_DATA 
						ON AL.SEQ = MIN_DATA.minseq 
						group by 
							".$word2."
						) as jisseki
						ON ".$word3." = jisseki.date
						ORDER BY ".$word3." DESC";
		}else if($an_type == 2){
			$sql = "WITH RECURSIVE cal AS (
								SELECT :from1 AS date 
								UNION ALL
								SELECT DATE_ADD(cal.date, INTERVAL 1 ".$word1.")
								FROM cal
								WHERE cal.date < :to1
						)
						SELECT 
							".$word3." as date
							,IFNULL(jisseki.訪問者数,0) as 訪問者数
							,IFNULL(jisseki.X ,0) as X
							,IFNULL(jisseki.instagram,0) as instagram
							,IFNULL(jisseki.facebook,0) as facebook
							,IFNULL(jisseki.google,0) as google
							,IFNULL(jisseki.訪問者数,0)-IFNULL(jisseki.X ,0)-IFNULL(jisseki.instagram,0)-IFNULL(jisseki.facebook,0)-IFNULL(jisseki.google,0) as その他
						FROM cal
						LEFT JOIN
						(SELECT 
							".$word2." as date
							,count(*) as 訪問者数 
							,sum(if(ref like '%//t.co/%',1,0)) as X
							,sum(if(ref like '%instagram.com%',1,0)) as instagram 
							,sum(if(ref like '%facebook%',1,0)) as facebook 
							,sum(if(ref like '%google%',1,0)) as google 
							FROM access_log AL 
							inner join ( SELECT date,mark_id,min(SEQ) as minseq FROM `access_log` where bot <> 'bot' and date between :from2 and :to2 group by date,mark_id ) as MIN_DATA 
							ON AL.SEQ = MIN_DATA.minseq 
							group by 
								".$word2."
							) as jisseki
							ON ".$word3." = jisseki.date
							ORDER BY ".$word3." DESC";
		}
		log_writer2("\$sql",$sql,"lv3");
		$stmt = $pdo_h->prepare($sql);
		$stmt->bindValue("from1", $from, PDO::PARAM_STR);
		$stmt->bindValue("to1", $to, PDO::PARAM_STR);
		$stmt->bindValue("from2", $from, PDO::PARAM_STR);
		$stmt->bindValue("to2", $to, PDO::PARAM_STR);

		$stmt->execute();
		$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

		//log_writer2("\$data",$data,"lv3");
		//log_writer('\$talk',$talk);
	}
	header('Content-type: application/json');  
	echo json_encode($data, JSON_UNESCAPED_UNICODE);
	exit();
?>


(
    SELECT '2024-01-01' AS date  -- 開始日
    UNION ALL
    SELECT DATE_ADD(cal.date, INTERVAL 1 DAY)
    FROM cal
    WHERE cal.date < '2024-12-31'  -- 終了日
)