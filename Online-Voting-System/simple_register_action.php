<?php
session_start();
include "connection.php";

if (isset($_POST['register'])) {
    $firstname = trim($_POST['firstname']);
    $lastname = trim($_POST['lastname']);
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Basic validation
    if (empty($firstname) || empty($lastname) || empty($username) || empty($password)) {
        header("Location: simple_register.php?error=Please fill in all fields");
        exit();
    }
    
    if ($password !== $confirm_password) {
        header("Location: simple_register.php?error=Passwords do not match");
        exit();
    }
    
    if (strlen($password) < 6) {
        header("Location: simple_register.php?error=Password must be at least 6 characters long");
        exit();
    }
    
    // Check if username already exists
    $stmt = mysqli_prepare($con, "SELECT id FROM loginusers WHERE username = ?");
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if (mysqli_num_rows($result) > 0) {
        header("Location: simple_register.php?error=Username already exists. Please choose another.");
        exit();
    }
    
    // Hash password (using MD5 for compatibility with existing system)
    $hashed_password = md5($password);
    
    // Insert into loginusers table
    $stmt1 = mysqli_prepare($con, "INSERT INTO loginusers (username, password, rank, status) VALUES (?, ?, 'voter', 'ACTIVE')");
    mysqli_stmt_bind_param($stmt1, "ss", $username, $hashed_password);
    
    // Insert into voters table
    $stmt2 = mysqli_prepare($con, "INSERT INTO voters (firstname, lastname, username, status) VALUES (?, ?, ?, 'NOTVOTED')");
    mysqli_stmt_bind_param($stmt2, "sss", $firstname, $lastname, $username);
    
    if (mysqli_stmt_execute($stmt1) && mysqli_stmt_execute($stmt2)) {
        header("Location: simple_login.php?success=Registration successful! Please login with your credentials.");
        exit();
    } else {
        header("Location: simple_register.php?error=Registration failed. Please try again.");
        exit();
    }
} else {
    header("Location: simple_register.php");
    exit();
}
?>