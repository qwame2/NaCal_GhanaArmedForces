<?php
use Illuminate\Foundation\Application;
define('LARAVEL_START', microtime(true));
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\InventoryItem;

$description = $argv[1] ?? null;
$newStock = isset($argv[2]) ? floatval($argv[2]) : null;

if (!$description || is_null($newStock)) {
    echo "Usage: php public/adjust_stock.php \"<item description>\" <new stock balance>\n";
    exit(1);
}

$items = InventoryItem::where('description', $description)->get();
if ($items->isEmpty()) {
    echo "No inventory items found with description: '{$description}'\n";
    exit(1);
}

foreach ($items as $item) {
    $oldQty = $item->qty;
    $oldStock = $item->stock_balance;
    $item->qty = $newStock;
    $item->stock_balance = $newStock;
    $item->save();
    echo "Updated Item ID {$item->id} ('{$item->description}'): Qty changed from {$oldQty} to {$newStock}, Stock Balance changed from {$oldStock} to {$newStock}.\n";
}
