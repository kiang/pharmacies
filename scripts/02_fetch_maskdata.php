<?php

$maskDataFile = dirname(__DIR__) . '/raw/maskdata.csv';
file_put_contents($maskDataFile, file_get_contents('http://data.nhi.gov.tw/Datasets/Download.ashx?rid=A21030000I-D50001-001&l=https://data.nhi.gov.tw/resource/mask/maskdata.csv'));

$fh1 = fopen(dirname(__DIR__) . '/data.csv', 'r');
$ref = array();
$head = fgetcsv($fh1, 2048);
while($line = fgetcsv($fh1, 2048)) {
    $data = array_combine($head, $line);
    $ref[$line[0]] = $data;
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
$fc = array(
    'type' => 'FeatureCollection',
    'features' => array(),
);
while($line = fgetcsv($fh2, 2048)) {
    if(isset($ref[$line[0]])) {
        $f = array(
            'type' => 'Feature',
            'properties' => array(
                'name' => $ref[$line[0]]['醫事機構名稱'],
                'phone' => $ref[$line[0]]['電話'],
                'address' => $ref[$line[0]]['地 址 '],
                'mask_adult' => $line[4],
                'mask_child' => $line[5],
                'updated' => $line[6],
                'available' => $ref[$line[0]]['固定看診時段 '],
                'note' => $ref[$line[0]]['備註'],
            ),
            'geometry' => array(
                'type' => 'Point',
                'coordinates' => array(
                    $ref[$line[0]]['TGOS X'],
                    $ref[$line[0]]['TGOS Y'],
                ),
            ),
        );
        $fc['features'][] = $f;
    }
}
file_put_contents(dirname(__DIR__) . '/json/points.json', json_encode($fc, JSON_PRETTY_PRINT | JSON_NUMERIC_CHECK | JSON_UNESCAPED_UNICODE));