# MongoDB PHP Extension Installation Script
# For PHP 8.4.14 (ZTS, x64)

Write-Host "=== MongoDB PHP Extension Installer ===" -ForegroundColor Cyan
Write-Host ""

$phpExtDir = "C:\PHP\ext"
$phpIni = "C:\PHP\php.ini"
$downloadUrl = "https://windows.php.net/downloads/pecl/releases/mongodb/2.1.0/php_mongodb-2.1.0-8.4-ts-vs17-x64.zip"
$tempZip = "$env:TEMP\mongodb_ext.zip"
$extractPath = "$env:TEMP\mongodb_ext"

# Check paths
if (-not (Test-Path $phpExtDir)) {
    Write-Host "ERROR: Extension directory not found: $phpExtDir" -ForegroundColor Red
    exit 1
}

if (-not (Test-Path $phpIni)) {
    Write-Host "ERROR: php.ini not found: $phpIni" -ForegroundColor Red
    exit 1
}

# Download
Write-Host "Downloading MongoDB extension..." -ForegroundColor Yellow
try {
    Invoke-WebRequest -Uri $downloadUrl -OutFile $tempZip -UseBasicParsing
    Write-Host "Download complete" -ForegroundColor Green
} catch {
    Write-Host "ERROR: Failed to download" -ForegroundColor Red
    Write-Host $_.Exception.Message -ForegroundColor Red
    exit 1
}

# Extract
Write-Host "Extracting..." -ForegroundColor Yellow
Expand-Archive -Path $tempZip -DestinationPath $extractPath -Force
Write-Host "Extraction complete" -ForegroundColor Green

# Copy DLL
Write-Host "Installing DLL..." -ForegroundColor Yellow
$dllSource = "$extractPath\php_mongodb.dll"
if (Test-Path $dllSource) {
    Copy-Item $dllSource -Destination "$phpExtDir\php_mongodb.dll" -Force
    Write-Host "DLL installed to $phpExtDir" -ForegroundColor Green
} else {
    Write-Host "ERROR: php_mongodb.dll not found" -ForegroundColor Red
    exit 1
}

# Update php.ini
Write-Host "Updating php.ini..." -ForegroundColor Yellow
$content = Get-Content $phpIni -Raw
$extensionLine = "extension=mongodb"

if ($content -notlike "*$extensionLine*") {
    Add-Content -Path $phpIni -Value "`n$extensionLine"
    Write-Host "Added extension=mongodb to php.ini" -ForegroundColor Green
} else {
    Write-Host "Extension already in php.ini" -ForegroundColor Green
}

# Cleanup
Remove-Item $tempZip -Force -ErrorAction SilentlyContinue
Remove-Item $extractPath -Recurse -Force -ErrorAction SilentlyContinue

Write-Host ""
Write-Host "=== Installation Complete! ===" -ForegroundColor Green
Write-Host ""
Write-Host "Next steps:" -ForegroundColor Cyan
Write-Host "1. Restart your PHP server" -ForegroundColor White
Write-Host "2. Verify: php -m | Select-String mongodb" -ForegroundColor White
Write-Host "3. Test: php test_mongodb.php" -ForegroundColor White
