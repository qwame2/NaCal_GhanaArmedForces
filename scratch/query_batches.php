<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\InventoryBatch;

$out = "Recent Batches:\n";
try {
    $batches = InventoryBatch::orderBy('id', 'desc')->take(10)->get();
    foreach ($batches as $b) {
        $out .= sprintf(
            "ID: %d | Acq: %s | Supplier: %s | Donor: %s | DelPerson: %s | DelPhone: %s | Approved: %s\n",
            $b->id,
            $b->acquisition_type,
            $b->supplier_name,
            $b->donor_name,
            $b->delivery_person,
            $b->delivery_phone,
            $b->approval_status
        );
    }
} catch (\Exception $e) {
    $out .= "Error: " . $e->getMessage() . "\n";
}

file_put_contents(__DIR__ . '/batches_output.txt', $out);
echo "Query done!\n";
