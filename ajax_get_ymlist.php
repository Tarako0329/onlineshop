<?php
  require "php_header_admin.php";
	/*
	パラメータ：なし
	概要：アクセス解析の集計データ検索用の日付リスト取得用AJAX
	*/
	//ymlist
	$sql = "WITH RECURSIVE months AS (SELECT '2024-11-01' AS start_date UNION ALL SELECT DATE_ADD(start_date, INTERVAL 1 MONTH) FROM months WHERE start_date < NOW())	SELECT DATE_FORMAT(start_date, '%Y-%m') AS 年月	FROM months ORDER BY DATE_FORMAT(start_date, '%Y-%m') DESC";
	$ymlist = $db->SELECT($sql,[]);
	
	//ylist
	$sql = "WITH RECURSIVE months AS ( SELECT '2024-01-01' AS start_date UNION ALL SELECT DATE_ADD(start_date, INTERVAL 1 YEAR) FROM months WHERE start_date < now() ) SELECT DATE_FORMAT(start_date, '%Y') AS 年月 FROM months ORDER BY DATE_FORMAT(start_date, '%Y') DESC;";
	$ylist = $db->SELECT($sql,[]);
	$data = array(
		'ymlist' => $ymlist
		,'ylist' => $ylist
	);
	
	header('Content-type: application/json');  
	echo json_encode($data, JSON_UNESCAPED_UNICODE);
	exit();
?>