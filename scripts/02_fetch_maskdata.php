<?php
$basePath = dirname(__DIR__);
require $basePath . '/vendor/autoload.php';
use Goutte\Client;
$client = new Client();

$pFh = fopen($basePath . '/raw/pharmacyMore.csv', 'r');
$head = fgetcsv($pFh, 2048);
$pharmacyMore = array();
while($line = fgetcsv($pFh, 2048)) {
    $pharmacyMore[$line[0]] = $line[3];
}
fclose($pFh);

$today = date('Ymd');
$codePoolFile = $basePath . '/json/codes.json';
$codePool = array(
    'date' => $today,
    'pool' => array(),
);
if(file_exists($codePoolFile)) {
    $codePool = json_decode(file_get_contents($codePoolFile), true);
    if($codePool['date'] !== $today) {
        $codePool = array(
            'date' => $today,
            'pool' => array(),
        );
    }
}

$maskDataFile = $basePath . '/raw/maskdata.csv';
$client->request('GET', 'https://data.nhi.gov.tw/resource/mask/maskdata.csv');
file_put_contents($maskDataFile, $client->getResponse()->getContent());
$fh2 = fopen($maskDataFile, 'r');
/**
Array
(
    [0] => 醫事機構代碼
    [1] => 醫事機構名稱
    [2] => 醫事機構地址
    [3] => 醫事機構電話
    [4] => 成人口罩總剩餘數
    [5] => 兒童口罩剩餘數
    [6] => 來源資料時間
)
*/
$head = fgetcsv($fh2, 2048);
$maskData = array();
while($line = fgetcsv($fh2, 2048)) {
    if(!isset($codePool['pool'][$line[0]])) {
        $codePool['pool'][$line[0]] = 1;
    }
    $maskData[$line[0]] = $line;
}

$cFh = fopen($basePath . '/raw/custom_notices.csv', 'r');
$head = fgetcsv($cFh, 2048);
/*
Array
(
    [0] => Timestamp
    [1] => 醫事機構代碼
    [2] => 備註文字
    [3] => 藥局網址
)
*/
$notices = array();
while($line = fgetcsv($cFh, 2048)) {
    $notices[$line[1]] = $line;
}

$fh1 = fopen($basePath . '/data.csv', 'r');
$fc = array(
    'type' => 'FeatureCollection',
    'features' => array(),
);
$head = fgetcsv($fh1, 2048);
while($line = fgetcsv($fh1, 2048)) {
    if(!isset($codePool['pool'][$line[0]])) {
        continue;
    }
    $data = array_combine($head, $line);
    if(empty($data['備註']) && isset($pharmacyMore[$line[0]])) {
        $data['備註'] = $pharmacyMore[$line[0]];
    }
    if(!empty($data['TGOS X'])) {
        if(!isset($maskData[$line[0]])) {
            $maskData[$line[0]] = array(
                4 => 0,
                5 => 0,
                6 => date('Y/m/d H:i:s'),
            );
        }
        $f = array(
            'type' => 'Feature',
            'properties' => array(
                'id' => strval($line[0]),
                'name' => strval($data['醫事機構名稱']),
                'phone' => strval($data['電話']),
                'address' => strval($data['地址']),
                'mask_adult' => 0,
                'mask_child' => 0,
                'updated' => strval($maskData[$line[0]][6]),
                'available' => strval($data['固定看診時段']),
                'note' => $data['備註'], // from https://data.nhi.gov.tw/Datasets/DatasetDetail.aspx?id=441&Mid=A111068
                'custom_note' => isset($notices[$line[0]]) ? strval($notices[$line[0]][2]) : '', //藥局自行提供的備註訊息
                'website' => !empty($notices[$line[0]]) ? strval($notices[$line[0]][3]) : '', //藥局自行提供的網址
                'county' => strval($data['縣市']),
                'town' => strval($data['鄉鎮市區']),
                'cunli' => strval($data['村里']),
                'service_periods' => $data['看診星期'], // from https://data.nhi.gov.tw/Datasets/DatasetDetail.aspx?id=441&Mid=A111068
            ),
            'geometry' => array(
                'type' => 'Point',
                'coordinates' => array(
                    floatval($data['TGOS X']),
                    floatval($data['TGOS Y']),
                ),
            ),
        );
        $fc['features'][] = $f;
        unset($maskData[$line[0]]);
    }
}
fclose($fh1);

$jsonString = json_encode($fc, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
file_put_contents($basePath . '/json/points.json', $jsonString);

file_put_contents($codePoolFile, json_encode($codePool));

$errorFh = fopen($basePath . '/error.csv', 'w');
foreach($maskData AS $line) {
    array_pop($line);
    array_pop($line);
    array_pop($line);
    fputcsv($errorFh, $line);
}
fclose($errorFh);
