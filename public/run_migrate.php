<?php
use Illuminate\Support\Facades\Artisan;

define('LARAVEL_START', microtime(true));
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

try {
    echo "DB Connection: " . config('database.default') . "<br>";
    echo "DB Database: " . config('database.connections.' . config('database.default') . '.database') . "<br>";
    
    echo "<h3>Before Migrate - Columns on edit_requests:</h3><pre>";
    print_r(Illuminate\Support\Facades\Schema::getColumnListing('edit_requests'));
    echo "</pre>";

    echo "<h3>Artisan Migrate Output:</h3><pre>";
    Artisan::call('migrate', ['--force' => true]);
    echo Artisan::output() . "</pre>";
    
    echo "<h3>After Migrate - Columns on edit_requests:</h3><pre>";
    print_r(Illuminate\Support\Facades\Schema::getColumnListing('edit_requests'));
    echo "</pre>";
} catch (\Exception $e) {
    echo "Migration Failed:<br>" . $e->getMessage();
}
