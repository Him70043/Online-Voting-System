<!DOCTYPE html>

<html>

<head>
	<?php require 'header.php'; ?>
	<script src='https://www.google.com/recaptcha/api.js'></script>
</head>

<body>
	<div class="col-sm-12">
		<?php
		require_once __DIR__ . "/includes/SessionSecurity.php";
		
		// Initialize secure session
		SessionSecurity::initializeSecureSession();
		
		if (SessionSecurity::isLoggedIn()) {
			header("Location: voter.php");
		}
		include "includes/CSRFProtection.php";
		include "includes/InputValidation.php";
		?>
	</div>
	<br>
	<br>

	<div class="col-sm-12">
		<?php global $nam;
		echo $nam; ?>
		<?php global $error;
		echo $error; ?>
	</div>

	<div class="container" style="padding:100px;">
		<div class="row">
			<div class="col-sm-12" style="border:2px outset gray;">
				<div class="page-header text-center">
					<h3 class="specialHead">Register!.. </h3>
				</div>

				<center>
					<font size="4">
						<form action="reg_action.php" method="post" id="myform">
							<?php echo CSRFProtection::getTokenField(); ?>
							<?php echo InputValidation::generateHoneypotFields(); ?>
							
							<div class="form-group" style="margin-bottom: 15px;">
								<label for="firstname" style="display: block; margin-bottom: 5px; font-weight: bold;">Firstname:</label>
								<input type="text" name="firstname" id="firstname" value="" 
									   maxlength="50" pattern="[a-zA-Z\s\'-]+" 
									   title="Only letters, spaces, apostrophes, and hyphens allowed"
									   style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;" />
							</div>
							
							<div class="form-group" style="margin-bottom: 15px;">
								<label for="lastname" style="display: block; margin-bottom: 5px; font-weight: bold;">Lastname:</label>
								<input type="text" name="lastname" id="lastname" value="" 
									   maxlength="50" pattern="[a-zA-Z\s\'-]+" 
									   title="Only letters, spaces, apostrophes, and hyphens allowed"
									   style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;" />
							</div>
							
							<div class="form-group" style="margin-bottom: 15px;">
								<label for="username" style="display: block; margin-bottom: 5px; font-weight: bold;">Username:</label>
								<input type="text" name="username" id="username" value="" 
									   maxlength="50" pattern="[a-zA-Z0-9_-]+" 
									   title="Only letters, numbers, underscores, and hyphens allowed"
									   style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;" />
								<small style="color: #666; font-size: 12px;">3-50 characters, letters, numbers, underscores, and hyphens only</small>
							</div>
							
							<div class="form-group" style="margin-bottom: 15px;">
								<label for="password" style="display: block; margin-bottom: 5px; font-weight: bold;">Password:</label>
								<input type="password" name="password" id="password" value="" 
									   minlength="8" maxlength="128"
									   style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;" />
								<small style="color: #666; font-size: 12px;">
									Password must be at least 8 characters long and contain:<br>
									• At least one uppercase letter (A-Z)<br>
									• At least one lowercase letter (a-z)<br>
									• At least one number (0-9)<br>
									• At least one special character (!@#$%^&*)<br>
								</small>
							</div>
							
							<div class="g-recaptcha" data-sitekey="6LeD3hEUAAAAAKne6ua3iVmspK3AdilgB6dcjST0"></div>
							<br>
							<br>
							<input type="submit" name="submit" value="Next" 
								   style="background: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer;" />
						</form>
					</font>
				</center>
				<br><br>
			</div>
		</div>
	</div>
	<script type="text/javascript">
		var frmvalidator = new Validator("myform");
		frmvalidator.addValidation("firstname", "req", "Please enter student firstname");
		frmvalidator.addValidation("firstname", "maxlen=50");
		frmvalidator.addValidation("lastname", "req", "Please enter student lastname");
		frmvalidator.addValidation("lastname", "maxlen=50");
		frmvalidator.addValidation("username", "req", "Please enter student username");
		frmvalidator.addValidation("username", "maxlen=50");
		frmvalidator.addValidation("password", "req", "Please enter student password");
		frmvalidator.addValidation("password", "minlen=6", "Password must not be less than 6 characters.");
	</script>
</body>

</html>