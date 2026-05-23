<?php
$files = [
    __DIR__ . '/../resources/views/stock-check/index.blade.php',
    __DIR__ . '/../resources/views/requisitions/personnel.blade.php',
    __DIR__ . '/../resources/views/requisitions/history.blade.php',
    __DIR__ . '/../resources/views/reports/index.blade.php',
    __DIR__ . '/../resources/views/received-items/index.blade.php',
    __DIR__ . '/../resources/views/messages/index.blade.php',
    __DIR__ . '/../resources/views/layouts/dashboard.blade.php',
    __DIR__ . '/../resources/views/layouts/admin.blade.php',
    __DIR__ . '/../resources/views/dashboard.blade.php',
    __DIR__ . '/../resources/views/admin/requisitions.blade.php',
    __DIR__ . '/../resources/views/admin/permissions.blade.php',
    __DIR__ . '/../resources/views/admin/messages.blade.php'
];

foreach ($files as $path) {
    if (file_exists($path)) {
        $content = file_get_contents($path);
        
        // Match console.log/error/warn with multi-level balanced parenthesis matching
        $pattern = '/console\.(log|error|warn|info|debug)\s*\((?:[^()]*|\([^()]*\))*\);?/i';
        $cleaned = preg_replace($pattern, '/* console log removed */', $content);
        
        if ($cleaned !== $content) {
            file_put_contents($path, $cleaned);
            echo "Cleaned console logs in: " . basename($path) . "\n";
        } else {
            echo "No match in: " . basename($path) . "\n";
        }
    } else {
        echo "File does not exist: " . basename($path) . "\n";
    }
}
echo "Purge complete.";
