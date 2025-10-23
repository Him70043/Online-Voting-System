<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>🔐 Login - Online Voting System</title>
    
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
        .login-container {
            background: rgba(255,255,255,0.95);
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.3);
            margin-top: 100px;
        }
        .form-control {
            padding: 12px;
            border-radius: 8px;
            border: 2px solid #ddd;
            margin-bottom: 15px;
        }
        .btn-login {
            background: linear-gradient(45deg, #667eea, #764ba2);
            border: none;
            color: white;
            padding: 12px 30px;
            border-radius: 25px;
            font-weight: bold;
            width: 100%;
            margin-top: 10px;
        }
        .btn-login:hover {
            background: linear-gradient(45deg, #764ba2, #667eea);
            color: white;
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
    <nav class="navbar navbar-expand-lg navbar-dark" style="background: rgba(0,0,0,0.8);">
        <div class="container">
            <a class="navbar-brand" href="index.php" style="font-family: 'Ubuntu', sans-serif; font-size: 24px;">
                🗳️ Online Voting System
            </a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="index.php">🏠 Home</a>
                <a class="nav-link" href="register.php">📝 Register</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="login-container">
                    <div class="text-center mb-4">
                        <h2 style="color: #333; font-family: 'Ubuntu', sans-serif;">🔐 Login to Vote</h2>
                        <p style="color: #666;">Enter your credentials to access the voting system</p>
                    </div>

                    <?php 
                    // Display any error messages
                    if (isset($_GET['error'])) {
                        echo '<div class="alert alert-danger text-center">' . htmlspecialchars($_GET['error']) . '</div>';
                    }
                    if (isset($_GET['success'])) {
                        echo '<div class="alert alert-success text-center">' . htmlspecialchars($_GET['success']) . '</div>';
                    }
                    ?>

                    <form action="simple_login_action.php" method="post">
                        <div class="mb-3">
                            <label for="username" class="form-label" style="font-weight: bold;">👤 Username:</label>
                            <input type="text" class="form-control" id="username" name="username" 
                                   placeholder="Enter your username" required maxlength="50">
                        </div>
                        
                        <div class="mb-3">
                            <label for="password" class="form-label" style="font-weight: bold;">🔒 Password:</label>
                            <input type="password" class="form-control" id="password" name="password" 
                                   placeholder="Enter your password" required>
                        </div>
                        
                        <button type="submit" name="login" class="btn btn-login">
                            🔐 Login to Vote
                        </button>
                    </form>
                    
                    <div class="text-center mt-4">
                        <p style="color: #666;">Don't have an account? 
                            <a href="register.php" style="color: #667eea; text-decoration: none; font-weight: bold;">📝 Register here</a>
                        </p>
                        <p>
                            <a href="index.php" style="color: #667eea; text-decoration: none;">🏠 Back to Home</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>