<?php
/**
 * SessionSecurity Class
 * 
 * Provides comprehensive session security management including:
 * - Secure session configuration
 * - Session timeout management
 * - Session regeneration
 * - Proper session destruction
 * 
 * Requirements: 7.1, 7.2, 7.3, 7.4
 */
class SessionSecurity {
    
    // Session timeout in seconds (30 minutes)
    const SESSION_TIMEOUT = 1800; // 30 minutes * 60 seconds
    
    // Admin session timeout in seconds (15 minutes - shorter for admin)
    const ADMIN_SESSION_TIMEOUT = 900; // 15 minutes * 60 seconds
    
    /**
     * Initialize secure session configuration
     * Requirement 7.2: Add secure session configuration (httponly, secure flags)
     */
    public static function initializeSecureSession() {
        // Only start session if not already started
        if (session_status() === PHP_SESSION_NONE) {
            // Set secure session configuration before starting session
            ini_set('session.cookie_httponly', 1);
            ini_set('session.cookie_secure', self::isHTTPS());
            ini_set('session.cookie_samesite', 'Strict');
            ini_set('session.use_strict_mode', 1);
            ini_set('session.cookie_lifetime', 0); // Session cookies only
            
            // Set session name for better security
            session_name('VOTING_SESSION');
            
            // Start the session
            session_start();
            
            // Set initial session security data if not exists
            if (!isset($_SESSION['session_started'])) {
                $_SESSION['session_started'] = time();
                $_SESSION['last_activity'] = time();
                $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'] ?? '';
                $_SESSION['ip_address'] = self::getUserIP();
            }
        }
    }
    
    /**
     * Check if connection is HTTPS
     */
    private static function isHTTPS() {
        return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') 
            || $_SERVER['SERVER_PORT'] == 443
            || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');
    }
    
