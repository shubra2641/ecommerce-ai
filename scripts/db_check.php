<?php
// Simple script to bootstrap Laravel and print DB info
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

echo "App env: " . env('APP_ENV') . PHP_EOL;
echo "DB connection: " . config('database.default') . PHP_EOL;
echo "DB database: " . config('database.connections.' . config('database.default') . '.database') . PHP_EOL;

$has = Schema::hasColumn('settings', 'translations') ? 'yes' : 'no';
echo "settings.translations column exists? " . $has . PHP_EOL;

$cols = DB::select('SHOW FULL COLUMNS FROM settings');
foreach ($cols as $c) {
    echo $c->Field . "\t" . $c->Type . PHP_EOL;
}

$settings = DB::table('settings')->first();
if ($settings) {
    echo "settings row id=" . $settings->id . PHP_EOL;
    if (property_exists($settings, 'translations')) {
        echo "translations raw: " . $settings->translations . PHP_EOL;
    } else {
        echo "translations property not present on row\n";
    }
}
