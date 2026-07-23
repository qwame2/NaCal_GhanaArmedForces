<?php
$lines = file("C:\\Users\\USER\\.gemini\\antigravity-ide\\brain\\63cb63ce-9a86-4e12-8ad8-3f7a4431f1b2\\.system_generated\\logs\\transcript.jsonl");
foreach ($lines as $line) {
    $data = json_decode($line, true);
    if (isset($data['tool_calls'])) {
        foreach ($data['tool_calls'] as $tc) {
            if (strpos($tc['name'] ?? '', 'console') !== false || strpos($tc['name'] ?? '', 'log') !== false) {
                echo "Step " . $data['step_index'] . " Tool call: " . $tc['name'] . "\n";
            }
        }
    }
    if (isset($data['type']) && strpos(strtolower($data['type']), 'console') !== false) {
        echo "Step " . $data['step_index'] . " Type: " . $data['type'] . "\n";
        if (isset($data['content'])) {
            echo substr($data['content'], 0, 1000) . "\n\n";
        }
    }
}
