<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>🔐 Admin Access - Online Voting System</title>
    
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
        .admin-container {
            background: rgba(255,255,255,0.95);
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.3);
            margin-top: 50px;
        }
        .admin-card {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            padding: 20px;
            border-radius: 15px;
            margin: 10px 0;
            color: white;
            transition: all 0.3s;
            text-decoration: none;
            display: block;
        }
        .admin-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.3);
            color: white;
            text-decoration: none;
        }
        .danger-card {
            background: linear-gradient(135deg, #FF6B6B 0%, #FF8E53 100%) !important;
        }
        .success-card {
            background: linear-gradient(135deg, #56ab2f 0%, #a8e6cf 100%) !important;
        }
        .warning-card {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%) !important;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark" style="background: rgba(0,0,0,0.8);">
        <div class="container">
            <a class="navbar-brand" href="index.php" style="font-family: 'Ubuntu', sans-serif; font-size: 24px;">
                🗳️ Online Voting System - Admin Access
            </a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="index.php">🏠 Home</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="admin-container">
                    <div class="text-center mb-4">
                        <h2 style="color: #333; font-family: 'Ubuntu', sans-serif;">🔐 Admin Access Panel</h2>
                        <p style="color: #666;">Complete system administration and database management</p>
                        <div class="alert alert-info">
                            <strong>🔑 Admin Credentials:</strong><br>
                            Username: <code>admin</code><br>
                            Password: <code>himanshu123</code>
                        </div>
                    </div>

                    <div class="row">
                        <!-- Admin Login & Dashboard -->
                        <div class="col-md-6 mb-3">
                            <a href="admin_login.php" class="admin-card">
                                <h4>🔐 Admin Login</h4>
                                <p>Secure admin authentication</p>
                            </a>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <a href="admin_dashboard.php" class="admin-card success-card">
                                <h4>📊 Admin Dashboard</h4>
                                <p>Main administration panel</p>
                            </a>
                        </div>

                        <!-- Database Management -->
                        <div class="col-md-6 mb-3">
                            <a href="admin_database.php" class="admin-card warning-card">
                                <h4>🗄️ Database Admin</h4>
                                <p>Direct database operations</p>
                            </a>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <a href="add_team_members.php" class="admin-card">
                                <h4>➕ Add Team Members</h4>
                                <p>Add new voting options</p>
                            </a>
                        </div>

                        <!-- Security & Monitoring -->
                        <div class="col-md-6 mb-3">
                            <a href="security_dashboard.php" class="admin-card danger-card">
                                <h4>🛡️ Security Dashboard</h4>
                                <p>Security monitoring & logs</p>
                            </a>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <a href="vote_integrity_dashboard.php" class="admin-card">
                                <h4>🔍 Vote Integrity</h4>
                                <p>Vote validation & integrity</p>
                            </a>
                        </div>

                        <!-- System Configuration -->
                        <div class="col-md-6 mb-3">
                            <a href="verify_system_configuration.php" class="admin-card warning-card">
                                <h4>⚙️ System Config</h4>
                                <p>System configuration check</p>
                            </a>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <a href="launch_secure_voting_system.php" class="admin-card success-card">
                                <h4>🚀 Launch System</h4>
                                <p>Secure system launcher</p>
                            </a>
                        </div>
                    </div>

                    <div class="text-center mt-4">
                        <h4>📋 Quick Database Operations</h4>
                        <div class="row">
                            <div class="col-md-4 mb-2">
                                <a href="verify_database_security.php" class="btn btn-outline-primary w-100">🔍 Verify DB Security</a>
                            </div>
                            <div class="col-md-4 mb-2">
                                <a href="test_database_security.php" class="btn btn-outline-warning w-100">🧪 Test DB Security</a>
                            </div>
                            <div class="col-md-4 mb-2">
                                <a href="run_password_migration.php" class="btn btn-outline-success w-100">🔑 Password Migration</a>
                            </div>
                        </div>
                    </div>

                    <div class="text-center mt-4">
                        <div class="alert alert-warning">
                            <strong>⚠️ Security Notice:</strong><br>
                            Admin access provides full system control. Use responsibly and ensure secure credentials.
                        </div>
                        <a href="index.php" class="btn btn-outline-secondary me-2">🏠 Back to Home</a>
                        <a href="simple_results.php" class="btn btn-outline-primary">📊 View Results</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>