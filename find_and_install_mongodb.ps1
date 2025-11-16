# Script to help find and install MongoDB DLL
Write-Host "=== MongoDB DLL Finder & Installer ===" -ForegroundColor Cyan
Write-Host ""

# Check common download locations
$searchPaths = @(
    "$env:USERPROFILE\Downloads",
    "C:\Users\Excreos\Desktop",
    "C:\Users\Excreos\Desktop\VENTO"
)

$foundDll = $null

# Search for DLL files
foreach ($path in $searchPaths) {
    if (Test-Path $path) {
        Write-Host "Searching in: $path" -ForegroundColor Yellow
        $dlls = Get-ChildItem -Path $path -Filter "*mongodb*.dll" -Recurse -ErrorAction SilentlyContinue
        if ($dlls) {
            Write-Host "  Found: $($dlls[0].FullName)" -ForegroundColor Green
            $foundDll = $dlls[0].FullName
            break
        }
        
        # Also check for zip files that might contain the DLL
        $zips = Get-ChildItem -Path $path -Filter "*mongodb*.zip" -Recurse -ErrorAction SilentlyContinue
        if ($zips) {
            Write-Host "  Found ZIP: $($zips[0].FullName)" -ForegroundColor Yellow
            $extractPath = "$env:TEMP\mongodb_check"
            Expand-Archive -Path $zips[0].FullName -DestinationPath $extractPath -Force -ErrorAction SilentlyContinue
            $dllInZip = Get-ChildItem -Path $extractPath -Filter "php_mongodb.dll" -Recurse -ErrorAction SilentlyContinue
            if ($dllInZip) {
                Write-Host "  Found DLL in ZIP: $($dllInZip[0].FullName)" -ForegroundColor Green
                $foundDll = $dllInZip[0].FullName
                Remove-Item $extractPath -Recurse -Force -ErrorAction SilentlyContinue
                break
            }
            Remove-Item $extractPath -Recurse -Force -ErrorAction SilentlyContinue
        }
    }
}

if ($foundDll) {
    Write-Host ""
    Write-Host "Installing DLL..." -ForegroundColor Yellow
    $targetPath = "C:\PHP\ext\php_mongodb.dll"
    Copy-Item -Path $foundDll -Destination $targetPath -Force
    Write-Host "  Copied to: $targetPath" -ForegroundColor Green
    
    # Update php.ini
    $phpIni = "C:\PHP\php.ini"
    if (Test-Path $phpIni) {
        $content = Get-Content $phpIni -Raw
        if ($content -notlike "*extension=mongodb*") {
            Add-Content -Path $phpIni -Value "`nextension=mongodb"
            Write-Host "  Added to php.ini" -ForegroundColor Green
        } else {
            Write-Host "  Already in php.ini" -ForegroundColor Yellow
        }
    }
    
    Write-Host ""
    Write-Host "Installation complete! Restart your PHP server." -ForegroundColor Green
} else {
    Write-Host ""
    Write-Host "No MongoDB DLL found." -ForegroundColor Red
    Write-Host ""
    Write-Host "You need to download the Windows DLL version:" -ForegroundColor Yellow
    Write-Host "1. Visit: https://pecl.php.net/package/mongodb" -ForegroundColor White
    Write-Host "2. Click 'DLL' link next to version 2.1.x" -ForegroundColor White
    Write-Host "3. Download php_mongodb-X.X.X-8.4-ts-vs17-x64.zip (or 8.3 if 8.4 not available)" -ForegroundColor White
    Write-Host "4. Extract php_mongodb.dll from the ZIP" -ForegroundColor White
    Write-Host "5. Run this script again" -ForegroundColor White
}

