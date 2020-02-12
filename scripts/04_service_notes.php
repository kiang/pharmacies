<?php
$fh = fopen(dirname(__DIR__) . '/raw/A21030000I-D21005-004.csv', 'r');
$head = fgetcsv($fh, 2048);
/*
Array
(
    [0] => 醫事機構代碼
    [1] => 醫事機構名稱
    [2] => 醫事機構種類 
    [3] => 電話
    [4] => 地 址 
    [5] => 分區業務組
    [6] => 特約類別
    [7] => 服務項目 
    [8] => 診療科別 
    [9] => 終止合約或歇業日期 
    [10] => 固定看診時段 
    [11] => 備註
)
*/
$ref = array();
while($line = fgetcsv($fh, 2048)) {
    if(!empty($line[4])) {
        $city = mb_substr($line[4], 0, 3, 'utf-8');
        $city = str_replace('巿', '市', $city);
        if(!isset($ref[$city])) {
            $ref[$city] = array();
        }
        $ref[$city][$line[1]] = $line[0];
    }
}
$fh = fopen(dirname(__DIR__) . '/raw/pharmacyMore.csv', 'r');
$pool = array();
$poolHead = fgetcsv($fh, 2048); //醫事機構代碼,醫事機構名稱,營業時間,口罩開賣時間,來源
while($line = fgetcsv($fh, 2048)) {
    $pool[$line[0]] = $line;
}

$tmpFile = dirname(__DIR__) . '/raw/service_note/hsinchu_city.csv';
file_put_contents($tmpFile, file_get_contents('https://docs.google.com/spreadsheets/d/1S9KmFwA_p8fj2_Aom35Uai92saulNtYF4WmyAAnCqfY/gviz/tq?tqx=out:csv'));
$fh = fopen($tmpFile, 'r');
$head = fgetcsv($fh, 2048);
/*
Array
(
    [0] => 
    [1] => 醫事機構名稱
    [2] => 機構地址
    [3] => 電話區域號碼
    [4] => 電話號碼
    [5] => 明日販售時間
)
*/
while($line = fgetcsv($fh, 2048)) {
    if(isset($ref['新竹市'][$line[1]])) {
        $code = $ref['新竹市'][$line[1]];
        if(!isset($pool[$code])) {
            $pool[$code] = array(
                $code,
                $line[1],
                '',
                $line[5] . '開賣',
                'hsinchu_city',
            );
        }
    }
}

$fh = fopen(dirname(__DIR__) . '/raw/pharmacyMore.csv', 'w');
fputcsv($fh, $poolHead);
ksort($pool);
foreach($pool AS $line) {
    fputcsv($fh, $line);
}