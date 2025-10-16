<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of your Closure based console
| commands. Each Closure is bound to a command instance allowing a
| simple approach to interacting with each command's IO methods.
|
*/

/*
|--------------------------------------------------------------------------
| Custom Artisan Commands
|--------------------------------------------------------------------------
|
| Define custom console commands here
|
*/

// Inspiring Quote Command
Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->describe('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| Custom Commands Examples
|--------------------------------------------------------------------------
|
| Examples of other custom commands you might want to add
|
*/

// Example: Database backup command
// Artisan::command('backup:database', function () {
//     $this->info('Starting database backup...');
//     // Add backup logic here
//     $this->info('Database backup completed!');
// })->describe('Create a database backup');

// Example: Cache clear command
// Artisan::command('cache:clear-all', function () {
//     $this->info('Clearing all caches...');
//     Artisan::call('cache:clear');
//     Artisan::call('config:clear');
//     Artisan::call('route:clear');
//     Artisan::call('view:clear');
//     $this->info('All caches cleared!');
// })->describe('Clear all application caches');