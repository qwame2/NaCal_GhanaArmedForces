<?php

$filePath = __DIR__ . '/../resources/views/admin/messages.blade.php';
$content = file_get_contents($filePath);

// Extract script blocks
preg_match_all('/<script>(.*?)<\/script>/s', $content, $matches);

foreach ($matches[1] as $idx => $js) {
    // Replace blade variables with dummy js values
    $js = preg_replace('/\{\{\s*auth\(\)->id\(\)\s*\}\}/', '1', $js);
    $js = preg_replace('/\{\{\s*csrf_token\(\)\s*\}\}/', '"dummy_token"', $js);
    $js = preg_replace('/\{\{\s*route\(.*?\)\s*\}\}/', '"/dummy/route"', $js);
    $js = preg_replace('/\{\{\s*url\(.*?\)\s*\}\}/', '"/dummy/url"', $js);
    $js = preg_replace('/\{\{\s*asset\(.*?\)\s*\}\}/', '"/dummy/asset"', $js);

    // Save to temp file
    $tempFile = __DIR__ . "/temp_js_$idx.js";
    file_put_contents($tempFile, $js);

    echo "Checking syntax for script block $idx...\n";
    $output = [];
    $retval = 0;
    exec("node --check " . escapeshellarg($tempFile) . " 2>&1", $output, $retval);

    if ($retval === 0) {
        echo "Script block $idx: Syntax OK\n";
        unlink($tempFile);
    } else {
        echo "Script block $idx: Syntax ERROR\n";
        echo implode("\n", $output) . "\n";
        echo "Kept temp file: $tempFile\n";
    }
}

