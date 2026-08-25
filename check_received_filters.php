<?php
$content = file_get_contents('resources/views/received-items/index.blade.php');
$lines = explode("\n", $content);
foreach ($lines as $i => $line) {
    if (strpos($line, '<form') !== false || strpos($line, 'filter-form') !== false) {
        echo "Line " . ($i + 1) . ": $line\n";
    }
}
