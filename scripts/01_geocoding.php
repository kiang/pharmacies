<?php
$config = require __DIR__ . '/config.php';

require dirname(__DIR__) . '/vendor/autoload.php';
use Goutte\Client;
$client = new Client();
$client->request('GET', 'http://data.nhi.gov.tw/resource/Opendata/%E5%85%A8%E6%B0%91%E5%81%A5%E5%BA%B7%E4%BF%9D%E9%9A%AA%E7%89%B9%E7%B4%84%E9%99%A2%E6%89%80%E5%9B%BA%E5%AE%9A%E6%9C%8D%E5%8B%99%E6%99%82%E6%AE%B5.csv');
$noteFile = dirname(__DIR__) . '/raw/A21030000I-D21006-001.csv';
file_put_contents($noteFile, $client->getResponse()->getContent());

$fh = fopen($noteFile, 'r');
$head = fgetcsv($fh, 2048);
$note = array();
while($line = fgetcsv($fh, 2048)) {
    if($line[3] == 5 || false !== strpos($line[1], '衛生所')) {
        $note[$line[0]] = array(
            $line[5], //看診星期
            $line[6], //看診備註
        );
    }
}


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
    '2345130012' => array(
        '(03)8882592',
        '花蓮縣卓溪鄉卓溪村６鄰中正６５號',
    ),
    '2331200010' => array(
        '(02)26656272',
        '新北市坪林區坪林里２鄰坪林１０４號',
    ),
    '5932050180' => array(
        '(03)3520589',
        '桃園市蘆竹區錦中里２８鄰南崁路２１０號',
    ),
    '5937010882' => array(
        '(04)7374546',
        '彰化縣彰化市牛埔里２０鄰彰南路三段２００號',
    ),
    '5937010908' => array(
        '(04)7236611',
        '彰化縣彰化市五權里１鄰彰美路一段２３５號',
    ),
    '5939131437' => array(
        '(05)6933066',
        '雲林縣麥寮鄉麥豐村２６鄰華興路９１號',
    ),
    '5945011517' => array(
        '(03)8328152',
        '花蓮縣花蓮市國光里９鄰林森路４１９號',
    ),
    '2336050010' => array(
        '(04)26625040',
        '臺中市沙鹿區文昌街20號',
    ),
    '2331280018' => array(
        '(02)24921117',
        '新北市萬里區萬里里４鄰瑪鋉路１５７號',
    ),
    '2341060019' => array(
        '(06)5905398',
        '臺南市新化區健康路１４３號',
    ),
    '2346080011' => array(
        '(089)831022',
        '台東縣長濱鄉中興４６號',
    ),
    '2339100017' => array(
        '(05)5892273',
        '雲林縣林內鄉林中村１４鄰新興５４號',
    ),
);

