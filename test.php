<?php
require 'vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\EditRequest;

$reqs = EditRequest::all();
echo "Total Edit Requests: " . $reqs->count() . "\n\n";
foreach ($reqs as $r) {
    echo "ID: " . $r->id . "\n";
    echo "User ID: " . $r->user_id . "\n";
    echo "Item ID: " . $r->item_id . "\n";
    echo "Request Type: " . $r->request_type . "\n";
    echo "Status: " . $r->status . "\n";
    echo "Reason: " . $r->reason . "\n";
    echo "Payload: " . $r->payload . "\n";
    echo "Original Payload: " . $r->original_payload . "\n";
    echo "-----------------------------------------\n\n";
}
