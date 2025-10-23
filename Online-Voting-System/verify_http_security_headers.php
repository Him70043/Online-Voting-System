<?php
/**
 * Verify HTTP Security Headers Implementation
 * 
 * This script verifies that HTTP security headers are properly implemented
 * across all application pages and meets the security requirements.
 */

require_once __DIR__ . '/includes/HTTPSecurityHeaders.php';

// Function to check if a URL has proper security headers
function checkSecurityHeaders($url) {
    $headers = get_headers($url, 1);
    
    $security_headers = [
        'Content-Security-Policy' => false,
        'X-Frame-Options' => false,
        'X-XSS-Protection' => false,
        'X-Content-Type-Options' => false
    ];
    
    foreach ($headers as $header_name => $header_value) {
        $header_name = strtolower($header_name);
        
        if (strpos($header_name, 'content-security-policy') !== false) {
            $security_headers['Content-Security-Policy'] = true;
        }
        if (strpos($header_name, 'x-frame-options') !== false) {
            $security_headers['X-Frame-Options'] = true;
        }
        if (strpos($header_name, 'x-xss-protection') !== false) {
            $security_headers['X-XSS-Protection'] = true;
        }
        if (strpos($header_name, 'x-content-type-options') !== false) {
            $security_headers['X-Content-Type-Options'] = true;
        }
    }
    
    return $security_headers;
}

echo "<!DOCTYPE html>\n";
echo "<html>\n<head>\n";
echo "<title>HTTP Security Headers Verification</title>\n";
echo "<style>\n";
echo "body { font-family: Arial, sans-serif; margin: 20px; }\n";
echo ".pass { color: green; }\n";
echo ".fail { color: red; }\n";
echo ".header { background: #f0f0f0; padding: 10px; margin: 10px 0; }\n";
echo "</style>\n";
echo "</head>\n<body>\n";

echo "<h1>🔒 HTTP Security Headers Verification</h1>\n";

// Test the HTTPSecurityHeaders class directly
echo "<div class='header'>\n";
echo "<h2>Direct Class Testing</h2>\n";

// Initialize headers
HTTPSecurityHeaders::initialize();

// Check if headers are set
$current_headers = headers_list();
$required_headers = [
    'Content-Security-Policy' => false,
    'X-Frame-Options' => false,
    'X-XSS-Protection' => false,
    'X-Content-Type-Options' => false,
    'Referrer-Policy' => false,
    'Permissions-Policy' => false
];

foreach ($current_headers as $header) {
    foreach ($required_headers as $required_header => $found) {
        if (strpos($header, $required_header) === 0) {
            $required_headers[$required_header] = $header;
        }
    }
}

echo "<h3>Security Headers Status:</h3>\n";
foreach ($required_headers as $header_name => $header_value) {
    if ($header_value) {
        echo "<div class='pass'>✅ $header_name: " . htmlspecialchars($header_value) . "</div>\n";
    } else {
        echo "<div class='fail'>❌ $header_name: Not found</div>\n";
    }
}
echo "</div>\n";

// Test cookie security configuration
echo "<div class='header'>\n";
echo "<h2>Cookie Security Configuration</h2>\n";

$cookie_params = session_get_cookie_params();
echo "<h3>Session Cookie Parameters:</h3>\n";
echo "<ul>\n";
echo "<li>Lifetime: " . $cookie_params['lifetime'] . " seconds</li>\n";
echo "<li>Path: " . $cookie_params['path'] . "</li>\n";
echo "<li>Domain: " . ($cookie_params['domain'] ?: 'Not set') . "</li>\n";
echo "<li>Secure: " . ($cookie_params['secure'] ? 'Yes' : 'No') . "</li>\n";
echo "<li>HttpOnly: " . ($cookie_params['httponly'] ? 'Yes' : 'No') . "</li>\n";
echo "<li>SameSite: " . $cookie_params['samesite'] . "</li>\n";
echo "</ul>\n";

$cookie_security_score = 0;
if ($cookie_params['httponly']) $cookie_security_score++;
if ($cookie_params['samesite'] === 'Strict') $cookie_security_score++;
if ($cookie_params['lifetime'] <= 1800) $cookie_security_score++; // 30 minutes or less

if ($cookie_security_score >= 3) {
    echo "<div class='pass'>✅ Cookie security configuration is properly set</div>\n";
} else {
    echo "<div class='fail'>❌ Cookie security configuration needs improvement</div>\n";
}
echo "</div>\n";

