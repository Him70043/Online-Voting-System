<?php
/**
 * Brute Force Protection System
 * Implements account lockout and IP-based rate limiting
 * 
 * Features:
 * - Failed login attempt tracking
 * - Account lockout after 3 failed attempts
 * - IP-based rate limiting
 * - CAPTCHA protection for repeated failures
 * 
 * @author Himanshu Kumar
 * @version 1.0
 */

class BruteForceProtection {
    
    // Configuration constants
    const MAX_LOGIN_ATTEMPTS = 3;
    const LOCKOUT_DURATION = 900; // 15 minutes in seconds
    const IP_RATE_LIMIT = 10; // Max attempts per IP per hour
    const CAPTCHA_THRESHOLD = 2; // Show CAPTCHA after 2 failed attempts
    
    private static $connection;
    
    /**
     * Initialize the brute force protection system
     */
    public static function initialize($dbConnection) {
        self::$connection = $dbConnection;
        self::createTables();
    }
    
    /**
     * Create necessary database tables for tracking login attempts
     */
    private static function createTables() {
        // Table for tracking failed login attempts by username
        $sql1 = "CREATE TABLE IF NOT EXISTS login_attempts (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(100) NOT NULL,
            ip_address VARCHAR(45) NOT NULL,
            attempt_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            success BOOLEAN DEFAULT FALSE,
            user_agent TEXT,
            INDEX idx_username (username),
            INDEX idx_ip_address (ip_address),
            INDEX idx_attempt_time (attempt_time)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        
        // Table for account lockouts
        $sql2 = "CREATE TABLE IF NOT EXISTS account_lockouts (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(100) NOT NULL UNIQUE,
            locked_until TIMESTAMP NOT NULL,
            failed_attempts INT DEFAULT 0,
            last_attempt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_username (username),
            INDEX idx_locked_until (locked_until)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        
        // Table for IP-based rate limiting
        $sql3 = "CREATE TABLE IF NOT EXISTS ip_rate_limits (
            id INT AUTO_INCREMENT PRIMARY KEY,
            ip_address VARCHAR(45) NOT NULL UNIQUE,
            attempt_count INT DEFAULT 0,
            first_attempt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            last_attempt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            blocked_until TIMESTAMP NULL,
            INDEX idx_ip_address (ip_address),
            INDEX idx_blocked_until (blocked_until)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        
        mysqli_query(self::$connection, $sql1);
        mysqli_query(self::$connection, $sql2);
        mysqli_query(self::$connection, $sql3);
    }
    
    /**
     * Check if an account is currently locked
     */
    public static function isAccountLocked($username) {
        $stmt = mysqli_prepare(self::$connection, 
            "SELECT locked_until FROM account_lockouts WHERE username = ? AND locked_until > NOW()");
        mysqli_stmt_bind_param($stmt, "s", $username);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        $isLocked = mysqli_num_rows($result) > 0;
        mysqli_stmt_close($stmt);
        
        return $isLocked;
    }
    
    /**
     * Check if IP address is rate limited
     */
    public static function isIPRateLimited($ipAddress) {
        // Clean up old entries (older than 1 hour)
        $cleanupStmt = mysqli_prepare(self::$connection,
            "DELETE FROM ip_rate_limits WHERE first_attempt < DATE_SUB(NOW(), INTERVAL 1 HOUR) AND blocked_until IS NULL");
        mysqli_stmt_execute($cleanupStmt);
        mysqli_stmt_close($cleanupStmt);
        
        // Check current IP rate limit
        $stmt = mysqli_prepare(self::$connection,
            "SELECT attempt_count, blocked_until FROM ip_rate_limits WHERE ip_address = ?");
        mysqli_stmt_bind_param($stmt, "s", $ipAddress);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if ($row = mysqli_fetch_assoc($result)) {
            mysqli_stmt_close($stmt);
            
            // Check if IP is currently blocked
            if ($row['blocked_until'] && strtotime($row['blocked_until']) > time()) {
                return true;
            }
            
            // Check if rate limit exceeded
            return $row['attempt_count'] >= self::IP_RATE_LIMIT;
        }
        
        mysqli_stmt_close($stmt);
        return false;
    }
    
    /**
     * Get the number of failed attempts for a username
     */
    public static function getFailedAttempts($username) {
        $stmt = mysqli_prepare(self::$connection,
            "SELECT failed_attempts FROM account_lockouts WHERE username = ?");
        mysqli_stmt_bind_param($stmt, "s", $username);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if ($row = mysqli_fetch_assoc($result)) {
            mysqli_stmt_close($stmt);
            return $row['failed_attempts'];
        }
        
        mysqli_stmt_close($stmt);
        return 0;
    }
    
