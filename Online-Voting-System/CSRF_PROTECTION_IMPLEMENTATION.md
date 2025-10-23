# CSRF Protection Implementation Guide

## Overview

This document describes the comprehensive Cross-Site Request Forgery (CSRF) protection implementation for the Online Voting System. CSRF protection has been added to all forms and administrative actions to prevent unauthorized requests.

## Implementation Details

### 1. Core CSRF Protection Class

**File:** `includes/CSRFProtection.php`

The `CSRFProtection` class provides:
- Secure token generation using `random_bytes(32)`
- Token validation with timing attack protection (`hash_equals`)
- Session-based token storage with expiration (30 minutes)
- One-time token usage (tokens are cleared after validation)
- Comprehensive error handling and logging

### 2. Protected Forms

#### Voting Forms
- **File:** `voter.php`
- **Protection:** CSRF token added to voting form
- **Validation:** `submit_vote.php` validates token before processing votes

#### Authentication Forms
- **File:** `login.php`
- **Protection:** CSRF token added to login form
- **Validation:** `login_action.php` validates token before authentication

- **File:** `register.php`
- **Protection:** CSRF token added to registration form
- **Validation:** `reg_action.php` validates token before user creation

#### Admin Forms
- **File:** `admin_login.php`
- **Protection:** CSRF token added to admin login form
- **Validation:** Token validated before admin authentication

- **File:** `admin_database.php`
- **Protection:** CSRF token added to SQL query form
- **Validation:** Token validated before query execution

#### Password Reset Forms
- **File:** `password_reset_request.php`
- **Protection:** CSRF token added to reset request form
- **Validation:** `password_reset_action.php` validates token

- **File:** `password_reset_form.php`
- **Protection:** CSRF token added to password reset form
- **Validation:** `password_reset_process.php` validates token

### 3. Protected Admin Actions

#### Administrative Operations
- **File:** `admin_actions.php`
- **Protection:** All GET-based admin actions require CSRF tokens
- **Actions Protected:**
  - User deletion
  - Vote count resets
  - Data export
  - System maintenance operations

#### Dashboard Integration
- **File:** `admin_dashboard.php`
- **Protection:** JavaScript functions updated to include CSRF tokens in action URLs
- **Implementation:** Token passed via URL parameters for GET requests

## Security Features

### 1. Token Generation
```php
// Cryptographically secure random token
$token = bin2hex(random_bytes(32));
```

### 2. Timing Attack Protection
```php
// Use hash_equals to prevent timing attacks
$isValid = hash_equals($_SESSION['csrf_token'], $token);
```

### 3. Token Expiration
- Tokens expire after 30 minutes (1800 seconds)
- Expired tokens are automatically cleared from session

### 4. One-Time Usage
- Tokens are cleared after successful validation
- Prevents token reuse attacks

### 5. Request Method Validation
- POST requests require CSRF token validation
- GET requests for admin actions also protected

## Usage Examples

### Adding CSRF Protection to Forms

```php
// Include the CSRF protection class
include "includes/CSRFProtection.php";

// In your form HTML
<form method="post" action="process.php">
    <?php echo CSRFProtection::getTokenField(); ?>
    <!-- Other form fields -->
    <input type="submit" value="Submit">
</form>
```

### Validating CSRF Tokens

```php
// In your form processing script
include "includes/CSRFProtection.php";

// Method 1: Manual validation
if (!isset($_POST['csrf_token']) || !CSRFProtection::validateToken($_POST['csrf_token'])) {
    // Handle validation failure
    CSRFProtection::handleValidationFailure('form.php');
}

// Method 2: Automatic validation with redirect
CSRFProtection::verifyRequest('POST', 'form.php');
```

### Admin Action Protection

```php
// For GET-based admin actions
$csrf_token = CSRFProtection::generateToken();
$action_url = "admin_actions.php?action=delete_user&id=123&csrf_token=" . $csrf_token;
```

## Testing

### Test Script
Run `test_csrf_protection.php` to verify implementation:
- Token generation and uniqueness
- Token validation (valid/invalid/empty)
- Form field generation
- Session integration
- Security features

### Manual Testing
1. **Valid Token Test:**
   - Submit form with valid token → Should succeed
   
2. **Invalid Token Test:**
   - Submit form with modified token → Should fail
   
3. **Missing Token Test:**
   - Submit form without token → Should fail
   
4. **Expired Token Test:**
   - Wait 30+ minutes, submit form → Should fail
   
5. **Token Reuse Test:**
   - Submit same token twice → Second submission should fail

## Security Benefits

### 1. CSRF Attack Prevention
- Prevents unauthorized form submissions
- Protects against malicious websites triggering actions
- Validates request authenticity

### 2. Admin Action Security
- Protects administrative functions
- Prevents unauthorized user deletion
- Secures vote count modifications

### 3. Session Security
- Tokens tied to user sessions
- Automatic cleanup on logout
- Time-based expiration

### 4. Comprehensive Coverage
- All forms protected
- All admin actions secured
- Consistent implementation across system

## Requirements Satisfied

This implementation satisfies the following security requirements:

- **16.1:** HTTP security headers and web security measures
- **16.2:** Form submission security with CSRF tokens
- **16.3:** Referrer header validation and security
- **16.4:** Subresource Integrity and secure resource loading

## Maintenance

### Regular Tasks
1. **Monitor Logs:** Check for CSRF validation failures
2. **Update Tokens:** Ensure token generation remains secure
3. **Test Coverage:** Verify all new forms include CSRF protection
4. **Security Review:** Regular assessment of CSRF implementation

### Troubleshooting
- **Token Validation Failures:** Check session configuration
- **Form Submission Issues:** Verify token field inclusion
- **Admin Action Problems:** Confirm JavaScript token passing

## Best Practices

1. **Always Include Tokens:** Every form must have CSRF protection
2. **Validate Server-Side:** Never rely on client-side validation alone
3. **Use HTTPS:** Ensure tokens are transmitted securely
4. **Log Failures:** Monitor for potential attack attempts
5. **Regular Updates:** Keep security measures current

This CSRF protection implementation provides comprehensive security against Cross-Site Request Forgery attacks while maintaining system usability and performance.