<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    header("Location: simple_login.php?error=Please login to access the voting system");
    exit();
}

include "connection.php";

// Get user information
$username = $_SESSION['SESS_NAME'];

// Check if user has already voted for programming languages
$stmt = mysqli_prepare($con, "SELECT status FROM voters WHERE username = ?");
mysqli_stmt_bind_param($stmt, "s", $username);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$voter_info = mysqli_fetch_assoc($result);

$has_voted_languages = ($voter_info && $voter_info['status'] === 'VOTED');

// Create team_member_votes table if it doesn't exist
mysqli_query($con, "CREATE TABLE IF NOT EXISTS team_member_votes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL,
    member_id INT NOT NULL,
    voted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_vote (username)
)");

// Check if user has already voted for team members (we'll use a separate tracking)
$stmt2 = mysqli_prepare($con, "SELECT COUNT(*) as count FROM team_member_votes WHERE username = ?");
mysqli_stmt_bind_param($stmt2, "s", $username);
mysqli_stmt_execute($stmt2);
$result2 = mysqli_stmt_get_result($stmt2);
$team_vote_info = mysqli_fetch_assoc($result2);
$has_voted_team = ($team_vote_info && $team_vote_info['count'] > 0);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>🗳️ Multi-Question Voting - Online Voting System</title>
    
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
        .voting-container {
            background: rgba(255,255,255,0.95);
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.3);
            margin-top: 50px;
        }
        .question-section {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            padding: 20px;
            border-radius: 15px;
            margin: 20px 0;
            color: white;
        }
        .candidate-card {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            padding: 15px;
            border-radius: 10px;
            margin: 8px 0;
            color: white;
            transition: all 0.3s;
        }
        .candidate-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.3);
        }
        .team-card {
            background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%);
            color: #333;
        }
        .btn-vote {
            background: linear-gradient(45deg, #FF6B6B, #4ECDC4);
            border: none;
            color: white;
            padding: 8px 20px;
            border-radius: 20px;
            font-weight: bold;
        }
        .btn-vote:hover {
            background: linear-gradient(45deg, #4ECDC4, #FF6B6B);
            color: white;
        }
        .voted-section {
            background: linear-gradient(135deg, #56ab2f 0%, #a8e6cf 100%);
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark" style="background: rgba(0,0,0,0.8);">
        <div class="container">
            <a class="navbar-brand" href="index.php" style="font-family: 'Ubuntu', sans-serif; font-size: 24px;">
                🗳️ Online Voting System
            </a>
            <div class="navbar-nav ms-auto">
                <span class="navbar-text me-3" style="color: #fff;">👤 Welcome, <strong><?php echo htmlspecialchars($username); ?></strong></span>
                <a class="nav-link" href="multi_question_results.php">📊 Results</a>
                <a class="nav-link" href="simple_logout.php">🚪 Logout</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="voting-container">
                    <div class="text-center mb-4">
                        <h2 style="color: #333; font-family: 'Ubuntu', sans-serif;">🗳️ Multi-Question Voting</h2>
                        <p style="color: #666;">Vote on multiple questions - each vote is independent</p>
                    </div>

                    <!-- Question 1: Programming Languages -->
                    <div class="question-section <?php echo $has_voted_languages ? 'voted-section' : ''; ?>">
                        <h3>📱 Question 1: What's your favorite programming language?</h3>
                        <p>Choose the programming language you prefer most</p>
                        
                        <?php if ($has_voted_languages): ?>
                            <div class="alert alert-success">
                                <h5>✅ You have already voted for programming languages!</h5>
                                <p>Thank you for participating in this question.</p>
                            </div>
                        <?php else: ?>
                            <form action="multi_question_vote_action.php" method="post">
                                <input type="hidden" name="question_type" value="language">
                                <div class="row">
                                    <?php
                                    $result = mysqli_query($con, "SELECT * FROM languages ORDER BY fullname");
                                    if ($result && mysqli_num_rows($result) > 0) {
                                        while ($row = mysqli_fetch_assoc($result)) {
                                            echo '<div class="col-md-6 mb-2">';
                                            echo '<div class="candidate-card">';
                                            echo '<div class="d-flex justify-content-between align-items-center">';
                                            echo '<div>';
                                            echo '<h6>' . htmlspecialchars($row['fullname']) . '</h6>';
                                            echo '<small>' . htmlspecialchars($row['about']) . '</small>';
                                            echo '</div>';
                                            echo '<div>';
                                            echo '<button type="submit" name="vote" value="' . $row['lan_id'] . '" class="btn btn-vote btn-sm">';
                                            echo '🗳️ Vote';
                                            echo '</button>';
                                            echo '</div>';
                                            echo '</div>';
                                            echo '</div>';
                                            echo '</div>';
                                        }
                                    }
                                    ?>
                                </div>
                            </form>
                        <?php endif; ?>
                    </div>

                    <!-- Question 2: Team Members -->
                    <div class="question-section <?php echo $has_voted_team ? 'voted-section' : ''; ?>">
                        <h3>👥 Question 2: Who is the best team member?</h3>
                        <p>Vote for the team member you think contributes the most</p>
                        
                        <?php if ($has_voted_team): ?>
                            <div class="alert alert-success">
                                <h5>✅ You have already voted for team members!</h5>
                                <p>Thank you for participating in this question.</p>
                            </div>
                        <?php else: ?>
                            <form action="multi_question_vote_action.php" method="post">
                                <input type="hidden" name="question_type" value="team">
                                <div class="row">
                                    <?php
                                    $result = mysqli_query($con, "SELECT * FROM team_members ORDER BY fullname");
                                    if ($result && mysqli_num_rows($result) > 0) {
                                        while ($row = mysqli_fetch_assoc($result)) {
                                            echo '<div class="col-md-6 mb-2">';
                                            echo '<div class="candidate-card team-card">';
                                            echo '<div class="d-flex justify-content-between align-items-center">';
                                            echo '<div>';
                                            echo '<h6>' . htmlspecialchars($row['fullname']) . '</h6>';
                                            echo '<small>' . htmlspecialchars($row['about']) . '</small>';
                                            echo '</div>';
                                            echo '<div>';
                                            echo '<button type="submit" name="vote" value="' . $row['member_id'] . '" class="btn btn-vote btn-sm">';
                                            echo '🗳️ Vote';
                                            echo '</button>';
                                            echo '</div>';
                                            echo '</div>';
                                            echo '</div>';
                                            echo '</div>';
                                        }
                                    }
                                    ?>
                                </div>
                            </form>
                        <?php endif; ?>
                    </div>
                    
                    <div class="text-center mt-4">
                        <a href="multi_question_results.php" class="btn btn-outline-primary me-2">📊 View Results</a>
                        <a href="index.php" class="btn btn-outline-secondary">🏠 Home</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>