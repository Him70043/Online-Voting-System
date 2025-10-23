<?php
// Initialize HTTP Security Headers
if (file_exists(__DIR__ . '/includes/HTTPSecurityHeaders.php')) {
    require_once __DIR__ . '/includes/HTTPSecurityHeaders.php';
    if (class_exists('HTTPSecurityHeaders')) {
        HTTPSecurityHeaders::initialize();
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Login - Online Voting System by Himanshu Kumar</title>
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href='http://fonts.googleapis.com/css?family=Ubuntu' rel='stylesheet' type='text/css'>
    <link href='http://fonts.googleapis.com/css?family=Raleway' rel='stylesheet' type='text/css'>
    <link href='http://fonts.googleapis.com/css?family=Oswald' rel='stylesheet' type='text/css'>
    <link href='http://fonts.googleapis.com/css?family=Roboto+Condensed' rel='stylesheet' type='text/css'>
</head>

<body style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh;">
    <div class="container" style="padding-top: 100px;">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div style="background: rgba(255,255,255,0.95); padding: 40px; border-radius: 20px; box-shadow: 0 15px 35px rgba(0,0,0,0.3);">
                    <div class="text-center" style="margin-bottom: 30px;">
                        <h2 style="color: #333; font-weight: bold; margin-bottom: 10px;">🔐 Admin Access</h2>
                        <p style="color: #666; font-size: 16px;">Online Voting System Administration</p>
                        <p style="color: #888; font-size: 14px;">Developed by <strong>Himanshu Kumar</strong></p>
                    </div>

                    <?php
                    require_once "includes/SessionSecurity.php";
                    include "includes/CSRFProtection.php";
                    include "includes/XSSProtection.php";
                    include "includes/InputValidation.php";
                    require_once "includes/BruteForceProtection.php";
                    require_once "includes/SecurityLogger.php";
                    include "connection.php";
                    
                    // Initialize secure session
                    SessionSecurity::initializeSecureSession();
                    
                    // Set security headers
                    XSSProtection::setSecurityHeaders();
                    
                    // Initialize brute force protection
                    BruteForceProtection::initialize($con);
                    
                    // Initialize security logger
                    SecurityLogger::initialize($con);
                    
                    // Check if we need to show CAPTCHA for admin
                    $showCaptcha = false;
                    $captchaData = null;
                    if (isset($_POST['username']) || isset($_GET['username'])) {
                        $checkUsername = $_POST['username'] ?? $_GET['username'];
                        $showCaptcha = BruteForceProtection::shouldShowCaptcha($checkUsername);
                        if ($showCaptcha) {
                            $captchaData = BruteForceProtection::generateCaptcha();
                        }
                    }
                    
                    if (isset($_POST['admin_login'])) {
                        // Verify CSRF token
                        if (!isset($_POST['csrf_token']) || !CSRFProtection::validateToken($_POST['csrf_token'])) {
                            SecurityLogger::logSecurityEvent('CSRF_FAILURE', 'HIGH', 'Admin login CSRF token validation failed');
                            echo '<div class="alert alert-danger text-center">🔒 Security token validation failed. Please try again.</div>';
                        } else {
                            // Check submission rate limiting
                            $clientIP = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
                            $rateLimitKey = 'admin_login_' . $clientIP;
                            if (!InputValidation::checkSubmissionRate($rateLimitKey, 3, 300)) { // 3 attempts per 5 minutes
                                SecurityLogger::logSecurityEvent('RATE_LIMIT_EXCEEDED', 'HIGH', "Admin login rate limit exceeded from IP: $clientIP");
                                echo '<div class="alert alert-danger text-center">🚫 Too many admin login attempts. Please wait before trying again.</div>';
                            } else {
                                // Enhanced input validation
                                $validation = InputValidation::validateForm($_POST, 'admin_login');
                                if (!$validation['valid']) {
                                    $errorMessages = implode('<br>', $validation['errors']);
                                    SecurityLogger::logSecurityEvent('VALIDATION_FAILURE', 'MEDIUM', "Admin login validation failed: " . implode(', ', $validation['errors']));
                                    echo "<div class='alert alert-danger text-center'>❌ $errorMessages</div>";
                                } else {
                                    // Use validated and sanitized data
                                    $validatedData = $validation['data'];
                                    $username = $validatedData['username'];
                                    $password = $validatedData['password'];
                            
                            // Check if IP is rate limited
                            if (BruteForceProtection::isIPRateLimited(BruteForceProtection::getClientIP())) {
                                SecurityLogger::logAuthenticationAttempt($username, false, 'admin', 'IP rate limited');
                                echo '<div class="alert alert-danger text-center">🚫 Too many login attempts from your IP address. Please try again later.</div>';
                            }
                            // Check if account is locked
                            else if (BruteForceProtection::isAccountLocked($username)) {
                                $lockoutInfo = BruteForceProtection::getLockoutInfo($username);
                                $remainingMinutes = ceil($lockoutInfo['remaining_time'] / 60);
                                SecurityLogger::logAuthenticationAttempt($username, false, 'admin', 'Account locked');
                                echo "<div class='alert alert-danger text-center'>🔒 Account temporarily locked due to multiple failed login attempts. Please try again in $remainingMinutes minutes.</div>";
                            }
                            // Check CAPTCHA if required
                            else if (BruteForceProtection::shouldShowCaptcha($username)) {
                                if (!isset($_POST['captcha_answer']) || !BruteForceProtection::verifyCaptcha($_POST['captcha_answer'])) {
                                    BruteForceProtection::recordLoginAttempt($username, false);
                                    SecurityLogger::logAuthenticationAttempt($username, false, 'admin', 'Invalid CAPTCHA');
                                    echo '<div class="alert alert-danger text-center">❌ Invalid CAPTCHA answer. Please try again.</div>';
                                } else {
                                    // CAPTCHA passed, check credentials
                                    if ($username === 'admin' && $password === 'himanshu123') {
                                        BruteForceProtection::recordLoginAttempt($username, true);
                                        SecurityLogger::logAuthenticationAttempt($username, true, 'admin');
                                        SessionSecurity::startAdminSession('Himanshu Kumar');
                                        
                                        // Set admin privilege (default to admin level)
                                        require_once 'includes/AdminSecurity.php';
                                        AdminSecurity::setAdminPrivilege(AdminSecurity::PRIVILEGE_ADMIN);
                                        
                                        header("Location: admin_dashboard.php");
                                        exit();
                                    } else {
                                        BruteForceProtection::recordLoginAttempt($username, false);
                                        SecurityLogger::logAuthenticationAttempt($username, false, 'admin', 'Invalid credentials');
                                        echo '<div class="alert alert-danger text-center">❌ Invalid admin credentials!</div>';
                                    }
                                }
                            }
                            // Normal login check
                            else {
                                if ($username === 'admin' && $password === 'himanshu123') {
                                    BruteForceProtection::recordLoginAttempt($username, true);
                                    SecurityLogger::logAuthenticationAttempt($username, true, 'admin');
                                    SessionSecurity::startAdminSession('Himanshu Kumar');
                                    
                                    // Set admin privilege (default to admin level)
                                    require_once 'includes/AdminSecurity.php';
                                    AdminSecurity::setAdminPrivilege(AdminSecurity::PRIVILEGE_ADMIN);
                                    
                                    header("Location: admin_dashboard.php");
                                    exit();
                                } else {
                                    BruteForceProtection::recordLoginAttempt($username, false);
                                    SecurityLogger::logAuthenticationAttempt($username, false, 'admin', 'Invalid credentials');
                                    echo '<div class="alert alert-danger text-center">❌ Invalid admin credentials!</div>';
                                }
                            }
                                }
                            }
                        }
                    }
                    ?>

                    <form method="post" action="">
                        <?php echo CSRFProtection::getTokenField(); ?>
                        <?php echo InputValidation::generateHoneypotFields(); ?>
                        
                        <div class="form-group" style="margin-bottom: 20px;">
                            <label style="font-weight: bold; color: #333; margin-bottom: 8px;">👤 Admin Username:</label>
                            <input type="text" name="username" class="form-control" required maxlength="50"
                                   style="padding: 12px; border-radius: 10px; border: 2px solid #ddd; font-size: 16px;"
                                   placeholder="Enter admin username" 
                                   value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
                        </div>
                        
                        <div class="form-group" style="margin-bottom: 25px;">
                            <label style="font-weight: bold; color: #333; margin-bottom: 8px;">🔑 Admin Password:</label>
                            <input type="password" name="password" class="form-control" required maxlength="128"
                                   style="padding: 12px; border-radius: 10px; border: 2px solid #ddd; font-size: 16px;"
                                   placeholder="Enter admin password">
                        </div>
                        
                        <?php if ($showCaptcha && $captchaData): ?>
                        <div class="form-group" style="margin-bottom: 25px; background: #f8f9fa; padding: 15px; border-radius: 10px; border: 2px solid #dc3545;">
                            <label style="font-weight: bold; color: #dc3545; margin-bottom: 8px;">🔒 Security Verification Required</label>
                            <p style="font-size: 14px; color: #666; margin-bottom: 10px;">Multiple failed attempts detected. Please solve this simple math problem:</p>
                            <div style="font-size: 18px; font-weight: bold; color: #333; margin: 10px 0;">
                                <?php echo $captchaData['question']; ?>
                            </div>
                            <input type="number" name="captcha_answer" class="form-control" required 
                                   style="padding: 12px; border-radius: 10px; border: 2px solid #ddd; font-size: 16px; width: 150px;"
                                   placeholder="Enter answer">
                        </div>
                        <?php endif; ?>
                        
                        <button type="submit" name="admin_login" 
                                style="background: linear-gradient(45deg, #FF6B6B, #4ECDC4); color: white; border: none; padding: 15px; width: 100%; font-size: 18px; font-weight: bold; border-radius: 10px; cursor: pointer; transition: all 0.3s;">
                            🚀 Access Admin Panel
                        </button>
                    </form>

                    <div class="text-center" style="margin-top: 25px;">
                        <p style="color: #666; font-size: 14px;">Default Credentials:</p>
                        <p style="color: #888; font-size: 12px;">Username: <code>admin</code> | Password: <code>himanshu123</code></p>
                        <a href="index.php" style="color: #667eea; text-decoration: none; font-weight: bold;">← Back to Main Site</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
</body>
</html>