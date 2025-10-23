<?php
/**
 * Test Input Validation and Form Security Implementation
 * Tests all aspects of the enhanced input validation system
 */

require_once 'includes/InputValidation.php';
require_once 'connection.php';

echo "<h1>Input Validation and Form Security Test</h1>";
echo "<style>
    .test-section { margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }
    .pass { color: green; font-weight: bold; }
    .fail { color: red; font-weight: bold; }
    .info { color: blue; }
</style>";

// Test 1: Honeypot Field Generation
echo "<div class='test-section'>";
echo "<h2>Test 1: Honeypot Field Generation</h2>";
$honeypotFields = InputValidation::generateHoneypotFields();
if (!empty($honeypotFields) && strpos($honeypotFields, 'position: absolute') !== false) {
    echo "<span class='pass'>✓ PASS:</span> Honeypot fields generated successfully<br>";
    echo "<div class='info'>Generated fields: " . htmlspecialchars($honeypotFields) . "</div>";
} else {
    echo "<span class='fail'>✗ FAIL:</span> Honeypot fields not generated properly<br>";
}
echo "</div>";

// Test 2: Honeypot Detection
echo "<div class='test-section'>";
echo "<h2>Test 2: Honeypot Detection</h2>";
$testData = ['username' => 'testuser', 'password' => 'testpass', 'website' => 'spam'];
$honeypotDetected = InputValidation::checkHoneypot($testData);
if ($honeypotDetected) {
    echo "<span class='pass'>✓ PASS:</span> Honeypot detection working correctly<br>";
} else {
    echo "<span class='fail'>✗ FAIL:</span> Honeypot detection failed<br>";
}

$cleanData = ['username' => 'testuser', 'password' => 'testpass'];
$honeypotClean = InputValidation::checkHoneypot($cleanData);
if (!$honeypotClean) {
    echo "<span class='pass'>✓ PASS:</span> Clean data passes honeypot check<br>";
} else {
    echo "<span class='fail'>✗ FAIL:</span> Clean data incorrectly flagged as bot<br>";
}
echo "</div>";

// Test 3: Input Sanitization
echo "<div class='test-section'>";
echo "<h2>Test 3: Input Sanitization</h2>";
$maliciousInput = "<script>alert('xss')</script>test\x00\x01";
$sanitized = InputValidation::sanitizeInput($maliciousInput);
if (strpos($sanitized, '<script>') === false && strpos($sanitized, "\x00") === false) {
    echo "<span class='pass'>✓ PASS:</span> Input sanitization working correctly<br>";
    echo "<div class='info'>Original: " . htmlspecialchars($maliciousInput) . "</div>";
    echo "<div class='info'>Sanitized: " . htmlspecialchars($sanitized) . "</div>";
} else {
    echo "<span class='fail'>✗ FAIL:</span> Input sanitization failed<br>";
}
echo "</div>";

// Test 4: Field Validation
echo "<div class='test-section'>";
echo "<h2>Test 4: Field Validation</h2>";

// Test username validation
$validUsername = InputValidation::validateField('username', 'validuser123');
$invalidUsername = InputValidation::validateField('username', 'invalid@user');
$shortUsername = InputValidation::validateField('username', 'ab');

if ($validUsername['valid'] && !$invalidUsername['valid'] && !$shortUsername['valid']) {
    echo "<span class='pass'>✓ PASS:</span> Username validation working correctly<br>";
} else {
    echo "<span class='fail'>✗ FAIL:</span> Username validation failed<br>";
}

// Test firstname validation
$validFirstname = InputValidation::validateField('firstname', 'John');
$invalidFirstname = InputValidation::validateField('firstname', 'John123');

if ($validFirstname['valid'] && !$invalidFirstname['valid']) {
    echo "<span class='pass'>✓ PASS:</span> Firstname validation working correctly<br>";
} else {
    echo "<span class='fail'>✗ FAIL:</span> Firstname validation failed<br>";
}
echo "</div>";

// Test 5: Password Complexity Validation
echo "<div class='test-section'>";
echo "<h2>Test 5: Password Complexity Validation</h2>";
$weakPassword = InputValidation::validatePasswordComplexity('weak');
$strongPassword = InputValidation::validatePasswordComplexity('StrongPass123!');

if (!$weakPassword['valid'] && $strongPassword['valid']) {
    echo "<span class='pass'>✓ PASS:</span> Password complexity validation working correctly<br>";
} else {
    echo "<span class='fail'>✗ FAIL:</span> Password complexity validation failed<br>";
    echo "<div class='info'>Weak password errors: " . implode(', ', $weakPassword['errors']) . "</div>";
}
echo "</div>";

// Test 6: Form Validation (Registration)
echo "<div class='test-section'>";
echo "<h2>Test 6: Registration Form Validation</h2>";
$validRegistration = [
    'firstname' => 'John',
    'lastname' => 'Doe',
    'username' => 'johndoe123',
    'password' => 'StrongPass123!'
];
$invalidRegistration = [
    'firstname' => 'John123',
    'lastname' => '',
    'username' => 'invalid@user',
    'password' => 'weak'
];

