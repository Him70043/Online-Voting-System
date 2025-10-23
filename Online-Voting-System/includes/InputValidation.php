<?php
/**
 * Enhanced Input Validation and Form Security Class
 * Implements comprehensive server-side validation, honeypot detection, and form security
 * 
 * Requirements: 6.1, 6.2, 6.3, 6.4
 * - Add server-side validation for all form fields
 * - Implement file upload security (if needed)
 * - Create input length and format validation
 * - Add honeypot fields to detect bots
 */

class InputValidation {
    
    // Validation rules and patterns
    private static $validationRules = [
        'username' => [
            'min_length' => 3,
            'max_length' => 50,
            'pattern' => '/^[a-zA-Z0-9_-]+$/',
            'required' => true
        ],
        'password' => [
            'min_length' => 8,
            'max_length' => 128,
            'required' => true
        ],
        'firstname' => [
            'min_length' => 2,
            'max_length' => 50,
            'pattern' => '/^[a-zA-Z\s\'-]+$/',
            'required' => true
        ],
        'lastname' => [
            'min_length' => 2,
            'max_length' => 50,
            'pattern' => '/^[a-zA-Z\s\'-]+$/',
            'required' => true
        ],
        'email' => [
            'max_length' => 255,
            'pattern' => '/^[^\s@]+@[^\s@]+\.[^\s@]+$/',
            'required' => false
        ]
    ];
    
    // Honeypot field names (should be hidden and empty)
    private static $honeypotFields = ['website', 'url', 'homepage', 'email_confirm'];
    
    /**
     * Validate all form inputs
     */
    public static function validateForm($formData, $formType = 'general') {
        $errors = [];
        $cleanData = [];
        
        // Check for honeypot fields first
        $honeypotDetected = self::checkHoneypot($formData);
        if ($honeypotDetected) {
            return [
                'valid' => false,
                'errors' => ['Bot activity detected. Please try again.'],
                'data' => []
            ];
        }
        
        // Validate each field based on form type
        switch ($formType) {
            case 'registration':
                $requiredFields = ['firstname', 'lastname', 'username', 'password'];
                break;
            case 'login':
                $requiredFields = ['username', 'password'];
                break;
            case 'voting':
                return self::validateVotingForm($formData);
            case 'admin_login':
                $requiredFields = ['username', 'password'];
                break;
            default:
                $requiredFields = array_keys($formData);
        }
        
        foreach ($requiredFields as $field) {
            $value = isset($formData[$field]) ? $formData[$field] : '';
            $validation = self::validateField($field, $value);
            
            if (!$validation['valid']) {
                $errors = array_merge($errors, $validation['errors']);
            } else {
                $cleanData[$field] = $validation['clean_value'];
            }
        }
        
        // Additional form-specific validations
        if ($formType === 'registration') {
            $passwordValidation = self::validatePasswordComplexity($formData['password'] ?? '');
            if (!$passwordValidation['valid']) {
                $errors = array_merge($errors, $passwordValidation['errors']);
            }
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'data' => $cleanData
        ];
    }
    
    /**
     * Validate individual field
     */
    public static function validateField($fieldName, $value) {
        $errors = [];
        $cleanValue = self::sanitizeInput($value);
        
        // Get validation rules for this field
        $rules = self::$validationRules[$fieldName] ?? [];
        
        // Check if field is required
        if (isset($rules['required']) && $rules['required'] && empty($cleanValue)) {
            return [
                'valid' => false,
                'errors' => [ucfirst($fieldName) . ' is required.'],
                'clean_value' => $cleanValue
            ];
        }
        
        // Skip further validation if field is empty and not required
        if (empty($cleanValue) && (!isset($rules['required']) || !$rules['required'])) {
            return [
                'valid' => true,
                'errors' => [],
                'clean_value' => $cleanValue
            ];
        }
        
        // Length validation
        if (isset($rules['min_length']) && strlen($cleanValue) < $rules['min_length']) {
            $errors[] = ucfirst($fieldName) . ' must be at least ' . $rules['min_length'] . ' characters long.';
        }
        
        if (isset($rules['max_length']) && strlen($cleanValue) > $rules['max_length']) {
            $errors[] = ucfirst($fieldName) . ' must not exceed ' . $rules['max_length'] . ' characters.';
        }
        
        // Pattern validation
        if (isset($rules['pattern']) && !preg_match($rules['pattern'], $cleanValue)) {
            $errors[] = self::getPatternErrorMessage($fieldName);
        }
        
        // Additional security checks
        if (self::containsSuspiciousContent($cleanValue)) {
            $errors[] = ucfirst($fieldName) . ' contains invalid characters or suspicious content.';
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'clean_value' => $cleanValue
        ];
    }
    
