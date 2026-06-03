<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

try {
    echo "=== DATABASE DIAGNOSTICS ===\n";
    $tables = DB::select('SHOW TABLES');
    $dbName = DB::getDatabaseName();
    $keyName = "Tables_in_" . $dbName;

    foreach ($tables as $table) {
        $name = $table->$keyName;
        $count = DB::table($name)->count();
        echo "Table: {$name} | Rows: {$count}\n";
    }

    echo "\n=== INDEXES ===\n";
    foreach ($tables as $table) {
        $name = $table->$keyName;
        $indexes = DB::select("SHOW INDEX FROM `{$name}`");
        $idxList = [];
        foreach ($indexes as $idx) {
            $idxList[] = $idx->Key_name . '(' . $idx->Column_name . ')';
        }
        $idxList = array_unique($idxList);
        echo "Table: {$name} | Indexes: " . implode(', ', $idxList) . "\n";
    }

    echo "\n=== SLOW QUERIES / HIGH FREQUENCY CHECKS ===\n";
    // Check if there are columns that are frequently filtered or sorted but lack indexes
    // Let's also check for system_logs count and messages count.
    
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
