@echo off
REM PHP Gomoku Build Script for Windows

echo Building PHP Gomoku PHAR package...

REM Check if Box is installed
where box >nul 2>&1
if %errorlevel% neq 0 (
    echo Box is not installed. Installing Box...
    composer require --dev humbug/box
)

REM Check if composer.json exists
if not exist "composer.json" (
    echo Error: composer.json not found
    exit /b 1
)

REM Install or update dependencies
echo Installing/Updating dependencies...
composer install --no-dev --optimize-autoloader

REM Run Box to build the PHAR
echo Creating PHAR package...
vendor\bin\box.bat compile

REM Check if the PHAR was created successfully
if exist "gomoku.phar" (
    echo Build successful! PHAR package created: gomoku.phar
    
    REM Make the PHAR executable (Windows doesn't need chmod)
    echo You can now run the application with: gomoku.phar
) else (
    echo Error: Failed to create gomoku.phar
    exit /b 1
)