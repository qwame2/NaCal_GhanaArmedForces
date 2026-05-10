<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Message;
use Illuminate\Support\Facades\Schema;

echo "Checking 'messages' table schema...\n";
if (Schema::hasColumn('messages', 'is_automated')) {
    echo "SUCCESS: 'is_automated' column exists.\n";
} else {
    echo "ERROR: 'is_automated' column MISSING.\n";
}

$automatedCount = Message::where('is_automated', true)->count();
echo "Count of automated messages: $automatedCount\n";

$pendingMsgs = Message::where('message', 'like', '%Awaiting SRA Approval%')->get();
foreach ($pendingMsgs as $msg) {
    echo "Message ID: {$msg->id}, Automated: " . ($msg->is_automated ? 'YES' : 'NO') . "\n";
}
