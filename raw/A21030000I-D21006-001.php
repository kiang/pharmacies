<?php
$odsFile = __DIR__ . '/A21030000I-D21006-001.ods';
file_put_contents($odsFile, file_get_contents('https://data.nhi.gov.tw/resource/OpenData/%E5%85%A8%E6%B0%91%E5%81%A5%E5%BA%B7%E4%BF%9D%E9%9A%AA%E7%89%B9%E7%B4%84%E9%99%A2%E6%89%80%E5%9B%BA%E5%AE%9A%E7%9C%8B%E8%A8%BA%E6%99%82%E6%AE%B5.ods'));
// ref https://ask.libreoffice.org/en/question/144942/how-to-convert-to-csv-with-utf-8-encoding-using-lo5-command-line/
exec('libreoffice --headless --convert-to csv:"Text - txt - csv (StarCalc)":44,34,76 ' . $odsFile);
