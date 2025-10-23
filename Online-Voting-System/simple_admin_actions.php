<?php
session_start();
include "connection.php";

// Simple admin check
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: admin_login.php");
    exit();
}

// Include CSRF protection
include "includes/CSRFProtection.php";

// Generate CSRF token
$csrf_token = CSRFProtection::generateToken();
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Actions - Online Voting System</title>
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f8f9fa; }
        .admin-container { background: white; padding: 30px; border-radius: 10px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); margin: 20px 0; }
        .action-btn { margin: 10px; padding: 15px 25px; border: none; border-radius: 8px; font-weight: bold; cursor: pointer; text-decoration: none; display: inline-block; }
        .btn-danger { background: #dc3545; color: white; }
        .btn-info { background: #17a2b8; color: white; }
        .btn-warning { background: #ffc107; color: black; }
        .btn-success { background: #28a745; color: white; }
    </style>
</head>
<body>
    <div class="container">
        <div class="admin-container">
            <h2>⚡ Admin Quick Actions</h2>
            <p>Welcome, <?php echo htmlspecialchars($_SESSION['admin_username'] ?? 'Admin'); ?>!</p>
            
            <?php
            // Handle actions
            if (isset($_POST['action']) && isset($_POST['csrf_token'])) {
                if (CSRFProtection::validateToken($_POST['csrf_token'])) {
                    $action = $_POST['action'];
                    
                    switch ($action) {
                        case 'reset_votes':
                            mysqli_query($con, "UPDATE languages SET votecount = 0");
                            mysqli_query($con, "UPDATE team_members SET votecount = 0");
                            mysqli_query($con, "DELETE FROM votes");
                            mysqli_query($con, "DELETE FROM team_member_votes");
                            mysqli_query($con, "UPDATE voters SET status = 'NOTVOTED', voted = NULL, team_voted = NULL");
                            echo "<div class='alert alert-success'>✅ All votes have been reset!</div>";
                            break;
                            
                        case 'show_tables':
                            echo "<h3>📋 Database Tables:</h3>";
                            $result = mysqli_query($con, "SHOW TABLES");
                            if ($result) {
                                echo "<ul>";
                                while ($row = mysqli_fetch_array($result)) {
                                    echo "<li><strong>" . htmlspecialchars($row[0]) . "</strong></li>";
                                }
                                echo "</ul>";
                            }
                            break;
                            
                        case 'export_data':
                            echo "<h3>📥 Database Export:</h3>";
                            echo "<p>Exporting voting data...</p>";
                            
                            // Export languages
                            echo "<h4>Programming Languages:</h4>";
                            $result = mysqli_query($con, "SELECT * FROM languages ORDER BY votecount DESC");
                            if ($result) {
                                echo "<table class='table table-striped'>";
                                echo "<tr><th>ID</th><th>Language</th><th>Description</th><th>Votes</th></tr>";
                                while ($row = mysqli_fetch_assoc($result)) {
                                    echo "<tr>";
                                    echo "<td>" . htmlspecialchars($row['lan_id']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['fullname']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['about']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['votecount']) . "</td>";
                                    echo "</tr>";
                                }
                                echo "</table>";
                            }
                            
                            // Export team members
                            echo "<h4>Team Members:</h4>";
                            $result = mysqli_query($con, "SELECT * FROM team_members ORDER BY votecount DESC");
                            if ($result) {
                                echo "<table class='table table-striped'>";
                                echo "<tr><th>ID</th><th>Member</th><th>Description</th><th>Votes</th></tr>";
                                while ($row = mysqli_fetch_assoc($result)) {
                                    echo "<tr>";
                                    echo "<td>" . htmlspecialchars($row['member_id']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['fullname']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['about']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['votecount']) . "</td>";
                                    echo "</tr>";
                                }
                                echo "</table>";
                            }
                            break;
                    }
                } else {
                    echo "<div class='alert alert-danger'>❌ Security token validation failed!</div>";
                }
            }
            ?>
            
            <div class="row">
                <div class="col-md-6">
                    <h4>🔄 Reset Actions</h4>
                    <form method="post" style="display: inline;">
                        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                        <input type="hidden" name="action" value="reset_votes">
                        <button type="submit" class="action-btn btn-danger" onclick="return confirm('Are you sure you want to reset all votes?')">
                            🔄 Reset All Votes
                        </button>
                    </form>
                </div>
                
                <div class="col-md-6">
                    <h4>📊 Data Actions</h4>
                    <form method="post" style="display: inline;">
                        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                        <input type="hidden" name="action" value="export_data">
                        <button type="submit" class="action-btn btn-info">
                            📥 Export All Data
                        </button>
                    </form>
                    
                    <form method="post" style="display: inline;">
                        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                        <input type="hidden" name="action" value="show_tables">
                        <button type="submit" class="action-btn btn-warning">
                            📋 Show All Tables
                        </button>
                    </form>
                </div>
            </div>
            
            <hr>
            
            <h4>💻 SQL Query Interface</h4>
            <form method="post">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                <div class="form-group">
                    <label>Enter SQL Query:</label>
                    <textarea name="sql_query" class="form-control" rows="3" placeholder="SELECT * FROM languages;"><?php echo isset($_POST['sql_query']) ? htmlspecialchars($_POST['sql_query']) : 'SHOW TABLES;'; ?></textarea>
                </div>
                <button type="submit" name="execute_sql" class="btn btn-primary">🚀 Execute Query</button>
                <button type="button" class="btn btn-secondary" onclick="document.querySelector('textarea[name=sql_query]').value=''">🗑️ Clear</button>
            </form>
            
            <?php
            if (isset($_POST['execute_sql']) && isset($_POST['sql_query']) && isset($_POST['csrf_token'])) {
                if (CSRFProtection::validateToken($_POST['csrf_token'])) {
                    $sql = trim($_POST['sql_query']);
                    if (!empty($sql)) {
                        echo "<h4>Query Results:</h4>";
                        echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
                        echo "<strong>Query:</strong> " . htmlspecialchars($sql);
                        echo "</div>";
                        
                        $result = mysqli_query($con, $sql);
                        if ($result) {
                            if (mysqli_num_rows($result) > 0) {
                                echo "<table class='table table-striped table-sm'>";
                                
                                // Get column names
                                $fields = mysqli_fetch_fields($result);
                                echo "<tr>";
                                foreach ($fields as $field) {
                                    echo "<th>" . htmlspecialchars($field->name) . "</th>";
                                }
                                echo "</tr>";
                                
                                // Get data
                                while ($row = mysqli_fetch_assoc($result)) {
                                    echo "<tr>";
                                    foreach ($row as $value) {
                                        echo "<td>" . htmlspecialchars($value ?? 'NULL') . "</td>";
                                    }
                                    echo "</tr>";
                                }
                                echo "</table>";
                            } else {
                                echo "<div class='alert alert-info'>Query executed successfully. No results returned.</div>";
                            }
                        } else {
                            echo "<div class='alert alert-danger'>❌ Query Error: " . htmlspecialchars(mysqli_error($con)) . "</div>";
                        }
                    }
                } else {
                    echo "<div class='alert alert-danger'>❌ Security token validation failed!</div>";
                }
            }
            ?>
            
            <div class="text-center" style="margin-top: 30px;">
                <a href="admin_dashboard.php" class="btn btn-secondary">📊 Dashboard</a>
                <a href="admin_database.php" class="btn btn-info">🗃️ Database</a>
                <a href="index.php" class="btn btn-primary">🏠 Home</a>
                <a href="admin_login.php?logout=1" class="btn btn-danger">🚪 Logout</a>
            </div>
        </div>
    </div>
</body>
</html>