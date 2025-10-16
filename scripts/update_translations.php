<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

try {
    $json = json_encode(['en' => ['site_name' => 'TestName']]);
    $res = DB::update('update settings set translations = ? where id = ?', [$json, 1]);
    echo "Update result: ".var_export($res, true).PHP_EOL;
    $row = DB::select('select id, translations from settings where id = ?', [1]);
    var_export($row);
    echo PHP_EOL;
} catch (Exception $e) {
    echo "Exception: ".get_class($e)." - ".$e->getMessage().PHP_EOL;
    echo $e->getTraceAsString();
}
