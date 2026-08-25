<?php
$content = file_get_contents('app/Http/Controllers/AdminController.php');
$lines = explode("\n", $content);
foreach ($lines as $i => $line) {
    if (stripos($line, 'inventory') !== false) {
        echo "Line " . ($i + 1) . ": $line\n";
    }
}
