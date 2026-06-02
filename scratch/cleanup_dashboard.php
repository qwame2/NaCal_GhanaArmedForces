<?php
$filePath = 'c:\\xampp\\htdocs\\NaCal\\resources\\views\\dashboard.blade.php';

if (!file_exists($filePath)) {
    die("File not found: $filePath\n");
}

$content = file_get_contents($filePath);

// We want to find the exact boundary:
// Start: <script id="inventory-data" type="application/json"> (around line 832)
// End: The closing </div> of the modal overlay before @endpush (around line 2254)

$startMarker = '<script id="inventory-data" type="application/json">';
$endMarker = "</div>\n</div>\n@endpush";
// Also handle CRLF if present
if (strpos($content, "\r\n") !== false) {
    $endMarker = "</div>\r\n</div>\r\n@endpush";
}

$startIndex = strpos($content, $startMarker);
if ($startIndex === false) {
    die("Could not find start marker: $startMarker\n");
}

$endIndex = strpos($content, $endMarker, $startIndex);
if ($endIndex === false) {
    // Try with different spacing
    $endMarkerAlt = "</div>\n    </div>\n</div>\n@endpush";
    if (strpos($content, "\r\n") !== false) {
        $endMarkerAlt = "</div>\r\n    </div>\r\n</div>\r\n@endpush";
    }
    $endIndex = strpos($content, $endMarkerAlt, $startIndex);
    if ($endIndex === false) {
        // Fallback to regex finding the exact structure
        echo "Direct match failed, trying regex...\n";
        $pattern = '/<script id="inventory-data" type="application\/json">.*?<\/form>\s*<\/div>\s*<\/div>\s*(?=@endpush)/s';
        if (preg_match($pattern, $content, $matches, PREG_OFFSET_CAPTURE)) {
            $startIndex = $matches[0][1];
            $length = strlen($matches[0][0]);
            echo "Regex matched! Start index: $startIndex, Length: $length\n";
            $newContent = substr_replace($content, '', $startIndex, $length);
            
            // Backup
            file_put_contents($filePath . '.bak', $content);
            file_put_contents($filePath, $newContent);
            echo "Successfully updated using regex. Backup saved to {$filePath}.bak\n";
            exit(0);
        } else {
            die("Could not find legacy modal block with regex or direct matching.\n");
        }
    } else {
        $endIndex += strlen("</div>\r\n    </div>\r\n</div>") - strlen("</div>"); // adjust to end right before @endpush
    }
}

// Adjust endIndex to be right before @endpush
$length = $endIndex - $startIndex;

// Let's verify the text we are about to delete
$slice = substr($content, $startIndex, $length);
echo "Target block starts with:\n" . substr($slice, 0, 200) . "\n...\n";
echo "Target block ends with:\n" . substr($slice, -200) . "\n";
echo "Total block length to delete: $length characters.\n";

$newContent = substr_replace($content, '', $startIndex, $length);

// Backup original file
file_put_contents($filePath . '.bak', $content);

// Save new content
if (file_put_contents($filePath, $newContent) !== false) {
    echo "Successfully updated dashboard.blade.php. Backup saved to {$filePath}.bak\n";
} else {
    echo "Failed to write updated file.\n";
}
