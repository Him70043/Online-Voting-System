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
        include "includes/CSRFProtection.php";
        ?>
    </div>
    <br><br>
    
    <div class="container" style="padding:100px;">
        <div class="row">
            <div class="col-sm-12" style="border:2px outset gray;">
                <div class="page-header text-center">
                    <h3 class="specialHead">Password Reset Request</h3>
                </div>

                <?php global $message; echo $message; ?>
                <?php global $error; echo $error; ?>
                
                <br>
                <center>
                    <font size="4">
                        <form action="password_reset_action.php" method="post" id="resetform">
                            <?php echo CSRFProtection::getTokenField(); ?>
                            <p>Enter your username to request a password reset:</p>
                            Username:
                            <input type="text" name="username" value="" required>
                            <br><br>
                            <input type="submit" name="reset_request" value="Request Reset">
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
        frmvalidator.addValidation("username", "req", "Please Enter Username");
        frmvalidator.addValidation("username", "maxlen=50");
    </script>
</body>
</html>