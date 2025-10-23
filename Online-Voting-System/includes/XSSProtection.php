<?php
/**
 * XSS Protection Utility Class
 * Provides comprehensive XSS prevention and output escaping functions
 * 
 * @author Himanshu Kumar
 * @version 1.0
 */

class XSSProtection {
    
    /**
     * Escape HTML output to prevent XSS attacks
     * 
     * @param string $data The data to escape
     * @param int $flags Optional flags for htmlspecialchars
     * @param string $encoding Character encoding
     * @return string Escaped data safe for HTML output
     */
    public static function escapeHtml($data, $flags = ENT_QUOTES | ENT_HTML5, $encoding = 'UTF-8') {
        if (is_null($data)) {
            return '';
        }
        
        if (is_array($data)) {
            return array_map([self::class, 'escapeHtml'], $data);
        }
        
        return htmlspecialchars((string)$data, $flags, $encoding);
    }
    
    /**
     * Escape data for use in HTML attributes
     * 
     * @param string $data The data to escape
     * @return string Escaped data safe for HTML attributes
     */
    public static function escapeAttribute($data) {
        if (is_null($data)) {
            return '';
        }
        
        return htmlspecialchars((string)$data, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }
    
    /**
     * Escape data for use in JavaScript contexts
     * 
     * @param string $data The data to escape
     * @return string Escaped data safe for JavaScript
     */
    public static function escapeJs($data) {
        if (is_null($data)) {
            return '';
        }
        
        return json_encode((string)$data, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
    }
    
    /**
     * Escape data for use in URLs
     * 
     * @param string $data The data to escape
     * @return string URL-encoded data
     */
    public static function escapeUrl($data) {
        if (is_null($data)) {
            return '';
        }
        
        return urlencode((string)$data);
    }
    
    /**
     * Clean and sanitize user input
     * 
     * @param string $data The input data to clean
     * @return string Cleaned data
     */
    public static function cleanInput($data) {
        if (is_null($data)) {
            return '';
        }
        
        // Remove null bytes
        $data = str_replace(chr(0), '', $data);
        
        // Trim whitespace
        $data = trim($data);
        
        // Remove potential script tags and dangerous content
        $data = preg_replace('/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/mi', '', $data);
        $data = preg_replace('/<iframe\b[^<]*(?:(?!<\/iframe>)<[^<]*)*<\/iframe>/mi', '', $data);
        $data = preg_replace('/javascript:/i', '', $data);
        $data = preg_replace('/vbscript:/i', '', $data);
        $data = preg_replace('/onload/i', '', $data);
        $data = preg_replace('/onerror/i', '', $data);
        $data = preg_replace('/onclick/i', '', $data);
        
        return $data;
    }
    
    /**
     * Validate and sanitize integer input
     * 
     * @param mixed $data The input to validate
     * @return int Sanitized integer value
     */
    public static function sanitizeInt($data) {
        return (int) filter_var($data, FILTER_SANITIZE_NUMBER_INT);
    }
    
    /**
     * Validate and sanitize email input
     * 
     * @param string $email The email to validate
     * @return string|false Sanitized email or false if invalid
     */
    public static function sanitizeEmail($email) {
        return filter_var($email, FILTER_SANITIZE_EMAIL);
    }
    
    /**
     * Generate Content Security Policy header
     * 
     * @return string CSP header value
     */
    public static function getCSPHeader() {
        return "default-src 'self'; " .
               "script-src 'self' 'unsafe-inline' https://ajax.googleapis.com https://fonts.googleapis.com; " .
               "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; " .
               "font-src 'self' https://fonts.gstatic.com; " .
               "img-src 'self' data: https:; " .
               "connect-src 'self'; " .
               "frame-ancestors 'none'; " .
               "base-uri 'self'; " .
               "form-action 'self';";
    }
    
    /**
     * Set security headers to prevent XSS and other attacks
     */
    public static function setSecurityHeaders() {
        // Content Security Policy
        header("Content-Security-Policy: " . self::getCSPHeader());
        
        // X-Frame-Options to prevent clickjacking
        header("X-Frame-Options: DENY");
        
        // X-XSS-Protection
        header("X-XSS-Protection: 1; mode=block");
        
        // X-Content-Type-Options
        header("X-Content-Type-Options: nosniff");
        
        // Referrer Policy
        header("Referrer-Policy: strict-origin-when-cross-origin");
        
        // Strict Transport Security (if using HTTPS)
        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
            header("Strict-Transport-Security: max-age=31536000; includeSubDomains");
        }
    }
    
    /**
     * Helper function for safe output in templates
     * Alias for escapeHtml for convenience
     * 
     * @param string $data The data to escape
     * @return string Escaped data
     */
    public static function e($data) {
        return self::escapeHtml($data);
    }
}

// Convenience function for global use
if (!function_exists('xss_escape')) {
    function xss_escape($data) {
        return XSSProtection::escapeHtml($data);
    }
}

if (!function_exists('xss_attr')) {
    function xss_attr($data) {
        return XSSProtection::escapeAttribute($data);
    }
}

if (!function_exists('xss_js')) {
    function xss_js($data) {
        return XSSProtection::escapeJs($data);
    }
}
?>