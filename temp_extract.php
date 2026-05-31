<?php
$sourcePath = 'C:\Users\PAPA KWAME\.gemini\antigravity-ide\brain\c29db4b1-c3fc-41da-9dcd-21400b6dbd95\.system_generated\steps\2530\content.md';
$destPath = 'c:\xampp\htdocs\NaCal\public\js\sweetalert2@11.js';

if (!file_exists($sourcePath)) {
    echo "Source file does not exist\n";
    exit(1);
}

$content = file_get_contents($sourcePath);
// Find first occurrence of "---" and slice everything after it
$pos = strpos($content, "---");
if ($pos !== false) {
    $js = substr($content, $pos + 3);
    // Trim leading whitespace/newlines
    $js = ltrim($js);
} else {
    $js = $content;
}

$destDir = dirname($destPath);
if (!is_dir($destDir)) {
    mkdir($destDir, 0777, true);
}

if (file_put_contents($destPath, $js)) {
    echo "Successfully extracted and saved SweetAlert2 to $destPath\n";
} else {
    echo "Failed to save SweetAlert2 to $destPath\n";
}