// Test requirements compliance
echo "<div class='header'>\n";
echo "<h2>Requirements Compliance Check</h2>\n";

$requirements = [
    '16.1' => [
        'name' => 'Content-Security-Policy header',
        'met' => (bool)$required_headers['Content-Security-Policy']
    ],
    '16.2' => [
        'name' => 'X-Frame-Options to prevent clickjacking',
        'met' => (bool)$required_headers['X-Frame-Options']
    ],
    '16.3a' => [
        'name' => 'X-XSS-Protection',
        'met' => (bool)$required_headers['X-XSS-Protection']
    ],
    '16.3b' => [
        'name' => 'X-Content-Type-Options',
        'met' => (bool)$required_headers['X-Content-Type-Options']
    ],
    '16.4' => [
        'name' => 'Secure cookie settings',
        'met' => $cookie_security_score >= 3
    ]
];

$total_requirements = count($requirements);
$met_requirements = 0;

echo "<h3>Individual Requirements:</h3>\n";
foreach ($requirements as $req_id => $requirement) {
    if ($requirement['met']) {
        echo "<div class='pass'>✅ Requirement $req_id: {$requirement['name']} - PASSED</div>\n";
        $met_requirements++;
    } else {
        echo "<div class='fail'>❌ Requirement $req_id: {$requirement['name']} - FAILED</div>\n";
    }
}

echo "<h3>Overall Compliance:</h3>\n";
$compliance_percentage = ($met_requirements / $total_requirements) * 100;

if ($compliance_percentage == 100) {
    echo "<div class='pass'><strong>🎉 100% COMPLIANCE ACHIEVED!</strong></div>\n";
    echo "<div class='pass'>All HTTP security header requirements have been successfully implemented.</div>\n";
} else {
    echo "<div class='fail'><strong>⚠️ {$compliance_percentage}% COMPLIANCE</strong></div>\n";
    echo "<div class='fail'>$met_requirements out of $total_requirements requirements met.</div>\n";
}
echo "</div>\n";

// Test integration with application files
echo "<div class='header'>\n";
echo "<h2>Application Integration Test</h2>\n";

$test_files = [
    'index.php' => 'Main landing page',
    'login.php' => 'User login page',
    'register.php' => 'User registration page',
    'voter.php' => 'Voting interface',
    'admin_login.php' => 'Admin login page',
    'admin_dashboard.php' => 'Admin dashboard'
];

echo "<h3>Files with Security Headers Integration:</h3>\n";
foreach ($test_files as $file => $description) {
    if (file_exists($file)) {
        $content = file_get_contents($file);
        if (strpos($content, 'HTTPSecurityHeaders::initialize()') !== false) {
            echo "<div class='pass'>✅ $file ($description) - Security headers integrated</div>\n";
        } else {
            echo "<div class='fail'>❌ $file ($description) - Security headers NOT integrated</div>\n";
        }
    } else {
        echo "<div class='fail'>❌ $file - File not found</div>\n";
    }
}
echo "</div>\n";

// Security recommendations
echo "<div class='header'>\n";
echo "<h2>Security Recommendations</h2>\n";
echo "<ul>\n";
echo "<li>Ensure HTTPS is enabled in production for Strict-Transport-Security header</li>\n";
echo "<li>Regularly review and update Content-Security-Policy directives</li>\n";
echo "<li>Monitor security headers using browser developer tools</li>\n";
echo "<li>Consider implementing additional security headers like Expect-CT</li>\n";
echo "<li>Test the application with security scanning tools</li>\n";
echo "</ul>\n";
echo "</div>\n";

echo "<div class='header'>\n";
echo "<h2>Next Steps</h2>\n";
if ($compliance_percentage == 100) {
    echo "<p>✅ <strong>Task 8: Implement HTTP Security Headers</strong> is now complete!</p>\n";
    echo "<p>All security requirements have been successfully implemented:</p>\n";
    echo "<ul>\n";
    echo "<li>✅ Content-Security-Policy header added</li>\n";
    echo "<li>✅ X-Frame-Options implemented to prevent clickjacking</li>\n";
    echo "<li>✅ X-XSS-Protection and X-Content-Type-Options added</li>\n";
    echo "<li>✅ Secure cookie settings configured</li>\n";
    echo "</ul>\n";
} else {
    echo "<p>⚠️ Please address the failed requirements above before marking this task as complete.</p>\n";
}
echo "</div>\n";

echo "</body>\n</html>\n";
?>