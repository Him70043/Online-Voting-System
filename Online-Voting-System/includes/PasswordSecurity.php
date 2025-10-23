<?php
/**
 * Password Security Class
 * Handles secure password hashing, verification, and validation
 */
class PasswordSecurity {
    
    // Password complexity requirements
    const MIN_LENGTH = 8;
    const REQUIRE_UPPERCASE = true;
    const REQUIRE_LOWERCASE = true;
    const REQUIRE_NUMBERS = true;
    const REQUIRE_SPECIAL_CHARS = true;
    
    /**
     * Hash a password using bcrypt
     * @param string $password The plain text password
     * @return string The hashed password
     */
    public static function hashPassword($password) {
        return password_hash($password, PASSWORD_DEFAULT);
    }
    
    /**
     * Verify a password against its hash
     * @param string $password The plain text password
     * @param string $hash The stored hash
     * @return bool True if password matches, false otherwise
     */
    public static function verifyPassword($password, $hash) {
        return password_verify($password, $hash);
    }
    
    /**
     * Check if password needs rehashing (for security updates)
     * @param string $hash The stored hash
     * @return bool True if rehashing is needed
     */
    public static function needsRehash($hash) {
        return password_needs_rehash($hash, PASSWORD_DEFAULT);
    }
    
    /**
     * Validate password complexity requirements
     * @param string $password The password to validate
     * @return array Array with 'valid' boolean and 'errors' array
     */
    public static function validatePasswordComplexity($password) {
        $errors = [];
        
        // Check minimum length
        if (strlen($password) < self::MIN_LENGTH) {
            $errors[] = "Password must be at least " . self::MIN_LENGTH . " characters long";
        }
        
        // Check for uppercase letters
        if (self::REQUIRE_UPPERCASE && !preg_match('/[A-Z]/', $password)) {
            $errors[] = "Password must contain at least one uppercase letter";
        }
        
        // Check for lowercase letters
        if (self::REQUIRE_LOWERCASE && !preg_match('/[a-z]/', $password)) {
            $errors[] = "Password must contain at least one lowercase letter";
        }
        
        // Check for numbers
        if (self::REQUIRE_NUMBERS && !preg_match('/[0-9]/', $password)) {
            $errors[] = "Password must contain at least one number";
        }
        
        // Check for special characters
        if (self::REQUIRE_SPECIAL_CHARS && !preg_match('/[^A-Za-z0-9]/', $password)) {
            $errors[] = "Password must contain at least one special character";
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }
    
    /**
     * Generate a secure random password reset token
     * @return string The reset token
     */
    public static function generateResetToken() {
        return bin2hex(random_bytes(32));
    }
    
    /**
     * Check if a password is using old MD5 hashing
     * @param string $hash The hash to check
     * @return bool True if it's MD5, false otherwise
     */
    public static function isMD5Hash($hash) {
        return strlen($hash) === 32 && ctype_xdigit($hash);
    }
}
?>