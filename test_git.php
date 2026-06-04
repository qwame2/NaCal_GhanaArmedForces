<?php
$output = shell_exec('git status 2>&1');
file_put_contents(__DIR__ . '/test_git.txt', $output);
echo "Done status\n";

$output2 = shell_exec('git log -n 5 --oneline 2>&1');
file_put_contents(__DIR__ . '/test_git_log.txt', $output2);
echo "Done log\n";
