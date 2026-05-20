<?php
$content = file_get_contents('../resources/views/admin/messages.blade.php');
$lines = explode("\n", $content);
foreach ($lines as $i => $line) {
    if (strpos($line, '{') !== false && strpos($line, 'auth()') !== false) {
        echo "Line " . ($i + 1) . ": " . trim($line) . "\n";
    }
}
