<?php
echo "Cleaning storage/framework/views...<br>";
$files = glob(__DIR__ . '/../storage/framework/views/*.php');
foreach ($files as $file) {
    if (is_file($file)) {
        unlink($file);
        echo "Deleted: " . basename($file) . "<br>";
    }
}
echo "Done.";
