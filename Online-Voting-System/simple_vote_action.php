<?php
session_start();
include "connection.php";

// Check if user is logged in
if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    header("Location: simple_login.php?error=Please login to vote");
    exit();
}

if (isset($_POST['vote'])) {
    $username = $_SESSION['SESS_NAME'];
    $vote_choice = (int)$_POST['vote'];
    
    // Check if user has already voted
    $stmt = mysqli_prepare($con, "SELECT status FROM voters WHERE username = ?");
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $voter_info = mysqli_fetch_assoc($result);
    
    if ($voter_info && $voter_info['status'] === 'VOTED') {
        header("Location: simple_voter.php?error=You have already voted");
        exit();
    }
    
    // Validate vote choice
    $stmt = mysqli_prepare($con, "SELECT lan_id FROM languages WHERE lan_id = ?");
    mysqli_stmt_bind_param($stmt, "i", $vote_choice);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if (mysqli_num_rows($result) == 0) {
        header("Location: simple_voter.php?error=Invalid vote choice");
        exit();
    }
    
    // Start transaction
    mysqli_autocommit($con, FALSE);
    
    try {
        // Update vote count
        $stmt1 = mysqli_prepare($con, "UPDATE languages SET votecount = votecount + 1 WHERE lan_id = ?");
        mysqli_stmt_bind_param($stmt1, "i", $vote_choice);
        $result1 = mysqli_stmt_execute($stmt1);
        
        // Update voter status
        $stmt2 = mysqli_prepare($con, "UPDATE voters SET status = 'VOTED' WHERE username = ?");
        mysqli_stmt_bind_param($stmt2, "s", $username);
        $result2 = mysqli_stmt_execute($stmt2);
        
        if ($result1 && $result2) {
            // Commit transaction
            mysqli_commit($con);
            header("Location: simple_voter.php?success=Vote cast successfully!");
            exit();
        } else {
            // Rollback transaction
            mysqli_rollback($con);
            header("Location: simple_voter.php?error=Failed to cast vote. Please try again.");
            exit();
        }
    } catch (Exception $e) {
        // Rollback transaction
        mysqli_rollback($con);
        header("Location: simple_voter.php?error=An error occurred while casting your vote");
        exit();
    } finally {
        // Restore autocommit
        mysqli_autocommit($con, TRUE);
    }
} else {
    header("Location: simple_voter.php");
    exit();
}
?>