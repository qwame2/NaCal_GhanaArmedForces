<?php
$content = file_get_contents('app/Http/Controllers/AdminController.php');
$lines = explode("\n", $content);
for ($i = 940; $i < 1050; $i++) {
    if (isset($lines[$i])) {
        echo ($i + 1) . ": " . $lines[$i] . "\n";
    }
}
