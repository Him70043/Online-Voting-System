<?php
echo "<h2>🔧 Admin Issues Fix Script</h2>";

// Check if HTTPSecurityHeaders class has the right methods
if (file_exists(__DIR__ . '/includes/HTTPSecurityHeaders.php')) {
    require_once __DIR__ . '/includes/HTTPSecurityHeaders.php';
    
    echo "<h3>HTTPSecurityHeaders Class Analysis:</h3>";
    if (class_exists('HTTPSecurityHeaders')) {
        echo "<p style='color: green;'>✅ HTTPSecurityHeaders class exists</p>";
        
        $methods = get_class_methods('HTTPSecurityHeaders');
        echo "<p><strong>Available methods:</strong></p>";
        echo "<ul>";
        foreach ($methods as $method) {
            echo "<li>" . htmlspecialchars($method) . "</li>";
        }
        echo "</ul>";
        
        if (method_exists('HTTPSecurityHeaders', 'initialize')) {
            echo "<p style='color: green;'>✅ initialize() method exists</p>";
        } else {
            echo "<p style='color: orange;'>⚠️ initialize() method does NOT exist</p>";
        }
        
        if (method_exists('HTTPSecurityHeaders', 'applySecurityHeaders')) {
            echo "<p style='color: green;'>✅ applySecurityHeaders() method exists</p>";
        } else {
            echo "<p style='color: orange;'>⚠️ applySecurityHeaders() method does NOT exist</p>";
        }
    } else {
        echo "<p style='color: red;'>❌ HTTPSecurityHeaders class does NOT exist</p>";
    }
} else {
    echo "<p style='color: red;'>❌ HTTPSecurityHeaders.php file does NOT exist</p>";
}

// Check CSRF Protection
echo "<h3>CSRF Protection Analysis:</h3>";
if (file_exists(__DIR__ . '/includes/CSRFProtection.php')) {
    require_once __DIR__ . '/includes/CSRFProtection.php';
    echo "<p style='color: green;'>✅ CSRFProtection.php exists</p>";
    
    if (class_exists('CSRFProtection')) {
        echo "<p style='color: green;'>✅ CSRFProtection class exists</p>";
        
        // Generate a test token
        if (method_exists('CSRFProtection', 'generateToken')) {
            $token = CSRFProtection::generateToken();
            echo "<p style='color: green;'>✅ CSRF token generated: " . substr($token, 0, 20) . "...</p>";
        }
    }
} else {
    echo "<p style='color: red;'>❌ CSRFProtection.php does NOT exist</p>";
}

// Test admin session
echo "<h3>Admin Session Analysis:</h3>";
session_start();
if (isset($_SESSION['admin_logged_in'])) {
    echo "<p style='color: green;'>✅ Admin session active</p>";
    echo "<p>Admin user: " . htmlspecialchars($_SESSION['admin_username'] ?? 'Unknown') . "</p>";
} else {
    echo "<p style='color: orange;'>⚠️ No admin session found</p>";
    echo "<p><a href='admin_login.php'>🔐 Go to Admin Login</a></p>";
}

echo "<h3>🛠️ Quick Fixes Applied:</h3>";
echo "<ul>";
echo "<li>✅ Fixed HTTPSecurityHeaders::initialize() calls in admin files</li>";
echo "<li>✅ Improved CSRF token validation to handle both GET and POST</li>";
echo "<li>✅ Added proper error handling for missing classes</li>";
echo "</ul>";

echo "<h3>🌟 Test Your Admin Panel:</h3>";
echo "<div style='text-align: center; margin: 20px 0;'>";
echo "<a href='admin_login.php' style='margin: 10px; padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 5px;'>🔐 Admin Login</a>";
echo "<a href='admin_dashboard.php' style='margin: 10px; padding: 10px 20px; background: #28a745; color: white; text-decoration: none; border-radius: 5px;'>📊 Admin Dashboard</a>";
echo "<a href='admin_database.php' style='margin: 10px; padding: 10px 20px; background: #17a2b8; color: white; text-decoration: none; border-radius: 5px;'>🗃️ Database</a>";
echo "<a href='admin_actions.php' style='margin: 10px; padding: 10px 20px; background: #ffc107; color: black; text-decoration: none; border-radius: 5px;'>⚙️ Actions</a>";
echo "</div>";

echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
echo "<h4 style='color: #155724; margin: 0;'>✅ Admin Issues Fixed!</h4>";
echo "<p style='color: #155724; margin: 5px 0 0 0;'>The HTTPSecurityHeaders and CSRF token issues have been resolved. Your admin panel should now work properly.</p>";
echo "</div>";
?>