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
    <title>Database Viewer - Online Voting System</title>
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f8f9fa; }
        .admin-container { background: white; padding: 30px; border-radius: 10px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); margin: 20px 0; }
        table { font-size: 12px; }
        .table-container { max-height: 400px; overflow-y: auto; }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="admin-container">
            <h2>🗃️ Database Viewer</h2>
            <p>Welcome, <?php echo htmlspecialchars($_SESSION['admin_username'] ?? 'Admin'); ?>!</p>
            
            <div class="row">
                <div class="col-md-6">
                    <h4>📱 Programming Languages</h4>
                    <div class="table-container">
                        <?php
                        $result = mysqli_query($con, "SELECT * FROM languages ORDER BY votecount DESC");
                        if ($result && mysqli_num_rows($result) > 0) {
                            echo "<table class='table table-striped table-sm'>";
                            echo "<tr><th>ID</th><th>Language</th><th>Description</th><th>Votes</th></tr>";
                            while ($row = mysqli_fetch_assoc($result)) {
                                echo "<tr>";
                                echo "<td>" . htmlspecialchars($row['lan_id']) . "</td>";
                                echo "<td><strong>" . htmlspecialchars($row['fullname']) . "</strong></td>";
                                echo "<td>" . htmlspecialchars($row['about']) . "</td>";
                                echo "<td><span class='badge badge-primary'>" . htmlspecialchars($row['votecount']) . "</span></td>";
                                echo "</tr>";
                            }
                            echo "</table>";
                        } else {
                            echo "<p>No programming languages found.</p>";
                        }
                        ?>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <h4>👥 Team Members</h4>
                    <div class="table-container">
                        <?php
                        $result = mysqli_query($con, "SELECT * FROM team_members ORDER BY votecount DESC");
                        if ($result && mysqli_num_rows($result) > 0) {
                            echo "<table class='table table-striped table-sm'>";
                            echo "<tr><th>ID</th><th>Member</th><th>Description</th><th>Votes</th></tr>";
                            while ($row = mysqli_fetch_assoc($result)) {
                                echo "<tr>";
                                echo "<td>" . htmlspecialchars($row['member_id']) . "</td>";
                                echo "<td><strong>" . htmlspecialchars($row['fullname']) . "</strong></td>";
                                echo "<td>" . htmlspecialchars($row['about']) . "</td>";
                                echo "<td><span class='badge badge-success'>" . htmlspecialchars($row['votecount']) . "</span></td>";
                                echo "</tr>";
                            }
                            echo "</table>";
                        } else {
                            echo "<p>No team members found.</p>";
                        }
                        ?>
                    </div>
                </div>
            </div>
            
            <hr>
            
            <div class="row">
                <div class="col-md-6">
                    <h4>👤 Login Users</h4>
                    <div class="table-container">
                        <?php
                        $result = mysqli_query($con, "SELECT id, username, rank, status FROM loginusers ORDER BY id DESC");
                        if ($result && mysqli_num_rows($result) > 0) {
                            echo "<table class='table table-striped table-sm'>";
                            echo "<tr><th>ID</th><th>Username</th><th>Rank</th><th>Status</th></tr>";
                            while ($row = mysqli_fetch_assoc($result)) {
                                echo "<tr>";
                                echo "<td>" . htmlspecialchars($row['id']) . "</td>";
                                echo "<td><strong>" . htmlspecialchars($row['username']) . "</strong></td>";
                                echo "<td><span class='badge " . ($row['rank'] == 'admin' ? 'badge-danger' : 'badge-info') . "'>" . htmlspecialchars($row['rank']) . "</span></td>";
                                echo "<td><span class='badge " . ($row['status'] == 'ACTIVE' ? 'badge-success' : 'badge-warning') . "'>" . htmlspecialchars($row['status']) . "</span></td>";
                                echo "</tr>";
                            }
                            echo "</table>";
                        } else {
                            echo "<p>No login users found.</p>";
                        }
                        ?>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <h4>🗳️ Voters</h4>
                    <div class="table-container">
                        <?php
                        $result = mysqli_query($con, "SELECT * FROM voters ORDER BY username");
                        if ($result && mysqli_num_rows($result) > 0) {
                            echo "<table class='table table-striped table-sm'>";
                            echo "<tr><th>Name</th><th>Username</th><th>Status</th><th>Lang Vote</th><th>Team Vote</th></tr>";
                            while ($row = mysqli_fetch_assoc($result)) {
                                echo "<tr>";
                                echo "<td>" . htmlspecialchars($row['firstname'] . ' ' . $row['lastname']) . "</td>";
                                echo "<td><strong>" . htmlspecialchars($row['username']) . "</strong></td>";
                                echo "<td><span class='badge " . ($row['status'] == 'VOTED' ? 'badge-success' : 'badge-warning') . "'>" . htmlspecialchars($row['status']) . "</span></td>";
                                echo "<td>" . htmlspecialchars($row['voted'] ?? 'None') . "</td>";
                                echo "<td>" . htmlspecialchars($row['team_voted'] ?? 'None') . "</td>";
                                echo "</tr>";
                            }
                            echo "</table>";
                        } else {
                            echo "<p>No voters found.</p>";
                        }
                        ?>
                    </div>
                </div>
            </div>
            
            <hr>
            
            <h4>📊 Voting Statistics</h4>
            <div class="row">
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <h5 class="card-title">📱 Language Votes</h5>
                            <?php
                            $result = mysqli_query($con, "SELECT SUM(votecount) as total FROM languages");
                            $total = mysqli_fetch_assoc($result)['total'] ?? 0;
                            echo "<h3 class='text-primary'>$total</h3>";
                            ?>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <h5 class="card-title">👥 Team Votes</h5>
                            <?php
                            $result = mysqli_query($con, "SELECT SUM(votecount) as total FROM team_members");
                            $total = mysqli_fetch_assoc($result)['total'] ?? 0;
                            echo "<h3 class='text-success'>$total</h3>";
                            ?>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <h5 class="card-title">👤 Total Users</h5>
                            <?php
                            $result = mysqli_query($con, "SELECT COUNT(*) as total FROM loginusers");
                            $total = mysqli_fetch_assoc($result)['total'] ?? 0;
                            echo "<h3 class='text-info'>$total</h3>";
                            ?>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <h5 class="card-title">🗳️ Voters</h5>
                            <?php
                            $result = mysqli_query($con, "SELECT COUNT(*) as total FROM voters");
                            $total = mysqli_fetch_assoc($result)['total'] ?? 0;
                            echo "<h3 class='text-warning'>$total</h3>";
                            ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="text-center" style="margin-top: 30px;">
                <a href="basic_admin_panel.php" class="btn btn-primary">🔧 Admin Panel</a>
                <a href="multi_question_results.php" class="btn btn-info">📊 Results</a>
                <a href="index.php" class="btn btn-secondary">🏠 Home</a>
                <a href="admin_login.php?logout=1" class="btn btn-danger">🚪 Logout</a>
            </div>
        </div>
    </div>
</body>
</html>