$validResult = InputValidation::validateForm($validRegistration, 'registration');
$invalidResult = InputValidation::validateForm($invalidRegistration, 'registration');

if ($validResult['valid'] && !$invalidResult['valid']) {
    echo "<span class='pass'>✓ PASS:</span> Registration form validation working correctly<br>";
} else {
    echo "<span class='fail'>✗ FAIL:</span> Registration form validation failed<br>";
    if (!$validResult['valid']) {
        echo "<div class='info'>Valid form errors: " . implode(', ', $validResult['errors']) . "</div>";
    }
}
echo "</div>";

// Test 7: Voting Form Validation
echo "<div class='test-section'>";
echo "<h2>Test 7: Voting Form Validation</h2>";
$validVote = ['lan' => 'JAVA', 'team' => 'Himanshu'];
$invalidVote = ['lan' => 'InvalidLanguage', 'team' => 'InvalidMember'];
$emptyVote = [];

$validVoteResult = InputValidation::validateForm($validVote, 'voting');
$invalidVoteResult = InputValidation::validateForm($invalidVote, 'voting');
$emptyVoteResult = InputValidation::validateForm($emptyVote, 'voting');

if ($validVoteResult['valid'] && !$invalidVoteResult['valid'] && !$emptyVoteResult['valid']) {
    echo "<span class='pass'>✓ PASS:</span> Voting form validation working correctly<br>";
} else {
    echo "<span class='fail'>✗ FAIL:</span> Voting form validation failed<br>";
    if (!$validVoteResult['valid']) {
        echo "<div class='info'>Valid vote errors: " . implode(', ', $validVoteResult['errors']) . "</div>";
    }
}
echo "</div>";

// Test 8: Rate Limiting
echo "<div class='test-section'>";
echo "<h2>Test 8: Rate Limiting</h2>";
$testIdentifier = 'test_user_' . time();

// First few attempts should pass
$attempt1 = InputValidation::checkSubmissionRate($testIdentifier, 3, 60);
$attempt2 = InputValidation::checkSubmissionRate($testIdentifier, 3, 60);
$attempt3 = InputValidation::checkSubmissionRate($testIdentifier, 3, 60);
$attempt4 = InputValidation::checkSubmissionRate($testIdentifier, 3, 60); // Should fail

if ($attempt1 && $attempt2 && $attempt3 && !$attempt4) {
    echo "<span class='pass'>✓ PASS:</span> Rate limiting working correctly<br>";
} else {
    echo "<span class='fail'>✗ FAIL:</span> Rate limiting failed<br>";
    echo "<div class='info'>Attempts: $attempt1, $attempt2, $attempt3, $attempt4</div>";
}
echo "</div>";

// Test 9: Suspicious Content Detection
echo "<div class='test-section'>";
echo "<h2>Test 9: Suspicious Content Detection</h2>";
$suspiciousInputs = [
    "'; DROP TABLE users; --",
    "<script>alert('xss')</script>",
    "../../etc/passwd",
    "eval(malicious_code)"
];

$suspiciousDetected = 0;
foreach ($suspiciousInputs as $input) {
    $validation = InputValidation::validateField('username', $input);
    if (!$validation['valid']) {
        $suspiciousDetected++;
    }
}

if ($suspiciousDetected === count($suspiciousInputs)) {
    echo "<span class='pass'>✓ PASS:</span> Suspicious content detection working correctly<br>";
} else {
    echo "<span class='fail'>✗ FAIL:</span> Suspicious content detection failed<br>";
    echo "<div class='info'>Detected: $suspiciousDetected / " . count($suspiciousInputs) . "</div>";
}
echo "</div>";

// Test 10: File Upload Validation (Simulated)
echo "<div class='test-section'>";
echo "<h2>Test 10: File Upload Validation</h2>";

// Create a temporary test file
$testFile = sys_get_temp_dir() . '/test_upload.txt';
file_put_contents($testFile, 'This is a test file content');

$simulatedUpload = [
    'name' => 'test.txt',
    'type' => 'text/plain',
    'tmp_name' => $testFile,
    'error' => UPLOAD_ERR_OK,
    'size' => filesize($testFile)
];

$uploadValidation = InputValidation::validateFileUpload($simulatedUpload, ['text/plain'], 1024);

if ($uploadValidation['valid']) {
    echo "<span class='pass'>✓ PASS:</span> File upload validation working correctly<br>";
} else {
    echo "<span class='fail'>✗ FAIL:</span> File upload validation failed<br>";
    echo "<div class='info'>Errors: " . implode(', ', $uploadValidation['errors']) . "</div>";
}

// Clean up
unlink($testFile);
echo "</div>";

echo "<h2>Test Summary</h2>";
echo "<p>All input validation and form security tests completed. Review the results above to ensure all components are working correctly.</p>";
echo "<p><strong>Note:</strong> This test covers server-side validation, honeypot detection, rate limiting, and security measures implemented in the InputValidation class.</p>";
?>