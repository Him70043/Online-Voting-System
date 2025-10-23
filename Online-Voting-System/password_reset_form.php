<!DOCTYPE html>
<html>
<head>
    <?php require 'header.php'; ?>
</head>
<body>
    <div class="col-sm-12">
        <?php
        require_once "includes/SessionSecurity.php";
        
        // Initialize secure session
        SessionSecurity::initializeSecureSession();
        
        if (SessionSecurity::isLoggedIn()) {
            header("Location: voter.php");
        }
        
        // Validate reset token
        include "connection.php";
        include "includes/CSRFProtection.php";
        $token = isset($_GET['token']) ? $_GET['token'] : '';
        $validToken = false;
        
        if ($token) {
            $stmt = mysqli_prepare($con, "SELECT username FROM loginusers WHERE reset_token = ? AND reset_token_expires > NOW() AND status = 'ACTIVE'");
            mysqli_stmt_bind_param($stmt, "s", $token);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            
            if (mysqli_num_rows($result) > 0) {
                $validToken = true;
                $user = mysqli_fetch_assoc($result);
            }
            mysqli_stmt_close($stmt);
        }
        
        if (!$validToken) {
            echo "<center><h4><font color='#FF0000'>Invalid or expired reset token. <a href='password_reset_request.php'>Request a new one</a></h4></center></font>";
            exit();
        }
        ?>
    </div>
    <br><br>
    
    <div class="container" style="padding:100px;">
        <div class="row">
            <div class="col-sm-12" style="border:2px outset gray;">
                <div class="page-header text-center">
                    <h3 class="specialHead">Reset Password</h3>
                </div>

                <?php global $message; echo $message; ?>
                <?php global $error; echo $error; ?>
                
                <br>
                <center>
                    <font size="4">
                        <form action="password_reset_process.php" method="post" id="resetform">
                            <?php echo CSRFProtection::getTokenField(); ?>
                            <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
                            
                            <p>Enter your new password for user: <strong><?php echo htmlspecialchars($user['username']); ?></strong></p>
                            
                            New Password:
                            <input type="password" name="new_password" value="" required>
                            <br><br>
                            
                            Confirm Password:
                            <input type="password" name="confirm_password" value="" required>
                            <br>
                            <small style="color: #666; font-size: 12px;">
                                Password must be at least 8 characters long and contain:<br>
                                • At least one uppercase letter (A-Z)<br>
                                • At least one lowercase letter (a-z)<br>
                                • At least one number (0-9)<br>
                                • At least one special character (!@#$%^&*)<br>
                            </small>
                            <br><br>
                            
                            <input type="submit" name="reset_password" value="Reset Password">
                            <br><br>
                            <a href="login.php">Back to Login</a>
                        </form>
                    </font>
                </center>
                <br><br>
            </div>
        </div>
    </div>
    
    <script type="text/javascript">
        var frmvalidator = new Validator("resetform");
        frmvalidator.addValidation("new_password", "req", "Please Enter New Password");
        frmvalidator.addValidation("new_password", "minlen=8", "Password must be at least 8 characters long");
        frmvalidator.addValidation("confirm_password", "req", "Please Confirm Password");
        frmvalidator.addValidation("confirm_password", "eqelmnt=new_password", "Passwords do not match");
    </script>
</body>
</html>