<?php
	if (php_sapi_name() != 'cli') {
		//exit('このスクリプトはCLIからのみ実行可能です。');
	}
	$mypath = dirname(__DIR__);
	chdir($mypath);
	require "php_header_admin.php";

	//処理日の前月をyyyy-mm形式で取得
	$prevMonth = date('Y-m', strtotime('-1 month'));

	// ショップリストを取得
	$url_get_shops = ROOT_URL.'ajax_get_ShopList.php';
	$options = [
		'http' => [
			'method' => 'GET',
			'header' => [
				// 自分のファイル名をヘッダーに載せて送る
				"Referer: " .ROOT_URL. basename(__FILE__) 
			]
		]
	];
	$context = stream_context_create($options);
	$data = json_decode(file_get_contents($url_get_shops, false, $context), true);
	
	if(empty($data["Users_online"])){
		U::log("\$data", $data ?? "データが取得できませんでした。",4);
		exit("ショップが見つかりませんでした。");
	}

	// アクセス解析データ取得URL
	$url_get_analysis = ROOT_URL.'ajax_get_analysis.php';
	
	foreach ($data["Users_online"] as $shop) {
		//U::log("ショップ情報", $shop, 4);
		if (empty($shop['mail'])) {continue;}// メールアドレスがない場合はスキップ

		$get_analysis_rtn = true;//レポートデータ取得結果初期値（失敗時にfalseになる想定）
		for ($i=2; $i <= 4; $i++) { 
			// POSTパラメータ
			$data = [
				'an_type' => (string)$i,
				'from' => $prevMonth,
				'to' => $prevMonth,
				'tani' => 'm',
				'taishou_all' => 'true',
				'user_id' => $shop['uid']
			];

			// データを URLエンコード形式（key1=val1&key2=val2...）に変換
			$options = [
				'http' => [
					'method'  => 'POST',
					'header'  => [
						// 自分のファイル名をヘッダーに載せて送る
						"Referer: " .ROOT_URL. basename(__FILE__) 
						,'Content-type: application/x-www-form-urlencoded',
					],
					'content' => http_build_query($data)
				]
			];

			// コンテキストを作成
			$context = stream_context_create($options);

			// レポートデータ取得
			$response[$i] = json_decode(file_get_contents($url_get_analysis, false, $context), true);

			if ($response[$i]['status'] === 'alert-warning') {
				U::log("アクセス解析データの取得に失敗 - タイプ {$i}", $response[$i], 4);
				$get_analysis_rtn = false;
				break; // どれか1つでも失敗したらループを抜ける
			}
		}

		if ($get_analysis_rtn === false) {
			U::log("アクセス解析データの取得に失敗 - ショップ {$shop['yagou']}", $response ?? [], 4);
			continue; // このショップのレポート送信はスキップして次へ
		}
		$report = generateAccessReportTables($response[2] ?? [], $response[3] ?? [], $response[4] ?? []);

		$rtn = U::send_mail(
			$shop['mail']
			,"アクセスレポート"
			,$report
			,APP_NAME
			);

	}
	exit("$prevMonth 分のアクセスレポートの送信が完了しました。");

/**
 * アクセスレポートのHTML（実行前月の挨拶文付き・テーブル内年月なし）を生成する関数
 * 
 * @param array $adEffect 広告宣伝効果の配列
 * @param array $pageViews ページ別訪問者数の配列
 * @param array $repeaters リピーターの訪問状況の配列
 * @return string メール本文にそのまま使えるHTML文字列
 */