    /**
     * Validate voting form specifically
     */
    public static function validateVotingForm($formData) {
        $errors = [];
        $cleanData = [];
        
        // Check honeypot
        if (self::checkHoneypot($formData)) {
            return [
                'valid' => false,
                'errors' => ['Bot activity detected. Please try again.'],
                'data' => []
            ];
        }
        
        // At least one vote must be selected
        $languageVote = isset($formData['lan']) ? trim($formData['lan']) : '';
        $teamVote = isset($formData['team']) ? trim($formData['team']) : '';
        
        if (empty($languageVote) && empty($teamVote)) {
            $errors[] = 'Please select at least one option to vote.';
        }
        
        // Validate language vote if provided
        if (!empty($languageVote)) {
            $validLanguages = ['JAVA', 'PYTHON', 'C++', 'PHP', '.NET'];
            if (!in_array($languageVote, $validLanguages)) {
                $errors[] = 'Invalid programming language selection.';
            } else {
                $cleanData['lan'] = $languageVote;
            }
        }
        
        // Validate team vote if provided
        if (!empty($teamVote)) {
            $validTeamMembers = ['Himanshu', 'Prafful', 'Shoaib', 'Ansh'];
            if (!in_array($teamVote, $validTeamMembers)) {
                $errors[] = 'Invalid team member selection.';
            } else {
                $cleanData['team'] = $teamVote;
            }
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'data' => $cleanData
        ];
    }
    
    /**
     * Check for honeypot field activity
     */
    public static function checkHoneypot($formData) {
        foreach (self::$honeypotFields as $honeypotField) {
            if (isset($formData[$honeypotField]) && !empty($formData[$honeypotField])) {
                // Log potential bot activity
                error_log("Honeypot triggered: Field '$honeypotField' filled with value: " . $formData[$honeypotField]);
                return true;
            }
        }
        return false;
    }
    
    /**
     * Generate honeypot fields HTML
     */
    public static function generateHoneypotFields() {
        $html = '';
        $selectedFields = array_slice(self::$honeypotFields, 0, 2); // Use 2 random honeypot fields
        
        foreach ($selectedFields as $field) {
            $html .= '<div style="position: absolute; left: -9999px; opacity: 0; pointer-events: none;" aria-hidden="true">';
            $html .= '<label for="' . $field . '">Leave this field empty</label>';
            $html .= '<input type="text" name="' . $field . '" id="' . $field . '" value="" tabindex="-1" autocomplete="off">';
            $html .= '</div>';
        }
        
        return $html;
    }
    
    /**
     * Sanitize input data
     */
    public static function sanitizeInput($input) {
        if (is_array($input)) {
            return array_map([self::class, 'sanitizeInput'], $input);
        }
        
        // Remove null bytes and control characters
        $input = str_replace(chr(0), '', $input);
        $input = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $input);
        
        // Trim whitespace
        $input = trim($input);
        
