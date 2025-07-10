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

// Save to temporary file first
$tempFile = $outputFile . '.tmp';
$bytesWritten = file_put_contents($tempFile, $data);

if ($bytesWritten === false) {
    echo "Failed to write temporary file: $tempFile\n";
    exit(1);
}

echo "Downloaded " . number_format($bytesWritten) . " bytes\n";

// Validate KMZ contents
$zip = new ZipArchive();
if ($zip->open($tempFile) !== TRUE) {
    echo "Failed to open KMZ file\n";
    unlink($tempFile);
    exit(1);
}

// Look for KML file inside the KMZ
$kmlFound = false;
$hasFeatures = false;

for ($i = 0; $i < $zip->numFiles; $i++) {
    $filename = $zip->getNameIndex($i);
    if (pathinfo($filename, PATHINFO_EXTENSION) === 'kml') {
        $kmlFound = true;
        $kmlContent = $zip->getFromIndex($i);
        
        // Check if KML contains any Placemark features
        if (strpos($kmlContent, '<Placemark>') !== false || 
            strpos($kmlContent, '<Placemark ') !== false) {
            $hasFeatures = true;
            
            // Count features for logging
            $placemarkCount = substr_count($kmlContent, '<Placemark');
            echo "Found $placemarkCount Placemark(s) in $filename\n";
        }
        break;
    }
}

$zip->close();

if (!$kmlFound) {
    echo "No KML file found inside KMZ\n";
    unlink($tempFile);
    exit(1);
}

if (!$hasFeatures) {
    echo "No features found in KML - skipping update\n";
    unlink($tempFile);
    exit(0);
}

// Replace the original file only if validation passed
if (rename($tempFile, $outputFile)) {
    echo "Successfully updated: $outputFile\n";
    echo "File size: " . number_format(filesize($outputFile)) . " bytes\n";
    echo "Last modified: " . date('Y-m-d H:i:s', filemtime($outputFile)) . "\n";
} else {
    echo "Failed to update file: $outputFile\n";
    unlink($tempFile);
    exit(1);
}

?>