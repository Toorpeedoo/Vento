# MongoDB PHP Extension Installation Guide

## Your System Configuration
- PHP Version: 8.4.14
- Architecture: 64-bit
- Thread Safety: TS (Thread Safe)
- PHP Configuration: C:\PHP\php.ini
- PHP Extensions Folder: C:\PHP\ext\

## Installation Steps

### Option 1: Manual Installation (Recommended)

1. **Download the MongoDB Extension**
   - Visit: https://pecl.php.net/package/mongodb
   - Click on "DLL" link next to the latest version
   - Download: `php_mongodb-2.1.0-8.4-ts-vs17-x64.zip` (or latest 8.4 TS x64 version)

2. **Install the Extension**
   ```powershell
   # Extract the DLL file
   # Copy php_mongodb.dll to C:\PHP\ext\
   ```

3. **Enable the Extension**
   - Open `C:\PHP\php.ini` in a text editor
   - Add this line:
     ```
     extension=mongodb
     ```

4. **Restart PHP Server**
   ```powershell
   # Stop your current PHP server (Ctrl+C if running)
   # Then start it again
   php -S localhost:8000
   ```

5. **Verify Installation**
   ```powershell
   php -m | Select-String "mongodb"
   ```

### Option 2: Using PECL (if available)

```powershell
pecl install mongodb
```

## Quick Installation PowerShell Script

```powershell
# Download and install MongoDB extension
$phpVersion = "8.4"
$downloadUrl = "https://windows.php.net/downloads/pecl/releases/mongodb/2.1.0/php_mongodb-2.1.0-8.4-ts-vs17-x64.zip"
$tempZip = "$env:TEMP\mongodb_ext.zip"
$phpExtDir = "C:\PHP\ext"

# Download
Invoke-WebRequest -Uri $downloadUrl -OutFile $tempZip

# Extract
Expand-Archive -Path $tempZip -DestinationPath "$env:TEMP\mongodb_ext" -Force

# Copy DLL
Copy-Item "$env:TEMP\mongodb_ext\php_mongodb.dll" -Destination "$phpExtDir\php_mongodb.dll"

# Add to php.ini (if not already present)
$phpIni = "C:\PHP\php.ini"
$extensionLine = "extension=mongodb"
if ((Get-Content $phpIni) -notcontains $extensionLine) {
    Add-Content -Path $phpIni -Value "`n$extensionLine"
}

Write-Host "MongoDB extension installed! Please restart your PHP server."
```

## After Installation

1. Test the connection:
   ```powershell
   php test_mongodb.php
   ```

2. Or check in browser:
   - Start server: `php -S localhost:8000`
   - Visit: http://localhost:8000/test_mongodb.php

## Troubleshooting

- **"Cannot find php_mongodb.dll"**: Make sure it's in C:\PHP\ext\
- **"Unable to load extension"**: Check PHP version matches (8.4, TS, x64)
- **"Extension already loaded"**: Remove duplicate extension lines from php.ini

## Migration Notes

Your application has been converted from text file database to MongoDB:
- All ProductDatabaseUtil methods now use MongoDB
- All UserDatabaseUtil methods now use MongoDB
- Connection string: mongodb+srv://Vento:Vento@vento.gknvzdv.mongodb.net/?appName=VENTO
- Database name: vento_inventory
- Collections: users, products

## Next Steps

1. Install the MongoDB extension
2. Test the connection with `test_mongodb.php`
3. Create your first admin user through signup.php
4. Start using the application!
