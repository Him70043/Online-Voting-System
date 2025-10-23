<?php
session_start();
include "connection.php";
require_once "includes/PasswordSecurity.php";
include "includes/CSRFProtection.php";

if (isset($_POST['reset_request'])) {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || !CSRFProtection::validateToken($_POST['csrf_token'])) {
        $error = "<center><h4><font color='#FF0000'>Security token validation failed. Please try again.</h4></center></font>";
        include "password_reset_request.php";
        exit();
    }
    $username = mysqli_real_escape_string($con, $_POST['username']);
    
    // Check if user exists
    $stmt = mysqli_prepare($con, "SELECT id, username FROM loginusers WHERE username = ? AND status = 'ACTIVE'");
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if (mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);
        
        // Generate reset token
        $resetToken = PasswordSecurity::generateResetToken();
        $expiryTime = date('Y-m-d H:i:s', strtotime('+1 hour')); // Token expires in 1 hour
        
        // Store reset token in database
        $updateStmt = mysqli_prepare($con, "UPDATE loginusers SET reset_token = ?, reset_token_expires = ? WHERE username = ?");
        mysqli_stmt_bind_param($updateStmt, "sss", $resetToken, $expiryTime, $username);
        
        if (mysqli_stmt_execute($updateStmt)) {
            // In a real application, you would send this via email
            // For this demo, we'll display it (NOT recommended for production)
            $message = "<center><h4><font color='#008000'>Password reset requested successfully!<br>";
            $message .= "Reset Token: <strong>$resetToken</strong><br>";
            $message .= "Use this token to reset your password within 1 hour.<br>";
            $message .= "<a href='password_reset_form.php?token=$resetToken'>Click here to reset password</a>";
            $message .= "</h4></center></font>";
        } else {
            $error = "<center><h4><font color='#FF0000'>Error processing reset request. Please try again.</h4></center></font>";
        }
        
        mysqli_stmt_close($updateStmt);
    } else {
        // Don't reveal if username exists or not (security best practice)
        $message = "<center><h4><font color='#008000'>If the username exists, a reset token has been generated.</h4></center></font>";
    }
    
    mysqli_stmt_close($stmt);
    include "password_reset_request.php";
} else {
    header("Location: password_reset_request.php");
}
?>