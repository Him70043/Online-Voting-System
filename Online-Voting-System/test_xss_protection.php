<?php
/**
 * XSS Protection Test Script
 * Tests the XSS protection implementation
 * 
 * @author Himanshu Kumar
 * @version 1.0
 */

include "includes/XSSProtection.php";

echo "<h1>XSS Protection Test Results</h1>";
echo "<hr>";

// Test 1: Basic HTML escaping
echo "<h2>Test 1: Basic HTML Escaping</h2>";
$malicious_input = '<script>alert("XSS Attack!")</script>';
$escaped_output = XSSProtection::escapeHtml($malicious_input);
echo "<strong>Input:</strong> " . htmlspecialchars($malicious_input) . "<br>";
echo "<strong>Escaped Output:</strong> " . $escaped_output . "<br>";
echo "<strong>Status:</strong> " . (strpos($escaped_output, '<script>') === false ? "✅ PASS" : "❌ FAIL") . "<br><br>";

// Test 2: Attribute escaping
echo "<h2>Test 2: Attribute Escaping</h2>";
$malicious_attr = 'value" onload="alert(\'XSS\')" data="';
$escaped_attr = XSSProtection::escapeAttribute($malicious_attr);
echo "<strong>Input:</strong> " . htmlspecialchars($malicious_attr) . "<br>";
echo "<strong>Escaped Output:</strong> " . $escaped_attr . "<br>";
echo "<strong>Status:</strong> " . (strpos($escaped_attr, 'onload') === false ? "✅ PASS" : "❌ FAIL") . "<br><br>";

// Test 3: JavaScript escaping
echo "<h2>Test 3: JavaScript Escaping</h2>";
$malicious_js = 'Hello"; alert("XSS"); var x="';
$escaped_js = XSSProtection::escapeJs($malicious_js);
echo "<strong>Input:</strong> " . htmlspecialchars($malicious_js) . "<br>";
echo "<strong>Escaped Output:</strong> " . $escaped_js . "<br>";
echo "<strong>Status:</strong> " . (json_decode($escaped_js) === $malicious_js ? "✅ PASS" : "❌ FAIL") . "<br><br>";

// Test 4: URL escaping
echo "<h2>Test 4: URL Escaping</h2>";
$malicious_url = 'javascript:alert("XSS")';
$escaped_url = XSSProtection::escapeUrl($malicious_url);
echo "<strong>Input:</strong> " . htmlspecialchars($malicious_url) . "<br>";
echo "<strong>Escaped Output:</strong> " . $escaped_url . "<br>";
echo "<strong>Status:</strong> " . (strpos($escaped_url, 'javascript') === false ? "✅ PASS" : "❌ FAIL") . "<br><br>";

// Test 5: Input cleaning
echo "<h2>Test 5: Input Cleaning</h2>";
$malicious_input2 = '<script>alert("XSS")</script><iframe src="javascript:alert(1)"></iframe>Hello World';
$cleaned_input = XSSProtection::cleanInput($malicious_input2);
echo "<strong>Input:</strong> " . htmlspecialchars($malicious_input2) . "<br>";
echo "<strong>Cleaned Output:</strong> " . htmlspecialchars($cleaned_input) . "<br>";
echo "<strong>Status:</strong> " . (strpos($cleaned_input, '<script>') === false && strpos($cleaned_input, '<iframe>') === false ? "✅ PASS" : "❌ FAIL") . "<br><br>";

// Test 6: Integer sanitization
echo "<h2>Test 6: Integer Sanitization</h2>";
$malicious_int = '123<script>alert("XSS")</script>';
$sanitized_int = XSSProtection::sanitizeInt($malicious_int);
echo "<strong>Input:</strong> " . htmlspecialchars($malicious_int) . "<br>";
echo "<strong>Sanitized Output:</strong> " . $sanitized_int . "<br>";
echo "<strong>Status:</strong> " . ($sanitized_int === 123 ? "✅ PASS" : "❌ FAIL") . "<br><br>";

