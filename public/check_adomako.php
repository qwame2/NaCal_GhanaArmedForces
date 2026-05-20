<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$batches = \App\Models\InventoryBatch::where('supplier_name', 'like', '%Adomako%')->orWhere('donor_name', 'like', '%Adomako%')->get(['id', 'supplier_name', 'donor_name', 'acquisition_type', 'supplier_status']);
echo json_encode($batches, JSON_PRETTY_PRINT);
