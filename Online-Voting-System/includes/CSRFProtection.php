<?php
/**
 * CSRF Protection Utility Class
 * Provides token generation and validation for form security
 * 
 * @author Himanshu Kumar
 * @version 1.0
 */

class CSRFProtection {
    
    /**
     * Generate a secure CSRF token
     * @return string The generated token
     */
    public static function generateToken() {
        if (!isset($_SESSION)) {
            session_start();
        }
        
        // Generate a cryptographically secure random token
        $token = bin2hex(random_bytes(32));
        
        // Store token in session
        $_SESSION['csrf_token'] = $token;
        $_SESSION['csrf_token_time'] = time();
        
        return $token;
    }
    
    /**
     * Validate CSRF token from form submission
     * @param string $token The token to validate
     * @return bool True if valid, false otherwise
     */
    public static function validateToken($token) {
        if (!isset($_SESSION)) {
            session_start();
        }
        
        // Check if token exists in session
        if (!isset($_SESSION['csrf_token']) || !isset($_SESSION['csrf_token_time'])) {
            return false;
        }
        
        // Check token expiration (30 minutes)
        if (time() - $_SESSION['csrf_token_time'] > 1800) {
            self::clearToken();
            return false;
        }
        
        // Validate token using hash_equals to prevent timing attacks
        $isValid = hash_equals($_SESSION['csrf_token'], $token);
        
        // Clear token after validation (one-time use)
        if ($isValid) {
            self::clearToken();
        }
        
        return $isValid;
    }
    
    /**
     * Generate HTML input field for CSRF token
     * @return string HTML input field
     */
    public static function getTokenField() {
        $token = self::generateToken();
        return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
    }
    
    /**
     * Clear CSRF token from session
     */
    public static function clearToken() {
        if (isset($_SESSION['csrf_token'])) {
            unset($_SESSION['csrf_token']);
        }
        if (isset($_SESSION['csrf_token_time'])) {
            unset($_SESSION['csrf_token_time']);
        }
    }
    
    /**
     * Handle CSRF validation failure
     * @param string $redirectUrl URL to redirect to on failure
     */
    public static function handleValidationFailure($redirectUrl = 'index.php') {
        // Log security event
        error_log("CSRF Token Validation Failed - IP: " . $_SERVER['REMOTE_ADDR'] . " - Time: " . date('Y-m-d H:i:s'));
        
        // Set error message
        if (!isset($_SESSION)) {
            session_start();
        }
        $_SESSION['security_error'] = 'Security token validation failed. Please try again.';
        
        // Redirect to safe page
        header("Location: " . $redirectUrl);
        exit();
    }
    
    /**
     * Verify request method and CSRF token
     * @param string $expectedMethod Expected HTTP method (POST, GET, etc.)
     * @param string $redirectUrl URL to redirect to on failure
     * @return bool True if validation passes
     */
    public static function verifyRequest($expectedMethod = 'POST', $redirectUrl = 'index.php') {
        // Check request method
        if ($_SERVER['REQUEST_METHOD'] !== $expectedMethod) {
            self::handleValidationFailure($redirectUrl);
            return false;
        }
        
        // For POST requests, validate CSRF token
        if ($expectedMethod === 'POST') {
            if (!isset($_POST['csrf_token']) || !self::validateToken($_POST['csrf_token'])) {
                self::handleValidationFailure($redirectUrl);
                return false;
            }
        }
        
        return true;
    }
}
?>