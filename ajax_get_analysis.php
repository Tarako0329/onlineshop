<?php
  require "php_header.php";
	$rtn = csrf_checker(["shouhinMS.php"],["P","C","S"]);
	if(1<>1){
	  $msg=$rtn;
	  $alert_status = "alert-warning";
	  $reseve_status = true;
	}else{
	  //log_writer('\$_SESSION["uid"]',++$a);
		$askNO = rot13decrypt2($_SESSION["askNO"]);
		
		$sql = "WITH RECURSIVE cal AS (
								SELECT '2024-12-01' AS date  -- 開始日
								UNION ALL
								SELECT DATE_ADD(cal.date, INTERVAL 1 DAY)
								FROM cal
								WHERE cal.date < '2024-12-31'  -- 終了日
						)
						SELECT 
							cal.date
								,IFNULL(jisseki.訪問者数,0) as 訪問者数
								,IFNULL(jisseki.初訪問,0) as 初訪問
								,IFNULL(jisseki.再訪問,0) as 再訪問
						FROM cal
						LEFT JOIN
						(SELECT 
							AL.date
							,count(*) as 訪問者数 
							,sum(if(bot='first',1,0)) as 初訪問 
							,sum(if(bot='repeater',1,0)) as 再訪問 
						FROM access_log AL 
						inner join ( SELECT date,mark_id,min(SEQ) as minseq FROM `access_log` where bot <> 'bot' group by date,mark_id ) as MIN_DATA 
						ON AL.SEQ = MIN_DATA.minseq 
						group by AL.date 
						) as jisseki
						ON cal.date = jisseki.date
						ORDER BY cal.date DESC";
		$stmt = $pdo_h->prepare($sql);
		//$stmt->bindValue("askNO", $askNO, PDO::PARAM_INT);
		//$stmt->bindValue("askNO", 0, PDO::PARAM_INT);
		$stmt->execute();
		$data = $stmt->fetchAll(PDO::FETCH_ASSOC);
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