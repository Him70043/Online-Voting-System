<?php
/**
 * Verification Script for Input Validation and Form Security Implementation
 * Verifies that all requirements for Task 9 have been implemented correctly
 */

echo "<h1>Input Validation and Form Security Implementation Verification</h1>";
echo "<style>
    .verification-section { margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }
    .pass { color: green; font-weight: bold; }
    .fail { color: red; font-weight: bold; }
    .info { color: blue; margin: 10px 0; }
    .requirement { background: #f8f9fa; padding: 10px; margin: 10px 0; border-left: 4px solid #007bff; }
</style>";

$verificationResults = [];

// Requirement 6.1: Add server-side validation for all form fields
echo "<div class='verification-section'>";
echo "<h2>Requirement 6.1: Server-side validation for all form fields</h2>";
echo "<div class='requirement'>Verify that comprehensive server-side validation is implemented for all form fields including username, password, firstname, lastname, and voting options.</div>";

$checks = [
    'InputValidation class exists' => class_exists('InputValidation'),
    'validateForm method exists' => method_exists('InputValidation', 'validateForm'),
    'validateField method exists' => method_exists('InputValidation', 'validateField'),
    'sanitizeInput method exists' => method_exists('InputValidation', 'sanitizeInput'),
    'Registration form validation' => file_exists('reg_action.php') && strpos(file_get_contents('reg_action.php'), 'InputValidation::validateForm') !== false,
    'Login form validation' => file_exists('login_action.php') && strpos(file_get_contents('login_action.php'), 'InputValidation::validateForm') !== false,
    'Voting form validation' => file_exists('submit_vote.php') && strpos(file_get_contents('submit_vote.php'), 'InputValidation::validateForm') !== false,
    'Admin login validation' => file_exists('admin_login.php') && strpos(file_get_contents('admin_login.php'), 'InputValidation::validateForm') !== false
];

$passed = 0;
foreach ($checks as $check => $result) {
    if ($result) {
        echo "<span class='pass'>✓ PASS:</span> $check<br>";
        $passed++;
    } else {
        echo "<span class='fail'>✗ FAIL:</span> $check<br>";
    }
}

$verificationResults['6.1'] = $passed === count($checks);
echo "<div class='info'>Server-side validation: $passed/" . count($checks) . " checks passed</div>";
echo "</div>";

// Requirement 6.2: Implement file upload security (if needed)
echo "<div class='verification-section'>";
echo "<h2>Requirement 6.2: File upload security implementation</h2>";
echo "<div class='requirement'>Verify that file upload security measures are implemented including file type validation, size limits, and malicious content detection.</div>";

$fileUploadChecks = [
    'validateFileUpload method exists' => method_exists('InputValidation', 'validateFileUpload'),
    'containsMaliciousContent method exists' => method_exists('InputValidation', 'containsMaliciousContent'),
    'File type validation implemented' => file_exists('includes/InputValidation.php') && strpos(file_get_contents('includes/InputValidation.php'), 'allowedTypes') !== false,
    'File size validation implemented' => file_exists('includes/InputValidation.php') && strpos(file_get_contents('includes/InputValidation.php'), 'maxSize') !== false,
    'Malicious content detection' => file_exists('includes/InputValidation.php') && strpos(file_get_contents('includes/InputValidation.php'), 'maliciousPatterns') !== false
];

$filePassed = 0;
foreach ($fileUploadChecks as $check => $result) {
    if ($result) {
        echo "<span class='pass'>✓ PASS:</span> $check<br>";
        $filePassed++;
    } else {
        echo "<span class='fail'>✗ FAIL:</span> $check<br>";
    }
}

$verificationResults['6.2'] = $filePassed === count($fileUploadChecks);
echo "<div class='info'>File upload security: $filePassed/" . count($fileUploadChecks) . " checks passed</div>";
echo "</div>";

// Requirement 6.3: Create input length and format validation
echo "<div class='verification-section'>";
echo "<h2>Requirement 6.3: Input length and format validation</h2>";
echo "<div class='requirement'>Verify that input length limits and format validation (patterns) are implemented for all form fields.</div>";

$lengthFormatChecks = [
    'Validation rules defined' => file_exists('includes/InputValidation.php') && strpos(file_get_contents('includes/InputValidation.php'), 'validationRules') !== false,
    'Min/Max length validation' => file_exists('includes/InputValidation.php') && strpos(file_get_contents('includes/InputValidation.php'), 'min_length') !== false,
    'Pattern validation' => file_exists('includes/InputValidation.php') && strpos(file_get_contents('includes/InputValidation.php'), 'pattern') !== false,
    'Username format validation' => file_exists('includes/InputValidation.php') && strpos(file_get_contents('includes/InputValidation.php'), '/^[a-zA-Z0-9_-]+$/') !== false,
    'Name format validation' => file_exists('includes/InputValidation.php') && strpos(file_get_contents('includes/InputValidation.php'), '/^[a-zA-Z\s\'-]+$/') !== false,
    'Password complexity validation' => method_exists('InputValidation', 'validatePasswordComplexity'),
    'HTML form maxlength attributes' => file_exists('register.php') && strpos(file_get_contents('register.php'), 'maxlength=') !== false
];

$lengthPassed = 0;
foreach ($lengthFormatChecks as $check => $result) {
    if ($result) {
        echo "<span class='pass'>✓ PASS:</span> $check<br>";
        $lengthPassed++;
    } else {
        echo "<span class='fail'>✗ FAIL:</span> $check<br>";
    }
}

$verificationResults['6.3'] = $lengthPassed === count($lengthFormatChecks);
echo "<div class='info'>Length and format validation: $lengthPassed/" . count($lengthFormatChecks) . " checks passed</div>";
echo "</div>";

// Requirement 6.4: Add honeypot fields to detect bots
echo "<div class='verification-section'>";
echo "<h2>Requirement 6.4: Honeypot fields for bot detection</h2>";
echo "<div class='requirement'>Verify that honeypot fields are implemented in forms to detect and prevent bot submissions.</div>";

$honeypotChecks = [
    'generateHoneypotFields method exists' => method_exists('InputValidation', 'generateHoneypotFields'),
    'checkHoneypot method exists' => method_exists('InputValidation', 'checkHoneypot'),
    'Honeypot fields in voting form' => file_exists('voter.php') && strpos(file_get_contents('voter.php'), 'generateHoneypotFields') !== false,
    'Honeypot fields in registration form' => file_exists('register.php') && strpos(file_get_contents('register.php'), 'generateHoneypotFields') !== false,
    'Honeypot fields in login form' => file_exists('login.php') && strpos(file_get_contents('login.php'), 'generateHoneypotFields') !== false,
    'Honeypot fields in admin login' => file_exists('admin_login.php') && strpos(file_get_contents('admin_login.php'), 'generateHoneypotFields') !== false,
    'Honeypot validation in forms' => file_exists('submit_vote.php') && strpos(file_get_contents('submit_vote.php'), 'checkHoneypot') !== false,
    'Hidden honeypot styling' => file_exists('includes/InputValidation.php') && strpos(file_get_contents('includes/InputValidation.php'), 'position: absolute') !== false
];

$honeypotPassed = 0;
foreach ($honeypotChecks as $check => $result) {
    if ($result) {
        echo "<span class='pass'>✓ PASS:</span> $check<br>";
        $honeypotPassed++;
    } else {
        echo "<span class='fail'>✗ FAIL:</span> $check<br>";
    }
}

$verificationResults['6.4'] = $honeypotPassed === count($honeypotChecks);
echo "<div class='info'>Honeypot implementation: $honeypotPassed/" . count($honeypotChecks) . " checks passed</div>";
echo "</div>";

// Additional Security Features
echo "<div class='verification-section'>";
echo "<h2>Additional Security Features Implemented</h2>";

$additionalChecks = [
    'Rate limiting implemented' => method_exists('InputValidation', 'checkSubmissionRate'),
    'Suspicious content detection' => file_exists('includes/InputValidation.php') && strpos(file_get_contents('includes/InputValidation.php'), 'containsSuspiciousContent') !== false,
    'Security logging integration' => file_exists('reg_action.php') && strpos(file_get_contents('reg_action.php'), 'SecurityLogger::logSecurityEvent') !== false,
    'Input sanitization' => file_exists('includes/InputValidation.php') && strpos(file_get_contents('includes/InputValidation.php'), 'sanitizeInput') !== false,
    'Voting option validation' => file_exists('includes/InputValidation.php') && strpos(file_get_contents('includes/InputValidation.php'), 'validateVotingForm') !== false
];

$additionalPassed = 0;
foreach ($additionalChecks as $check => $result) {
    if ($result) {
        echo "<span class='pass'>✓ PASS:</span> $check<br>";
        $additionalPassed++;
    } else {
        echo "<span class='fail'>✗ FAIL:</span> $check<br>";
    }
}

echo "<div class='info'>Additional security features: $additionalPassed/" . count($additionalChecks) . " implemented</div>";
echo "</div>";

// Overall Verification Summary
echo "<div class='verification-section'>";
echo "<h2>Overall Implementation Verification</h2>";

$overallPassed = array_sum($verificationResults);
$totalRequirements = count($verificationResults);

if ($overallPassed === $totalRequirements) {
    echo "<span class='pass'>✓ ALL REQUIREMENTS PASSED:</span> Input Validation and Form Security implementation is complete<br>";
} else {
    echo "<span class='fail'>✗ SOME REQUIREMENTS FAILED:</span> $overallPassed/$totalRequirements requirements passed<br>";
}

echo "<div class='info'>";
echo "<h3>Implementation Summary:</h3>";
echo "<ul>";
echo "<li><strong>Server-side validation:</strong> " . ($verificationResults['6.1'] ? 'Implemented' : 'Needs work') . "</li>";
echo "<li><strong>File upload security:</strong> " . ($verificationResults['6.2'] ? 'Implemented' : 'Needs work') . "</li>";
echo "<li><strong>Length and format validation:</strong> " . ($verificationResults['6.3'] ? 'Implemented' : 'Needs work') . "</li>";
echo "<li><strong>Honeypot bot detection:</strong> " . ($verificationResults['6.4'] ? 'Implemented' : 'Needs work') . "</li>";
echo "</ul>";
echo "</div>";

echo "<div class='info'>";
echo "<h3>Files Modified/Created:</h3>";
echo "<ul>";
echo "<li><code>includes/InputValidation.php</code> - Main validation class</li>";
echo "<li><code>voter.php</code> - Added honeypot fields and validation</li>";
echo "<li><code>submit_vote.php</code> - Enhanced vote validation</li>";
echo "<li><code>register.php</code> - Improved registration form</li>";
echo "<li><code>reg_action.php</code> - Enhanced registration processing</li>";
echo "<li><code>login.php</code> - Added honeypot fields</li>";
echo "<li><code>login_action.php</code> - Enhanced login validation</li>";
echo "<li><code>admin_login.php</code> - Added admin form validation</li>";
echo "<li><code>test_input_validation.php</code> - Comprehensive test suite</li>";
echo "<li><code>verify_input_validation.php</code> - This verification script</li>";
echo "</ul>";
echo "</div>";

echo "</div>";

echo "<p><strong>Next Steps:</strong> Run the test suite at <a href='test_input_validation.php'>test_input_validation.php</a> to verify functionality.</p>";
?>