    /**
     * Get user's real IP address
     */
    private static function getUserIP() {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            return $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            return $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            return $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        }
    }
    
    /**
     * Validate session security and check for timeout
     * Requirement 7.1: Implement session timeout after 30 minutes of inactivity
     */
    public static function validateSession($isAdmin = false) {
        self::initializeSecureSession();
        
        // Check if session variables exist
        if (!isset($_SESSION['last_activity']) || !isset($_SESSION['session_started'])) {
            self::destroySession();
            return false;
        }
        
        // Determine timeout based on user type
        $timeout = $isAdmin ? self::ADMIN_SESSION_TIMEOUT : self::SESSION_TIMEOUT;
        
        // Check for session timeout
        if (time() - $_SESSION['last_activity'] > $timeout) {
            self::destroySession();
            return false;
        }
        
        // Check for session hijacking attempts
        if (!self::validateSessionIntegrity()) {
            self::destroySession();
            return false;
        }
        
        // Update last activity time
        $_SESSION['last_activity'] = time();
        
        // Regenerate session ID periodically (every 5 minutes)
        if (!isset($_SESSION['last_regeneration']) || 
            (time() - $_SESSION['last_regeneration']) > 300) {
            self::regenerateSessionId();
        }
        
        return true;
    }
    
    /**
     * Validate session integrity to prevent hijacking
     */
    private static function validateSessionIntegrity() {
        // Check user agent consistency
        if (isset($_SESSION['user_agent']) && 
            $_SESSION['user_agent'] !== ($_SERVER['HTTP_USER_AGENT'] ?? '')) {
            return false;
        }
        
        // Check IP address consistency (optional - can be disabled for mobile users)
        if (isset($_SESSION['ip_address']) && 
            $_SESSION['ip_address'] !== self::getUserIP()) {
            // Log potential session hijacking attempt
            error_log("Potential session hijacking detected. Session IP: " . 
                     $_SESSION['ip_address'] . ", Current IP: " . self::getUserIP());
            // For now, we'll allow IP changes (mobile users, proxy changes)
            // return false;
        }
        
        return true;
    }
    
    /**
     * Regenerate session ID for security
     * Requirement 7.3: Create session regeneration on login
     */
    public static function regenerateSessionId() {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_regenerate_id(true);
            $_SESSION['last_regeneration'] = time();
        }
    }
    
    /**
     * Start authenticated session after successful login
     * Requirement 7.3: Create session regeneration on login
     */
    public static function startAuthenticatedSession($username, $rank = 'voter') {
        self::initializeSecureSession();
        
        // Regenerate session ID on login for security
        self::regenerateSessionId();
        
        // Set session data
        $_SESSION['SESS_NAME'] = $username;
        $_SESSION['SESS_RANK'] = $rank;
        $_SESSION['login_time'] = time();
        $_SESSION['last_activity'] = time();
        $_SESSION['session_started'] = time();
        $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $_SESSION['ip_address'] = self::getUserIP();
        $_SESSION['last_regeneration'] = time();
        
        // Log successful login
        self::logSecurityEvent('login_success', $username);
    }
    
    /**
     * Start admin session after successful admin login
     */
    public static function startAdminSession($adminName) {
        self::initializeSecureSession();
        
        // Regenerate session ID on admin login
        self::regenerateSessionId();
        
        // Set admin session data
        $_SESSION['ADMIN_LOGGED_IN'] = true;
        $_SESSION['ADMIN_NAME'] = $adminName;
        $_SESSION['admin_login_time'] = time();
        $_SESSION['last_activity'] = time();
        $_SESSION['session_started'] = time();
        $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $_SESSION['ip_address'] = self::getUserIP();
        $_SESSION['last_regeneration'] = time();
        
        // Log successful admin login
        self::logSecurityEvent('admin_login_success', $adminName);
    }
    
    /**
     * Properly destroy session on logout
     * Requirement 7.4: Implement proper session destruction on logout
     */
    public static function destroySession() {
        if (session_status() === PHP_SESSION_ACTIVE) {
            // Log logout event if user was logged in
            if (isset($_SESSION['SESS_NAME'])) {
                self::logSecurityEvent('logout', $_SESSION['SESS_NAME']);
            } elseif (isset($_SESSION['ADMIN_NAME'])) {
                self::logSecurityEvent('admin_logout', $_SESSION['ADMIN_NAME']);
            }
            
            // Clear all session variables
            $_SESSION = array();
            
            // Delete the session cookie
            if (ini_get("session.use_cookies")) {
                $params = session_get_cookie_params();
                setcookie(session_name(), '', time() - 42000,
                    $params["path"], $params["domain"],
                    $params["secure"], $params["httponly"]
                );
            }
            
            // Destroy the session
            session_destroy();
        }
    }
    
    /**
     * Check if user is logged in
     */
    public static function isLoggedIn() {
        return self::validateSession() && isset($_SESSION['SESS_NAME']);
    }
    
    /**
     * Check if admin is logged in
     */
    public static function isAdminLoggedIn() {
        return self::validateSession(true) && 
               isset($_SESSION['ADMIN_LOGGED_IN']) && 
               $_SESSION['ADMIN_LOGGED_IN'] === true;
    }
    
    /**
     * Get session timeout remaining in seconds
     */
    public static function getTimeoutRemaining($isAdmin = false) {
        if (!isset($_SESSION['last_activity'])) {
            return 0;
        }
        
        $timeout = $isAdmin ? self::ADMIN_SESSION_TIMEOUT : self::SESSION_TIMEOUT;
        $elapsed = time() - $_SESSION['last_activity'];
        $remaining = $timeout - $elapsed;
        
        return max(0, $remaining);
    }
    
    /**
     * Get session information for debugging/monitoring
     */
    public static function getSessionInfo() {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            return null;
        }
        
        return [
            'session_id' => session_id(),
            'session_started' => $_SESSION['session_started'] ?? null,
            'last_activity' => $_SESSION['last_activity'] ?? null,
            'login_time' => $_SESSION['login_time'] ?? $_SESSION['admin_login_time'] ?? null,
            'user_agent' => $_SESSION['user_agent'] ?? null,
            'ip_address' => $_SESSION['ip_address'] ?? null,
            'timeout_remaining' => self::getTimeoutRemaining(isset($_SESSION['ADMIN_LOGGED_IN'])),
            'is_admin' => isset($_SESSION['ADMIN_LOGGED_IN']) && $_SESSION['ADMIN_LOGGED_IN'] === true
        ];
    }
    
    /**
     * Log security events
     */
    private static function logSecurityEvent($event, $username = '') {
        $timestamp = date('Y-m-d H:i:s');
        $ip = self::getUserIP();
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
        
        $logEntry = "[$timestamp] $event - User: $username - IP: $ip - UserAgent: $userAgent\n";
        
        // Create logs directory if it doesn't exist
        $logDir = dirname(__FILE__) . '/../logs';
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        
        // Write to security log
        file_put_contents($logDir . '/security.log', $logEntry, FILE_APPEND | LOCK_EX);
    }
    
    /**
     * Clean expired sessions (can be called by cron job)
     */
    public static function cleanExpiredSessions() {
        // This would typically clean up session files on disk
        // For now, we'll just log the cleanup attempt
        self::logSecurityEvent('session_cleanup', 'system');
    }
}
?>