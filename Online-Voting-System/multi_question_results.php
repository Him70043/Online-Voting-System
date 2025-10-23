<?php
session_start();
include "connection.php";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>📊 Multi-Question Results - Online Voting System</title>
    
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
        .results-container {
            background: rgba(255,255,255,0.95);
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.3);
            margin-top: 50px;
        }
        .question-results {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            padding: 20px;
            border-radius: 15px;
            margin: 20px 0;
            color: white;
        }
        .result-card {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            padding: 15px;
            border-radius: 10px;
            margin: 8px 0;
            color: white;
            transition: all 0.3s;
        }
        .result-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.3);
        }
        .team-result-card {
            background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%);
            color: #333;
        }
        .winner-card {
            background: linear-gradient(135deg, #FFD700, #FFA500) !important;
            border: 3px solid #FF6B6B;
        }
        .progress-custom {
            height: 25px;
            border-radius: 12px;
            background: rgba(255,255,255,0.3);
        }
        .progress-bar-custom {
            background: linear-gradient(45deg, #FF6B6B, #4ECDC4);
            border-radius: 12px;
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
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a class="nav-link" href="multi_question_voter.php">🗳️ Vote</a>
                    <a class="nav-link" href="simple_logout.php">🚪 Logout</a>
                <?php else: ?>
                    <a class="nav-link" href="simple_login.php">🔐 Login</a>
                    <a class="nav-link" href="simple_register.php">📝 Register</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="results-container">
                    <div class="text-center mb-4">
                        <h2 style="color: #333; font-family: 'Ubuntu', sans-serif;">📊 Multi-Question Voting Results</h2>
                        <p style="color: #666;">Real-time results for all voting questions</p>
                    </div>

                    <!-- Question 1 Results: Programming Languages -->
                    <div class="question-results">
                        <h3>📱 Question 1: Favorite Programming Language</h3>
                        
                        <?php
                        // Get total votes for languages
                        $total_result = mysqli_query($con, "SELECT SUM(votecount) as total FROM languages");
                        $total_row = mysqli_fetch_assoc($total_result);
                        $total_lang_votes = $total_row['total'] ?? 0;
                        
                        if ($total_lang_votes > 0) {
                            echo '<p>Total votes: <strong>' . $total_lang_votes . '</strong></p>';
                            
                            $result = mysqli_query($con, "SELECT * FROM languages ORDER BY votecount DESC");
                            if ($result && mysqli_num_rows($result) > 0) {
                                $position = 1;
                                while ($row = mysqli_fetch_assoc($result)) {
                                    $percentage = round(($row['votecount'] / $total_lang_votes) * 100, 1);
                                    $isWinner = ($position === 1 && $row['votecount'] > 0);
                                    
                                    echo '<div class="result-card' . ($isWinner ? ' winner-card' : '') . '">';
                                    echo '<div class="d-flex justify-content-between align-items-center mb-2">';
                                    echo '<div>';
                                    echo '<h5>' . ($isWinner ? '🏆 ' : '#' . $position . ' ') . htmlspecialchars($row['fullname']) . '</h5>';
                                    echo '<small>' . htmlspecialchars($row['about']) . '</small>';
                                    echo '</div>';
                                    echo '<div class="text-end">';
                                    echo '<strong>' . $row['votecount'] . ' votes</strong><br>';
                                    echo '<small>' . $percentage . '%</small>';
                                    echo '</div>';
                                    echo '</div>';
                                    
                                    echo '<div class="progress progress-custom">';
                                    echo '<div class="progress-bar progress-bar-custom" style="width: ' . $percentage . '%">';
                                    echo $percentage . '%';
                                    echo '</div>';
                                    echo '</div>';
                                    echo '</div>';
                                    
                                    $position++;
                                }
                            }
                        } else {
                            echo '<div class="alert alert-info">No votes cast for programming languages yet.</div>';
                        }
                        ?>
                    </div>

                    <!-- Question 2 Results: Team Members -->
                    <div class="question-results" style="background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%); color: #333;">
                        <h3>👥 Question 2: Best Team Member</h3>
                        
                        <?php
                        // Get total votes for team members
                        $total_result = mysqli_query($con, "SELECT SUM(votecount) as total FROM team_members");
                        $total_row = mysqli_fetch_assoc($total_result);
                        $total_team_votes = $total_row['total'] ?? 0;
                        
                        if ($total_team_votes > 0) {
                            echo '<p>Total votes: <strong>' . $total_team_votes . '</strong></p>';
                            
                            $result = mysqli_query($con, "SELECT * FROM team_members ORDER BY votecount DESC");
                            if ($result && mysqli_num_rows($result) > 0) {
                                $position = 1;
                                while ($row = mysqli_fetch_assoc($result)) {
                                    $percentage = round(($row['votecount'] / $total_team_votes) * 100, 1);
                                    $isWinner = ($position === 1 && $row['votecount'] > 0);
                                    
                                    echo '<div class="result-card team-result-card' . ($isWinner ? ' winner-card' : '') . '">';
                                    echo '<div class="d-flex justify-content-between align-items-center mb-2">';
                                    echo '<div>';
                                    echo '<h5>' . ($isWinner ? '🏆 ' : '#' . $position . ' ') . htmlspecialchars($row['fullname']) . '</h5>';
                                    echo '<small>' . htmlspecialchars($row['about']) . '</small>';
                                    echo '</div>';
                                    echo '<div class="text-end">';
                                    echo '<strong>' . $row['votecount'] . ' votes</strong><br>';
                                    echo '<small>' . $percentage . '%</small>';
                                    echo '</div>';
                                    echo '</div>';
                                    
                                    echo '<div class="progress progress-custom">';
                                    echo '<div class="progress-bar progress-bar-custom" style="width: ' . $percentage . '%">';
                                    echo $percentage . '%';
                                    echo '</div>';
                                    echo '</div>';
                                    echo '</div>';
                                    
                                    $position++;
                                }
                            }
                        } else {
                            echo '<div class="alert alert-info">No votes cast for team members yet.</div>';
                        }
                        ?>
                    </div>
                    
                    <div class="text-center mt-4">
                        <a href="index.php" class="btn btn-outline-primary me-2">🏠 Home</a>
                        <?php if (isset($_SESSION['user_id'])): ?>
                            <a href="multi_question_voter.php" class="btn btn-outline-success">🗳️ Vote Now</a>
                        <?php else: ?>
                            <a href="simple_login.php" class="btn btn-outline-success">🔐 Login to Vote</a>
                        <?php endif; ?>
                    </div>
                    
                    <div class="text-center mt-3">
                        <small style="color: #666;">
                            Results update in real-time • Last updated: <?php echo date('Y-m-d H:i:s'); ?>
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>