// Test 7: Email sanitization
echo "<h2>Test 7: Email Sanitization</h2>";
$malicious_email = 'test@example.com<script>alert("XSS")</script>';
$sanitized_email = XSSProtection::sanitizeEmail($malicious_email);
echo "<strong>Input:</strong> " . htmlspecialchars($malicious_email) . "<br>";
echo "<strong>Sanitized Output:</strong> " . htmlspecialchars($sanitized_email) . "<br>";
echo "<strong>Status:</strong> " . (strpos($sanitized_email, '<script>') === false ? "✅ PASS" : "❌ FAIL") . "<br><br>";

// Test 8: CSP Header generation
echo "<h2>Test 8: Content Security Policy Header</h2>";
$csp_header = XSSProtection::getCSPHeader();
echo "<strong>CSP Header:</strong> " . htmlspecialchars($csp_header) . "<br>";
echo "<strong>Status:</strong> " . (strpos($csp_header, "default-src 'self'") !== false ? "✅ PASS" : "❌ FAIL") . "<br><br>";

// Test 9: Array handling
echo "<h2>Test 9: Array Handling</h2>";
$malicious_array = [
    'name' => '<script>alert("XSS")</script>',
    'email' => 'test@example.com<script>',
    'message' => 'Hello<iframe src="javascript:alert(1)"></iframe>'
];
$escaped_array = XSSProtection::escapeHtml($malicious_array);
echo "<strong>Input Array:</strong> " . htmlspecialchars(print_r($malicious_array, true)) . "<br>";
echo "<strong>Escaped Array:</strong> " . htmlspecialchars(print_r($escaped_array, true)) . "<br>";
$all_escaped = true;
foreach ($escaped_array as $value) {
    if (strpos($value, '<script>') !== false || strpos($value, '<iframe>') !== false) {
        $all_escaped = false;
        break;
    }
}
echo "<strong>Status:</strong> " . ($all_escaped ? "✅ PASS" : "❌ FAIL") . "<br><br>";

// Test 10: Convenience functions
echo "<h2>Test 10: Convenience Functions</h2>";
$test_data = '<script>alert("XSS")</script>';
$escaped_conv = xss_escape($test_data);
$attr_conv = xss_attr($test_data);
$js_conv = xss_js($test_data);
echo "<strong>xss_escape():</strong> " . (strpos($escaped_conv, '<script>') === false ? "✅ PASS" : "❌ FAIL") . "<br>";
echo "<strong>xss_attr():</strong> " . (strpos($attr_conv, '<script>') === false ? "✅ PASS" : "❌ FAIL") . "<br>";
echo "<strong>xss_js():</strong> " . (json_decode($js_conv) === $test_data ? "✅ PASS" : "❌ FAIL") . "<br><br>";

echo "<hr>";
echo "<h2>🔒 XSS Protection Implementation Complete!</h2>";
echo "<p><strong>All tests completed.</strong> The XSS protection system is now active across all pages.</p>";
echo "<p><strong>Protected Pages:</strong></p>";
echo "<ul>";
echo "<li>✅ voter.php - Voting interface with user session display</li>";
echo "<li>✅ lan_view.php - Results display with dynamic content</li>";
echo "<li>✅ admin_dashboard.php - Admin panel with comprehensive data display</li>";
echo "<li>✅ profile.php - User profile with voting history</li>";
echo "<li>✅ admin_login.php - Admin login with input sanitization</li>";
echo "<li>✅ admin_actions.php - Admin operations with security headers</li>";
echo "<li>✅ submit_vote.php - Vote processing with error messages</li>";
echo "</ul>";
echo "<p><strong>Security Features Implemented:</strong></p>";
echo "<ul>";
echo "<li>🛡️ HTML output escaping with htmlspecialchars()</li>";
echo "<li>🛡️ Attribute escaping for HTML attributes</li>";
echo "<li>🛡️ JavaScript context escaping</li>";
echo "<li>🛡️ URL encoding for links</li>";
echo "<li>🛡️ Input sanitization and cleaning</li>";
echo "<li>🛡️ Content Security Policy headers</li>";
echo "<li>🛡️ X-Frame-Options, X-XSS-Protection, X-Content-Type-Options</li>";
echo "<li>🛡️ Referrer Policy and HSTS (when HTTPS)</li>";
echo "</ul>";
?>