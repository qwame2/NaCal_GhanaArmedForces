<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;

try {
    $hasTable = Schema::hasTable('inventory_batches');
    echo "Has inventory_batches table: " . ($hasTable ? "Yes" : "No") . "\n";
    if ($hasTable) {
        $columns = Schema::getColumnListing('inventory_batches');
        echo "Columns:\n";
        print_r($columns);
    }
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
