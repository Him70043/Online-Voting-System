<?php
// Initialize HTTP Security Headers
if (file_exists(__DIR__ . '/includes/HTTPSecurityHeaders.php')) {
    require_once __DIR__ . '/includes/HTTPSecurityHeaders.php';
    if (class_exists('HTTPSecurityHeaders') && method_exists('HTTPSecurityHeaders', 'applySecurityHeaders')) {
        HTTPSecurityHeaders::applySecurityHeaders();
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Database Manager - Online Voting System by Himanshu Kumar</title>
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href='http://fonts.googleapis.com/css?family=Ubuntu' rel='stylesheet' type='text/css'>
    <style>
        .sql-editor {
            background: #2d3748;
            color: #e2e8f0;
            border: none;
            border-radius: 10px;
            padding: 20px;
            font-family: 'Courier New', monospace;
            font-size: 14px;
            min-height: 200px;
        }
        .result-table {
            max-height: 400px;
            overflow-y: auto;
            border: 1px solid #ddd;
            border-radius: 10px;
        }
    </style>
</head>

<body style="background: #f8f9fa;">
    <?php
    require_once "includes/SessionSecurity.php";
    
    // Check admin authentication with session security
    if (!SessionSecurity::isAdminLoggedIn()) {
        header("Location: admin_login.php");
        exit();
    }
    include "connection.php";
    include "includes/CSRFProtection.php";
    ?>

    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg" style="background: linear-gradient(45deg, #667eea, #764ba2); box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
        <div class="container">
            <a class="navbar-brand" href="admin_dashboard.php" style="color: white; font-weight: bold; font-size: 24px;">
                🗄️ Database Manager
            </a>
            <div class="navbar-nav ml-auto">
                <a href="admin_dashboard.php" class="btn btn-light mr-2">📊 Dashboard</a>
                <a href="admin_logout.php" class="btn btn-danger">🚪 Logout</a>
            </div>
        </div>
    </nav>

    <div class="container-fluid" style="padding: 30px;">
        
        <!-- Quick Actions -->
        <div class="row mb-4">
            <div class="col-12">
                <div style="background: white; padding: 25px; border-radius: 15px; box-shadow: 0 5px 15px rgba(0,0,0,0.1);">
                    <h4 style="color: #333; margin-bottom: 20px;">⚡ Quick Database Actions</h4>
                    <div class="row">
                        <div class="col-md-3">
                            <a href="admin_actions.php?action=reset_all_votes&csrf_token=<?php echo CSRFProtection::generateToken(); ?>" class="btn btn-warning btn-block" 
                               onclick="return confirm('Reset ALL votes? This cannot be undone!')">
                                🔄 Reset All Votes
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="admin_actions.php?action=export_data&csrf_token=<?php echo CSRFProtection::generateToken(); ?>" class="btn btn-success btn-block">
                                📥 Export All Data
                            </a>
                        </div>
                        <div class="col-md-3">
                            <button class="btn btn-info btn-block" onclick="showBackup()">
                                💾 Backup Database
                            </button>
                        </div>
                        <div class="col-md-3">
                            <button class="btn btn-primary btn-block" onclick="showTables()">
                                📋 Show All Tables
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- SQL Query Interface -->
        <div class="row">
            <div class="col-12">
                <div style="background: white; padding: 25px; border-radius: 15px; box-shadow: 0 5px 15px rgba(0,0,0,0.1);">
                    <h4 style="color: #333; margin-bottom: 20px;">💻 SQL Query Interface</h4>
                    
                    <form method="post" action="">
                        <?php echo CSRFProtection::getTokenField(); ?>
                        <div class="form-group">
                            <label style="font-weight: bold; color: #333;">Enter SQL Query:</label>
                            <textarea name="sql_query" class="form-control sql-editor" placeholder="SELECT * FROM loginusers;"><?php echo isset($_POST['sql_query']) ? htmlspecialchars($_POST['sql_query']) : ''; ?></textarea>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <button type="submit" name="execute_query" class="btn btn-primary btn-lg">
                                    🚀 Execute Query
                                </button>
                                <button type="button" class="btn btn-secondary btn-lg ml-2" onclick="clearQuery()">
                                    🗑️ Clear
                                </button>
                            </div>
                            <div class="col-md-6 text-right">
                                <div class="btn-group">
                                    <button type="button" class="btn btn-outline-info" onclick="insertSampleQuery('SELECT * FROM loginusers;')">Users</button>
                                    <button type="button" class="btn btn-outline-info" onclick="insertSampleQuery('SELECT * FROM voters;')">Voters</button>
                                    <button type="button" class="btn btn-outline-info" onclick="insertSampleQuery('SELECT * FROM languages ORDER BY votecount DESC;')">Languages</button>
                                    <button type="button" class="btn btn-outline-info" onclick="insertSampleQuery('SELECT * FROM team_members ORDER BY votecount DESC;')">Team</button>
                                </div>
                            </div>
                        </div>
                    </form>

                    <?php
                    if (isset($_POST['execute_query']) && !empty($_POST['sql_query'])) {
                        // Verify CSRF token
                        if (!isset($_POST['csrf_token']) || !CSRFProtection::validateToken($_POST['csrf_token'])) {
                            echo "<div class='alert alert-danger'>🔒 Security token validation failed. Please refresh and try again.</div>";
                        } else {
                        $query = trim($_POST['sql_query']);
                        
                        // Basic security validation for admin queries
                        $dangerous_keywords = ['DROP DATABASE', 'DROP SCHEMA', 'TRUNCATE', 'DELETE FROM loginusers', 'DELETE FROM voters'];
                        $is_dangerous = false;
                        foreach ($dangerous_keywords as $keyword) {
                            if (stripos($query, $keyword) !== false) {
                                $is_dangerous = true;
                                break;
                            }
                        }
                        
                        echo "<div style='margin-top: 30px;'>";
                        echo "<h5 style='color: #333;'>📊 Query Results:</h5>";
                        echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 10px; margin-bottom: 15px;'>";
                        echo "<code>" . htmlspecialchars($query) . "</code>";
                        echo "</div>";
                        
                        if ($is_dangerous) {
                            echo "<div class='alert alert-danger'>❌ This query contains potentially dangerous operations and has been blocked for security.</div>";
                        } else {
                            try {
                                $result = mysqli_query($con, $query);
                            
                            if ($result) {
                                if (mysqli_num_rows($result) > 0) {
                                    echo "<div class='result-table'>";
                                    echo "<table class='table table-striped table-hover mb-0'>";
                                    
                                    // Get column names
                                    $fields = mysqli_fetch_fields($result);
                                    echo "<thead style='background: linear-gradient(45deg, #667eea, #764ba2); color: white; position: sticky; top: 0;'><tr>";
                                    foreach ($fields as $field) {
                                        echo "<th>" . $field->name . "</th>";
                                    }
                                    echo "</tr></thead><tbody>";
                                    
                                    // Display data
                                    while ($row = mysqli_fetch_assoc($result)) {
                                        echo "<tr>";
                                        foreach ($row as $value) {
                                            echo "<td>" . htmlspecialchars($value) . "</td>";
                                        }
                                        echo "</tr>";
                                    }
                                    echo "</tbody></table>";
                                    echo "</div>";
                                    echo "<p class='mt-2 text-muted'>Rows returned: " . mysqli_num_rows($result) . "</p>";
                                } else {
                                    echo "<div class='alert alert-info'>✅ Query executed successfully. No rows returned.</div>";
                                    if (mysqli_affected_rows($con) > 0) {
                                        echo "<p class='text-success'>Affected rows: " . mysqli_affected_rows($con) . "</p>";
                                    }
                                }
                            } else {
                                echo "<div class='alert alert-danger'>❌ Error: " . mysqli_error($con) . "</div>";
                            }
                            } catch (Exception $e) {
                                echo "<div class='alert alert-danger'>❌ Exception: " . $e->getMessage() . "</div>";
                            }
                        }
                        echo "</div>";
                        }
                    }
                    ?>
                </div>
            </div>
        </div>

        <!-- Database Schema -->
        <div class="row mt-4">
            <div class="col-12">
                <div style="background: white; padding: 25px; border-radius: 15px; box-shadow: 0 5px 15px rgba(0,0,0,0.1);">
                    <h4 style="color: #333; margin-bottom: 20px;">🗂️ Database Schema</h4>
                    
                    <div class="row">
                        <div class="col-md-3">
                            <div style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); padding: 20px; border-radius: 10px; color: white; margin-bottom: 15px;">
                                <h6 style="margin: 0; font-weight: bold;">👥 loginusers</h6>
                                <small>id, username, password, rank, status</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%); padding: 20px; border-radius: 10px; color: white; margin-bottom: 15px;">
                                <h6 style="margin: 0; font-weight: bold;">🗳️ voters</h6>
                                <small>firstname, lastname, username, status, voted, team_voted</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 20px; border-radius: 10px; color: white; margin-bottom: 15px;">
                                <h6 style="margin: 0; font-weight: bold;">🚀 languages</h6>
                                <small>lan_id, fullname, about, votecount</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); padding: 20px; border-radius: 10px; color: white; margin-bottom: 15px;">
                                <h6 style="margin: 0; font-weight: bold;">👑 team_members</h6>
                                <small>member_id, fullname, about, votecount</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer style="background: linear-gradient(45deg, #667eea, #764ba2); color: white; text-align: center; padding: 20px; margin-top: 50px;">
        <p style="margin: 0;">© 2025 Online Voting System - Database Manager | Developed by <strong>Himanshu Kumar</strong></p>
    </footer>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
    
    <script>
    function insertSampleQuery(query) {
        document.querySelector('textarea[name="sql_query"]').value = query;
    }
    
    function clearQuery() {
        document.querySelector('textarea[name="sql_query"]').value = '';
    }
    
    function showTables() {
        insertSampleQuery('SHOW TABLES;');
    }
    
    function showBackup() {
        alert('Database backup functionality would typically export the entire database structure and data. For now, use the Export Data button to get CSV exports.');
    }
    </script>
</body>
</html>