    /**
     * Check if CAPTCHA should be shown
     */
    public static function shouldShowCaptcha($username) {
        return self::getFailedAttempts($username) >= self::CAPTCHA_THRESHOLD;
    }
    
    /**
     * Record a login attempt
     */
    public static function recordLoginAttempt($username, $success = false) {
        $ipAddress = self::getClientIP();
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        
        // Record the attempt
        $stmt = mysqli_prepare(self::$connection,
            "INSERT INTO login_attempts (username, ip_address, success, user_agent) VALUES (?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "ssis", $username, $ipAddress, $success, $userAgent);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        
        // Update IP rate limiting
        self::updateIPRateLimit($ipAddress);
        
        if (!$success) {
            self::handleFailedAttempt($username);
        } else {
            self::clearFailedAttempts($username);
        }
    }
    
    /**
     * Handle a failed login attempt
     */
    private static function handleFailedAttempt($username) {
        // Check if account lockout record exists
        $stmt = mysqli_prepare(self::$connection,
            "SELECT failed_attempts FROM account_lockouts WHERE username = ?");
        mysqli_stmt_bind_param($stmt, "s", $username);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if ($row = mysqli_fetch_assoc($result)) {
            // Update existing record
            $failedAttempts = $row['failed_attempts'] + 1;
            mysqli_stmt_close($stmt);
            
            if ($failedAttempts >= self::MAX_LOGIN_ATTEMPTS) {
                // Lock the account
                $lockUntil = date('Y-m-d H:i:s', time() + self::LOCKOUT_DURATION);
                $updateStmt = mysqli_prepare(self::$connection,
                    "UPDATE account_lockouts SET failed_attempts = ?, locked_until = ?, last_attempt = NOW() WHERE username = ?");
                mysqli_stmt_bind_param($updateStmt, "iss", $failedAttempts, $lockUntil, $username);
            } else {
                // Just increment failed attempts
                $updateStmt = mysqli_prepare(self::$connection,
                    "UPDATE account_lockouts SET failed_attempts = ?, last_attempt = NOW() WHERE username = ?");
                mysqli_stmt_bind_param($updateStmt, "is", $failedAttempts, $username);
            }
            
            mysqli_stmt_execute($updateStmt);
            mysqli_stmt_close($updateStmt);
        } else {
            // Create new record
            mysqli_stmt_close($stmt);
            $insertStmt = mysqli_prepare(self::$connection,
                "INSERT INTO account_lockouts (username, failed_attempts) VALUES (?, 1)");
            mysqli_stmt_bind_param($insertStmt, "s", $username);
            mysqli_stmt_execute($insertStmt);
            mysqli_stmt_close($insertStmt);
        }
    }
    
    /**
     * Clear failed attempts for successful login
     */
    private static function clearFailedAttempts($username) {
        $stmt = mysqli_prepare(self::$connection,
            "DELETE FROM account_lockouts WHERE username = ?");
        mysqli_stmt_bind_param($stmt, "s", $username);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }
    
    /**
     * Update IP rate limiting
     */
    private static function updateIPRateLimit($ipAddress) {
        // Check if IP record exists
        $stmt = mysqli_prepare(self::$connection,
            "SELECT attempt_count FROM ip_rate_limits WHERE ip_address = ?");
        mysqli_stmt_bind_param($stmt, "s", $ipAddress);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if ($row = mysqli_fetch_assoc($result)) {
            // Update existing record
            $attemptCount = $row['attempt_count'] + 1;
            mysqli_stmt_close($stmt);
            
            if ($attemptCount >= self::IP_RATE_LIMIT) {
                // Block IP for 1 hour
                $blockUntil = date('Y-m-d H:i:s', time() + 3600);
                $updateStmt = mysqli_prepare(self::$connection,
                    "UPDATE ip_rate_limits SET attempt_count = ?, last_attempt = NOW(), blocked_until = ? WHERE ip_address = ?");
                mysqli_stmt_bind_param($updateStmt, "iss", $attemptCount, $blockUntil, $ipAddress);
            } else {
                $updateStmt = mysqli_prepare(self::$connection,
                    "UPDATE ip_rate_limits SET attempt_count = ?, last_attempt = NOW() WHERE ip_address = ?");
                mysqli_stmt_bind_param($updateStmt, "is", $attemptCount, $ipAddress);
            }
            
            mysqli_stmt_execute($updateStmt);
            mysqli_stmt_close($updateStmt);
        } else {
            // Create new record
            mysqli_stmt_close($stmt);
            $insertStmt = mysqli_prepare(self::$connection,
                "INSERT INTO ip_rate_limits (ip_address, attempt_count) VALUES (?, 1)");
            mysqli_stmt_bind_param($insertStmt, "s", $ipAddress);
            mysqli_stmt_execute($insertStmt);
            mysqli_stmt_close($insertStmt);
        }
    }
    
    /**
     * Get client IP address
     */
    public static function getClientIP() {
        $ipKeys = ['HTTP_CF_CONNECTING_IP', 'HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'];
        
        foreach ($ipKeys as $key) {
            if (!empty($_SERVER[$key])) {
                $ip = $_SERVER[$key];
                if (strpos($ip, ',') !== false) {
                    $ip = trim(explode(',', $ip)[0]);
                }
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    return $ip;
                }
            }
        }
        
        return $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    }
    
