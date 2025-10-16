<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Settings;

try {
    $s = Settings::first();
    echo "Got settings id=" . $s->id . PHP_EOL;
    $data = ['translations' => ['en' => ['site_name' => 'Etest']], 'site_name' => 'Etest'];
    $res = $s->update($data);
    echo "update returned: ".var_export($res, true).PHP_EOL;
    $s2 = Settings::first();
    var_export($s2->translations);
    echo PHP_EOL;
} catch (Exception $e) {
    echo "Exception: " . get_class($e) . " - " . $e->getMessage() . PHP_EOL;
    echo $e->getTraceAsString();
}
