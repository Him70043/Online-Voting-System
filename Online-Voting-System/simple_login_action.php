<?php
session_start();
include "connection.php";

if (isset($_POST['login'])) {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    
    // Basic validation
    if (empty($username) || empty($password)) {
        header("Location: simple_login.php?error=Please fill in all fields");
        exit();
    }
    
    // Check credentials
    $stmt = mysqli_prepare($con, "SELECT id, username, password, status FROM loginusers WHERE username = ?");
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if ($result && mysqli_num_rows($result) == 1) {
        $user = mysqli_fetch_assoc($result);
        
        // Check if account is active
        if ($user['status'] !== 'ACTIVE') {
            header("Location: simple_login.php?error=Account is not active");
            exit();
        }
        
        // Verify password (assuming MD5 for existing users)
        if (md5($password) === $user['password']) {
            // Login successful
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['SESS_NAME'] = $user['username'];
            $_SESSION['username'] = $user['username'];
            
            // Redirect to voter page
            header("Location: simple_voter.php");
            exit();
        } else {
            header("Location: simple_login.php?error=Invalid username or password");
            exit();
        }
    } else {
        header("Location: simple_login.php?error=Invalid username or password");
        exit();
    }
} else {
    header("Location: simple_login.php");
    exit();
}
?>