$localFile = dirname(__DIR__) . '/raw/A21030000I-D21005-004.csv';
$client->request('GET', 'https://data.nhi.gov.tw/Datasets/DatasetResource.ashx?rId=A21030000I-D21005-004');
file_put_contents($localFile, $client->getResponse()->getContent());
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
$head[17] = '看診星期';
$oFh = fopen(dirname(__DIR__) . '/data.csv', 'w');
fputcsv($oFh, $head);
while($line = fgetcsv($fh, 2048)) {
    $line[4] = trim($line[4]);
    $line[12] = '';
    $line[13] = '';
    $line[14] = '';
    $line[15] = '';
    $line[16] = '';
    $line[17] = '';
    if(isset($note[$line[0]])) {
        $line[11] = $note[$line[0]][1];
        $line[17] = $note[$line[0]][0];
    }
    if(isset($fixes[$line[0]])) {
        $line[3] = $fixes[$line[0]][0];
        $line[4] = $fixes[$line[0]][1];
    }
    $line[11] = str_replace('\\', '/', $line[11]);
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
$client->request('GET', 'https://data.nhi.gov.tw/Datasets/DatasetResource.ashx?rId=A21030000I-D21004-004');
file_put_contents($localFile, $client->getResponse()->getContent());
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
    $line[17] = '';
    $line[11] = str_replace('\\', '/', $line[11]);
    if(isset($note[$line[0]])) {
        $line[11] = $note[$line[0]][1];
        $line[17] = $note[$line[0]][0];
    }
    if(isset($fixes[$line[0]])) {
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

$missingLines =
'2101010013	松山健康服務中心	台北市松山區八德路４段６９４號１、２樓	(02)27653147
2101100227	中山健康服務中心	台北市中山區松江路３６７號１樓	(02)25013363
2101110027	內湖健康服務中心	台北市內湖區民權東路６段９９號	(02)27908387
2101120014	南港健康服務中心	台北市南港區南港路１段３６０號１樓	(02)27868756
2101150012	士林健康服務中心	台北市士林區中正路４３９號１樓	(02)28836268
2101161033	北投健康服務中心	臺北市北投區石牌路２段１１１號	(02)28261026
2101170050	信義健康服務中心	台北市信義區信義路５段１５號	(02)87804152
2101180038	中正健康服務中心	台北市中正區牯嶺街２４號	(02)23210168
2101191068	萬華健康服務中心	台北市萬華區東園街１５２號	(02)23395384
2101020019	大安健康服務中心	台北市大安區辛亥路３段１５號	(02)27390997
2101090011	大同健康服務中心	台北市大同區昌吉街５２號１至２樓	(02)25948971
2101200017	政大(文山健康服務中心)	臺北市文山區木柵路３段２２０號	(02)22343501
5931033998	新資生永貞大藥局	新北市永和區永貞路４２９號１樓	(02)29227202
5905320742	榮記藥健康藥局	臺南市東區成大里育樂街１５６－４號底層	(06)2760588
5938032471	草屯啄木鳥藥局	南投縣草屯鎮中興路２２號	(049)2323560
5903220114	鄧博士藥局	臺中市中區中山路３２５號１樓	(04)22256851
5940111558	均安藥局	嘉義縣太保市中山路２段２９－２號１樓	(05)2381076
5931017627	長頸鹿藥師藥局	新北市板橋區忠孝路１８１號（１樓）	(02)29598095
5901203339	光點景興藥局	臺北市文山區景興路１０９號	(02)29335881
5907300917	左營藥局	高雄市左營區華夏路３２５號	(07)5501017
5938041872	優適藥局	南投縣竹山鎮大明路１４９－５號	(049)2630355
5938041881	明仁藥局	南投縣竹山鎮集山路三段９４８－２號	(049)2659650';
$lines = explode("\n", $missingLines);
foreach($lines AS $line) {
    $cols = explode("\t", $line);
    $longLine = array_fill(0, 18, '');
    $longLine[0] = $cols[0];
    $longLine[1] = $cols[1];
    $longLine[3] = $cols[3];
    $longLine[4] = $cols[2];
    $longLine[11] = '販售口罩時段為每星期一至星期日上午9點至12點';
    if(!empty($longLine[4])) {
        $tgosFile = dirname(__DIR__) . '/tgos/' . $longLine[4] . '.json';
        if(!file_exists($tgosFile)) {
            $apiUrl = $config['tgos']['url'] . '?' . http_build_query(array(
                'oAPPId' => $config['tgos']['APPID'], //應用程式識別碼(APPId)
                'oAPIKey' => $config['tgos']['APIKey'], // 應用程式介接驗證碼(APIKey)
                'oAddress' => $longLine[4], //所要查詢的門牌位置
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
            $longLine[12] = $json['AddressList'][0]['X'];
            $longLine[13] = $json['AddressList'][0]['Y'];
            $longLine[14] = $json['AddressList'][0]['COUNTY'];
            $longLine[15] = $json['AddressList'][0]['TOWN'];
            $longLine[16] = $json['AddressList'][0]['VILLAGE'];
        }
    }
    fputcsv($oFh, $longLine);
}