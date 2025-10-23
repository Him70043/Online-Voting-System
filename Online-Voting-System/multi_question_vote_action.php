<?php
session_start();
include "connection.php";

// Check if user is logged in
if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    header("Location: simple_login.php?error=Please login to vote");
    exit();
}

if (isset($_POST['vote']) && isset($_POST['question_type'])) {
    $username = $_SESSION['SESS_NAME'];
    $vote_choice = (int)$_POST['vote'];
    $question_type = $_POST['question_type'];
    
    // Start transaction
    mysqli_autocommit($con, FALSE);
    
    try {
        if ($question_type === 'language') {
            // Handle programming language vote
            
            // Check if user has already voted for languages
            $stmt = mysqli_prepare($con, "SELECT status FROM voters WHERE username = ?");
            mysqli_stmt_bind_param($stmt, "s", $username);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $voter_info = mysqli_fetch_assoc($result);
            
            if ($voter_info && $voter_info['status'] === 'VOTED') {
                header("Location: multi_question_voter.php?error=You have already voted for programming languages");
                exit();
            }
            
            // Validate vote choice
            $stmt = mysqli_prepare($con, "SELECT lan_id FROM languages WHERE lan_id = ?");
            mysqli_stmt_bind_param($stmt, "i", $vote_choice);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            
            if (mysqli_num_rows($result) == 0) {
                header("Location: multi_question_voter.php?error=Invalid language choice");
                exit();
            }
            
            // Update vote count
            $stmt1 = mysqli_prepare($con, "UPDATE languages SET votecount = votecount + 1 WHERE lan_id = ?");
            mysqli_stmt_bind_param($stmt1, "i", $vote_choice);
            $result1 = mysqli_stmt_execute($stmt1);
            
            // Update voter status
            $stmt2 = mysqli_prepare($con, "UPDATE voters SET status = 'VOTED' WHERE username = ?");
            mysqli_stmt_bind_param($stmt2, "s", $username);
            $result2 = mysqli_stmt_execute($stmt2);
            
            if ($result1 && $result2) {
                mysqli_commit($con);
                header("Location: multi_question_voter.php?success=Programming language vote cast successfully!");
                exit();
            } else {
                mysqli_rollback($con);
                header("Location: multi_question_voter.php?error=Failed to cast language vote");
                exit();
            }
            
        } elseif ($question_type === 'team') {
            // Handle team member vote
            
            // Create team_member_votes table if it doesn't exist
            mysqli_query($con, "CREATE TABLE IF NOT EXISTS team_member_votes (
                id INT AUTO_INCREMENT PRIMARY KEY,
                username VARCHAR(50) NOT NULL,
                member_id INT NOT NULL,
                voted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY unique_vote (username)
            )");
            
            // Check if user has already voted for team members
            $stmt = mysqli_prepare($con, "SELECT COUNT(*) as count FROM team_member_votes WHERE username = ?");
            mysqli_stmt_bind_param($stmt, "s", $username);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $team_vote_info = mysqli_fetch_assoc($result);
            
            if ($team_vote_info && $team_vote_info['count'] > 0) {
                header("Location: multi_question_voter.php?error=You have already voted for team members");
                exit();
            }
            
            // Validate vote choice
            $stmt = mysqli_prepare($con, "SELECT member_id FROM team_members WHERE member_id = ?");
            mysqli_stmt_bind_param($stmt, "i", $vote_choice);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            
            if (mysqli_num_rows($result) == 0) {
                header("Location: multi_question_voter.php?error=Invalid team member choice");
                exit();
            }
            
            // Update vote count
            $stmt1 = mysqli_prepare($con, "UPDATE team_members SET votecount = votecount + 1 WHERE member_id = ?");
            mysqli_stmt_bind_param($stmt1, "i", $vote_choice);
            $result1 = mysqli_stmt_execute($stmt1);
            
            // Record the vote
            $stmt2 = mysqli_prepare($con, "INSERT INTO team_member_votes (username, member_id) VALUES (?, ?)");
            mysqli_stmt_bind_param($stmt2, "si", $username, $vote_choice);
            $result2 = mysqli_stmt_execute($stmt2);
            
            if ($result1 && $result2) {
                mysqli_commit($con);
                header("Location: multi_question_voter.php?success=Team member vote cast successfully!");
                exit();
            } else {
                mysqli_rollback($con);
                header("Location: multi_question_voter.php?error=Failed to cast team member vote");
                exit();
            }
        }
        
    } catch (Exception $e) {
        mysqli_rollback($con);
        header("Location: multi_question_voter.php?error=An error occurred while casting your vote");
        exit();
    } finally {
        mysqli_autocommit($con, TRUE);
    }
} else {
    header("Location: multi_question_voter.php");
    exit();
}
?>