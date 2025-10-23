<!DOCTYPE html>

<html>

<head>
	<?php require 'header.php'; ?>
</head>

<body>
	<div class="col-sm-12">
		<?php
		require_once "includes/SessionSecurity.php";
		require_once "includes/BruteForceProtection.php";
		include "connection.php";
		
		// Initialize secure session
		SessionSecurity::initializeSecureSession();
		
		if (SessionSecurity::isLoggedIn()) {
			header("Location: voter.php");
		}
		include "includes/CSRFProtection.php";
		include "includes/InputValidation.php";
		
		// Initialize brute force protection
		BruteForceProtection::initialize($con);
		
		// Check if we need to show CAPTCHA
		$showCaptcha = false;
		$captchaData = null;
		if (isset($_POST['username']) || isset($_GET['username'])) {
			$checkUsername = $_POST['username'] ?? $_GET['username'];
			$showCaptcha = BruteForceProtection::shouldShowCaptcha($checkUsername);
			if ($showCaptcha) {
				$captchaData = BruteForceProtection::generateCaptcha();
			}
		}
		?>
	</div>
	<br>
	<br>
	
	<div class="container" style="padding:100px;">
		<div class="row">
			<div class="col-sm-12" style="border:2px outset gray;">

				<div class="page-header text-center">
					<h3 class="specialHead">Login for Voting!.. </h3>
				</div>

				<?php global $nam;
				echo $nam; ?>
				<?php global $error;
				echo $error; ?>
				<br>
				<center>
					<font size="4">
						<form action="login_action.php" method="post" id="myform">
							<?php echo CSRFProtection::getTokenField(); ?>
							<?php echo InputValidation::generateHoneypotFields(); ?>
							
							<div class="form-group" style="margin-bottom: 15px;">
								<label for="username" style="display: block; margin-bottom: 5px; font-weight: bold;">Username:</label>
								<input type="text" name="username" id="username" 
									   value="<?php echo isset($_GET['username']) ? htmlspecialchars($_GET['username']) : ''; ?>" 
									   maxlength="50"
									   style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
							</div>
							
							<div class="form-group" style="margin-bottom: 15px;">
								<label for="password" style="display: block; margin-bottom: 5px; font-weight: bold;">Password:</label>
								<input type="password" name="password" id="password" value="" maxlength="128"
									   style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
							</div>
							
							<?php if ($showCaptcha && $captchaData): ?>
							<div id="captcha-section" style="background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 10px 0;">
								<strong style="color: #dc3545;">🔒 Security Verification Required</strong><br>
								<p style="font-size: 14px; color: #666;">Multiple failed attempts detected. Please solve this simple math problem:</p>
								<div style="font-size: 18px; font-weight: bold; color: #333; margin: 10px 0;">
									<?php echo $captchaData['question']; ?>
								</div>
								<input type="number" name="captcha_answer" placeholder="Enter answer" required 
									   style="padding: 8px; border: 2px solid #ddd; border-radius: 4px; width: 100px;">
							</div>
							<?php endif; ?>
							
							<input type="submit" name="login" value="Login">
						</form>
						<br>
						<a href="password_reset_request.php" style="font-size: 14px;">Forgot Password?</a>
					</font>
				</center>
				<br><br>
			</div>
		</div>
	</div>
	</div>
	<script type="text/javascript">
		var frmvalidator = new Validator("myform");
		frmvalidator.addValidation("username", "req", "Please Enter Username");
		frmvalidator.addValidation("username", "maxlen=50");
		frmvalidator.addValidation("password", "req", "Please Enter Password");
	</script>
</body>

</html>