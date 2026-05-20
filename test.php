<?php
require 'vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Artisan;

echo "<h3>Migration status:</h3><pre>";
Artisan::call('migrate:status');
echo Artisan::output();
echo "</pre>";

echo "<h3>Columns on edit_requests:</h3><pre>";
print_r(Schema::getColumnListing('edit_requests'));
echo "</pre>";
