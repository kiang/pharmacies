<?php
$rootPath = dirname(__DIR__);

$now = date('Y-m-d H:i:s');

exec("cd {$rootPath} && /usr/bin/git pull");

exec("php -q {$rootPath}/scripts/01_geocoding.php");
exec("php -q {$rootPath}/scripts/03_fix_from_lanma.php");

exec("cd {$rootPath} && /usr/bin/git add -A");

exec("cd {$rootPath} && /usr/bin/git commit --author 'auto commit <noreply@localhost>' -m 'daily update @ {$now}'");

exec("cd {$rootPath} && /usr/bin/git push origin master");