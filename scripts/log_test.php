<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';

use Illuminate\Foundation\Bootstrap\LoadEnvironmentVariables;

// Boot the application enough to use Log facade
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Use logger
$logger = $app->make('log');
$logger->info('TEST INFO: this should NOT appear when LOG_LEVEL=warning');
$logger->warning('TEST WARNING: this SHOULD appear when LOG_LEVEL=warning');

echo "Logged test messages\n";
