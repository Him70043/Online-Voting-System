<?php
// Redirect to the working simple voter page
header("Location: simple_voter.php");
exit();
?>
<!DOCTYPE html>

<html>

<head>
    <?php require 'header_voter.php'; ?>
    <!-- Bootstrap -->
    <link href="css/bootstrap.min.css" rel="stylesheet">

    <link href='http://fonts.googleapis.com/css?family=Ubuntu' rel='stylesheet' type='text/css'>
    <link href='http://fonts.googleapis.com/css?family=Raleway' rel='stylesheet' type='text/css'>
    <link href='http://fonts.googleapis.com/css?family=Oswald' rel='stylesheet' type='text/css'>
    <link href='http://fonts.googleapis.com/css?family=Roboto+Condensed' rel='stylesheet' type='text/css'>

</head>

<body>
    <div class="col-sm-12">
        <?php
        require_once __DIR__ . "/includes/SessionSecurity.php";
        
        // Initialize secure session
        SessionSecurity::initializeSecureSession();
        include "auth.php";
        include "includes/CSRFProtection.php";
        include "includes/XSSProtection.php";
        include "includes/InputValidation.php";
        
        // Set security headers
        XSSProtection::setSecurityHeaders();
        ?>
    </div>
    <div class="container" style="padding:100px;">
        <div class="row">
            <div class="col-sm-12" style="border:2px outset gray;">

                <div class="page-header text-center">
                    <h2 class="specialHead"> Welcome <?php echo XSSProtection::escapeHtml($_SESSION['SESS_NAME']); ?> </h2>
                    <p class="normalFont" style="font-size:18px;">Cast Your Vote - Make Your Voice Heard!</p>
                </div>

                <form action="submit_vote.php" name="vote" method="post" id="myform">
                    <?php echo CSRFProtection::getTokenField(); ?>
                    <?php echo InputValidation::generateHoneypotFields(); ?>
                    
                    <!-- Programming Language Voting Section -->
                    <div class="voting-section" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 30px; margin: 20px 0; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.3);">
                        <center>
                            <h3 style="color: white; margin-bottom: 25px; font-weight: bold; text-shadow: 2px 2px 4px rgba(0,0,0,0.5);">
                                🚀 What is your favorite programming language?
                            </h3>
                            <div style="display: flex; flex-wrap: wrap; justify-content: center; gap: 15px;">
                                <label class="vote-option" style="background: rgba(255,255,255,0.9); padding: 15px 25px; border-radius: 10px; cursor: pointer; transition: all 0.3s; display: flex; align-items: center; min-width: 120px;">
                                    <input type="radio" name="lan" value="JAVA" style="margin-right: 10px; transform: scale(1.2);"> 
                                    <span style="font-weight: bold; color: #333;">☕ JAVA</span>
                                </label>
                                <label class="vote-option" style="background: rgba(255,255,255,0.9); padding: 15px 25px; border-radius: 10px; cursor: pointer; transition: all 0.3s; display: flex; align-items: center; min-width: 120px;">
                                    <input type="radio" name="lan" value="PYTHON" style="margin-right: 10px; transform: scale(1.2);"> 
                                    <span style="font-weight: bold; color: #333;">🐍 PYTHON</span>
                                </label>
                                <label class="vote-option" style="background: rgba(255,255,255,0.9); padding: 15px 25px; border-radius: 10px; cursor: pointer; transition: all 0.3s; display: flex; align-items: center; min-width: 120px;">
                                    <input type="radio" name="lan" value="C++" style="margin-right: 10px; transform: scale(1.2);"> 
                                    <span style="font-weight: bold; color: #333;">⚡ C++</span>
                                </label>
                                <label class="vote-option" style="background: rgba(255,255,255,0.9); padding: 15px 25px; border-radius: 10px; cursor: pointer; transition: all 0.3s; display: flex; align-items: center; min-width: 120px;">
                                    <input type="radio" name="lan" value="PHP" style="margin-right: 10px; transform: scale(1.2);"> 
                                    <span style="font-weight: bold; color: #333;">🌐 PHP</span>
                                </label>
                                <label class="vote-option" style="background: rgba(255,255,255,0.9); padding: 15px 25px; border-radius: 10px; cursor: pointer; transition: all 0.3s; display: flex; align-items: center; min-width: 120px;">
                                    <input type="radio" name="lan" value=".NET" style="margin-right: 10px; transform: scale(1.2);"> 
                                    <span style="font-weight: bold; color: #333;">🔷 .NET</span>
                                </label>
                            </div>
                        </center>
                    </div>

                    <!-- Team Member Voting Section -->
                    <div class="voting-section" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); padding: 30px; margin: 20px 0; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.3);">
                        <center>
                            <h3 style="color: white; margin-bottom: 25px; font-weight: bold; text-shadow: 2px 2px 4px rgba(0,0,0,0.5);">
                                👥 Who is the best team member?
                            </h3>
                            <div style="display: flex; flex-wrap: wrap; justify-content: center; gap: 15px;">
                                <label class="vote-option" style="background: rgba(255,255,255,0.9); padding: 15px 25px; border-radius: 10px; cursor: pointer; transition: all 0.3s; display: flex; align-items: center; min-width: 150px;">
                                    <input type="radio" name="team" value="Himanshu" style="margin-right: 10px; transform: scale(1.2);"> 
                                    <span style="font-weight: bold; color: #333;">👑 Himanshu</span>
                                </label>
                                <label class="vote-option" style="background: rgba(255,255,255,0.9); padding: 15px 25px; border-radius: 10px; cursor: pointer; transition: all 0.3s; display: flex; align-items: center; min-width: 150px;">
                                    <input type="radio" name="team" value="Prafful" style="margin-right: 10px; transform: scale(1.2);"> 
                                    <span style="font-weight: bold; color: #333;">💻 Prafful</span>
                                </label>
                                <label class="vote-option" style="background: rgba(255,255,255,0.9); padding: 15px 25px; border-radius: 10px; cursor: pointer; transition: all 0.3s; display: flex; align-items: center; min-width: 150px;">
                                    <input type="radio" name="team" value="Shoaib" style="margin-right: 10px; transform: scale(1.2);"> 
                                    <span style="font-weight: bold; color: #333;">🎨 Shoaib</span>
                                </label>
                                <label class="vote-option" style="background: rgba(255,255,255,0.9); padding: 15px 25px; border-radius: 10px; cursor: pointer; transition: all 0.3s; display: flex; align-items: center; min-width: 150px;">
                                    <input type="radio" name="team" value="Ansh" style="margin-right: 10px; transform: scale(1.2);"> 
                                    <span style="font-weight: bold; color: #333;">🔍 Ansh</span>
                                </label>
                            </div>
                        </center>
                    </div>

                    <?php 
                    global $msg; 
                    if (isset($msg)) {
                        echo XSSProtection::escapeHtml($msg); 
                    }
                    ?>
                    <?php 
                    global $error; 
                    if (isset($error)) {
                        echo XSSProtection::escapeHtml($error); 
                    }
                    ?>
                    
                    <center>
                        <button type="submit" name="submit" style="background: linear-gradient(45deg, #FF6B6B, #4ECDC4); color: white; border: none; padding: 15px 40px; font-size: 18px; font-weight: bold; border-radius: 25px; cursor: pointer; box-shadow: 0 5px 15px rgba(0,0,0,0.2); transition: all 0.3s; margin: 20px 0;">
                            🗳️ Submit Your Votes
                        </button>
                    </center>
                    
                </form>

                <style>
                .vote-option:hover {
                    transform: translateY(-3px);
                    box-shadow: 0 5px 15px rgba(0,0,0,0.2);
                    background: rgba(255,255,255,1) !important;
                }
                button[type="submit"]:hover {
                    transform: translateY(-2px);
                    box-shadow: 0 8px 25px rgba(0,0,0,0.3);
                }
                </style>
            </div>
        </div>
    </div>
    
</body>

</html>