    /**
     * Get lockout information for display
     */
    public static function getLockoutInfo($username) {
        $stmt = mysqli_prepare(self::$connection,
            "SELECT locked_until, failed_attempts FROM account_lockouts WHERE username = ? AND locked_until > NOW()");
        mysqli_stmt_bind_param($stmt, "s", $username);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if ($row = mysqli_fetch_assoc($result)) {
            mysqli_stmt_close($stmt);
            return [
                'locked_until' => $row['locked_until'],
                'failed_attempts' => $row['failed_attempts'],
                'remaining_time' => strtotime($row['locked_until']) - time()
            ];
        }
        
        mysqli_stmt_close($stmt);
        return null;
    }
    
    /**
     * Generate simple CAPTCHA
     */
    public static function generateCaptcha() {
        $num1 = rand(1, 10);
        $num2 = rand(1, 10);
        $answer = $num1 + $num2;
        
        $_SESSION['captcha_answer'] = $answer;
        
        return [
            'question' => "$num1 + $num2 = ?",
            'num1' => $num1,
            'num2' => $num2
        ];
    }
    
    /**
     * Verify CAPTCHA answer
     */
    public static function verifyCaptcha($userAnswer) {
        if (!isset($_SESSION['captcha_answer'])) {
            return false;
        }
        
        $correct = $_SESSION['captcha_answer'] == intval($userAnswer);
        unset($_SESSION['captcha_answer']); // Clear after use
        
        return $correct;
    }
    
    /**
     * Clean up old records (should be called periodically)
     */
    public static function cleanup() {
        // Remove old login attempts (older than 24 hours)
        $stmt1 = mysqli_prepare(self::$connection,
            "DELETE FROM login_attempts WHERE attempt_time < DATE_SUB(NOW(), INTERVAL 24 HOUR)");
        mysqli_stmt_execute($stmt1);
        mysqli_stmt_close($stmt1);
        
        // Remove expired lockouts
        $stmt2 = mysqli_prepare(self::$connection,
            "DELETE FROM account_lockouts WHERE locked_until < NOW()");
        mysqli_stmt_execute($stmt2);
        mysqli_stmt_close($stmt2);
        
        // Remove old IP rate limits
        $stmt3 = mysqli_prepare(self::$connection,
            "DELETE FROM ip_rate_limits WHERE first_attempt < DATE_SUB(NOW(), INTERVAL 24 HOUR) AND blocked_until IS NULL");
        mysqli_stmt_execute($stmt3);
        mysqli_stmt_close($stmt3);
    }
    
    /**
     * Get security statistics for admin dashboard
     */
    public static function getSecurityStats() {
        $stats = [];
        
        // Failed attempts in last 24 hours
        $stmt1 = mysqli_prepare(self::$connection,
            "SELECT COUNT(*) as count FROM login_attempts WHERE success = 0 AND attempt_time > DATE_SUB(NOW(), INTERVAL 24 HOUR)");
        mysqli_stmt_execute($stmt1);
        $result1 = mysqli_stmt_get_result($stmt1);
        $stats['failed_attempts_24h'] = mysqli_fetch_assoc($result1)['count'];
        mysqli_stmt_close($stmt1);
        
        // Currently locked accounts
        $stmt2 = mysqli_prepare(self::$connection,
            "SELECT COUNT(*) as count FROM account_lockouts WHERE locked_until > NOW()");
        mysqli_stmt_execute($stmt2);
        $result2 = mysqli_stmt_get_result($stmt2);
        $stats['locked_accounts'] = mysqli_fetch_assoc($result2)['count'];
        mysqli_stmt_close($stmt2);
        
        // Blocked IPs
        $stmt3 = mysqli_prepare(self::$connection,
            "SELECT COUNT(*) as count FROM ip_rate_limits WHERE blocked_until > NOW()");
        mysqli_stmt_execute($stmt3);
        $result3 = mysqli_stmt_get_result($stmt3);
        $stats['blocked_ips'] = mysqli_fetch_assoc($result3)['count'];
        mysqli_stmt_close($stmt3);
        
        return $stats;
    }
}
?>