<?php
$rootPath = dirname(__DIR__);

$now = date('Y-m-d H:i:s');

exec("cd {$rootPath} && /usr/bin/git pull");

exec("php -q {$rootPath}/scripts/02_fetch_maskdata.php");

exec("cd {$rootPath} && /usr/bin/git add -A");

exec("cd {$rootPath} && /usr/bin/git commit --author 'auto commit <noreply@localhost>' -m 'auto update @ {$now}'");

exec("cd {$rootPath} && /usr/bin/git push origin master");

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://mask.goodideas-studio.com/sync');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Length: 0'));
curl_exec($ch);