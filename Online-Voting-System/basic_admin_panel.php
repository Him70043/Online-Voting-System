<?php
session_start();
include "connection.php";

// Simple admin check
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: admin_login.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Basic Admin Panel - Online Voting System</title>
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f8f9fa; }
        .admin-container { background: white; padding: 30px; border-radius: 10px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); margin: 20px 0; }
        .action-btn { margin: 10px; padding: 15px 25px; border: none; border-radius: 8px; font-weight: bold; cursor: pointer; text-decoration: none; display: inline-block; }
        .btn-danger { background: #dc3545; color: white; }
        .btn-info { background: #17a2b8; color: white; }
        .btn-warning { background: #ffc107; color: black; }
        .btn-success { background: #28a745; color: white; }
        table { font-size: 12px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="admin-container">
            <h2>🔧 Basic Admin Panel</h2>
            <p>Welcome, <?php echo htmlspecialchars($_SESSION['admin_username'] ?? 'Admin'); ?>!</p>
            
            <?php
            // Handle actions without CSRF for simplicity
            if (isset($_POST['action'])) {
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
                }
            }
            
            // Handle SQL queries
            if (isset($_POST['execute_sql']) && isset($_POST['sql_query'])) {
                $sql = trim($_POST['sql_query']);
                if (!empty($sql)) {
                    echo "<h4>Query Results:</h4>";
                    echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
                    echo "<strong>Query:</strong> " . htmlspecialchars($sql);
                    echo "</div>";
                    
                    $result = mysqli_query($con, $sql);
                    if ($result) {
                        if (mysqli_num_rows($result) > 0) {
                            echo "<div style='overflow-x: auto;'>";
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
                            echo "</div>";
                        } else {
                            echo "<div class='alert alert-info'>Query executed successfully. No results returned.</div>";
                        }
                    } else {
                        echo "<div class='alert alert-danger'>❌ Query Error: " . htmlspecialchars(mysqli_error($con)) . "</div>";
                    }
                }
            }
            ?> 
           
            <div class="row">
                <div class="col-md-6">
                    <h4>🔄 Quick Actions</h4>
                    <form method="post" style="display: inline;">
                        <input type="hidden" name="action" value="reset_votes">
                        <button type="submit" class="action-btn btn-danger" onclick="return confirm('Are you sure you want to reset all votes?')">
                            🔄 Reset All Votes
                        </button>
                    </form>
                </div>
                
                <div class="col-md-6">
                    <h4>📊 Quick Queries</h4>
                    <form method="post" style="display: inline;">
                        <input type="hidden" name="sql_query" value="SELECT * FROM languages ORDER BY votecount DESC;">
                        <button type="submit" name="execute_sql" class="action-btn btn-info">
                            📱 Languages
                        </button>
                    </form>
                    
                    <form method="post" style="display: inline;">
                        <input type="hidden" name="sql_query" value="SELECT * FROM team_members ORDER BY votecount DESC;">
                        <button type="submit" name="execute_sql" class="action-btn btn-warning">
                            👥 Team Members
                        </button>
                    </form>
                    
                    <form method="post" style="display: inline;">
                        <input type="hidden" name="sql_query" value="SELECT * FROM voters;">
                        <button type="submit" name="execute_sql" class="action-btn btn-success">
                            🗳️ Voters
                        </button>
                    </form>
                </div>
            </div>
            
            <hr>
            
            <h4>💻 SQL Query Interface</h4>
            <form method="post">
                <div class="form-group">
                    <label>Enter SQL Query:</label>
                    <textarea name="sql_query" class="form-control" rows="3" placeholder="SELECT * FROM languages;"><?php echo isset($_POST['sql_query']) ? htmlspecialchars($_POST['sql_query']) : 'SHOW TABLES;'; ?></textarea>
                </div>
                <button type="submit" name="execute_sql" class="btn btn-primary">🚀 Execute Query</button>
                <button type="button" class="btn btn-secondary" onclick="document.querySelector('textarea[name=sql_query]').value=''">🗑️ Clear</button>
            </form>
            
            <hr>
            
            <h4>📋 Common Queries</h4>
            <div class="row">
                <div class="col-md-6">
                    <h5>Database Structure:</h5>
                    <form method="post" style="margin: 5px 0;">
                        <input type="hidden" name="sql_query" value="SHOW TABLES;">
                        <button type="submit" name="execute_sql" class="btn btn-sm btn-outline-primary">Show Tables</button>
                    </form>
                    
                    <form method="post" style="margin: 5px 0;">
                        <input type="hidden" name="sql_query" value="SELECT * FROM loginusers;">
                        <button type="submit" name="execute_sql" class="btn btn-sm btn-outline-info">All Users</button>
                    </form>
                </div>
                
                <div class="col-md-6">
                    <h5>Voting Data:</h5>
                    <form method="post" style="margin: 5px 0;">
                        <input type="hidden" name="sql_query" value="SELECT SUM(votecount) as total_lang_votes FROM languages;">
                        <button type="submit" name="execute_sql" class="btn btn-sm btn-outline-success">Total Language Votes</button>
                    </form>
                    
                    <form method="post" style="margin: 5px 0;">
                        <input type="hidden" name="sql_query" value="SELECT SUM(votecount) as total_team_votes FROM team_members;">
                        <button type="submit" name="execute_sql" class="btn btn-sm btn-outline-warning">Total Team Votes</button>
                    </form>
                </div>
            </div>
            
            <div class="text-center" style="margin-top: 30px;">
                <a href="admin_dashboard.php" class="btn btn-secondary">📊 Dashboard</a>
                <a href="multi_question_results.php" class="btn btn-info">📊 Results</a>
                <a href="index.php" class="btn btn-primary">🏠 Home</a>
                <a href="admin_login.php?logout=1" class="btn btn-danger">🚪 Logout</a>
            </div>
        </div>
    </div>
</body>
</html>