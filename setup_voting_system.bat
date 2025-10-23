@echo off
echo ========================================
echo Online Voting System Setup Script
echo ========================================
echo.

echo Step 1: Installing XAMPP...
echo Please run the xampp-installer.exe file that was downloaded
echo Install XAMPP to the default location: C:\xampp
echo.
pause

echo Step 2: Starting XAMPP Services...
echo Please start XAMPP Control Panel and start Apache and MySQL services
echo.
pause

echo Step 3: Setting up project files...
if not exist "C:\xampp\htdocs\" (
    echo ERROR: XAMPP not found at C:\xampp\
    echo Please install XAMPP first
    pause
    exit /b 1
)

echo Copying project files to XAMPP htdocs...
xcopy "Online-Voting-System" "C:\xampp\htdocs\Online-Voting-System\" /E /I /Y

echo Step 4: Database setup instructions...
echo.
echo 1. Open your web browser
echo 2. Go to: http://localhost/phpmyadmin/
echo 3. Click "New" to create a database
echo 4. Name it: polltest
echo 5. Click "Create"
echo 6. Select the polltest database
echo 7. Click "Import" tab
echo 8. Choose file: C:\xampp\htdocs\Online-Voting-System\db\polltest.sql
echo 9. Click "Go" to import
echo.
pause

echo Step 5: Testing the system...
echo.
echo Open your web browser and go to:
echo http://localhost/Online-Voting-System/
echo.
echo You should see the Online Voting System homepage
echo.
echo Default test login credentials are available in the database
echo Or you can register a new account
echo.
echo Setup complete!
pause