<!DOCTYPE html>

<html>

<head>
	<?php
	require 'header.php';
	?>
</head>

<body>
	<div class="container" style="padding:100px;">
		<div class="row">
			<div class="col-sm-12" style="border:2px outset gray;">

				<div class="page-header text-center">
					<?php
					session_start();
					$captcha = "";
					include "connection.php";
					require_once "includes/PasswordSecurity.php";
					include "includes/CSRFProtection.php";
					include "includes/InputValidation.php";
					require_once "includes/SecurityLogger.php";
					
					// Initialize security logger
					SecurityLogger::initialize($con);
					
					if (isset($_POST['submit'])) {
						// Verify CSRF token
						if (!isset($_POST['csrf_token']) || !CSRFProtection::validateToken($_POST['csrf_token'])) {
							SecurityLogger::logSecurityEvent('CSRF_FAILURE', 'HIGH', 'Registration CSRF token validation failed');
							$nam = "<center><h4><font color='#FF0000'>Security token validation failed. Please try again.</h4></center></font>";
							include('register.php');
							exit();
						}
						
						// Check submission rate limiting
						$clientIP = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
						$rateLimitKey = 'register_' . $clientIP;
						if (!InputValidation::checkSubmissionRate($rateLimitKey, 3, 300)) { // 3 attempts per 5 minutes
							SecurityLogger::logSecurityEvent('RATE_LIMIT_EXCEEDED', 'HIGH', "Registration rate limit exceeded from IP: $clientIP");
							$nam = "<center><h4><font color='#FF0000'>Too many registration attempts. Please wait before trying again.</h4></center></font>";
							include('register.php');
							exit();
						}
						
						// Enhanced input validation
						$validation = InputValidation::validateForm($_POST, 'registration');
						if (!$validation['valid']) {
							$errorMessages = implode('<br>', $validation['errors']);
							SecurityLogger::logSecurityEvent('VALIDATION_FAILURE', 'MEDIUM', "Registration validation failed: " . implode(', ', $validation['errors']));
							$nam = "<center><h4><font color='#FF0000'>$errorMessages</h4></center></font>";
							include('register.php');
							exit();
						}
						
						// Use validated and sanitized data
						$validatedData = $validation['data'];
						$name = $validatedData['firstname'];
						$name2 = $validatedData['lastname'];
						$name3 = $validatedData['username'];
						$pass = $validatedData['password'];
						
						// Additional password complexity validation
						$passwordValidation = InputValidation::validatePasswordComplexity($pass);
						if (!$passwordValidation['valid']) {
							$errorMessages = implode('<br>', $passwordValidation['errors']);
							$nam = "<center><h4><font color='#FF0000'>Password Requirements:<br>$errorMessages</h4></center></font>";
							include('register.php');
							exit();
						}

						$stmt_check = mysqli_prepare($con, 'SELECT username FROM loginusers WHERE username = ?');
						mysqli_stmt_bind_param($stmt_check, "s", $name3);
						mysqli_stmt_execute($stmt_check);
						$sq = mysqli_stmt_get_result($stmt_check);
						$exist = mysqli_num_rows($sq);
						mysqli_stmt_close($stmt_check);

						if ($exist == 1) {
							SecurityLogger::logSecurityEvent('DUPLICATE_USERNAME', 'MEDIUM', "Registration attempt with existing username: $name3");
							$nam = "<center><h4><font color='#FF0000'>The username already exists, please choose another.</h4></center></font>";
							unset($username);
							include('register.php');
							exit();
						}
						
						// Hash the password securely
						$hashedPassword = PasswordSecurity::hashPassword($pass);
						
						// Use prepared statements for secure database insertion
						$stmt1 = mysqli_prepare($con, "INSERT INTO voters(firstname, lastname, username) VALUES(?, ?, ?)");
						mysqli_stmt_bind_param($stmt1, "sss", $name, $name2, $name3);
						$sql = mysqli_stmt_execute($stmt1);
						
						if (!$sql) {
							die(mysqli_error($con));
						}
						
						$stmt2 = mysqli_prepare($con, "INSERT INTO loginusers(username, password) VALUES(?, ?)");
						mysqli_stmt_bind_param($stmt2, "ss", $name3, $hashedPassword);
						$sql2 = mysqli_stmt_execute($stmt2);
						
						if (!$sql2) {
							SecurityLogger::logSecurityEvent('REGISTRATION_ERROR', 'HIGH', "Database error during registration for user: $name3");
							die(mysqli_error($con));
						} else {
							SecurityLogger::logSecurityEvent('USER_REGISTERED', 'INFO', "New user registered: $name3");
							echo " <h2 class='specialHead'> Successfully Registered!   </h2> <a href= 'login.php'> Click here to Login </a>";
						}
						
						mysqli_stmt_close($stmt1);
						mysqli_stmt_close($stmt2);
					} else {
						$error = "<center><h4><font color='#FF0000'>Registration Failed Due To Error !</h4></center></font>";
						include "register.php";
					}
					?>
				</div>
			</div>
		</div>
	</div>
</body>

</html>