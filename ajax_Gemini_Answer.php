<?php
//テスト用
require "php_header.php";
use GeminiAPI\Client;
use GeminiAPI\Resources\ModelName;
use GeminiAPI\Resources\Parts\TextPart;

//$ApiKey = 'ここにApiKey入れる';
$client = new Client(GEMINI);
$response = $client->geminiPro()->generateContent(
  new TextPart('ここに質問いれる'),
);

echo $response->text();
?>