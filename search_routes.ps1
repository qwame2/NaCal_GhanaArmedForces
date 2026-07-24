$f = 'routes\web.php'
$lines = Get-Content $f
for ($i = 0; $i -lt $lines.Length; $i++) {
    $line = $lines[$i]
    if ($line -match 'supplier|suppliers') {
        Write-Host "$($i + 1): $($line.Trim())"
    }
}
