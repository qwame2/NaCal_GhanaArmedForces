<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$req = \App\Models\StoreRequisition::with(['items', 'requester'])->find(10);
if ($req) {
    echo "ID: " . $req->id . "\n";
    echo "Status: " . $req->status . "\n";
    echo "Origin Admin Status: " . $req->origin_admin_status . "\n";
    echo "Main Admin Status: " . $req->main_admin_status . "\n";
    echo "Alternative Status: " . $req->alternative_status . "\n";
    echo "Department: " . $req->department . "\n";
    echo "Requester Name: " . $req->requester_name . "\n";
    echo "Requires DG Approval: " . ($req->requires_dg_approval ? 'Yes' : 'No') . "\n";
    echo "DG Status: " . $req->dg_status . "\n";
    echo "Origin Approved By: " . $req->origin_approved_by . "\n";
    echo "Main Approved By: " . $req->main_approved_by . "\n";
} else {
    echo "Requisition not found.\n";
}
