<?php
require_once "includes/SessionSecurity.php";

// Initialize secure session and validate
if (!SessionSecurity::isLoggedIn()) {
	header("Location: login.php");
	exit();
}
?>