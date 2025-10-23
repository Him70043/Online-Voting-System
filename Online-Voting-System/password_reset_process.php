<?php
session_start();
include "connection.php";
require_once "includes/PasswordSecurity.php";
include "includes/CSRFProtection.php";

if (isset($_POST['reset_password'])) {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || !CSRFProtection::validateToken($_POST['csrf_token'])) {
        $error = "<center><h4><font color='#FF0000'>Security token validation failed. Please try again.</h4></center></font>";
        include "password_reset_form.php";
        exit();
    }
    $token = mysqli_real_escape_string($con, $_POST['token']);
    $newPassword = $_POST['new_password'];
    $confirmPassword = $_POST['confirm_password'];
    
    // Validate passwords match
    if ($newPassword !== $confirmPassword) {
        $error = "<center><h4><font color='#FF0000'>Passwords do not match!</h4></center></font>";
        include "password_reset_form.php";
        exit();
    }
    
    // Validate password complexity
    $passwordValidation = PasswordSecurity::validatePasswordComplexity($newPassword);
    if (!$passwordValidation['valid']) {
        $errorMessages = implode('<br>', $passwordValidation['errors']);
        $error = "<center><h4><font color='#FF0000'>Password Requirements:<br>$errorMessages</h4></center></font>";
        include "password_reset_form.php";
        exit();
    }
    
    // Verify token is still valid
    $stmt = mysqli_prepare($con, "SELECT username FROM loginusers WHERE reset_token = ? AND reset_token_expires > NOW() AND status = 'ACTIVE'");
    mysqli_stmt_bind_param($stmt, "s", $token);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if (mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);
        $username = $user['username'];
        
        // Hash the new password
        $hashedPassword = PasswordSecurity::hashPassword($newPassword);
        
        // Update password and clear reset token
        $updateStmt = mysqli_prepare($con, "UPDATE loginusers SET password = ?, reset_token = NULL, reset_token_expires = NULL, needs_password_reset = 0, password_changed_at = NOW() WHERE username = ?");
        mysqli_stmt_bind_param($updateStmt, "ss", $hashedPassword, $username);
        
        if (mysqli_stmt_execute($updateStmt)) {
            $message = "<center><h4><font color='#008000'>Password reset successfully! <a href='login.php'>Click here to login</a></h4></center></font>";
        } else {
            $error = "<center><h4><font color='#FF0000'>Error updating password. Please try again.</h4></center></font>";
        }
        
        mysqli_stmt_close($updateStmt);
    } else {
        $error = "<center><h4><font color='#FF0000'>Invalid or expired reset token.</h4></center></font>";
    }
    
    mysqli_stmt_close($stmt);
    
    // Display result
    echo "<!DOCTYPE html><html><head>";
    require 'header.php';
    echo "</head><body><div class='container' style='padding:100px;'><div class='row'><div class='col-sm-12' style='border:2px outset gray;'><div class='page-header text-center'>";
    echo isset($message) ? $message : $error;
    echo "</div></div></div></div></body></html>";
    
} else {
    header("Location: password_reset_request.php");
}
?>