        return $input;
    }
    
    /**
     * Check for suspicious content
     */
    private static function containsSuspiciousContent($input) {
        // Check for common injection patterns
        $suspiciousPatterns = [
            '/(<script|<\/script|javascript:|vbscript:|onload=|onerror=)/i',
            '/(union\s+select|drop\s+table|delete\s+from|insert\s+into)/i',
            '/(\.\.\/)/',
            '/(eval\s*\(|exec\s*\(|system\s*\()/i'
        ];
        
        foreach ($suspiciousPatterns as $pattern) {
            if (preg_match($pattern, $input)) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Validate password complexity
     */
    public static function validatePasswordComplexity($password) {
        $errors = [];
        
        if (strlen($password) < 8) {
            $errors[] = 'Password must be at least 8 characters long.';
        }
        
        if (!preg_match('/[A-Z]/', $password)) {
            $errors[] = 'Password must contain at least one uppercase letter.';
        }
        
        if (!preg_match('/[a-z]/', $password)) {
            $errors[] = 'Password must contain at least one lowercase letter.';
        }
        
        if (!preg_match('/[0-9]/', $password)) {
            $errors[] = 'Password must contain at least one number.';
        }
        
        if (!preg_match('/[!@#$%^&*()_+\-=\[\]{};\':"\\|,.<>\/?]/', $password)) {
            $errors[] = 'Password must contain at least one special character.';
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }
    
    /**
     * Validate file upload
     */
    public static function validateFileUpload($file, $allowedTypes = [], $maxSize = 5242880) { // 5MB default
        $errors = [];
        
        if (!isset($file['error']) || is_array($file['error'])) {
            $errors[] = 'Invalid file upload.';
            return ['valid' => false, 'errors' => $errors];
        }
        
        // Check upload errors
        switch ($file['error']) {
            case UPLOAD_ERR_OK:
                break;
            case UPLOAD_ERR_NO_FILE:
                $errors[] = 'No file was uploaded.';
                break;
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                $errors[] = 'File size exceeds the maximum allowed size.';
                break;
            default:
                $errors[] = 'Unknown file upload error.';
                break;
        }
        
        // Check file size
        if ($file['size'] > $maxSize) {
            $errors[] = 'File size exceeds the maximum allowed size of ' . ($maxSize / 1024 / 1024) . 'MB.';
        }
        
        // Check file type
        if (!empty($allowedTypes)) {
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mimeType = $finfo->file($file['tmp_name']);
            
            if (!in_array($mimeType, $allowedTypes)) {
                $errors[] = 'File type not allowed. Allowed types: ' . implode(', ', $allowedTypes);
            }
        }
        
        // Check for malicious content
        if (self::containsMaliciousContent($file['tmp_name'])) {
            $errors[] = 'File contains potentially malicious content.';
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }
    
    /**
     * Check for malicious content in uploaded files
     */
    private static function containsMaliciousContent($filePath) {
        $content = file_get_contents($filePath, false, null, 0, 1024); // Read first 1KB
        
        $maliciousPatterns = [
            '/<\?php/i',
            '/<script/i',
            '/eval\s*\(/i',
            '/exec\s*\(/i',
            '/system\s*\(/i',
            '/shell_exec/i'
        ];
        
        foreach ($maliciousPatterns as $pattern) {
            if (preg_match($pattern, $content)) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Get user-friendly error message for pattern validation
     */
    private static function getPatternErrorMessage($fieldName) {
        switch ($fieldName) {
            case 'username':
                return 'Username can only contain letters, numbers, underscores, and hyphens.';
            case 'firstname':
            case 'lastname':
                return ucfirst($fieldName) . ' can only contain letters, spaces, apostrophes, and hyphens.';
            case 'email':
                return 'Please enter a valid email address.';
            default:
                return ucfirst($fieldName) . ' contains invalid characters.';
        }
    }
    
    /**
     * Rate limiting for form submissions
     */
    public static function checkSubmissionRate($identifier, $maxAttempts = 5, $timeWindow = 300) { // 5 attempts per 5 minutes
        $cacheFile = sys_get_temp_dir() . '/form_rate_' . md5($identifier);
        
        if (file_exists($cacheFile)) {
            $data = json_decode(file_get_contents($cacheFile), true);
            $currentTime = time();
            
            // Clean old attempts
            $data['attempts'] = array_filter($data['attempts'], function($timestamp) use ($currentTime, $timeWindow) {
                return ($currentTime - $timestamp) < $timeWindow;
            });
            
            if (count($data['attempts']) >= $maxAttempts) {
                return false; // Rate limit exceeded
            }
        } else {
            $data = ['attempts' => []];
        }
        
        // Record this attempt
        $data['attempts'][] = time();
        file_put_contents($cacheFile, json_encode($data));
        
        return true;
    }
}