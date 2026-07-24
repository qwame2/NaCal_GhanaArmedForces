$files = Get-ChildItem -Path 'app\Http\Controllers\' -Filter '*.php' -Recurse

foreach ($f in $files) {
    $content = Get-Content $f.FullName -Raw
    if ($content -match 'function requisitions') {
        Write-Host "Match in $($f.FullName)"
    }
}
