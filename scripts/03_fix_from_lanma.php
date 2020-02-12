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