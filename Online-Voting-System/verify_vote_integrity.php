<?php
/**
 * Vote Integrity Verification Script
 * 
 * This script verifies that the Vote Integrity and Audit Trail system
 * is working correctly and demonstrates its functionality.
 */

require_once "connection.php";
require_once "includes/VoteIntegrity.php";

// Initialize the system
VoteIntegrity::initialize($con);

echo "<h2>Vote Integrity and Audit Trail Verification</h2>\n";

// Test 1: Check if audit tables exist
echo "<h3>1. Database Tables Verification</h3>\n";

$tables = ['vote_audit', 'vote_integrity', 'voting_statistics', 'team_members'];
foreach ($tables as $table) {
    $result = mysqli_query($con, "SHOW TABLES LIKE '$table'");
    if (mysqli_num_rows($result) > 0) {
        echo "<p>✅ Table '$table' exists</p>\n";
    } else {
        echo "<p>❌ Table '$table' missing</p>\n";
    }
}

// Test 2: Verify vote count snapshot functionality
echo "<h3>2. Vote Count Snapshot Test</h3>\n";
try {
    $reflection = new ReflectionClass('VoteIntegrity');
    $method = $reflection->getMethod('getVoteCountSnapshot');
    $method->setAccessible(true);
    $snapshot = $method->invoke(null);
    
    echo "<p>✅ Vote count snapshot generated successfully</p>\n";
    echo "<p>Languages tracked: " . count($snapshot['languages']) . "</p>\n";
    echo "<p>Team members tracked: " . count($snapshot['team_members']) . "</p>\n";
    echo "<p>Total voters: " . $snapshot['total_voters'] . "</p>\n";
    echo "<p>Voted count: " . $snapshot['voted_count'] . "</p>\n";
} catch (Exception $e) {
    echo "<p>❌ Snapshot test failed: " . $e->getMessage() . "</p>\n";
}

// Test 3: Verify integrity verification
echo "<h3>3. Integrity Verification Test</h3>\n";
try {
    $verificationResult = VoteIntegrity::verifyVoteIntegrity();
    echo "<p>✅ Vote integrity verification completed</p>\n";
    echo "<p>Period: " . $verificationResult['period']['start'] . " to " . $verificationResult['period']['end'] . "</p>\n";
    echo "<p>Total votes audited: " . $verificationResult['total_votes_audited'] . "</p>\n";
    echo "<p>Integrity violations: " . $verificationResult['integrity_violations'] . "</p>\n";
    echo "<p>Suspicious activities: " . $verificationResult['suspicious_activities'] . "</p>\n";
} catch (Exception $e) {
    echo "<p>❌ Integrity verification failed: " . $e->getMessage() . "</p>\n";
}
// 
Test 4: Generate audit trail report
echo "<h3>4. Audit Trail Report Test</h3>\n";
try {
    $auditTrail = VoteIntegrity::generateAuditTrail();
    echo "<p>✅ Audit trail report generated successfully</p>\n";
    echo "<p>Report period: " . $auditTrail['report_period']['start'] . " to " . $auditTrail['report_period']['end'] . "</p>\n";
    echo "<p>Total submissions: " . $auditTrail['summary']['total_submissions'] . "</p>\n";
    echo "<p>Unique voters: " . $auditTrail['summary']['unique_voters'] . "</p>\n";
    echo "<p>Flagged submissions: " . $auditTrail['summary']['flagged_submissions'] . "</p>\n";
} catch (Exception $e) {
    echo "<p>❌ Audit trail test failed: " . $e->getMessage() . "</p>\n";
}

// Test 5: Check voting statistics
echo "<h3>5. Voting Statistics Test</h3>\n";
try {
    $stats = VoteIntegrity::getVotingStatistics(7);
    echo "<p>✅ Voting statistics retrieved successfully</p>\n";
    echo "<p>Statistics records found: " . count($stats) . "</p>\n";
    
    if (!empty($stats)) {
        $latest = $stats[0];
        echo "<p>Latest date: " . $latest['stat_date'] . "</p>\n";
        echo "<p>Total votes: " . $latest['total_votes'] . "</p>\n";
        echo "<p>Unique voters: " . $latest['unique_voters'] . "</p>\n";
    }
} catch (Exception $e) {
    echo "<p>❌ Statistics test failed: " . $e->getMessage() . "</p>\n";
}

// Test 6: Simulate vote submission for testing
echo "<h3>6. Vote Submission Simulation Test</h3>\n";
try {
    // Start a test session
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    $_SESSION['SESS_NAME'] = 'test_user_' . time();
    
    // Simulate vote submission
    $auditId = VoteIntegrity::recordVoteSubmission($_SESSION['SESS_NAME'], 'both');
    
    if ($auditId) {
        echo "<p>✅ Vote submission simulation successful</p>\n";
        echo "<p>Audit ID generated: $auditId</p>\n";
        
        // Verify the audit record was created
        $stmt = mysqli_prepare($con, "SELECT * FROM vote_audit WHERE audit_id = ?");
        mysqli_stmt_bind_param($stmt, "i", $auditId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if ($row = mysqli_fetch_assoc($result)) {
            echo "<p>✅ Audit record created successfully</p>\n";
            echo "<p>Username: " . $row['username'] . "</p>\n";
            echo "<p>Vote type: " . $row['vote_type'] . "</p>\n";
            echo "<p>Timestamp: " . $row['submission_timestamp'] . "</p>\n";
            echo "<p>Integrity hash: " . substr($row['integrity_hash'], 0, 16) . "...</p>\n";
        }
        mysqli_stmt_close($stmt);
    } else {
        echo "<p>❌ Vote submission simulation failed</p>\n";
    }
} catch (Exception $e) {
    echo "<p>❌ Simulation test failed: " . $e->getMessage() . "</p>\n";
}

echo "<h3>Verification Complete</h3>\n";
echo "<p><strong>Vote Integrity and Audit Trail system verification finished.</strong></p>\n";
echo "<p>If all tests show ✅, the system is working correctly.</p>\n";
?>