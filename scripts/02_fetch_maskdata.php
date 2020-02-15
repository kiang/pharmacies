<?php
$pFh = fopen(dirname(__DIR__) . '/raw/pharmacyMore.csv', 'r');
$head = fgetcsv($pFh, 2048);
$pharmacyMore = array();
while($line = fgetcsv($pFh, 2048)) {
    $pharmacyMore[$line[0]] = $line[3];
}
fclose($pFh);

$maskDataFile = dirname(__DIR__) . '/raw/maskdata.csv';
file_put_contents($maskDataFile, file_get_contents('http://data.nhi.gov.tw/Datasets/Download.ashx?rid=A21030000I-D50001-001&l=https://data.nhi.gov.tw/resource/mask/maskdata.csv'));
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
    $maskData[$line[0]] = $line;
}

$cFh = fopen(dirname(__DIR__) . '/raw/custom_notices.csv', 'r');
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

$fh1 = fopen(dirname(__DIR__) . '/data.csv', 'r');
$fc = array(
    'type' => 'FeatureCollection',
    'features' => array(),
);
$head = fgetcsv($fh1, 2048);
while($line = fgetcsv($fh1, 2048)) {
    if(!isset($maskData[$line[0]])) {
        continue;
    }
    $data = array_combine($head, $line);
    if(empty($data['備註']) && isset($pharmacyMore[$line[0]])) {
        $data['備註'] = $pharmacyMore[$line[0]];
    }
    if(!empty($data['TGOS X'])) {
        $f = array(
            'type' => 'Feature',
            'properties' => array(
                'id' => strval($line[0]),
                'name' => strval($data['醫事機構名稱']),
                'phone' => strval($data['電話']),
                'address' => strval($data['地 址 ']),
                'mask_adult' => intval($maskData[$line[0]][4]),
                'mask_child' => intval($maskData[$line[0]][5]),
                'updated' => strval($maskData[$line[0]][6]),
                'available' => strval($data['固定看診時段 ']),
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
file_put_contents(dirname(__DIR__) . '/json/points.json', $jsonString);

if(!empty($maskData)) {
    $errorFh = fopen(dirname(__DIR__) . '/error.csv', 'w');
    foreach($maskData AS $line) {
        fputcsv($errorFh, $line);
    }
    fclose($errorFh);
}