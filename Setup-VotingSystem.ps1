# Online Voting System Setup Script
Write-Host "========================================" -ForegroundColor Green
Write-Host "Online Voting System Setup Script" -ForegroundColor Green
Write-Host "========================================" -ForegroundColor Green
Write-Host ""

# Check if XAMPP is already installed
if (Test-Path "C:\xampp\xampp-control.exe") {
    Write-Host "XAMPP is already installed!" -ForegroundColor Green
    
    # Start XAMPP services
    Write-Host "Starting XAMPP Control Panel..." -ForegroundColor Yellow
    Start-Process "C:\xampp\xampp-control.exe"
    
    Write-Host "Please start Apache and MySQL services from XAMPP Control Panel" -ForegroundColor Yellow
    Read-Host "Press Enter when services are started"
} else {
    Write-Host "XAMPP not found. Please install XAMPP first." -ForegroundColor Red
    Write-Host "1. Run the xampp-installer.exe file" -ForegroundColor Yellow
    Write-Host "2. Install to default location: C:\xampp" -ForegroundColor Yellow
    Write-Host "3. Start XAMPP Control Panel and start Apache and MySQL" -ForegroundColor Yellow
    Read-Host "Press Enter when XAMPP is installed and services are running"
}

# Copy project files
Write-Host "Setting up project files..." -ForegroundColor Yellow

if (Test-Path "C:\xampp\htdocs\") {
    if (Test-Path "Online-Voting-System") {
        Write-Host "Copying project files to XAMPP htdocs..." -ForegroundColor Yellow
        Copy-Item "Online-Voting-System" "C:\xampp\htdocs\" -Recurse -Force
        Write-Host "Project files copied successfully!" -ForegroundColor Green
    } else {
        Write-Host "ERROR: Online-Voting-System folder not found in current directory" -ForegroundColor Red
        exit 1
    }
} else {
    Write-Host "ERROR: XAMPP htdocs folder not found. Please install XAMPP first." -ForegroundColor Red
    exit 1
}

# Database setup instructions
Write-Host ""
Write-Host "========================================" -ForegroundColor Green
Write-Host "Database Setup Instructions" -ForegroundColor Green
Write-Host "========================================" -ForegroundColor Green
Write-Host "1. Open your web browser" -ForegroundColor Yellow
Write-Host "2. Go to: http://localhost/phpmyadmin/" -ForegroundColor Yellow
Write-Host "3. Click 'New' to create a database" -ForegroundColor Yellow
Write-Host "4. Name it: polltest" -ForegroundColor Yellow
Write-Host "5. Click 'Create'" -ForegroundColor Yellow
Write-Host "6. Select the polltest database" -ForegroundColor Yellow
Write-Host "7. Click 'Import' tab" -ForegroundColor Yellow
Write-Host "8. Choose file: C:\xampp\htdocs\Online-Voting-System\db\polltest.sql" -ForegroundColor Yellow
Write-Host "9. Click 'Go' to import" -ForegroundColor Yellow
Write-Host ""

# Open phpMyAdmin automatically
Write-Host "Opening phpMyAdmin in your default browser..." -ForegroundColor Green
Start-Process "http://localhost/phpmyadmin/"

Read-Host "Press Enter when database setup is complete"

# Final instructions
Write-Host ""
Write-Host "========================================" -ForegroundColor Green
Write-Host "Setup Complete!" -ForegroundColor Green
Write-Host "========================================" -ForegroundColor Green
Write-Host "Your Online Voting System is ready!" -ForegroundColor Green
Write-Host ""
Write-Host "Access your system at:" -ForegroundColor Yellow
Write-Host "http://localhost/Online-Voting-System/" -ForegroundColor Cyan
Write-Host ""
Write-Host "Features available:" -ForegroundColor Yellow
Write-Host "- User Registration: http://localhost/Online-Voting-System/register.php" -ForegroundColor Cyan
Write-Host "- User Login: http://localhost/Online-Voting-System/login.php" -ForegroundColor Cyan
Write-Host "- Voting Interface: Available after login" -ForegroundColor Cyan
Write-Host "- Vote Statistics: Available on main page" -ForegroundColor Cyan
Write-Host ""

# Open the voting system
Write-Host "Opening the Online Voting System..." -ForegroundColor Green
Start-Process "http://localhost/Online-Voting-System/"

Write-Host "Setup script completed successfully!" -ForegroundColor Green