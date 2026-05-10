<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$partials = App\Models\InventoryBatch::where('supplier_status', 'LIKE', '%Partial%')->get();
echo "Total Partials found: " . $partials->count() . "\n";
foreach ($partials as $p) {
    echo "ID: {$p->id}, Name: {$p->supplier_name}, Status: {$p->supplier_status}, Approval: {$p->approval_status}\n";
}
