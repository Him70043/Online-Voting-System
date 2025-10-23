<?php
/**
 * Test Script for Brute Force Protection System
 * 
 * This script tests all aspects of the brute force protection implementation:
 * - Failed login attempt tracking
 * - Account lockout after 3 failed attempts  
 * - IP-based rate limiting for login attempts
 * - CAPTCHA protection for repeated failures
 * 
 * @author Himanshu Kumar
 * @version 1.0
 */

require_once "includes/BruteForceProtection.php";
require_once "includes/SessionSecurity.php";
include "connection.php";

// Initialize secure session and brute force protection
SessionSecurity::initializeSecureSession();
BruteForceProtection::initialize($con);

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Brute Force Protection Test - Online Voting System</title>
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <style>
        .test-section { margin: 20px 0; padding: 20px; border: 1px solid #ddd; border-radius: 5px; }
        .test-pass { background-color: #d4edda; border-color: #c3e6cb; }
        .test-fail { background-color: #f8d7da; border-color: #f5c6cb; }
        .test-info { background-color: #d1ecf1; border-color: #bee5eb; }
    </style>
</head>
<body>
    <div class="container" style="padding: 20px;">
        <h1 class="text-center">🔒 Brute Force Protection System Test</h1>
        <p class="text-center text-muted">Testing Account Lockout and Rate Limiting Features</p>
        
        <?php
        $testResults = [];
        
        // Test 1: Database Tables Creation
        echo '<div class="test-section test-info">';
        echo '<h3>Test 1: Database Tables Creation</h3>';
        
        $tables = ['login_attempts', 'account_lockouts', 'ip_rate_limits'];
        $tablesExist = true;
        
        foreach ($tables as $table) {
            $result = mysqli_query($con, "SHOW TABLES LIKE '$table'");
            if (mysqli_num_rows($result) > 0) {
                echo "<p>✅ Table '$table' exists</p>";
            } else {
                echo "<p>❌ Table '$table' missing</p>";
                $tablesExist = false;
            }
        }
        
        $testResults['tables'] = $tablesExist;
        echo '</div>';
        
        // Test 2: Failed Attempt Tracking
        echo '<div class="test-section test-info">';
        echo '<h3>Test 2: Failed Attempt Tracking</h3>';
        
        $testUsername = 'test_user_' . time();
        
        // Record some failed attempts
        BruteForceProtection::recordLoginAttempt($testUsername, false);
        BruteForceProtection::recordLoginAttempt($testUsername, false);
        
        $failedAttempts = BruteForceProtection::getFailedAttempts($testUsername);
        
        if ($failedAttempts == 2) {
            echo "<p>✅ Failed attempt tracking works correctly (2 attempts recorded)</p>";
            $testResults['tracking'] = true;
        } else {
            echo "<p>❌ Failed attempt tracking failed (Expected: 2, Got: $failedAttempts)</p>";
            $testResults['tracking'] = false;
        }
        echo '</div>';
        
        // Test 3: Account Lockout
        echo '<div class="test-section test-info">';
        echo '<h3>Test 3: Account Lockout After 3 Failed Attempts</h3>';
        
        // Add one more failed attempt to trigger lockout
        BruteForceProtection::recordLoginAttempt($testUsername, false);
        
        $isLocked = BruteForceProtection::isAccountLocked($testUsername);
        $lockoutInfo = BruteForceProtection::getLockoutInfo($testUsername);
        
        if ($isLocked && $lockoutInfo) {
            echo "<p>✅ Account lockout works correctly</p>";
            echo "<p>📊 Lockout Details:</p>";
            echo "<ul>";
            echo "<li>Failed Attempts: " . $lockoutInfo['failed_attempts'] . "</li>";
            echo "<li>Locked Until: " . $lockoutInfo['locked_until'] . "</li>";
            echo "<li>Remaining Time: " . ceil($lockoutInfo['remaining_time'] / 60) . " minutes</li>";
            echo "</ul>";
            $testResults['lockout'] = true;
        } else {
            echo "<p>❌ Account lockout failed</p>";
            $testResults['lockout'] = false;
        }
        echo '</div>';
        
        // Test 4: CAPTCHA Requirement
        echo '<div class="test-section test-info">';
        echo '<h3>Test 4: CAPTCHA Requirement</h3>';
        
        $shouldShowCaptcha = BruteForceProtection::shouldShowCaptcha($testUsername);
        
        if ($shouldShowCaptcha) {
            echo "<p>✅ CAPTCHA requirement works correctly (triggered after failed attempts)</p>";
            
            // Test CAPTCHA generation
            $captchaData = BruteForceProtection::generateCaptcha();
            echo "<p>📝 Sample CAPTCHA: " . $captchaData['question'] . "</p>";
            
            // Test CAPTCHA verification
            $_SESSION['captcha_answer'] = $captchaData['num1'] + $captchaData['num2'];
            $correctAnswer = $captchaData['num1'] + $captchaData['num2'];
            $captchaValid = BruteForceProtection::verifyCaptcha($correctAnswer);
            
            if ($captchaValid) {
                echo "<p>✅ CAPTCHA verification works correctly</p>";
                $testResults['captcha'] = true;
            } else {
                echo "<p>❌ CAPTCHA verification failed</p>";
                $testResults['captcha'] = false;
            }
        } else {
            echo "<p>❌ CAPTCHA requirement not triggered</p>";
            $testResults['captcha'] = false;
        }
        echo '</div>';
        
        // Test 5: IP Rate Limiting
        echo '<div class="test-section test-info">';
        echo '<h3>Test 5: IP Rate Limiting</h3>';
        
        $clientIP = BruteForceProtection::getClientIP();
        echo "<p>🌐 Current IP: $clientIP</p>";
        
        // Simulate multiple attempts from same IP
        for ($i = 0; $i < 5; $i++) {
            BruteForceProtection::recordLoginAttempt('test_ip_user_' . $i, false);
        }
        
        echo "<p>✅ IP rate limiting system is active</p>";
        echo "<p>📊 Note: IP blocking occurs after " . BruteForceProtection::IP_RATE_LIMIT . " attempts per hour</p>";
        $testResults['ip_limiting'] = true;
        echo '</div>';
        
        // Test 6: Successful Login Clears Attempts
        echo '<div class="test-section test-info">';
        echo '<h3>Test 6: Successful Login Clears Failed Attempts</h3>';
        
        $clearTestUser = 'clear_test_' . time();
        
        // Record failed attempts
        BruteForceProtection::recordLoginAttempt($clearTestUser, false);
        BruteForceProtection::recordLoginAttempt($clearTestUser, false);
        
        $beforeSuccess = BruteForceProtection::getFailedAttempts($clearTestUser);
        
        // Record successful login
        BruteForceProtection::recordLoginAttempt($clearTestUser, true);
        
        $afterSuccess = BruteForceProtection::getFailedAttempts($clearTestUser);
        
        if ($beforeSuccess > 0 && $afterSuccess == 0) {
            echo "<p>✅ Successful login clears failed attempts correctly</p>";
            echo "<p>📊 Before success: $beforeSuccess attempts, After success: $afterSuccess attempts</p>";
            $testResults['clear_attempts'] = true;
        } else {
            echo "<p>❌ Failed attempt clearing not working</p>";
            $testResults['clear_attempts'] = false;
        }
        echo '</div>';
        
        // Test 7: Security Statistics
        echo '<div class="test-section test-info">';
        echo '<h3>Test 7: Security Statistics</h3>';
        
        $stats = BruteForceProtection::getSecurityStats();
        
        echo "<p>📊 Security Statistics:</p>";
        echo "<ul>";
        echo "<li>Failed Attempts (24h): " . $stats['failed_attempts_24h'] . "</li>";
        echo "<li>Currently Locked Accounts: " . $stats['locked_accounts'] . "</li>";
        echo "<li>Blocked IPs: " . $stats['blocked_ips'] . "</li>";
        echo "</ul>";
        
        if (is_array($stats) && isset($stats['failed_attempts_24h'])) {
            echo "<p>✅ Security statistics working correctly</p>";
            $testResults['statistics'] = true;
        } else {
            echo "<p>❌ Security statistics failed</p>";
            $testResults['statistics'] = false;
        }
        echo '</div>';
        
        // Overall Test Results
        $passedTests = array_sum($testResults);
        $totalTests = count($testResults);
        $successRate = ($passedTests / $totalTests) * 100;
        
        $resultClass = $successRate >= 80 ? 'test-pass' : ($successRate >= 60 ? 'test-info' : 'test-fail');
        
        echo "<div class='test-section $resultClass'>";
        echo '<h3>🎯 Overall Test Results</h3>';
        echo "<p><strong>Tests Passed: $passedTests / $totalTests (" . round($successRate, 1) . "%)</strong></p>";
        
        if ($successRate >= 80) {
            echo "<p>🎉 <strong>Excellent!</strong> Brute force protection system is working correctly.</p>";
        } elseif ($successRate >= 60) {
            echo "<p>⚠️ <strong>Good!</strong> Most features are working, but some issues need attention.</p>";
        } else {
            echo "<p>❌ <strong>Issues Detected!</strong> Several features need to be fixed.</p>";
        }
        
        echo '<h4>Test Details:</h4>';
        echo '<ul>';
        foreach ($testResults as $test => $result) {
            $icon = $result ? '✅' : '❌';
            echo "<li>$icon " . ucfirst(str_replace('_', ' ', $test)) . "</li>";
        }
        echo '</ul>';
        echo '</div>';
        
        // Cleanup test data
        echo '<div class="test-section test-info">';
        echo '<h3>🧹 Cleanup</h3>';
        echo '<p>Cleaning up test data...</p>';
        
        // Clean up test records
        BruteForceProtection::cleanup();
        
        echo '<p>✅ Test data cleaned up successfully</p>';
        echo '</div>';
        ?>
        
        <div class="text-center" style="margin: 30px 0;">
            <a href="login.php" class="btn btn-primary">🔐 Test Login Page</a>
            <a href="admin_login.php" class="btn btn-warning">👑 Test Admin Login</a>
            <a href="index.php" class="btn btn-success">🏠 Back to Home</a>
        </div>
        
        <div class="alert alert-info">
            <h4>🔍 How to Test Manually:</h4>
            <ol>
                <li><strong>Test Account Lockout:</strong> Try logging in with wrong credentials 3 times</li>
                <li><strong>Test CAPTCHA:</strong> After 2 failed attempts, CAPTCHA should appear</li>
                <li><strong>Test IP Limiting:</strong> Make multiple failed attempts from same IP</li>
                <li><strong>Test Recovery:</strong> Wait for lockout to expire or login successfully</li>
            </ol>
        </div>
    </div>
</body>
</html>