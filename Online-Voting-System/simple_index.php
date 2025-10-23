<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>🗳️ Online Voting System - by Himanshu Kumar</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href='https://fonts.googleapis.com/css?family=Ubuntu' rel='stylesheet' type='text/css'>
    <link href='https://fonts.googleapis.com/css?family=Raleway' rel='stylesheet' type='text/css'>
    
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Raleway', sans-serif;
        }
        .main-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 60px 0;
            text-align: center;
            border-radius: 20px;
            margin: 20px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.3);
        }
        .feature-card {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            padding: 30px;
            border-radius: 15px;
            margin: 10px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
            transition: all 0.3s;
            color: white;
            text-align: center;
        }
        .feature-card:hover {
            transform: translateY(-5px);
        }
        .navbar-custom {
            background: rgba(0,0,0,0.8) !important;
        }
        .btn-custom {
            background: linear-gradient(45deg, #FF6B6B, #4ECDC4);
            border: none;
            color: white;
            padding: 12px 30px;
            border-radius: 25px;
            font-weight: bold;
            text-decoration: none;
            display: inline-block;
            margin: 10px;
            transition: all 0.3s;
        }
        .btn-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
            color: white;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <?php
    // Simple session check
    session_start();
    if (isset($_SESSION['user_id']) && !empty($_SESSION['user_id'])) {
        header("Location: voter.php");
        exit();
    }
    ?>

    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark navbar-custom fixed-top">
        <div class="container">
            <a class="navbar-brand" href="#" style="font-family: 'Ubuntu', sans-serif; font-size: 24px;">
                🗳️ Online Voting System
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="register.php"><strong>📝 Register</strong></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="login.php"><strong>🔐 Login</strong></a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="admin_login.php" style="color: #FF6B6B;"><strong>🔒 Admin</strong></a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container" style="margin-top: 100px;">
        
        <!-- Hero Section -->
        <div class="main-header">
            <div class="container">
                <img src="images/now.png" width="300px" alt="Voting Icon" style="filter: drop-shadow(0 5px 15px rgba(0,0,0,0.3)); margin-bottom: 20px;">
                <h1 style="font-size: 48px; font-family: 'Ubuntu', sans-serif; text-shadow: 2px 2px 4px rgba(0,0,0,0.5);">
                    🗳️ Online Voting System
                </h1>
                <p style="font-size: 28px; text-shadow: 1px 1px 2px rgba(0,0,0,0.5); margin: 20px 0;">
                    Welcome to the Future of Digital Democracy!
                </p>
                <p style="font-size: 18px; margin-top: 15px;">
                    Developed by <strong>Himanshu Kumar</strong>
                </p>
                
                <!-- Action Buttons -->
                <div style="margin-top: 30px;">
                    <a href="register.php" class="btn-custom">📝 Register to Vote</a>
                    <a href="login.php" class="btn-custom">🔐 Login</a>
                    <a href="lan_view.php" class="btn-custom">📊 View Results</a>
                </div>
            </div>
        </div>

        <!-- What is it Section -->
        <div class="text-center" style="margin: 40px 0;">
            <div style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); padding: 50px; border-radius: 20px; color: white; box-shadow: 0 15px 35px rgba(0,0,0,0.3);">
                <h1 style="font-size: 44px; text-shadow: 2px 2px 4px rgba(0,0,0,0.5);">🚀 WHAT IS IT?</h1>
                <p style="font-size: 24px; text-shadow: 1px 1px 2px rgba(0,0,0,0.5);">An Interactive & Secure Way of Digital Voting</p>
                <p style="font-size: 16px; margin-top: 15px;">Experience the power of modern democracy with our advanced voting platform</p>
            </div>
        </div>

        <!-- Features Section -->
        <div class="row" style="margin: 50px 0;">
            <div class="col-md-4">
                <div class="feature-card">
                    <h2 style="font-size: 28px;">📝 Register</h2>
                    <p>Quick and secure registration process. Just provide your basic details and join our democratic platform!</p>
                    <a href="register.php" class="btn btn-light btn-sm">Get Started</a>
                </div>
            </div>
            <div class="col-md-4">
                <div class="feature-card" style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);">
                    <h2 style="font-size: 28px;">👤 Profile</h2>
                    <p>Manage your account and view your voting history. Track your participation in our democratic process!</p>
                    <a href="login.php" class="btn btn-light btn-sm">Login First</a>
                </div>
            </div>
            <div class="col-md-4">
                <div class="feature-card" style="background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%); color: #333;">
                    <h2 style="font-size: 28px;">📊 Statistics</h2>
                    <p>Real-time voting results and comprehensive analytics. See how the community is voting!</p>
                    <a href="lan_view.php" class="btn btn-dark btn-sm">View Results</a>
                </div>
            </div>
        </div>

        <!-- Current Voting Status -->
        <div class="text-center" style="margin: 40px 0;">
            <div style="background: rgba(255,255,255,0.9); padding: 30px; border-radius: 15px; color: #333;">
                <h3>🗳️ Current Voting Status</h3>
                <?php
                include "connection.php";
                if ($con) {
                    $result = mysqli_query($con, "SELECT * FROM languages ORDER BY votecount DESC");
                    if ($result && mysqli_num_rows($result) > 0) {
                        echo "<div class='row'>";
                        while ($row = mysqli_fetch_assoc($result)) {
                            $percentage = 0;
                            $total_result = mysqli_query($con, "SELECT SUM(votecount) as total FROM languages");
                            if ($total_result) {
                                $total_row = mysqli_fetch_assoc($total_result);
                                $total_votes = $total_row['total'];
                                if ($total_votes > 0) {
                                    $percentage = round(($row['votecount'] / $total_votes) * 100, 1);
                                }
                            }
                            
                            echo "<div class='col-md-6 mb-3'>";
                            echo "<div style='background: linear-gradient(45deg, #667eea, #764ba2); color: white; padding: 15px; border-radius: 10px;'>";
                            echo "<h5>" . htmlspecialchars($row['fullname']) . "</h5>";
                            echo "<p>" . htmlspecialchars($row['about']) . "</p>";
                            echo "<div class='progress' style='height: 25px;'>";
                            echo "<div class='progress-bar bg-warning' style='width: " . $percentage . "%'>" . $row['votecount'] . " votes (" . $percentage . "%)</div>";
                            echo "</div>";
                            echo "</div>";
                            echo "</div>";
                        }
                        echo "</div>";
                    } else {
                        echo "<p>No voting options available yet.</p>";
                    }
                } else {
                    echo "<p style='color: red;'>Database connection error. Please try again later.</p>";
                }
                ?>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="text-center text-white" style="background: rgba(0,0,0,0.8); padding: 20px; margin-top: 50px;">
        <p>&copy; 2025 Online Voting System by Himanshu Kumar. All rights reserved.</p>
        <p>🔒 Secure • 🗳️ Democratic • 🚀 Modern</p>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>