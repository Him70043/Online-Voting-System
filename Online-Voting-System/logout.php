<?php
require_once __DIR__ . "/includes/SessionSecurity.php";

// Initialize session to check current state
SessionSecurity::initializeSecureSession();

// Properly destroy session
SessionSecurity::destroySession();

// Redirect to login page
header("Location: login.php");
exit();
?>
