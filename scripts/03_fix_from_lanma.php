<?php

$fh = fopen(dirname(__DIR__) . '/raw/lanma.csv', 'r');
$head = fgetcsv($fh, 2048);
$ref = array();
while($line = fgetcsv($fh, 2048)) {
    $ref[$line[0]] = array($line[6], $line[5]);
}
fclose($fh);
/*
geocoding from https://maps.nlsc.gov.tw/T09/mapshow.action?language=ZH
*/
$ref['2331200010'] = array(121.711626,24.935877);
$ref['2345130012'] = array(121.304189,23.345992);
$ref['5937010882'] = array(120.597116,24.08167);
$ref['5937010908'] = array(120.536861,24.089358);
/*
geocoding from littlebtc at g0v slack
*/
$ref['5903270810'] = array(120.639038,24.182868);
$ref['5903270918'] = array(120.637262,24.177244);
$ref['5903271068'] = array(120.637229,24.178166);

$ref['5931016004'] = array(121.464886,25.018384); // corrected by Yiwen Chen from FB
$ref['5931033130'] = array(121.517612,25.006090); // https://github.com/WJWang/mask-help-info-api/issues/2#issuecomment-589128402

$ref['5931033176'] = array(121.518552,25.007578); // https://github.com/kiang/pharmacies/issues/7
$ref['5931101455'] = array(121.461145,25.136232); // https://github.com/kiang/pharmacies/issues/7
$ref['5931100092'] = array(121.461358,25.136273); // https://github.com/kiang/pharmacies/issues/7
$ref['5901092078'] = array(121.519123,25.0573489); // https://github.com/kiang/pharmacies/issues/8

$ref['5932023094'] = array(121.217593,24.962847); // https://geobingan.info/report/04d252bd51d944379835d8dfa18dc2c2

$ref['5931080148'] = array(121.352382,24.954769); // https://github.com/kiang/pharmacies/issues/13
$ref['5932021643'] = array(121.226650,24.951139); // https://github.com/kiang/pharmacies/issues/13
$ref['5932022373'] = array(121.229109,24.946061); // https://github.com/kiang/pharmacies/issues/13
$ref['5941041322'] = array(120.254197,23.181737); // https://www.ptt.cc/bbs/Gossiping/M.1585483490.A.DAD.html
$ref['5939032180'] = array(120.430587,23.705627); // https://github.com/kiang/pharmacies/issues/16

$fh = fopen(dirname(__DIR__) . '/data.csv', 'r');
$pool = array();
$pool[] = fgetcsv($fh, 2048);
while($line = fgetcsv($fh, 2048)) {
    if(isset($ref[$line[0]])) {
        $line[12] = $ref[$line[0]][0];
        $line[13] = $ref[$line[0]][1];
    }
    $pool[] = $line;
}
fclose($fh);

$oFh = fopen(dirname(__DIR__) . '/data.csv', 'w');
foreach($pool AS $line) {
    fputcsv($oFh, $line);
}