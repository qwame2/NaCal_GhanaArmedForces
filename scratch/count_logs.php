<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    $total = \App\Models\SystemLog::count();
    $archived = \App\Models\SystemLog::where('is_archived', true)->count();
    $notArchived = \App\Models\SystemLog::where('is_archived', false)->count();
    
    echo "Total: $total\n";
    echo "Archived: $archived\n";
    echo "Not Archived: $notArchived\n";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage();
}
