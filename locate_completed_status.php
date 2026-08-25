<?php
$content = file_get_contents('app/Http/Controllers/EditRequestController.php');
$lines = explode("\n", $content);
foreach ($lines as $i => $line) {
    if (strpos($line, "'completed'") !== false || strpos($line, '"completed"') !== false) {
        echo "Line " . ($i + 1) . ": $line\n";
    }
}
