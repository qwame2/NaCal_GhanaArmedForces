<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$requests = \App\Models\EditRequest::where('status', 'pending')->get();
$out = "Pending Edit Requests Count: " . $requests->count() . "\n\n";

foreach ($requests as $r) {
    $out .= "ID: {$r->id} | User: " . ($r->user->name ?? 'N/A') . " | Type: {$r->request_type} | Status: {$r->status}\n";
    $out .= "Payload: " . $r->payload . "\n";
    $out .= "--------------------------------------------------\n\n";
}

file_put_contents(__DIR__ . '/pending_requests.txt', $out);
echo "Pending requests queried successfully.\n";
