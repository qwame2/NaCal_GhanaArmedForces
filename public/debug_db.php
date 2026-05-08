<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Http\Kernel::class)->handle(Illuminate\Http\Request::capture());

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

header('Content-Type: application/json');

try {
    $tables = DB::select('SHOW TABLES');
    $tableNames = array_map(function($t) { return array_values((array)$t)[0]; }, $tables);
    
    $editRequestsExists = Schema::hasTable('edit_requests');
    $columns = $editRequestsExists ? Schema::getColumnListing('edit_requests') : [];

    echo json_encode([
        'database' => DB::getDatabaseName(),
        'tables' => $tableNames,
        'edit_requests_exists' => $editRequestsExists,
        'columns' => $columns,
    ], JSON_PRETTY_PRINT);
} catch (\Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
