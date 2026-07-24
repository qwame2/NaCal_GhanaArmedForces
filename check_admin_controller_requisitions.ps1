$f = 'app\Http\Controllers\AdminController.php'
$lines = Get-Content $f
for ($i = 0; $i -lt $lines.Length; $i++) {
    $line = $lines[$i]
    if ($line -match 'requisition') {
        Write-Host "$($i + 1): $($line.Trim())"
    }
}
