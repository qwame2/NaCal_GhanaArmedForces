$f = 'resources\views\reports\index.blade.php'
$c = Get-Content $f -Raw -Encoding UTF8
$c = $c -replace '#881337', '#059669'
$c = $c -replace '#9f1239', '#047857'
$c = $c -replace '#4c0519', '#065f46'
$c = $c -replace 'rgba\(136,19,55', 'rgba(5,150,105'
$c = $c -replace 'rgba\(136, 19, 55', 'rgba(5, 150, 105'
$c = $c -replace '136,19,55\)', '5,150,105)'
[System.IO.File]::WriteAllText((Resolve-Path $f).Path, $c, [System.Text.Encoding]::UTF8)
Write-Host 'Done'
