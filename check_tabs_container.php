<?php
$content = file_get_contents('resources/views/admin/inventory/index.blade.php');
$lines = explode("\n", $content);
foreach ($lines as $i => $line) {
    if (strpos($line, 'class="tabs"') !== false || strpos($line, "class='tabs'") !== false || strpos($line, 'tab-content') !== false || strpos($line, 'tab-trigger') !== false) {
        echo "Line " . ($i + 1) . ": $line\n";
    }
}
