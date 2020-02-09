<?php

$maskDataFile = dirname(__DIR__) . '/raw/maskdata.csv';
file_put_contents($maskDataFile, file_get_contents('http://data.nhi.gov.tw/Datasets/Download.ashx?rid=A21030000I-D50001-001&l=https://data.nhi.gov.tw/resource/mask/maskdata.csv'));

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
    $data = array_combine($head, $line);
    if(!empty($data['TGOS X'])) {
        $f = array(
            'type' => 'Feature',
            'properties' => array(
                'id' => $line[0],
                'name' => $data['醫事機構名稱'],
                'phone' => $data['電話'],
                'address' => $data['地 址 '],
                'mask_adult' => 0,
                'mask_child' => 0,
                'updated' => '',
                'available' => $data['固定看診時段 '],
                'note' => $data['備註'],
                'custom_note' => isset($notices[$line[0]]) ? $notices[$line[0]][2] : '', //藥局自行提供的備註訊息
                'website' => isset($notices[$line[0]]) ? $notices[$line[0]][3] : '', //藥局自行提供的網址
                'county' => $data['縣市'],
                'town' => $data['鄉鎮市區'],
                'cunli' => $data['村里'],
            ),
            'geometry' => array(
                'type' => 'Point',
                'coordinates' => array(
                    $data['TGOS X'],
                    $data['TGOS Y'],
                ),
            ),
        );
        $fc['features'][] = $f;
    }
}
fclose($fh1);

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
foreach($fc['features'] AS $k => $f) {
    if(isset($maskData[$f['properties']['id']])) {
        $total = $maskData[$f['properties']['id']][4] + $maskData[$f['properties']['id']][5];
        $fc['features'][$k]['properties']['mask_adult'] = $maskData[$f['properties']['id']][4];
        $fc['features'][$k]['properties']['mask_child'] = $maskData[$f['properties']['id']][5];
        $fc['features'][$k]['properties']['updated'] = $maskData[$f['properties']['id']][6];
        unset($maskData[$f['properties']['id']]);
    }
    $fc['features'][$k]['properties']['id'] = 'id_' . $fc['features'][$k]['properties']['id'];
}
$jsonString = json_encode($fc, JSON_PRETTY_PRINT | JSON_NUMERIC_CHECK | JSON_UNESCAPED_UNICODE);
$jsonString = str_replace('"id": "id_', '"id": "', $jsonString);
file_put_contents(dirname(__DIR__) . '/json/points.json', $jsonString);

if(!empty($maskData)) {
    $errorFh = fopen(dirname(__DIR__) . '/error.csv', 'w');
    foreach($maskData AS $line) {
        fputcsv($errorFh, $line);
    }
    fclose($errorFh);
}