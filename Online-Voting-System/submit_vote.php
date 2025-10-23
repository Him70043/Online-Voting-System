<?php
include "connection.php";
include "includes/CSRFProtection.php";
include "includes/XSSProtection.php";
include "includes/InputValidation.php";
require_once "includes/SessionSecurity.php";
require_once "includes/SecurityLogger.php";
require_once "includes/VoteIntegrity.php";

// Initialize secure session and validate authentication
SessionSecurity::initializeSecureSession();
if (!SessionSecurity::isLoggedIn()) {
    header("Location: login.php");
    exit();
}

// Initialize security logger and vote integrity system
SecurityLogger::initialize($con);
VoteIntegrity::initialize($con);

// Verify CSRF token and request method
if (!CSRFProtection::verifyRequest('POST', 'voter.php')) {
    exit(); // CSRFProtection::verifyRequest handles the redirect
}

// Check submission rate limiting
$clientIP = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
$rateLimitKey = 'vote_' . $clientIP . '_' . $_SESSION['SESS_NAME'];
if (!InputValidation::checkSubmissionRate($rateLimitKey, 3, 60)) { // 3 attempts per minute
    SecurityLogger::logSecurityEvent('RATE_LIMIT_EXCEEDED', 'HIGH', "Vote submission rate limit exceeded for user: " . $_SESSION['SESS_NAME']);
    $error = "<center><h4><font color='#FF0000'>Too many submission attempts. Please wait a moment before trying again.</h4></center></font>";
    include "voter.php";
    exit();
}

// Enhanced input validation for voting form
$validation = InputValidation::validateForm($_POST, 'voting');
if (!$validation['valid']) {
    $errorMessages = implode('<br>', $validation['errors']);
    $error = "<center><h4><font color='#FF0000'>$errorMessages</h4></center></font>";
    include "voter.php";
    exit();
}

// Use validated and sanitized data
$validatedData = $validation['data'];

$sess = $_SESSION['SESS_NAME'];

// Check if user has already voted using prepared statement
$stmt_check = mysqli_prepare($con, 'SELECT * FROM voters WHERE username = ? AND status = "VOTED"');
mysqli_stmt_bind_param($stmt_check, "s", $_SESSION['SESS_NAME']);
mysqli_stmt_execute($stmt_check);
$result = mysqli_stmt_get_result($stmt_check);

if (mysqli_num_rows($result) > 0) {
	$msg = "<center><h4><font color='#FF0000'>You have already voted, No need to vote again</h4></center></font>";
	mysqli_stmt_close($stmt_check);
	include 'voter.php';
	exit();
} else {
	mysqli_stmt_close($stmt_check);
	$success = true;
	
	// Process language vote if provided using prepared statement
	if (isset($validatedData['lan'])) {
		$stmt1 = mysqli_prepare($con, 'UPDATE languages SET votecount = votecount + 1 WHERE fullname = ?');
		mysqli_stmt_bind_param($stmt1, "s", $validatedData['lan']);
		$sql1 = mysqli_stmt_execute($stmt1);
		mysqli_stmt_close($stmt1);
		
		$stmt3 = mysqli_prepare($con, 'UPDATE voters SET voted = ? WHERE username = ?');
		mysqli_stmt_bind_param($stmt3, "ss", $validatedData['lan'], $_SESSION['SESS_NAME']);
		$sql3 = mysqli_stmt_execute($stmt3);
		mysqli_stmt_close($stmt3);
		
		if (!$sql1 || !$sql3) $success = false;
	}
	
	// Process team member vote if provided using prepared statement
	if (isset($validatedData['team'])) {
		$stmt4 = mysqli_prepare($con, 'UPDATE team_members SET votecount = votecount + 1 WHERE fullname = ?');
		mysqli_stmt_bind_param($stmt4, "s", $validatedData['team']);
		$sql4 = mysqli_stmt_execute($stmt4);
		mysqli_stmt_close($stmt4);
		
		$stmt5 = mysqli_prepare($con, 'UPDATE voters SET team_voted = ? WHERE username = ?');
		mysqli_stmt_bind_param($stmt5, "ss", $validatedData['team'], $_SESSION['SESS_NAME']);
		$sql5 = mysqli_stmt_execute($stmt5);
		mysqli_stmt_close($stmt5);
		
		if (!$sql4 || !$sql5) $success = false;
	}
	
	// Mark user as voted using prepared statement
	$stmt2 = mysqli_prepare($con, 'UPDATE voters SET status = "VOTED" WHERE username = ?');
	mysqli_stmt_bind_param($stmt2, "s", $_SESSION['SESS_NAME']);
	$sql2 = mysqli_stmt_execute($stmt2);
	mysqli_stmt_close($stmt2);
	
	if (!$sql2) $success = false;
	
	if (!$success) {
		die("Error on mysql query" . mysqli_error($con));
	} else {
		// Determine vote type for logging
		$voteType = 'both';
		if (isset($validatedData['lan']) && !isset($validatedData['team'])) {
			$voteType = 'language';
		} elseif (!isset($validatedData['lan']) && isset($validatedData['team'])) {
			$voteType = 'team';
		}
		
		// Record vote submission with integrity tracking and audit trail
		$auditId = VoteIntegrity::recordVoteSubmission($_SESSION['SESS_NAME'], $voteType, $validatedData);
		
		// Log voting activity (privacy-preserving)
		SecurityLogger::logVotingActivity($_SESSION['SESS_NAME'], $voteType);
		
		$msg = "<center><h4><font color='#00AA00'>🎉 Congratulations! You have successfully cast your vote(s). Thank you for participating!</h4></center></font>";
		include 'voter.php';
		exit();
	}
}
