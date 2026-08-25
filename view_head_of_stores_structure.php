<?php
$content = file_get_contents('app/Http/Controllers/HeadOfStoresController.php');
$lines = explode("\n", $content);
foreach ($lines as $i => $line) {
    if (stripos($line, 'function') !== false) {
        echo "Line " . ($i + 1) . ": $line\n";
    }
}
