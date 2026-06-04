<?php

use Illuminate\Support\Facades\Facade;

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';

$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = \App\Models\User::find(5);
if (!$user) {
    echo "User 5 not found.\n";
    exit(1);
}

auth()->login($user);

// Execute notifications logic directly
try {
    $acknowledged = \App\Models\NotificationAcknowledgement::where('user_id', auth()->id())
        ->pluck('item_description')
        ->toArray();
} catch (\Exception $e) {
    $acknowledged = [];
}

$acknowledgedClean = array_map('trim', $acknowledged);

$items = \App\Models\InventoryItem::selectRaw('description, SUM(CAST(REPLACE(stock_balance, ",", "") AS DECIMAL(15,2))) as total_stock')
    ->whereNotIn(\DB::raw('TRIM(description)'), array_map('trim', $acknowledged))
    ->groupBy('description')
    ->get();

$allNotifs = [];
foreach ($items as $item) {
    $threshold = \App\Models\Setting::getItemThreshold($item->description);
    if ($threshold > 0 && (float)$item->total_stock < $threshold) {
        $unit = \App\Models\Setting::getItemUnit($item->description);
        $allNotifs[] = [
            'category' => 'alert',
            'type' => 'warning', 
            'title' => 'Low Stock: ' . $item->description, 
            'message' => "Stock level (" . number_format($item->total_stock, 0) . " {$unit}) is below threshold (" . $threshold . ")."
        ];
    }
}

// Expired alerts (Admins only)
if (auth()->user()->is_admin) {
    $expiredItems = \App\Models\InventoryItem::selectRaw('description, SUM(CAST(REPLACE(stock_balance, ",", "") AS DECIMAL(15,2))) as total_stock, SUM(CAST(REPLACE(qty, ",", "") AS DECIMAL(15,2))) as total_qty')
        ->whereNotIn(\DB::raw('TRIM(description)'), array_map('trim', $acknowledged))
        ->groupBy('description')
        ->havingRaw('SUM(CAST(REPLACE(stock_balance, ",", "") AS DECIMAL(15,2))) = 0 AND SUM(CAST(REPLACE(qty, ",", "") AS DECIMAL(15,2))) >= 1')
        ->get();
        
    foreach($expiredItems as $item) {
        $allNotifs[] = [
            'category' => 'alert',
            'type' => 'danger', 
            'title' => 'Expired Record: ' . $item->description
        ];
    }
}

// System logs
$systemLogs = \App\Models\SystemLog::orderBy('created_at', 'desc')
    ->limit(100)
    ->get();
    
foreach($systemLogs as $log) {
    $isConcerned = false;
    if (auth()->user()->is_admin) {
        $isConcerned = true;
    } else {
        $nameLower = strtolower(auth()->user()->name);
        $usernameLower = strtolower(auth()->user()->username);
        $descLower = strtolower($log->description);
        
        if (str_contains($descLower, $nameLower) || str_contains($descLower, $usernameLower)) {
            $isConcerned = true;
        } elseif ($log->user_id === auth()->id()) {
            $action = strtoupper($log->action ?? '');
            if (in_array($action, ['ADD_INVENTORY', 'EDIT_INVENTORY', 'SUPPLEMENT_INVENTORY', 'ISSUE_ITEM', 'RETURN_ITEM', 'AUTHORIZATION'])) {
                $isConcerned = true;
            }
        }
    }

    if (!$isConcerned) {
        continue;
    }

    $allNotifs[] = [
        'category' => 'system',
        'type' => 'info',
        'title' => $log->action,
        'message' => $log->description
    ];
}

echo "NOTIFICATIONS COUNT: " . count($allNotifs) . "\n";
print_r($allNotifs);
