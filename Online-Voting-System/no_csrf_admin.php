<?php
include "connection.php";

// Very simple admin check - just check if they know the password
$admin_password = "himanshu123";
$is_admin = false;

if (isset($_POST['admin_pass']) && $_POST['admin_pass'] === $admin_password) {
    $is_admin = true;
} elseif (isset($_GET['pass']) && $_GET['pass'] === $admin_password) {
    $is_admin = true;
}

if (!$is_admin) {
    echo "<form method='post'>";
    echo "<h3>Admin Access</h3>";
    echo "<input type='password' name='admin_pass' placeholder='Enter admin password' required>";
    echo "<button type='submit'>Access Admin</button>";
    echo "<p><small>Password: himanshu123</small></p>";
    echo "</form>";
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>No CSRF Admin Panel</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        table { border-collapse: collapse; width: 100%; margin: 10px 0; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .btn { padding: 10px 15px; margin: 5px; background: #007bff; color: white; border: none; cursor: pointer; }
        .btn-danger { background: #dc3545; }
        .btn-success { background: #28a745; }
        textarea { width: 100%; height: 100px; }
    </style>
</head>
<body>
    <h2>🔧 No CSRF Admin Panel</h2>
    
    <?php
    // Handle actions
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'reset_votes':
                mysqli_query($con, "UPDATE languages SET votecount = 0");
                mysqli_query($con, "UPDATE team_members SET votecount = 0");
                mysqli_query($con, "DELETE FROM votes");
                mysqli_query($con, "DELETE FROM team_member_votes");
                mysqli_query($con, "UPDATE voters SET status = 'NOTVOTED', voted = NULL, team_voted = NULL");
                echo "<div style='background: #d4edda; padding: 10px; margin: 10px 0; border-radius: 5px;'>✅ All votes reset!</div>";
                break;
        }
    }
    
    // Handle SQL queries
    if (isset($_POST['sql_query']) && !empty(trim($_POST['sql_query']))) {
        $sql = trim($_POST['sql_query']);
        echo "<h3>Query Results:</h3>";
        echo "<p><strong>Query:</strong> " . htmlspecialchars($sql) . "</p>";
        
        $result = mysqli_query($con, $sql);
        if ($result) {
            if (mysqli_num_rows($result) > 0) {
                echo "<table>";
                
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
                echo "<p>Query executed successfully. No results returned.</p>";
            }
        } else {
            echo "<p style='color: red;'>❌ Query Error: " . htmlspecialchars(mysqli_error($con)) . "</p>";
        }
    }
    ?>
    
    <h3>🔄 Quick Actions</h3>
    <form method="post" style="display: inline;">
        <input type="hidden" name="action" value="reset_votes">
        <button type="submit" class="btn btn-danger" onclick="return confirm('Reset all votes?')">🔄 Reset All Votes</button>
    </form>
    
    <h3>📊 Quick Queries</h3>
    <form method="post" style="display: inline;">
        <input type="hidden" name="sql_query" value="SELECT * FROM languages ORDER BY votecount DESC;">
        <button type="submit" class="btn">📱 Languages</button>
    </form>
    
    <form method="post" style="display: inline;">
        <input type="hidden" name="sql_query" value="SELECT * FROM team_members ORDER BY votecount DESC;">
        <button type="submit" class="btn">👥 Team Members</button>
    </form>
    
    <form method="post" style="display: inline;">
        <input type="hidden" name="sql_query" value="SELECT * FROM voters;">
        <button type="submit" class="btn">🗳️ Voters</button>
    </form>
    
    <form method="post" style="display: inline;">
        <input type="hidden" name="sql_query" value="SELECT * FROM loginusers;">
        <button type="submit" class="btn">👤 Users</button>
    </form>
    
    <form method="post" style="display: inline;">
        <input type="hidden" name="sql_query" value="SHOW TABLES;">
        <button type="submit" class="btn">📋 Tables</button>
    </form>
    
    <h3>💻 SQL Query Interface</h3>
    <form method="post">
        <textarea name="sql_query" placeholder="Enter your SQL query here..."><?php echo isset($_POST['sql_query']) ? htmlspecialchars($_POST['sql_query']) : 'SELECT * FROM languages;'; ?></textarea><br>
        <button type="submit" class="btn btn-success">🚀 Execute Query</button>
        <button type="button" onclick="document.querySelector('textarea').value=''">🗑️ Clear</button>
    </form>
    
    <hr>
    <p><a href="multi_question_voter.php">🗳️ Go Vote</a> | <a href="multi_question_results.php">📊 Results</a> | <a href="index.php">🏠 Home</a></p>
</body>
</html>