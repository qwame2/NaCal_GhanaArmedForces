<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    $columns = Illuminate\Support\Facades\DB::select('DESCRIBE system_logs');
    foreach ($columns as $column) {
        echo $column->Field . ' - ' . $column->Type . ' - ' . $column->Default . "\n";
    }
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage();
}
