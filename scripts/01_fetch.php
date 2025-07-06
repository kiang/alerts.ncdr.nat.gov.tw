<?php

$url = 'https://alerts.ncdr.nat.gov.tw/DownLoadNewAssistData.ashx/1';
$outputFile = __DIR__ . '/../docs/typhoon.kmz';

echo "Fetching KMZ data from: $url\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36');
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Accept: */*',
    'Accept-Language: en-US,en;q=0.9',
    'Accept-Encoding: gzip, deflate, br',
    'Connection: keep-alive',
    'Cache-Control: no-cache',
    'Pragma: no-cache'
]);
curl_setopt($ch, CURLOPT_ENCODING, '');

$data = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);

if ($error) {
    echo "cURL Error: $error\n";
    curl_close($ch);
    exit(1);
}

if ($httpCode !== 200) {
    echo "HTTP Error: $httpCode\n";
    curl_close($ch);
    exit(1);
}

curl_close($ch);

if (empty($data)) {
    echo "No data received\n";
    exit(1);
}

$bytesWritten = file_put_contents($outputFile, $data);

if ($bytesWritten === false) {
    echo "Failed to write file: $outputFile\n";
    exit(1);
}

echo "Successfully saved " . number_format($bytesWritten) . " bytes to: $outputFile\n";
echo "File size: " . number_format(filesize($outputFile)) . " bytes\n";
echo "Last modified: " . date('Y-m-d H:i:s', filemtime($outputFile)) . "\n";

?>