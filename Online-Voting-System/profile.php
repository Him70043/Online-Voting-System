<!DOCTYPE html>

<html>

<head>
    <?php
    require 'header_voter.php';
    ?>
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
        include "connection.php";
        include "includes/XSSProtection.php";
        
        // Set security headers
        XSSProtection::setSecurityHeaders();
        ?>
    </div>
    <div class="container" style="padding:100px;">
        <div class="row">
            <div class="col-sm-12" style="border:2px outset gray;">

                <div class="page-header text-center" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 30px; border-radius: 15px; margin: 20px 0; box-shadow: 0 10px 30px rgba(0,0,0,0.3);">
                    <h2 class="specialHead" style="color: white; text-shadow: 2px 2px 4px rgba(0,0,0,0.5);"> 👋 Welcome <?php echo XSSProtection::escapeHtml($_SESSION['SESS_NAME']); ?>! </h2>
                    <p style="color: white; font-size: 18px; margin-top: 10px;">Your Voting Profile & Activity</p>
                </div>
                
                <div class="voting-status" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); padding: 25px; border-radius: 15px; margin: 20px 0; box-shadow: 0 10px 30px rgba(0,0,0,0.3);">
                    <?php
                    $username = $_SESSION['SESS_NAME'];
                    $stmt_profile = mysqli_prepare($con, 'SELECT status, voted, team_voted FROM voters WHERE username = ?');
                    mysqli_stmt_bind_param($stmt_profile, "s", $_SESSION['SESS_NAME']);
                    mysqli_stmt_execute($stmt_profile);
                    $result = mysqli_stmt_get_result($stmt_profile);
                    if ($result) {
                        $row = mysqli_fetch_assoc($result);
                        if ($row['status'] == 'VOTED') {
                            echo '<h3 style="color: white; text-align: center; margin-bottom: 20px; text-shadow: 2px 2px 4px rgba(0,0,0,0.5);">🎉 Your Voting Status: COMPLETED</h3>';
                            
                            echo '<div style="display: flex; flex-wrap: wrap; justify-content: center; gap: 20px;">';
                            
                            // Programming Language Vote
                            if (!empty($row['voted'])) {
                                echo '<div style="background: rgba(255,255,255,0.9); padding: 20px; border-radius: 10px; min-width: 250px; text-align: center; box-shadow: 0 5px 15px rgba(0,0,0,0.2);">
                                        <h4 style="color: #333; margin-bottom: 15px;">🚀 Programming Language</h4>
                                        <div style="background: linear-gradient(45deg, #667eea, #764ba2); color: white; padding: 15px; border-radius: 8px; font-weight: bold; font-size: 18px;">
                                            ' . XSSProtection::escapeHtml($row['voted']) . '
                                        </div>
                                      </div>';
                            }
                            
                            // Team Member Vote
                            if (!empty($row['team_voted'])) {
                                echo '<div style="background: rgba(255,255,255,0.9); padding: 20px; border-radius: 10px; min-width: 250px; text-align: center; box-shadow: 0 5px 15px rgba(0,0,0,0.2);">
                                        <h4 style="color: #333; margin-bottom: 15px;">👥 Best Team Member</h4>
                                        <div style="background: linear-gradient(45deg, #f093fb, #f5576c); color: white; padding: 15px; border-radius: 8px; font-weight: bold; font-size: 18px;">
                                            ' . XSSProtection::escapeHtml($row['team_voted']) . '
                                        </div>
                                      </div>';
                            }
                            
                            echo '</div>';
                            
                            if (empty($row['voted']) && empty($row['team_voted'])) {
                                echo '<p style="color: white; text-align: center; font-size: 18px;">No votes recorded yet.</p>';
                            }
                            
                        } else {
                            echo '<div style="text-align: center;">
                                    <h3 style="color: white; margin-bottom: 20px; text-shadow: 2px 2px 4px rgba(0,0,0,0.5);">⏳ Voting Status: PENDING</h3>
                                    <p style="color: white; font-size: 18px; margin-bottom: 20px;">You have not voted yet. Please submit your vote!</p>
                                    <a href="voter.php" style="background: linear-gradient(45deg, #FF6B6B, #4ECDC4); color: white; text-decoration: none; padding: 15px 30px; border-radius: 25px; font-weight: bold; display: inline-block; transition: all 0.3s; box-shadow: 0 5px 15px rgba(0,0,0,0.2);" onmouseover="this.style.transform=\'translateY(-2px)\'" onmouseout="this.style.transform=\'translateY(0)\'">
                                        🗳️ Cast Your Vote Now
                                    </a>
                                  </div>';
                        }
                        mysqli_stmt_close($stmt_profile);
                    }
                    ?>
                </div>
                
                <div style="text-align: center; margin: 30px 0;">
                    <a href="lan_view.php" style="background: linear-gradient(45deg, #4facfe, #00f2fe); color: white; text-decoration: none; padding: 12px 25px; border-radius: 20px; font-weight: bold; display: inline-block; margin: 10px; transition: all 0.3s; box-shadow: 0 5px 15px rgba(0,0,0,0.2);" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='translateY(0)'">
                        📊 View Live Results
                    </a>
                    <a href="voter.php" style="background: linear-gradient(45deg, #fa709a, #fee140); color: white; text-decoration: none; padding: 12px 25px; border-radius: 20px; font-weight: bold; display: inline-block; margin: 10px; transition: all 0.3s; box-shadow: 0 5px 15px rgba(0,0,0,0.2);" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='translateY(0)'">
                        🗳️ Vote Again (if allowed)
                    </a>
                </div>
            </div>
        </div>

    </div>
    <!-- Footer -->
    <nav class="navbar fixed-bottom navbar-light bg-light">
        <footer class="page-footer font-small special-color-dark pt-4">
            <div class="footer-copyright text-center py-3">© 2025 Copyright:
                <a href="/"> Online Voting System by Himanshu Kumar </a>
            </div>
        </footer>
    </nav>
</body>

</html>