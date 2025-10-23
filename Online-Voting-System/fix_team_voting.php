<?php
include "connection.php";
session_start();

echo "<h2>🔧 Fix Team Voting Issue</h2>";

// Get current user
$username = $_SESSION['SESS_NAME'] ?? 'test_user';
echo "<p>Current user: <strong>" . htmlspecialchars($username) . "</strong></p>";

// Check current team voting status
echo "<h3>Current Team Voting Status:</h3>";
$result = mysqli_query($con, "SELECT * FROM team_member_votes WHERE username = '$username'");
if ($result) {
    $count = mysqli_num_rows($result);
    echo "<p>Team votes for this user: <strong>$count</strong></p>";
    
    if ($count > 0) {
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>ID</th><th>Username</th><th>Member ID</th><th>Voted At</th></tr>";
        while ($row = mysqli_fetch_assoc($result)) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row['id']) . "</td>";
            echo "<td>" . htmlspecialchars($row['username']) . "</td>";
            echo "<td>" . htmlspecialchars($row['member_id']) . "</td>";
            echo "<td>" . htmlspecialchars($row['voted_at']) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
} else {
    echo "<p style='color: red;'>Error checking team votes: " . mysqli_error($con) . "</p>";
}

// Check team_members table
echo "<h3>Team Members Table:</h3>";
$result = mysqli_query($con, "SELECT * FROM team_members");
if ($result && mysqli_num_rows($result) > 0) {
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>ID</th><th>Name</th><th>Description</th><th>Votes</th></tr>";
    while ($row = mysqli_fetch_assoc($result)) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['member_id']) . "</td>";
        echo "<td>" . htmlspecialchars($row['fullname']) . "</td>";
        echo "<td>" . htmlspecialchars($row['about']) . "</td>";
        echo "<td>" . htmlspecialchars($row['votecount']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color: red;'>❌ No team members found!</p>";
}

// Fix options
echo "<h3>🛠️ Fix Options:</h3>";

if (isset($_POST['action'])) {
    switch ($_POST['action']) {
        case 'clear_team_votes':
            mysqli_query($con, "DELETE FROM team_member_votes WHERE username = '$username'");
            echo "<div style='background: #d4edda; padding: 10px; margin: 10px 0; border-radius: 5px;'>✅ Cleared team votes for $username</div>";
            break;
            
        case 'clear_all_team_votes':
            mysqli_query($con, "DELETE FROM team_member_votes");
            echo "<div style='background: #d4edda; padding: 10px; margin: 10px 0; border-radius: 5px;'>✅ Cleared ALL team votes</div>";
            break;
            
        case 'reset_team_table':
            mysqli_query($con, "DROP TABLE IF EXISTS team_member_votes");
            mysqli_query($con, "CREATE TABLE team_member_votes (
                id INT AUTO_INCREMENT PRIMARY KEY,
                username VARCHAR(50) NOT NULL,
                member_id INT NOT NULL,
                voted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY unique_vote (username)
            )");
            echo "<div style='background: #d4edda; padding: 10px; margin: 10px 0; border-radius: 5px;'>✅ Reset team_member_votes table</div>";
            break;
            
        case 'add_team_members':
            // Clear existing team members
            mysqli_query($con, "DELETE FROM team_members");
            
            // Add fresh team members
            $team_members = [
                ['Himanshu', 'Team Leader & Full Stack Developer'],
                ['Praffull', 'Backend Developer & Database Expert'],
                ['Bhavesh', 'Team Member - Developer and contributor'],
                ['Eve', 'Team Member - Developer and contributor']
            ];
            
            foreach ($team_members as $member) {
                $stmt = mysqli_prepare($con, "INSERT INTO team_members (fullname, about, votecount) VALUES (?, ?, 0)");
                mysqli_stmt_bind_param($stmt, "ss", $member[0], $member[1]);
                if (mysqli_stmt_execute($stmt)) {
                    echo "<p style='color: green;'>✅ Added: " . htmlspecialchars($member[0]) . "</p>";
                } else {
                    echo "<p style='color: red;'>❌ Failed to add: " . htmlspecialchars($member[0]) . "</p>";
                }
                mysqli_stmt_close($stmt);
            }
            break;
    }
    
    // Refresh the page to show updated data
    echo "<script>setTimeout(function(){ location.reload(); }, 2000);</script>";
}
?>

<form method="post" style="margin: 10px 0;">
    <input type="hidden" name="action" value="clear_team_votes">
    <button type="submit" style="background: #ffc107; color: black; padding: 10px 15px; border: none; border-radius: 5px;">
        🗑️ Clear My Team Votes Only
    </button>
</form>

<form method="post" style="margin: 10px 0;">
    <input type="hidden" name="action" value="clear_all_team_votes">
    <button type="submit" style="background: #dc3545; color: white; padding: 10px 15px; border: none; border-radius: 5px;" onclick="return confirm('Clear ALL team votes?')">
        🗑️ Clear ALL Team Votes
    </button>
</form>

<form method="post" style="margin: 10px 0;">
    <input type="hidden" name="action" value="reset_team_table">
    <button type="submit" style="background: #17a2b8; color: white; padding: 10px 15px; border: none; border-radius: 5px;" onclick="return confirm('Reset team votes table?')">
        🔄 Reset Team Votes Table
    </button>
</form>

<form method="post" style="margin: 10px 0;">
    <input type="hidden" name="action" value="add_team_members">
    <button type="submit" style="background: #28a745; color: white; padding: 10px 15px; border: none; border-radius: 5px;">
        ➕ Add/Refresh Team Members
    </button>
</form>

<hr>
<h3>🔗 Test Links:</h3>
<p><a href="fresh_multi_vote.php">🗳️ Fresh Multi-Question Voting</a></p>
<p><a href="multi_question_voter.php">🗳️ Original Multi-Question Voting</a></p>
<p><a href="multi_question_results.php">📊 Results</a></p>
<p><a href="no_csrf_admin.php?pass=himanshu123">🔧 Admin Panel</a></p>