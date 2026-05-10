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
    Artisan::call('migrate', [
        '--force' => true
    ]);
    echo "Migration Successful:<br><pre>" . Artisan::output() . "</pre>";
} catch (\Exception $e) {
    echo "Migration Failed:<br>" . $e->getMessage();
}