function generateAccessReportTables(array $adEffect, array $pageViews, array $repeaters): string 
{
	// --------------------------------------------------
	// 調整可能なパラメータ（ショップ名）
	// --------------------------------------------------
	$appName = "Cafe Present"; 

	// --------------------------------------------------
	// 【修正】実行日時の「前月」を自動計算するロジック
	// --------------------------------------------------
	// 現在日時から1ヶ月前のDateTimeオブジェクトを作成します
	$date = new DateTime();
	$date->modify('-1 month');
	
	// それぞれ「XXXX」年、「XX」月の形式で取得
	$reportYear  = $date->format('Y'); // 例: 2026
	$reportMonth = $date->format('m'); // 例: 04

	// メール全体でデザインが崩れないためのインラインCSS
	$tableStyle = "style='border-collapse: collapse; width: 100%; margin-bottom: 30px; font-family: sans-serif;'";
	$thStyle    = "style='background-color: #4CAF50; color: white; padding: 10px; text-align: left; border: 1px solid #ddd; font-size: 14px;'";
	$tdStyle    = "style='padding: 10px; border: 1px solid #ddd; font-size: 14px; color: #333;'";
	$bgGray     = "style='background-color: #f9f9f9;'";

	// --------------------------------------------------
	// 挨拶文の作成
	// --------------------------------------------------
	$html = "<div style='font-family: sans-serif; font-size: 15px; line-height: 1.6; color: #333; margin-bottom: 30px;'>";
	$html .= "<p>いつも <strong>" . htmlspecialchars($appName) . "</strong> をご贔屓にしていただきありがとうございます。</p>";
	$html .= "<p>" . htmlspecialchars($reportYear) . "年" . htmlspecialchars($reportMonth) . "月のアクセスレポートをお送りいたします。</p>";
	$html .= "</div>";

	// --------------------------------------------------
	// 1. 広告宣伝効果テーブルの作成（集計月を削除）
	// --------------------------------------------------
	$html .= "<h3 style='color: #2E7D32; border-left: 5px solid #4CAF50; padding-left: 10px; font-family: sans-serif;'>■ 広告宣伝効果（流入元分析）</h3>";
	$html .= "<table border='1' {$tableStyle}>";
	$html .= "<thead>
				<tr>
					<th {$thStyle}>総訪問者数</th>
					<th {$thStyle}>X(旧Twitter)</th>
					<th {$thStyle}>Instagram</th>
					<th {$thStyle}>Facebook</th>
					<th {$thStyle}>Google</th>
					<th {$thStyle}>その他</th>
				</tr>
			  </thead>";
	$html .= "<tbody>";
	if(empty($adEffect)){
		$html .= "<tr><td colspan='7' {$tdStyle} style='text-align: center;'>データがありません</td></tr>";
	}
	foreach ($adEffect as $row) {
		$html .= "<tr>";
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
	// 2. ページ別訪問者数テーブルの作成（集計月を削除）
	// --------------------------------------------------
	$html .= "<h3 style='color: #2E7D32; border-left: 5px solid #4CAF50; padding-left: 10px; font-family: sans-serif;'>■ ページ別訪問者数</h3>";
	$html .= "<table border='1' {$tableStyle}>";
	$html .= "<thead>
				<tr>
					<th {$thStyle}>商品名 / ページ名</th>
					<th {$thStyle}>訪問者数</th>
				</tr>
			  </thead>";
	$html .= "<tbody>";
	if(empty($pageViews)){
		$html .= "<tr><td colspan='2' {$tdStyle} style='text-align: center;'>データがありません</td></tr>";
	}
	foreach ($pageViews as $index => $row) {
		$rowStyle = ($index % 2 === 0) ? "" : $bgGray;
		$html .= "<tr {$rowStyle}>";
		$html .= "<td {$tdStyle}>" . htmlspecialchars($row['shouhinNM']) . "</td>";
		$html .= "<td {$tdStyle}>" . htmlspecialchars($row['訪問者数']) . " PV</td>";
		$html .= "</tr>";
	}
	$html .= "</tbody></table>";

	// --------------------------------------------------
	// 3. リピーターの訪問状況テーブルの作成（変更なし）
	// --------------------------------------------------
	$html .= "<h3 style='color: #2E7D32; border-left: 5px solid #4CAF50; padding-left: 10px; font-family: sans-serif;'>■ リピーターの行動履歴</h3>";
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
	if(empty($repeaters)){
		$html .= "<tr><td colspan='4' {$tdStyle} style='text-align: center;'>データがありません</td></tr>";
	}
	foreach ($repeaters as $index => $row) {
		$rowStyle = ($index % 2 === 0) ? "" : $bgGray;
		$html .= "<tr {$rowStyle}>";
		$html .= "<td {$tdStyle}>" . htmlspecialchars($row['datetime']) . "</td>";
		$html .= "<td {$tdStyle}><strong>" . htmlspecialchars($row['name']) . " 様</strong></td>";
		
		$snsColor = ($row['koukoku_sns'] === 'instagram') ? "color: #E1306C; font-weight: bold;" : "color: #666;";
		$html .= "<td {$tdStyle} style='{$snsColor}'>" . htmlspecialchars($row['koukoku_sns']) . "</td>";
		
		$html .= "<td {$tdStyle}>" . htmlspecialchars($row['PAGE_NAME']) . "</td>";
		$html .= "</tr>";
	}
	$html .= "</tbody></table>";

	return $html;
}

?>