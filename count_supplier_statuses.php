<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$statuses = DB::table('inventory_batches')
    ->select('supplier_status', DB::raw('count(*) as total'))
    ->groupBy('supplier_status')
    ->get();

foreach ($statuses as $s) {
    echo "Supplier Status: '" . ($s->supplier_status ?? 'NULL') . "' | Count: {$s->total}\n";
}
