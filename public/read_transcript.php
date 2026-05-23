<?php
$logPath = "C:/Users/PAPA KWAME/.gemini/antigravity-ide/brain/abf0707e-e38c-4dd2-89b7-5172094a29d6/.system_generated/logs/transcript.jsonl";
if (!file_exists($logPath)) {
    die("File not found: $logPath");
}

$handle = fopen($logPath, "r");
$matches = [];
$count = 0;
while (($line = fgets($handle)) !== false) {
    if (stripos($line, "issue-items") !== false || stripos($line, "disbursements") !== false) {
        $count++;
        // Try to decode JSON to get type and content
        $data = json_decode($line, true);
        if ($data) {
            $matches[] = [
                'type' => $data['type'] ?? 'unknown',
                'tool_calls' => isset($data['tool_calls']) ? count($data['tool_calls']) : 0,
                'content_snippet' => substr($data['content'] ?? '', 0, 500)
            ];
        }
        if ($count > 30) break;
    }
}
fclose($handle);

echo "=== MATCHES IN TRANSCRIPT ===\n";
print_r($matches);
