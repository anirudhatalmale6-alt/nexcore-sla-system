<?php
require __DIR__ . '/application/vendor/autoload.php';
$app = require_once __DIR__ . '/application/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->call('cache:clear');
echo "cache:clear done\n";
$kernel->call('config:clear');
echo "config:clear done\n";
$kernel->call('route:clear');
echo "route:clear done\n";
$kernel->call('view:clear');
echo "view:clear done\n";
echo "ALL CACHES CLEARED SUCCESSFULLY";
