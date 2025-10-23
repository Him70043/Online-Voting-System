<?php
/**
 * Test HTTP Security Headers Implementation
 * 
 * This script tests the HTTP security headers functionality to ensure
 * all required security headers are properly implemented.
 */

require_once __DIR__ . '/includes/HTTPSecurityHeaders.php';

// Start output buffering to capture headers
ob_start();

echo "<h1>HTTP Security Headers Test</h1>\n";

// Test 1: Initialize security headers
echo "<h2>Test 1: Initialize Security Headers</h2>\n";
try {
    HTTPSecurityHeaders::initialize();
    echo "✅ Security headers initialized successfully<br>\n";
} catch (Exception $e) {
    echo "❌ Error initializing security headers: " . $e->getMessage() . "<br>\n";
}

// Test 2: Check individual header methods
echo "<h2>Test 2: Individual Header Methods</h2>\n";

// Test Content-Security-Policy
echo "<h3>Content-Security-Policy Header</h3>\n";
$headers_before = headers_list();
HTTPSecurityHeaders::applySecurityHeaders();
$headers_after = headers_list();

$csp_found = false;
foreach ($headers_after as $header) {
    if (strpos($header, 'Content-Security-Policy') === 0) {
        echo "✅ CSP Header: " . htmlspecialchars($header) . "<br>\n";
        $csp_found = true;
        break;
    }
}
if (!$csp_found) {
    echo "❌ Content-Security-Policy header not found<br>\n";
}

// Test X-Frame-Options
$frame_options_found = false;
foreach ($headers_after as $header) {
    if (strpos($header, 'X-Frame-Options') === 0) {
        echo "✅ Frame Options Header: " . htmlspecialchars($header) . "<br>\n";
        $frame_options_found = true;
        break;
    }
}
if (!$frame_options_found) {
    echo "❌ X-Frame-Options header not found<br>\n";
}

// Test X-XSS-Protection
$xss_protection_found = false;
foreach ($headers_after as $header) {
    if (strpos($header, 'X-XSS-Protection') === 0) {
        echo "✅ XSS Protection Header: " . htmlspecialchars($header) . "<br>\n";
        $xss_protection_found = true;
        break;
    }
}
if (!$xss_protection_found) {
    echo "❌ X-XSS-Protection header not found<br>\n";
}

// Test X-Content-Type-Options
$content_type_options_found = false;
foreach ($headers_after as $header) {
    if (strpos($header, 'X-Content-Type-Options') === 0) {
        echo "✅ Content Type Options Header: " . htmlspecialchars($header) . "<br>\n";
        $content_type_options_found = true;
        break;
    }
}
if (!$content_type_options_found) {
    echo "❌ X-Content-Type-Options header not found<br>\n";
}

// Test 3: Cookie Security Configuration
echo "<h2>Test 3: Cookie Security Configuration</h2>\n";

// Test secure cookie settings
try {
    HTTPSecurityHeaders::configureCookieSettings();
    echo "✅ Cookie security settings configured<br>\n";
    
    // Check session cookie parameters
    $cookieParams = session_get_cookie_params();
    echo "Session Cookie Parameters:<br>\n";
    echo "- Lifetime: " . $cookieParams['lifetime'] . " seconds<br>\n";
    echo "- Path: " . $cookieParams['path'] . "<br>\n";
    echo "- HttpOnly: " . ($cookieParams['httponly'] ? 'Yes' : 'No') . "<br>\n";
    echo "- SameSite: " . $cookieParams['samesite'] . "<br>\n";
    
    if ($cookieParams['httponly'] && $cookieParams['samesite'] === 'Strict') {
        echo "✅ Cookie security parameters properly configured<br>\n";
    } else {
        echo "❌ Cookie security parameters not properly configured<br>\n";
    }
} catch (Exception $e) {
    echo "❌ Error configuring cookie settings: " . $e->getMessage() . "<br>\n";
}

// Test 4: Secure Cookie Creation
echo "<h2>Test 4: Secure Cookie Creation</h2>\n";
try {
    HTTPSecurityHeaders::setSecureCookie('test_cookie', 'test_value', time() + 3600);
    echo "✅ Secure cookie creation method works<br>\n";
} catch (Exception $e) {
    echo "❌ Error creating secure cookie: " . $e->getMessage() . "<br>\n";
}

// Test 5: Security Headers Status
echo "<h2>Test 5: Security Headers Status</h2>\n";
$status = HTTPSecurityHeaders::getSecurityHeadersStatus();
if (isset($status['error'])) {
    echo "❌ " . $status['error'] . "<br>\n";
} else {
    foreach ($status as $header => $value) {
        if ($value === 'Not set') {
            echo "❌ $header: Not set<br>\n";
        } else {
            echo "✅ $header: Set<br>\n";
        }
    }
}

// Test 6: Requirements Verification
echo "<h2>Test 6: Requirements Verification</h2>\n";

$requirements_met = [
    '16.1 - Content-Security-Policy' => $csp_found,
    '16.2 - X-Frame-Options (Clickjacking Protection)' => $frame_options_found,
    '16.3 - X-XSS-Protection' => $xss_protection_found,
    '16.3 - X-Content-Type-Options' => $content_type_options_found,
    '16.4 - Secure Cookie Settings' => $cookieParams['httponly'] && $cookieParams['samesite'] === 'Strict'
];

$all_requirements_met = true;
foreach ($requirements_met as $requirement => $met) {
    if ($met) {
        echo "✅ Requirement $requirement: PASSED<br>\n";
    } else {
        echo "❌ Requirement $requirement: FAILED<br>\n";
        $all_requirements_met = false;
    }
}

echo "<h2>Overall Test Result</h2>\n";
if ($all_requirements_met) {
    echo "🎉 <strong>ALL TESTS PASSED!</strong> HTTP Security Headers implementation is complete and meets all requirements.<br>\n";
} else {
    echo "⚠️ <strong>SOME TESTS FAILED!</strong> Please review the failed requirements above.<br>\n";
}

// Display all current headers for debugging
echo "<h2>Debug: All Current Headers</h2>\n";
echo "<pre>\n";
foreach (headers_list() as $header) {
    echo htmlspecialchars($header) . "\n";
}
echo "</pre>\n";

// End output buffering and send content
ob_end_flush();
?>