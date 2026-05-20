<?php
$content = file_get_contents('../resources/views/admin/messages.blade.php');
$lines = explode("\n", $content);
foreach ($lines as $i => $line) {
    if (stripos($line, 'varian') !== false || stripos($line, 'vSign') !== false || stripos($line, 'vColor') !== false) {
        echo "Line " . ($i + 1) . ": " . trim($line) . "\n";
    }
}
