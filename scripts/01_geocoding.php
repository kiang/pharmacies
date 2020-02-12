<?php

$config = require __DIR__ . '/config.php';

$fixes = array(
    '5901090092' => array(
        '02 -25353536',
        '台北市大同區民族西路303巷19號1樓',
    ),
    '5901090270' => array(
        '02 -25925727',
        '台北市大同區延平北路3段61號－3',
    ),
    '5903010305' => array(
        '04-25122212',
        '臺中市豐原區圓環東路617號',
    ),
);

$localFile = dirname(__DIR__) . '/raw/A21030000I-D21005-004.csv';
file_put_contents($localFile, file_get_contents('https://data.nhi.gov.tw/Datasets/DatasetResource.ashx?rId=A21030000I-D21005-004'));
$fh = fopen($localFile, 'r');
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
$head = fgetcsv($fh, 2048);
if('efbbbf' === bin2hex(substr($head[0], 0, 3))) {
    $head[0] = substr($head[0], 3);
}
$head[12] = 'TGOS X';
$head[13] = 'TGOS Y';
$head[14] = '縣市';
$head[15] = '鄉鎮市區';
$head[16] = '村里';
$oFh = fopen(dirname(__DIR__) . '/data.csv', 'w');
fputcsv($oFh, $head);
while($line = fgetcsv($fh, 2048)) {
    $line[4] = trim($line[4]);
    $line[12] = '';
    $line[13] = '';
    $line[14] = '';
    $line[15] = '';
    $line[16] = '';
    if(isset($fixes[$line[0]]) && empty($line[4])) {
        $line[3] = $fixes[$line[0]][0];
        $line[4] = $fixes[$line[0]][1];
    }
    if(!empty($line[4])) {
        $tgosFile = dirname(__DIR__) . '/tgos/' . $line[4] . '.json';
        if(!file_exists($tgosFile)) {
            $apiUrl = $config['tgos']['url'] . '?' . http_build_query(array(
                'oAPPId' => $config['tgos']['APPID'], //應用程式識別碼(APPId)
                'oAPIKey' => $config['tgos']['APIKey'], // 應用程式介接驗證碼(APIKey)
                'oAddress' => $line[4], //所要查詢的門牌位置
                'oSRS' => 'EPSG:4326', //回傳的坐標系統
                'oFuzzyType' => '2', //模糊比對的代碼
                'oResultDataType' => 'JSON', //回傳的資料格式
                'oFuzzyBuffer' => '0', //模糊比對回傳門牌號的許可誤差範圍
                'oIsOnlyFullMatch' => 'false', //是否只進行完全比對
                'oIsLockCounty' => 'true', //是否鎖定縣市
                'oIsLockTown' => 'false', //是否鎖定鄉鎮市區
                'oIsLockVillage' => 'false', //是否鎖定村里
                'oIsLockRoadSection' => 'false', //是否鎖定路段
                'oIsLockLane' => 'false', //是否鎖定巷
                'oIsLockAlley' => 'false', //是否鎖定弄
                'oIsLockArea' => 'false', //是否鎖定地區
                'oIsSameNumber_SubNumber' => 'true', //號之、之號是否視為相同
                'oCanIgnoreVillage' => 'true', //找不時是否可忽略村里
                'oCanIgnoreNeighborhood' => 'true', //找不時是否可忽略鄰
                'oReturnMaxCount' => '0', //如為多筆時，限制回傳最大筆數
            ));
            $content = file_get_contents($apiUrl);
            file_put_contents($tgosFile, $content);
        }
        $content = file_get_contents($tgosFile);
        $pos = strpos($content, '{');
        $posEnd = strrpos($content, '}');
        $json = json_decode(substr($content, $pos, $posEnd - $pos + 1), true);
        if(isset($json['AddressList'][0])) {
            $line[12] = $json['AddressList'][0]['X'];
            $line[13] = $json['AddressList'][0]['Y'];
            $line[14] = $json['AddressList'][0]['COUNTY'];
            $line[15] = $json['AddressList'][0]['TOWN'];
            $line[16] = $json['AddressList'][0]['VILLAGE'];
        }
    }
    fputcsv($oFh, $line);
}

$localFile = dirname(__DIR__) . '/raw/A21030000I-D21004-004.csv';
file_put_contents($localFile, file_get_contents('https://data.nhi.gov.tw/Datasets/DatasetResource.ashx?rId=A21030000I-D21004-004'));
$fh = fopen($localFile, 'r');
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
$head = fgetcsv($fh, 2048);
while($line = fgetcsv($fh, 2048)) {
    if(false === strpos($line[1], '衛生所')) {
        continue;
    }
    $line[4] = trim($line[4]);
    $line[12] = '';
    $line[13] = '';
    $line[14] = '';
    $line[15] = '';
    $line[16] = '';
    if(!empty($line[4])) {
        $tgosFile = dirname(__DIR__) . '/tgos/' . $line[4] . '.json';
        if(!file_exists($tgosFile)) {
            $apiUrl = $config['tgos']['url'] . '?' . http_build_query(array(
                'oAPPId' => $config['tgos']['APPID'], //應用程式識別碼(APPId)
                'oAPIKey' => $config['tgos']['APIKey'], // 應用程式介接驗證碼(APIKey)
                'oAddress' => $line[4], //所要查詢的門牌位置
                'oSRS' => 'EPSG:4326', //回傳的坐標系統
                'oFuzzyType' => '2', //模糊比對的代碼
                'oResultDataType' => 'JSON', //回傳的資料格式
                'oFuzzyBuffer' => '0', //模糊比對回傳門牌號的許可誤差範圍
                'oIsOnlyFullMatch' => 'false', //是否只進行完全比對
                'oIsLockCounty' => 'true', //是否鎖定縣市
                'oIsLockTown' => 'false', //是否鎖定鄉鎮市區
                'oIsLockVillage' => 'false', //是否鎖定村里
                'oIsLockRoadSection' => 'false', //是否鎖定路段
                'oIsLockLane' => 'false', //是否鎖定巷
                'oIsLockAlley' => 'false', //是否鎖定弄
                'oIsLockArea' => 'false', //是否鎖定地區
                'oIsSameNumber_SubNumber' => 'true', //號之、之號是否視為相同
                'oCanIgnoreVillage' => 'true', //找不時是否可忽略村里
                'oCanIgnoreNeighborhood' => 'true', //找不時是否可忽略鄰
                'oReturnMaxCount' => '0', //如為多筆時，限制回傳最大筆數
            ));
            $content = file_get_contents($apiUrl);
            file_put_contents($tgosFile, $content);
        }
        $content = file_get_contents($tgosFile);
        $pos = strpos($content, '{');
        $posEnd = strrpos($content, '}');
        $json = json_decode(substr($content, $pos, $posEnd - $pos + 1), true);
        if(isset($json['AddressList'][0])) {
            $line[12] = $json['AddressList'][0]['X'];
            $line[13] = $json['AddressList'][0]['Y'];
            $line[14] = $json['AddressList'][0]['COUNTY'];
            $line[15] = $json['AddressList'][0]['TOWN'];
            $line[16] = $json['AddressList'][0]['VILLAGE'];
        }
    }
    fputcsv($oFh, $line);
}
