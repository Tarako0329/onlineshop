<?php
	if (php_sapi_name() != 'cli') {
		//exit('このスクリプトはCLIからのみ実行可能です。');
	}
	$mypath = dirname(__DIR__);
	chdir($mypath);
	require "php_header_admin.php";

	// 送信先のURL
	$url = ROOT_URL.'ajax_get_analysis.php';



	for ($i=2; $i <= 4; $i++) { 
		// 送信したいデータ（連想配列）
		$data = [
		    'an_type' => (string)$i,
		    'from' => '2026-03',
		    'to' => '2026-03',
		    'tani' => 'm',
		    'taishou_all' => 'true',
			'user_id' => '2'
		];

		// データを URLエンコード形式（key1=val1&key2=val2...）に変換
		$options = [
			'http' => [
				'method'  => 'POST',
				'header'  => 'Content-type: application/x-www-form-urlencoded',
				'content' => http_build_query($data)
			]
		];

		// コンテキストを作成
		$context = stream_context_create($options);

		// リクエスト送信とレスポンスの取得
		$response[$i] = file_get_contents($url, false, $context);

		// 結果を表示
		//echo "受信側の応答: " . $response[$i] . "\n";
	}

	echo generateAccessReportTables($response[2], $response[3], $response[4]);


/**
 * アクセスレポートのHTMLテーブル一式を生成する関数
 * 
 * @param array $adEffect 広告宣伝効果の配列
 * @param array $pageViews ページ別訪問者数の配列
 * @param array $repeaters リピーターの訪問状況の配列
 * @return string メール本文に埋め込めるHTML文字列
 */
function generateAccessReportTables(array $adEffect, array $pageViews, array $repeaters): string 
{
    // メールクライアントで崩れないための共通テーブルスタイル（インラインCSS）
    $tableStyle = "style='border-collapse: collapse; width: 100%; margin-bottom: 30px; font-family: sans-serif;'";
    $thStyle    = "style='background-color: #4CAF50; color: white; padding: 10px; text-align: left; border: 1px solid #ddd; font-size: 14px;'";
    $tdStyle    = "style='padding: 10px; border: 1px solid #ddd; font-size: 14px; color: #333;'";
    $bgGray     = "style='background-color: #f9f9f9;'";

    $html = "";

    // --------------------------------------------------
    // 1. 広告宣伝効果テーブルの作成
    // --------------------------------------------------
    $html .= "<h3 style='color: #2E7D32; border-left: 5px solid #4CAF50; padding-left: 10px;'>■ 広告宣伝効果（流入元分析）</h3>";
    $html .= "<table border='1' {$tableStyle}>";
    $html .= "<thead>
                <tr>
                    <th {$thStyle}>集計月</th>
                    <th {$thStyle}>総訪問者数</th>
                    <th {$thStyle}>X(旧Twitter)</th>
                    <th {$thStyle}>Instagram</th>
                    <th {$thStyle}>Facebook</th>
                    <th {$thStyle}>Google</th>
                    <th {$thStyle}>その他</th>
                </tr>
              </thead>";
    $html .= "<tbody>";
    foreach ($adEffect as $row) {
        $html .= "<tr>";
        $html .= "<td {$tdStyle}>" . htmlspecialchars($row['date']) . "</td>";
        $html .= "<td {$tdStyle}><strong>" . htmlspecialchars($row['訪問者数']) . " 名</strong></td>";
        $html .= "<td {$tdStyle}>" . htmlspecialchars($row['X']) . "</td>";
        $html .= "<td {$tdStyle}>" . htmlspecialchars($row['instagram']) . "</td>";
        $html .= "<td {$tdStyle}>" . htmlspecialchars($row['facebook']) . "</td>";
        $html .= "<td {$tdStyle}>" . htmlspecialchars($row['google']) . "</td>";
        $html .= "<td {$tdStyle}>" . htmlspecialchars($row['その他']) . "</td>";
        $html .= "</tr>";
    }
    $html .= "</tbody></table>";

    // --------------------------------------------------
    // 2. ページ別訪問者数テーブルの作成
    // --------------------------------------------------
    $html .= "<h3 style='color: #2E7D32; border-left: 5px solid #4CAF50; padding-left: 10px;'>■ ページ別訪問者数</h3>";
    $html .= "<table border='1' {$tableStyle}>";
    $html .= "<thead>
                <tr>
                    <th {$thStyle}>集計月</th>
                    <th {$thStyle}>商品名 / ページ名</th>
                    <th {$thStyle}>訪問者数</th>
                </tr>
              </thead>";
    $html .= "<tbody>";
    foreach ($pageViews as $index => $row) {
        // 1行ごとに背景色を変えて見やすくする（縞模様）
        $rowStyle = ($index % 2 === 0) ? "" : $bgGray;
        
        $html .= "<tr {$rowStyle}>";
        $html .= "<td {$tdStyle}>" . htmlspecialchars($row['date']) . "</td>";
        $html .= "<td {$tdStyle}>" . htmlspecialchars($row['shouhinNM']) . "</td>";
        $html .= "<td {$tdStyle}>" . htmlspecialchars($row['訪問者数']) . " PV</td>";
        $html .= "</tr>";
    }
    $html .= "</tbody></table>";

    // --------------------------------------------------
    // 3. リピーターの訪問状況テーブルの作成
    // --------------------------------------------------
    $html .= "<h3 style='color: #2E7D32; border-left: 5px solid #4CAF50; padding-left: 10px;'>■ リピーターの行動履歴</h3>";
    $html .= "<table border='1' {$tableStyle}>";
    $html .= "<thead>
                <tr>
                    <th {$thStyle}>アクセス日時</th>
                    <th {$thStyle}>顧客名</th>
                    <th {$thStyle}>流入元SNS</th>
                    <th {$thStyle}>閲覧したページ</th>
                </tr>
              </thead>";
    $html .= "<tbody>";
    foreach ($repeaters as $index => $row) {
        $rowStyle = ($index % 2 === 0) ? "" : $bgGray;
        
        $html .= "<tr {$rowStyle}>";
        $html .= "<td {$tdStyle}>" . htmlspecialchars($row['datetime']) . "</td>";
        $html .= "<td {$tdStyle}><strong>" . htmlspecialchars($row['name']) . " 様</strong></td>";
        
        // SNSの種類によって文字色を変える（視認性アップ）
        $snsColor = ($row['koukoku_sns'] === 'instagram') ? "color: #E1306C; font-weight: bold;" : "color: #666;";
        $html .= "<td {$tdStyle} style='{$snsColor}'>" . htmlspecialchars($row['koukoku_sns']) . "</td>";
        
        $html .= "<td {$tdStyle}>" . htmlspecialchars($row['PAGE_NAME']) . "</td>";
        $html .= "</tr>";
    }
    $html .= "</tbody></table>";

    return $html;
}