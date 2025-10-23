<?php
/**
 * Security Logging Test Script
 * Tests all security logging functionality
 */

require_once "includes/SecurityLogger.php";
include "connection.php";

// Initialize security logger
SecurityLogger::initialize($con);

echo "<h2>Security Logging Test Results</h2>";

// Test 1: Authentication Logging
echo "<h3>Test 1: Authentication Logging</h3>";
SecurityLogger::logAuthenticationAttempt('test_user', true, 'user');
SecurityLogger::logAuthenticationAttempt('test_admin', false, 'admin', 'Invalid credentials');
echo "✅ Authentication attempts logged successfully<br>";

// Test 2: Voting Activity Logging
echo "<h3>Test 2: Voting Activity Logging</h3>";
SecurityLogger::logVotingActivity('test_voter', 'language');
SecurityLogger::logVotingActivity('test_voter2', 'both');
echo "✅ Voting activities logged successfully<br>";

// Test 3: Admin Action Logging
echo "<h3>Test 3: Admin Action Logging</h3>";
SecurityLogger::logAdminAction(
    'admin_test', 
    'delete_user', 
    'Test admin action - deleted user test_user', 
    'loginusers', 
    123,
    ['username' => 'test_user'],
    null
);
echo "✅ Admin actions logged successfully<br>";

// Test 4: Security Event Logging
echo "<h3>Test 4: Security Event Logging</h3>";
SecurityLogger::logSecurityEvent('test_event', 'medium', 'Test security event for logging verification');
echo "✅ Security events logged successfully<br>";

// Test 5: Statistics Retrieval
echo "<h3>Test 5: Statistics Retrieval</h3>";
$stats = SecurityLogger::getSecurityStatistics(7);
if ($stats) {
    echo "✅ Security statistics retrieved successfully<br>";
    echo "<pre>" . print_r($stats, true) . "</pre>";
} else {
    echo "⚠️ No statistics available yet<br>";
}

// Test 6: Recent Events Retrieval
echo "<h3>Test 6: Recent Events Retrieval</h3>";
$events = SecurityLogger::getRecentSecurityEvents(10);
if (!empty($events)) {
    echo "✅ Recent events retrieved successfully (" . count($events) . " events)<br>";
    foreach ($events as $event) {
        echo "- " . $event['timestamp'] . ": " . $event['event_type'] . " (" . $event['severity'] . ")<br>";
    }
} else {
    echo "⚠️ No recent events found<br>";
}

// Test 7: Authentication Logs Retrieval
echo "<h3>Test 7: Authentication Logs Retrieval</h3>";
$authLogs = SecurityLogger::getAuthenticationLogs(10);
if (!empty($authLogs)) {
    echo "✅ Authentication logs retrieved successfully (" . count($authLogs) . " logs)<br>";
    foreach ($authLogs as $log) {
        echo "- " . $log['timestamp'] . ": " . $log['username'] . " (" . ($log['success'] ? 'Success' : 'Failed') . ")<br>";
    }
} else {
    echo "⚠️ No authentication logs found<br>";
}

// Test 8: Database Tables Check
echo "<h3>Test 8: Database Tables Check</h3>";
$tables = ['security_auth_logs', 'security_vote_logs', 'security_admin_logs', 'security_events'];
foreach ($tables as $table) {
    $result = mysqli_query($con, "SHOW TABLES LIKE '$table'");
    if (mysqli_num_rows($result) > 0) {
        echo "✅ Table '$table' exists<br>";
        
        // Check if table has data
        $countResult = mysqli_query($con, "SELECT COUNT(*) as count FROM $table");
        $count = mysqli_fetch_assoc($countResult)['count'];
        echo "&nbsp;&nbsp;&nbsp;Records: $count<br>";
    } else {
        echo "❌ Table '$table' does not exist<br>";
    }
}

// Test 9: Log Directory Check
echo "<h3>Test 9: Log Directory and Files Check</h3>";
$logDir = 'logs/security/';
if (is_dir($logDir)) {
    echo "✅ Log directory exists: $logDir<br>";
    $files = glob($logDir . '*.log');
    if (!empty($files)) {
        echo "✅ Log files found (" . count($files) . " files):<br>";
        foreach ($files as $file) {
            echo "&nbsp;&nbsp;&nbsp;- " . basename($file) . " (" . filesize($file) . " bytes)<br>";
        }
    } else {
        echo "⚠️ No log files found in directory<br>";
    }
} else {
    echo "❌ Log directory does not exist<br>";
}

echo "<h3>✅ Security Logging Test Complete!</h3>";
echo "<p><a href='security_dashboard.php'>View Security Dashboard</a> | <a href='admin_dashboard.php'>Back to Admin</a></p>";
?>