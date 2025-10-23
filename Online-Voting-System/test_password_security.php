<?php
/**
 * Test script for Password Security implementation
 * This script tests the password hashing and validation functionality
 */

require_once "includes/PasswordSecurity.php";

echo "<h2>Password Security Implementation Test</h2>";

// Test 1: Password Hashing
echo "<h3>Test 1: Password Hashing</h3>";
$testPassword = "TestPassword123!";
$hash = PasswordSecurity::hashPassword($testPassword);
echo "<p>Original Password: $testPassword</p>";
echo "<p>Hashed Password: $hash</p>";
echo "<p>Hash Length: " . strlen($hash) . " characters</p>";

// Test 2: Password Verification
echo "<h3>Test 2: Password Verification</h3>";
$isValid = PasswordSecurity::verifyPassword($testPassword, $hash);
echo "<p>Password Verification: " . ($isValid ? "✅ PASS" : "❌ FAIL") . "</p>";

$wrongPassword = "WrongPassword";
$isInvalid = PasswordSecurity::verifyPassword($wrongPassword, $hash);
echo "<p>Wrong Password Test: " . (!$isInvalid ? "✅ PASS" : "❌ FAIL") . "</p>";

// Test 3: MD5 Detection
echo "<h3>Test 3: MD5 Hash Detection</h3>";
$md5Hash = md5("testpassword");
$bcryptHash = $hash;
echo "<p>MD5 Hash Detection: " . (PasswordSecurity::isMD5Hash($md5Hash) ? "✅ PASS" : "❌ FAIL") . "</p>";
echo "<p>Bcrypt Hash Detection: " . (!PasswordSecurity::isMD5Hash($bcryptHash) ? "✅ PASS" : "❌ FAIL") . "</p>";

// Test 4: Password Complexity Validation
echo "<h3>Test 4: Password Complexity Validation</h3>";

$testPasswords = [
    "weak" => "weak",
    "NoNumbers!" => "NoNumbers!",
    "nonumbers123" => "nonumbers123",
    "NOLOWERCASE123!" => "NOLOWERCASE123!",
    "NoSpecialChars123" => "NoSpecialChars123",
    "ValidPass123!" => "ValidPass123!"
];

foreach ($testPasswords as $label => $password) {
    $validation = PasswordSecurity::validatePasswordComplexity($password);
    echo "<p>$label ('$password'): " . ($validation['valid'] ? "✅ VALID" : "❌ INVALID") . "</p>";
    if (!$validation['valid']) {
        echo "<ul>";
        foreach ($validation['errors'] as $error) {
            echo "<li style='color: red;'>$error</li>";
        }
        echo "</ul>";
    }
}

// Test 5: Reset Token Generation
echo "<h3>Test 5: Reset Token Generation</h3>";
$token1 = PasswordSecurity::generateResetToken();
$token2 = PasswordSecurity::generateResetToken();
echo "<p>Token 1: $token1 (Length: " . strlen($token1) . ")</p>";
echo "<p>Token 2: $token2 (Length: " . strlen($token2) . ")</p>";
echo "<p>Tokens are unique: " . ($token1 !== $token2 ? "✅ PASS" : "❌ FAIL") . "</p>";

echo "<h3>All Tests Completed!</h3>";
echo "<p><strong>Note:</strong> Run the database migration script (run_password_migration.php) before using the new authentication system.</p>";
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; }
h2, h3 { color: #333; }
p { margin: 5px 0; }
ul { margin: 5px 0 15px 20px; }
</style>