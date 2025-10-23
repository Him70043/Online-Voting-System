<?php
session_start();
include "connection.php";

// Simple login check
if (!isset($_SESSION['user_id'])) {
    echo "<h3>Please login first</h3>";
    echo "<p><a href='simple_login.php'>🔐 Login Here</a></p>";
    exit();
}

$username = $_SESSION['SESS_NAME'];

// Check voting status
$lang_voted = false;
$team_voted = false;

// Check language vote
$result = mysqli_query($con, "SELECT status FROM voters WHERE username = '$username'");
if ($result && mysqli_num_rows($result) > 0) {
    $row = mysqli_fetch_assoc($result);
    $lang_voted = ($row['status'] === 'VOTED');
}

// Check team vote
$result = mysqli_query($con, "SELECT COUNT(*) as count FROM team_member_votes WHERE username = '$username'");
if ($result) {
    $row = mysqli_fetch_assoc($result);
    $team_voted = ($row['count'] > 0);
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Fresh Multi-Question Voting</title>
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; }
        .container { background: white; margin: 50px auto; padding: 30px; border-radius: 15px; max-width: 800px; }
        .question { background: #f8f9fa; padding: 20px; margin: 20px 0; border-radius: 10px; }
        .voted { background: #d4edda; }
        .option { background: #e3f2fd; padding: 10px; margin: 5px 0; border-radius: 5px; }
        .btn { margin: 5px; }
    </style>
</head>
<body>
    <div class="container">
        <h2>🗳️ Fresh Multi-Question Voting</h2>
        <p>Welcome, <strong><?php echo htmlspecialchars($username); ?></strong>!</p>
        
        <!-- Question 1: Programming Languages -->
        <div class="question <?php echo $lang_voted ? 'voted' : ''; ?>">
            <h3>📱 Question 1: Favorite Programming Language</h3>
            
            <?php if ($lang_voted): ?>
                <div class="alert alert-success">✅ You have already voted for programming languages!</div>
            <?php else: ?>
                <p>Choose your favorite programming language:</p>
                <form action="multi_question_vote_action.php" method="post">
                    <input type="hidden" name="question_type" value="language">
                    <?php
                    $result = mysqli_query($con, "SELECT * FROM languages ORDER BY fullname");
                    if ($result && mysqli_num_rows($result) > 0) {
                        while ($row = mysqli_fetch_assoc($result)) {
                            echo '<div class="option">';
                            echo '<strong>' . htmlspecialchars($row['fullname']) . '</strong> - ';
                            echo htmlspecialchars($row['about']);
                            echo ' <button type="submit" name="vote" value="' . $row['lan_id'] . '" class="btn btn-primary btn-sm">Vote</button>';
                            echo '</div>';
                        }
                    } else {
                        echo '<p style="color: red;">No programming languages found!</p>';
                    }
                    ?>
                </form>
            <?php endif; ?>
        </div>
        
        <!-- Question 2: Team Members -->
        <div class="question <?php echo $team_voted ? 'voted' : ''; ?>">
            <h3>👥 Question 2: Best Team Member</h3>
            
            <?php if ($team_voted): ?>
                <div class="alert alert-success">✅ You have already voted for team members!</div>
            <?php else: ?>
                <p>Vote for the best team member:</p>
                <form action="multi_question_vote_action.php" method="post">
                    <input type="hidden" name="question_type" value="team">
                    <?php
                    $result = mysqli_query($con, "SELECT * FROM team_members ORDER BY fullname");
                    if ($result && mysqli_num_rows($result) > 0) {
                        while ($row = mysqli_fetch_assoc($result)) {
                            echo '<div class="option">';
                            echo '<strong>' . htmlspecialchars($row['fullname']) . '</strong> - ';
                            echo htmlspecialchars($row['about']);
                            echo ' <button type="submit" name="vote" value="' . $row['member_id'] . '" class="btn btn-success btn-sm">Vote</button>';
                            echo '</div>';
                        }
                    } else {
                        echo '<p style="color: red;">No team members found!</p>';
                        echo '<p><a href="diagnose_issues.php">🔍 Run Diagnostics</a> to fix this.</p>';
                    }
                    ?>
                </form>
            <?php endif; ?>
        </div>
        
        <div class="text-center">
            <a href="multi_question_results.php" class="btn btn-info">📊 View Results</a>
            <a href="diagnose_issues.php" class="btn btn-warning">🔍 Diagnostics</a>
            <a href="no_csrf_admin.php?pass=himanshu123" class="btn btn-secondary">🔧 Admin</a>
            <a href="index.php" class="btn btn-primary">🏠 Home</a>
        </div>
    </div>
</body>
</html>