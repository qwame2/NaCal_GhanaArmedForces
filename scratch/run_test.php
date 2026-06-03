<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Cache;
use App\Models\Setting;
use App\Models\SystemLog;

echo "--- Caching Diagnostics ---\n";

// 1. Clear Cache
echo "Flushing cache...\n";
Cache::flush();

// 2. Fetch setting (should write to cache)
$t1 = microtime(true);
$threshold = Setting::get('low_stock_threshold', 100);
$d1 = microtime(true) - $t1;
echo "1st fetch: threshold = $threshold (took " . number_format($d1 * 1000, 2) . "ms)\n";

// 3. Fetch setting again (should hit cache)
$t2 = microtime(true);
$threshold2 = Setting::get('low_stock_threshold', 100);
$d2 = microtime(true) - $t2;
echo "2nd fetch: threshold = $threshold2 (took " . number_format($d2 * 1000, 2) . "ms)\n";

// Verify cache has it
$cacheHas = Cache::has('setting_low_stock_threshold') ? 'Yes' : 'No';
echo "Is in persistent cache? $cacheHas\n";

// 4. Save a setting (should trigger invalidation)
echo "Updating setting to test invalidation...\n";
Setting::set('low_stock_threshold', 120, 'integer');
$cacheHasAfterSave = Cache::has('setting_low_stock_threshold') ? 'Yes' : 'No';
echo "Is in persistent cache after save? $cacheHasAfterSave\n";

// Reset back
Setting::set('low_stock_threshold', 100, 'integer');

// 5. Test log saving invalidation
echo "Saving a SystemLog to test logs cache invalidation...\n";
Cache::put('global_recent_system_logs', ['dummy'], 60);
echo "Recent logs cache before: " . (Cache::has('global_recent_system_logs') ? 'Exists' : 'Empty') . "\n";
SystemLog::create([
    'event_type' => 'SYSTEM',
    'action' => 'TEST_CACHE',
    'description' => 'Test system log for caching invalidation.',
    'severity' => 'info'
]);
echo "Recent logs cache after: " . (Cache::has('global_recent_system_logs') ? 'Exists' : 'Empty') . "\n";

echo "Diagnostics Completed Successfully!\n";
