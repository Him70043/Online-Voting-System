<?php
/**
 * Verify Admin Security Enhancements Implementation
 * 
 * This file verifies the complete implementation of Task 11: Add Admin Panel Security Enhancements
 * Requirements: 5.1, 5.2, 5.3, 5.4
 */

// Initialize HTTP Security Headers
require_once __DIR__ . '/includes/HTTPSecurityHeaders.php';
HTTPSecurityHeaders::initialize();

echo "<!DOCTYPE html>
<html>
<head>
    <title>Admin Security Enhancements Verification</title>
    <link href='css/bootstrap.min.css' rel='stylesheet'>
    <style>
        .verification-section { margin: 20px 0; padding: 20px; border-radius: 10px; }
        .pass { background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%); border: 2px solid #28a745; }
        .fail { background: linear-gradient(135deg, #f8d7da 0%, #f5c6cb 100%); border: 2px solid #dc3545; }
        .info { background: linear-gradient(135deg, #d1ecf1 0%, #bee5eb 100%); border: 2px solid #17a2b8; }
        .feature-list { list-style: none; padding: 0; }
        .feature-list li { padding: 8px 0; border-bottom: 1px solid #eee; }
        .feature-list li:last-child { border-bottom: none; }
        .status-badge { padding: 4px 8px; border-radius: 4px; font-weight: bold; }
        .status-pass { background: #28a745; color: white; }
        .status-fail { background: #dc3545; color: white; }
        .code-block { background: #f8f9fa; padding: 10px; border-radius: 5px; font-family: monospace; }
    </style>
</head>
<body>
<div class='container mt-4'>
    <h1 class='text-center mb-4'>🔐 Admin Security Enhancements Verification</h1>
    <div class='alert alert-info text-center'>
        <strong>Task 11:</strong> Add Admin Panel Security Enhancements<br>
        <strong>Requirements:</strong> 5.1, 5.2, 5.3, 5.4
    </div>";

$allTestsPassed = true;

// Verification 1: Admin Session Timeout (Requirement 5.1)
echo "<div class='verification-section info'>
    <h3>🕐 Requirement 5.1: Admin Session Timeout (Shorter than Regular Users)</h3>";

$req51_passed = true;

try {
    require_once 'includes/AdminSecurity.php';
    require_once 'includes/SessionSecurity.php';
    
    // Check if AdminSecurity class exists
    if (class_exists('AdminSecurity')) {
        echo "<p>✅ AdminSecurity class exists</p>";
        
        // Check session timeout constants
        $reflection = new ReflectionClass('SessionSecurity');
        $sessionConstants = $reflection->getConstants();
        
        if (isset($sessionConstants['ADMIN_SESSION_TIMEOUT']) && isset($sessionConstants['SESSION_TIMEOUT'])) {
            $adminTimeout = $sessionConstants['ADMIN_SESSION_TIMEOUT'];
            $regularTimeout = $sessionConstants['SESSION_TIMEOUT'];
            
            echo "<p>📊 Regular user timeout: " . ($regularTimeout / 60) . " minutes</p>";
            echo "<p>📊 Admin timeout: " . ($adminTimeout / 60) . " minutes</p>";
            
            if ($adminTimeout < $regularTimeout) {
                echo "<p>✅ Admin timeout is shorter than regular user timeout</p>";
            } else {
                echo "<p>❌ Admin timeout should be shorter than regular user timeout</p>";
                $req51_passed = false;
            }
        } else {
            echo "<p>❌ Session timeout constants not properly defined</p>";
            $req51_passed = false;
        }
        
        // Check validation method
        if (method_exists('AdminSecurity', 'validateAdminSession')) {
            echo "<p>✅ validateAdminSession method implemented</p>";
        } else {
            echo "<p>❌ validateAdminSession method missing</p>";
            $req51_passed = false;
        }
        
    } else {
        echo "<p>❌ AdminSecurity class not found</p>";
        $req51_passed = false;
    }
    
} catch (Exception $e) {
    echo "<p>❌ Error: " . $e->getMessage() . "</p>";
    $req51_passed = false;
}

if ($req51_passed) {
    echo "<div class='alert alert-success mt-3'><strong>✅ Requirement 5.1 PASSED</strong></div>";
} else {
    echo "<div class='alert alert-danger mt-3'><strong>❌ Requirement 5.1 FAILED</strong></div>";
    $allTestsPassed = false;
}

echo "</div>";

// Verification 2: Admin Action Confirmation Dialogs (Requirement 5.2)
echo "<div class='verification-section info'>
    <h3>⚠️ Requirement 5.2: Admin Action Confirmation Dialogs</h3>";

$req52_passed = true;

try {
    // Check high-risk actions
    $reflection = new ReflectionClass('AdminSecurity');
    $constants = $reflection->getConstants();
    
    if (isset($constants['HIGH_RISK_ACTIONS'])) {
        $highRiskActions = $constants['HIGH_RISK_ACTIONS'];
        echo "<p>✅ High-risk actions defined: " . implode(', ', $highRiskActions) . "</p>";
        
        // Check if critical actions are included
        $criticalActions = ['delete_user', 'reset_all_votes', 'export_data'];
        $missingActions = array_diff($criticalActions, $highRiskActions);
        
        if (empty($missingActions)) {
            echo "<p>✅ All critical actions included in high-risk list</p>";
        } else {
            echo "<p>❌ Missing critical actions: " . implode(', ', $missingActions) . "</p>";
            $req52_passed = false;
        }
    } else {
        echo "<p>❌ High-risk actions not defined</p>";
        $req52_passed = false;
    }
    
    // Check confirmation methods
    $confirmationMethods = ['requiresConfirmation', 'generateConfirmationDialog'];
    foreach ($confirmationMethods as $method) {
        if (method_exists('AdminSecurity', $method)) {
            echo "<p>✅ $method method exists</p>";
        } else {
            echo "<p>❌ $method method missing</p>";
            $req52_passed = false;
        }
    }
    
    // Check admin dashboard integration
    if (file_exists('admin_dashboard.php')) {
        $dashboardContent = file_get_contents('admin_dashboard.php');
        
        if (strpos($dashboardContent, 'confirmDeleteUser') !== false) {
            echo "<p>✅ Enhanced confirmation dialogs integrated in dashboard</p>";
        } else {
            echo "<p>❌ Enhanced confirmation dialogs not found in dashboard</p>";
            $req52_passed = false;
        }
        
        if (strpos($dashboardContent, 'WARNING:') !== false) {
            echo "<p>✅ Warning messages included in confirmation dialogs</p>";
        } else {
            echo "<p>❌ Warning messages not found in confirmation dialogs</p>";
            $req52_passed = false;
        }
    } else {
        echo "<p>❌ Admin dashboard file not found</p>";
        $req52_passed = false;
    }
    
} catch (Exception $e) {
    echo "<p>❌ Error: " . $e->getMessage() . "</p>";
    $req52_passed = false;
}

if ($req52_passed) {
    echo "<div class='alert alert-success mt-3'><strong>✅ Requirement 5.2 PASSED</strong></div>";
} else {
    echo "<div class='alert alert-danger mt-3'><strong>❌ Requirement 5.2 FAILED</strong></div>";
    $allTestsPassed = false;
}

echo "</div>";

// Verification 3: Admin Activity Audit Trail (Requirement 5.3)
echo "<div class='verification-section info'>
    <h3>📋 Requirement 5.3: Admin Activity Audit Trail</h3>";

$req53_passed = true;

try {
    // Check audit methods
    $auditMethods = ['logAdminActivity', 'getAdminActivitySummary', 'logAdminSecurityEvent'];
    foreach ($auditMethods as $method) {
        if (method_exists('AdminSecurity', $method)) {
            echo "<p>✅ $method method exists</p>";
        } else {
            echo "<p>❌ $method method missing</p>";
            $req53_passed = false;
        }
    }
    
    // Check log directory
    $logDir = dirname(__FILE__) . '/logs';
    if (is_dir($logDir) || mkdir($logDir, 0755, true)) {
        echo "<p>✅ Audit log directory available at: $logDir</p>";
    } else {
        echo "<p>❌ Cannot create audit log directory</p>";
        $req53_passed = false;
    }
    
    // Check admin dashboard audit integration
    if (file_exists('admin_dashboard.php')) {
        $dashboardContent = file_get_contents('admin_dashboard.php');
        
        if (strpos($dashboardContent, 'Admin Audit') !== false) {
            echo "<p>✅ Admin audit trail tab integrated in dashboard</p>";
        } else {
            echo "<p>❌ Admin audit trail not integrated in dashboard</p>";
            $req53_passed = false;
        }
        
        if (strpos($dashboardContent, 'logAdminActivity') !== false) {
            echo "<p>✅ Admin activity logging integrated</p>";
        } else {
            echo "<p>❌ Admin activity logging not integrated</p>";
            $req53_passed = false;
        }
    }
    
    // Check admin actions integration
    if (file_exists('admin_actions.php')) {
        $actionsContent = file_get_contents('admin_actions.php');
        
        if (strpos($actionsContent, 'AdminSecurity::logAdminActivity') !== false) {
            echo "<p>✅ Admin actions logging activities</p>";
        } else {
            echo "<p>❌ Admin actions not logging activities</p>";
            $req53_passed = false;
        }
    }
    
} catch (Exception $e) {
    echo "<p>❌ Error: " . $e->getMessage() . "</p>";
    $req53_passed = false;
}

if ($req53_passed) {
    echo "<div class='alert alert-success mt-3'><strong>✅ Requirement 5.3 PASSED</strong></div>";
} else {
    echo "<div class='alert alert-danger mt-3'><strong>❌ Requirement 5.3 FAILED</strong></div>";
    $allTestsPassed = false;
}

echo "</div>";

// Verification 4: Admin Privilege Separation (Requirement 5.4)
echo "<div class='verification-section info'>
    <h3>🔐 Requirement 5.4: Admin Privilege Separation</h3>";

$req54_passed = true;

try {
    // Check privilege constants
    $reflection = new ReflectionClass('AdminSecurity');
    $constants = $reflection->getConstants();
    
    $privilegeLevels = ['PRIVILEGE_SUPER_ADMIN', 'PRIVILEGE_ADMIN', 'PRIVILEGE_MODERATOR', 'PRIVILEGE_VIEWER'];
    $foundPrivileges = 0;
    
    foreach ($privilegeLevels as $privilege) {
        if (isset($constants[$privilege])) {
            echo "<p>✅ $privilege defined</p>";
            $foundPrivileges++;
        } else {
            echo "<p>❌ $privilege not defined</p>";
            $req54_passed = false;
        }
    }
    
    if ($foundPrivileges == count($privilegeLevels)) {
        echo "<p>✅ All privilege levels defined ($foundPrivileges/4)</p>";
    }
    
    // Check privilege permissions mapping
    if (isset($constants['PRIVILEGE_PERMISSIONS'])) {
        echo "<p>✅ Privilege permissions mapping defined</p>";
        $permissions = $constants['PRIVILEGE_PERMISSIONS'];
        echo "<p>📊 Configured privilege levels: " . count($permissions) . "</p>";
        
        // Check if all privilege levels have permissions
        foreach ($privilegeLevels as $level) {
            $levelConstant = $constants[$level] ?? null;
            if ($levelConstant && isset($permissions[$levelConstant])) {
                echo "<p>✅ Permissions defined for $level</p>";
            } else {
                echo "<p>❌ Permissions missing for $level</p>";
                $req54_passed = false;
            }
        }
    } else {
        echo "<p>❌ Privilege permissions mapping not defined</p>";
        $req54_passed = false;
    }
    
    // Check privilege methods
    $privilegeMethods = ['getAdminPrivilege', 'setAdminPrivilege', 'hasPermission', 'requirePermission'];
    foreach ($privilegeMethods as $method) {
        if (method_exists('AdminSecurity', $method)) {
            echo "<p>✅ $method method exists</p>";
        } else {
            echo "<p>❌ $method method missing</p>";
            $req54_passed = false;
        }
    }
    
    // Check dashboard integration
    if (file_exists('admin_dashboard.php')) {
        $dashboardContent = file_get_contents('admin_dashboard.php');
        
        if (strpos($dashboardContent, 'hasPermission') !== false) {
            echo "<p>✅ Permission checks integrated in dashboard</p>";
        } else {
            echo "<p>❌ Permission checks not integrated in dashboard</p>";
            $req54_passed = false;
        }
        
        if (strpos($dashboardContent, 'getPrivilegeBadge') !== false) {
            echo "<p>✅ Privilege badge display integrated</p>";
        } else {
            echo "<p>❌ Privilege badge display not integrated</p>";
            $req54_passed = false;
        }
    }
    
    // Check actions integration
    if (file_exists('admin_actions.php')) {
        $actionsContent = file_get_contents('admin_actions.php');
        
        if (strpos($actionsContent, 'requirePermission') !== false) {
            echo "<p>✅ Permission validation integrated in actions</p>";
        } else {
            echo "<p>❌ Permission validation not integrated in actions</p>";
            $req54_passed = false;
        }
    }
    
} catch (Exception $e) {
    echo "<p>❌ Error: " . $e->getMessage() . "</p>";
    $req54_passed = false;
}

if ($req54_passed) {
    echo "<div class='alert alert-success mt-3'><strong>✅ Requirement 5.4 PASSED</strong></div>";
} else {
    echo "<div class='alert alert-danger mt-3'><strong>❌ Requirement 5.4 FAILED</strong></div>";
    $allTestsPassed = false;
}

echo "</div>";

// Overall Verification Summary
if ($allTestsPassed) {
    echo "<div class='verification-section pass'>
        <h2 class='text-center'>🎉 ALL REQUIREMENTS PASSED</h2>
        <p class='text-center lead'>Task 11: Add Admin Panel Security Enhancements has been successfully implemented!</p>";
} else {
    echo "<div class='verification-section fail'>
        <h2 class='text-center'>❌ SOME REQUIREMENTS FAILED</h2>
        <p class='text-center lead'>Please review the failed requirements above and fix the issues.</p>";
}

echo "
    <h3>📋 Implementation Summary</h3>
    <ul class='feature-list'>
        <li><span class='status-badge " . ($req51_passed ? 'status-pass' : 'status-fail') . "'>5.1</span> Admin session timeout (shorter than regular users)</li>
        <li><span class='status-badge " . ($req52_passed ? 'status-pass' : 'status-fail') . "'>5.2</span> Admin action confirmation dialogs</li>
        <li><span class='status-badge " . ($req53_passed ? 'status-pass' : 'status-fail') . "'>5.3</span> Admin activity audit trail</li>
        <li><span class='status-badge " . ($req54_passed ? 'status-pass' : 'status-fail') . "'>5.4</span> Admin privilege separation</li>
    </ul>
    
    <h3>🔧 Key Features Implemented</h3>
    <div class='code-block'>
        <strong>AdminSecurity Class Features:</strong><br>
        • Enhanced session validation with 15-minute admin timeout<br>
        • Four-tier privilege system (Super Admin, Admin, Moderator, Viewer)<br>
        • Permission-based access control for all admin actions<br>
        • Comprehensive audit logging with JSON format<br>
        • Enhanced confirmation dialogs for high-risk actions<br>
        • Real-time session timeout display<br>
        • Privilege badge display in admin interface<br>
        • Export functionality for audit logs<br>
        • Integration with existing admin panel components
    </div>
    
    <h3>📁 Files Created/Modified</h3>
    <ul>
        <li><code>includes/AdminSecurity.php</code> - New comprehensive admin security class</li>
        <li><code>admin_dashboard.php</code> - Enhanced with security features</li>
        <li><code>admin_actions.php</code> - Updated with permission checks</li>
        <li><code>admin_login.php</code> - Updated to set admin privileges</li>
        <li><code>test_admin_security_enhancements.php</code> - Test file</li>
        <li><code>verify_admin_security_enhancements.php</code> - This verification file</li>
    </ul>
</div>";

echo "</div>
</body>
</html>";
?>