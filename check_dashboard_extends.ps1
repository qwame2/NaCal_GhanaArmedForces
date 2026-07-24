$files = Get-ChildItem -Path 'resources\views\' -Filter '*.blade.php' -Recurse

foreach ($f in $files) {
    $content = Get-Content $f.FullName -Raw
    if ($content -match "@extends\('layouts\.dashboard'\)") {
        Write-Host "$($f.FullName.Replace((Get-Location).Path, ''))"
    }
}
