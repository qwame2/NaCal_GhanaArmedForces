<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(Illuminate\Http\Request::capture());

echo 'delegation_otp_code: "' . \App\Models\Setting::get('delegation_otp_code') . "\"\n";
echo 'delegation_otp_expires_at: "' . \App\Models\Setting::get('delegation_otp_expires_at') . "\"\n";
echo 'delegated_approver_id: "' . \App\Models\Setting::get('delegated_approver_id') . "\"\n";
