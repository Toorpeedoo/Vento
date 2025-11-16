# MongoDB PHP Extension DLL Installation Guide

## Current Status
- ❌ MongoDB extension is NOT installed
- ❌ Website will NOT work until extension is installed

## What You Need

You need to download a **Windows DLL file** (`.dll`), NOT a source package (`.tgz`).

### Where to Get It

**Option 1: PECL Website (Recommended)**
1. Go to: https://pecl.php.net/package/mongodb
2. Scroll down to find version **2.1.0** or **2.1.4**
3. Look for a **"DLL"** link in the download section
4. Click it to download a `.zip` file
5. Extract `php_mongodb.dll` from the ZIP

**Option 2: GitHub Releases**
1. Visit: https://github.com/mongodb/mongo-php-driver/releases
2. Look for Windows DLL builds
3. Download the appropriate version for PHP 8.4 TS x64

**Option 3: Try PHP 8.3 DLL (May Work)**
If PHP 8.4 DLL is not available:
- Download: `php_mongodb-2.1.x-8.3-ts-vs17-x64.zip`
- Extract `php_mongodb.dll`

## Installation Steps

Once you have `php_mongodb.dll`:

### Step 1: Copy DLL to Extensions Folder
```powershell
Copy-Item "path\to\php_mongodb.dll" -Destination "C:\PHP\ext\php_mongodb.dll"
```

### Step 2: Enable in php.ini
1. Open `C:\PHP\php.ini` in a text editor
2. Add this line (if not already present):
   ```
   extension=mongodb
   ```
3. Save the file

### Step 3: Restart PHP Server
Stop and restart your PHP server.

### Step 4: Verify Installation
```powershell
php -m | Select-String mongodb
```
Should output: `mongodb`

### Step 5: Test Connection
```powershell
php test_mongodb.php
```

## Quick Install Script

Once you have the DLL file, run this in PowerShell:

```powershell
# Replace "path\to\php_mongodb.dll" with actual path to your DLL
$dllPath = "path\to\php_mongodb.dll"

# Copy to extensions folder
Copy-Item $dllPath -Destination "C:\PHP\ext\php_mongodb.dll" -Force

# Add to php.ini
$phpIni = "C:\PHP\php.ini"
$content = Get-Content $phpIni -Raw
if ($content -notlike "*extension=mongodb*") {
    Add-Content -Path $phpIni -Value "`nextension=mongodb"
}

Write-Host "Installation complete! Restart your PHP server."
```

## Alternative: Use find_and_install_mongodb.ps1

If you have the DLL file (or ZIP containing it) in your Downloads folder or Desktop:
```powershell
powershell -ExecutionPolicy Bypass -File find_and_install_mongodb.ps1
```

This script will automatically find and install it for you.

