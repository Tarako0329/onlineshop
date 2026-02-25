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

	//$an_type = $_POST["an_type"];
	if(1<>1){
	  $msg=$rtn;
	  $alert_status = "alert-warning";
	  $reseve_status = true;
	}else{
		//ymlist
		$sql = "WITH RECURSIVE months AS (SELECT '2024-11-01' AS start_date UNION ALL SELECT DATE_ADD(start_date, INTERVAL 1 MONTH) FROM months WHERE start_date < NOW())	SELECT DATE_FORMAT(start_date, '%Y-%m') AS 年月	FROM months ORDER BY DATE_FORMAT(start_date, '%Y-%m') DESC";
		$stmt = $pdo_h->prepare($sql);
		$stmt->execute();
		$ymlist = $stmt->fetchAll(PDO::FETCH_ASSOC);
		
		//ylist
		$sql = "WITH RECURSIVE months AS ( SELECT '2024-01-01' AS start_date UNION ALL SELECT DATE_ADD(start_date, INTERVAL 1 YEAR) FROM months WHERE start_date < now() ) SELECT DATE_FORMAT(start_date, '%Y') AS 年月 FROM months ORDER BY DATE_FORMAT(start_date, '%Y') DESC;";
		$stmt = $pdo_h->prepare($sql);
		$stmt->execute();
		$ylist = $stmt->fetchAll(PDO::FETCH_ASSOC);

		//log_writer2("\$data",$data,"lv3");
		//log_writer('\$talk',$talk);
		$data = array(
			'ymlist' => $ymlist
			,'ylist' => $ylist
		);
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