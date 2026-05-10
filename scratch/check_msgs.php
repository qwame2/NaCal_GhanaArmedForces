<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Message;

$msgs = Message::where('message', 'like', '%SRA REQUEST REJECTED%')
    ->orWhere('message', 'like', '%Awaiting SRA Approval from Admin%')
    ->get();

echo "Count: " . $msgs->count() . "\n";
foreach ($msgs as $msg) {
    echo "ID: {$msg->id}, Sender: {$msg->sender_id}, Receiver: {$msg->receiver_id}, Msg: " . substr(strip_tags($msg->message), 0, 50) . "...\n";
}
