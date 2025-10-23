<?php
require_once __DIR__ . "/includes/SessionSecurity.php";
include "connection.php"; 
require_once __DIR__ . "/includes/PasswordSecurity.php";
include "includes/CSRFProtection.php";
include "includes/InputValidation.php";
require_once "includes/BruteForceProtection.php";
require_once "includes/SecurityLogger.php";

// Initialize secure session
SessionSecurity::initializeSecureSession();

// Initialize brute force protection
BruteForceProtection::initialize($con);

// Initialize security logger
SecurityLogger::initialize($con);

if(isset($_POST['login'])) {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || !CSRFProtection::validateToken($_POST['csrf_token'])) {
        SecurityLogger::logSecurityEvent('CSRF_FAILURE', 'HIGH', 'Login CSRF token validation failed');
        $error = "<center><h4><font color='#FF0000'>Security token validation failed. Please try again.</h4></center></font>";
        include "login.php";
        exit();
    }
    
    // Check submission rate limiting
    $clientIP = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $rateLimitKey = 'login_' . $clientIP;
    if (!InputValidation::checkSubmissionRate($rateLimitKey, 5, 300)) { // 5 attempts per 5 minutes
        SecurityLogger::logSecurityEvent('RATE_LIMIT_EXCEEDED', 'HIGH', "Login rate limit exceeded from IP: $clientIP");
        $error = "<center><h4><font color='#FF0000'>Too many login attempts. Please wait before trying again.</h4></center></font>";
        include "login.php";
        exit();
    }
    
    // Enhanced input validation
    $validation = InputValidation::validateForm($_POST, 'login');
    if (!$validation['valid']) {
        $errorMessages = implode('<br>', $validation['errors']);
        SecurityLogger::logSecurityEvent('VALIDATION_FAILURE', 'MEDIUM', "Login validation failed: " . implode(', ', $validation['errors']));
        $error = "<center><h4><font color='#FF0000'>$errorMessages</h4></center></font>";
        include "login.php";
        exit();
    }
    
    // Use validated and sanitized data
    $validatedData = $validation['data'];
    $username = $validatedData['username'];
    $password = $validatedData['password'];
    
    // Check if IP is rate limited
    if (BruteForceProtection::isIPRateLimited(BruteForceProtection::getClientIP())) {
        SecurityLogger::logAuthenticationAttempt($username, false, 'user', 'IP rate limited');
        $error = "<center><h4><font color='#FF0000'>Too many login attempts from your IP address. Please try again later.</h4></center></font>";
        include "login.php";
        exit();
    }
    
    // Check if account is locked
    if (BruteForceProtection::isAccountLocked($username)) {
        $lockoutInfo = BruteForceProtection::getLockoutInfo($username);
        $remainingMinutes = ceil($lockoutInfo['remaining_time'] / 60);
        SecurityLogger::logAuthenticationAttempt($username, false, 'user', 'Account locked');
        $error = "<center><h4><font color='#FF0000'>Account temporarily locked due to multiple failed login attempts. Please try again in $remainingMinutes minutes.</h4></center></font>";
        include "login.php";
        exit();
    }
    
    // Check if CAPTCHA is required and validate it
    if (BruteForceProtection::shouldShowCaptcha($username)) {
        if (!isset($_POST['captcha_answer']) || !BruteForceProtection::verifyCaptcha($_POST['captcha_answer'])) {
            BruteForceProtection::recordLoginAttempt($username, false);
            SecurityLogger::logAuthenticationAttempt($username, false, 'user', 'Invalid CAPTCHA');
            $error = "<center><h4><font color='#FF0000'>Invalid CAPTCHA answer. Please try again.</h4></center></font>";
            include "login.php";
            exit();
        }
    }
    
    // Use prepared statement to get user data
    $stmt = mysqli_prepare($con, "SELECT * FROM loginusers WHERE username = ? AND status = 'ACTIVE'");
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if (mysqli_num_rows($result) > 0) {
        $member = mysqli_fetch_assoc($result);
        
        // Password validation will handle both MD5 and bcrypt passwords
        
        $storedPassword = $member['password'];
        $passwordValid = false;
        
        // Check if it's an old MD5 password (for backward compatibility during transition)
        if (PasswordSecurity::isMD5Hash($storedPassword)) {
            // Verify against MD5 (temporary backward compatibility)
            if ($storedPassword === md5($password)) {
                $passwordValid = true;
                
                // Upgrade to bcrypt hash
                $newHash = PasswordSecurity::hashPassword($password);
                $updateStmt = mysqli_prepare($con, "UPDATE loginusers SET password = ? WHERE username = ?");
                mysqli_stmt_bind_param($updateStmt, "ss", $newHash, $username);
                mysqli_stmt_execute($updateStmt);
                mysqli_stmt_close($updateStmt);
            }
        } else {
            // Use secure bcrypt verification
            $passwordValid = PasswordSecurity::verifyPassword($password, $storedPassword);
            
            // Check if password needs rehashing (security updates)
            if ($passwordValid && PasswordSecurity::needsRehash($storedPassword)) {
                $newHash = PasswordSecurity::hashPassword($password);
                $updateStmt = mysqli_prepare($con, "UPDATE loginusers SET password = ? WHERE username = ?");
                mysqli_stmt_bind_param($updateStmt, "ss", $newHash, $username);
                mysqli_stmt_execute($updateStmt);
                mysqli_stmt_close($updateStmt);
            }
        }
        
        if ($passwordValid) {
            // Record successful login attempt
            BruteForceProtection::recordLoginAttempt($username, true);
            SecurityLogger::logAuthenticationAttempt($username, true, 'user');
            
            // Start authenticated session with security features
            SessionSecurity::startAuthenticatedSession($member['username'], $member['rank']);
            
            if($member['rank']=='administrator'){
                header("location: admin.php");
            }
            else if($member['rank']=='voter'){
                header("location: voter.php");
            }
        } else {
            // Record failed login attempt
            BruteForceProtection::recordLoginAttempt($username, false);
            SecurityLogger::logAuthenticationAttempt($username, false, 'user', 'Invalid credentials');
            $error = "<center><h4><font color='#FF0000'>Incorrect Username or Password</h4></center></font>";
            include "login.php";
        }
    } else {
        // Record failed login attempt for non-existent user
        BruteForceProtection::recordLoginAttempt($username, false);
        SecurityLogger::logAuthenticationAttempt($username, false, 'user', 'User not found');
        $error = "<center><h4><font color='#FF0000'>Incorrect Username or Password</h4></center></font>";
        include "login.php";
    }
    
    mysqli_stmt_close($stmt);
} else {
    $error = "<center><h4><font color='#FF0000'>Invalid Username or Password</h4></center></font>";
    include "login.php";
}
?>