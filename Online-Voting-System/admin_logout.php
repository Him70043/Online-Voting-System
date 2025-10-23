<?php
// Initialize HTTP Security Headers
require_once __DIR__ . '/includes/HTTPSecurityHeaders.php';
HTTPSecurityHeaders::initialize();

require_once "includes/SessionSecurity.php";

// Initialize session to check current state
SessionSecurity::initializeSecureSession();

// Properly destroy admin session
SessionSecurity::destroySession();

// Redirect to admin login page
header("Location: admin_login.php");
exit();
?>