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

// Check if user has already voted
$stmt = mysqli_prepare($con, "SELECT status FROM voters WHERE username = ?");
mysqli_stmt_bind_param($stmt, "s", $username);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$voter_info = mysqli_fetch_assoc($result);

$has_voted = ($voter_info && $voter_info['status'] === 'VOTED');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>🗳️ Voting Panel - Online Voting System</title>
    
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
        .candidate-card {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            padding: 20px;
            border-radius: 15px;
            margin: 10px 0;
            color: white;
            transition: all 0.3s;
        }
        .candidate-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.3);
        }
        .btn-vote {
            background: linear-gradient(45deg, #FF6B6B, #4ECDC4);
            border: none;
            color: white;
            padding: 10px 25px;
            border-radius: 25px;
            font-weight: bold;
        }
        .btn-vote:hover {
            background: linear-gradient(45deg, #4ECDC4, #FF6B6B);
            color: white;
        }
        .voted-badge {
            background: linear-gradient(45deg, #28a745, #20c997);
            color: white;
            padding: 5px 15px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: bold;
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
                <a class="nav-link" href="simple_results.php">📊 Results</a>
                <a class="nav-link" href="simple_logout.php">🚪 Logout</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="voting-container">
                    <div class="text-center mb-4">
                        <h2 style="color: #333; font-family: 'Ubuntu', sans-serif;">🗳️ Cast Your Vote</h2>
                        <p style="color: #666;">Vote for your favorite programming language or best team member</p>
                        
                        <?php if ($has_voted): ?>
                            <div class="alert alert-success">
                                <h4>✅ You have already voted!</h4>
                                <p>Thank you for participating in the voting process.</p>
                                <a href="simple_results.php" class="btn btn-primary">📊 View Results</a>
                            </div>
                        <?php endif; ?>
                    </div>

                    <?php if (!$has_voted): ?>
                        <form action="simple_vote_action.php" method="post">
                            <div class="row">
                                <?php
                                // Get all voting options
                                $result = mysqli_query($con, "SELECT * FROM languages ORDER BY fullname");
                                if ($result && mysqli_num_rows($result) > 0) {
                                    while ($row = mysqli_fetch_assoc($result)) {
                                        echo '<div class="col-md-6 mb-3">';
                                        echo '<div class="candidate-card">';
                                        echo '<div class="d-flex justify-content-between align-items-center">';
                                        echo '<div>';
                                        echo '<h5>' . htmlspecialchars($row['fullname']) . '</h5>';
                                        echo '<p class="mb-2">' . htmlspecialchars($row['about']) . '</p>';
                                        echo '<small>Current votes: ' . $row['votecount'] . '</small>';
                                        echo '</div>';
                                        echo '<div>';
                                        echo '<button type="submit" name="vote" value="' . $row['lan_id'] . '" class="btn btn-vote">';
                                        echo '🗳️ Vote';
                                        echo '</button>';
                                        echo '</div>';
                                        echo '</div>';
                                        echo '</div>';
                                        echo '</div>';
                                    }
                                } else {
                                    echo '<div class="col-12">';
                                    echo '<div class="alert alert-warning text-center">';
                                    echo 'No voting options available at the moment.';
                                    echo '</div>';
                                    echo '</div>';
                                }
                                ?>
                            </div>
                        </form>
                    <?php else: ?>
                        <!-- Show current results for voted users -->
                        <div class="row">
                            <div class="col-12">
                                <h4 class="text-center mb-4">📊 Current Voting Results</h4>
                                <?php
                                $result = mysqli_query($con, "SELECT * FROM languages ORDER BY votecount DESC");
                                if ($result && mysqli_num_rows($result) > 0) {
                                    $total_result = mysqli_query($con, "SELECT SUM(votecount) as total FROM languages");
                                    $total_row = mysqli_fetch_assoc($total_result);
                                    $total_votes = ($total_row['total'] && $total_row['total'] > 0) ? $total_row['total'] : 1;
                                    
                                    while ($row = mysqli_fetch_assoc($result)) {
                                        $percentage = round(($row['votecount'] / $total_votes) * 100, 1);
                                        echo '<div class="mb-3">';
                                        echo '<div style="background: linear-gradient(45deg, #667eea, #764ba2); color: white; padding: 15px; border-radius: 10px;">';
                                        echo '<div class="d-flex justify-content-between align-items-center">';
                                        echo '<div>';
                                        echo '<h5>' . htmlspecialchars($row['fullname']) . '</h5>';
                                        echo '<p class="mb-0">' . htmlspecialchars($row['about']) . '</p>';
                                        echo '</div>';
                                        echo '<div class="text-end">';
                                        echo '<div class="voted-badge">' . $row['votecount'] . ' votes</div>';
                                        echo '<small>' . $percentage . '%</small>';
                                        echo '</div>';
                                        echo '</div>';
                                        echo '<div class="progress mt-2" style="height: 10px;">';
                                        echo '<div class="progress-bar bg-warning" style="width: ' . $percentage . '%"></div>';
                                        echo '</div>';
                                        echo '</div>';
                                        echo '</div>';
                                    }
                                }
                                ?>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <div class="text-center mt-4">
                        <a href="simple_results.php" class="btn btn-outline-primary me-2">📊 View Full Results</a>
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