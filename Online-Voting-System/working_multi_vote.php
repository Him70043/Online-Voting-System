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

// Simple voting status check - just check if records exist
$lang_voted = false;
$team_voted = false;

// Check language vote - look in voters table
$result = mysqli_query($con, "SELECT voted FROM voters WHERE username = '$username' AND voted IS NOT NULL");
$lang_voted = ($result && mysqli_num_rows($result) > 0);

// Check team vote - look in team_member_votes table
$result = mysqli_query($con, "SELECT id FROM team_member_votes WHERE username = '$username'");
$team_voted = ($result && mysqli_num_rows($result) > 0);

// Handle voting
if (isset($_POST['vote_language']) && !$lang_voted) {
    $language_id = (int)$_POST['language_id'];
    
    // Update language vote count
    mysqli_query($con, "UPDATE languages SET votecount = votecount + 1 WHERE lan_id = $language_id");
    
    // Update voter status
    $lang_name = mysqli_fetch_assoc(mysqli_query($con, "SELECT fullname FROM languages WHERE lan_id = $language_id"))['fullname'];
    mysqli_query($con, "UPDATE voters SET status = 'VOTED', voted = '$lang_name' WHERE username = '$username'");
    
    $lang_voted = true;
    echo "<div style='background: #d4edda; padding: 15px; margin: 10px 0; border-radius: 5px; color: #155724;'>✅ Language vote recorded!</div>";
}

if (isset($_POST['vote_team']) && !$team_voted) {
    $member_id = (int)$_POST['member_id'];
    
    // Update team member vote count
    mysqli_query($con, "UPDATE team_members SET votecount = votecount + 1 WHERE member_id = $member_id");
    
    // Record team vote
    mysqli_query($con, "INSERT INTO team_member_votes (username, member_id) VALUES ('$username', $member_id)");
    
    $team_voted = true;
    echo "<div style='background: #d4edda; padding: 15px; margin: 10px 0; border-radius: 5px; color: #155724;'>✅ Team vote recorded!</div>";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Working Multi-Question Voting</title>
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); 
            min-height: 100vh; 
            font-family: Arial, sans-serif;
        }
        .container { 
            background: white; 
            margin: 50px auto; 
            padding: 30px; 
            border-radius: 15px; 
            max-width: 900px; 
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
        }
        .question { 
            background: #f8f9fa; 
            padding: 25px; 
            margin: 25px 0; 
            border-radius: 15px; 
            border-left: 5px solid #007bff;
        }
        .voted { 
            background: #d4edda; 
            border-left: 5px solid #28a745;
        }
        .option { 
            background: white; 
            padding: 15px; 
            margin: 10px 0; 
            border-radius: 10px; 
            border: 2px solid #e9ecef;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .option:hover {
            border-color: #007bff;
            box-shadow: 0 2px 10px rgba(0,123,255,0.1);
        }
        .btn-vote { 
            background: #007bff; 
            color: white; 
            border: none; 
            padding: 8px 20px; 
            border-radius: 20px; 
            font-weight: bold;
            cursor: pointer;
        }
        .btn-vote:hover { 
            background: #0056b3; 
        }
        .team-option {
            border-color: #28a745;
        }
        .team-option:hover {
            border-color: #1e7e34;
            box-shadow: 0 2px 10px rgba(40,167,69,0.1);
        }
        .btn-team { 
            background: #28a745; 
        }
        .btn-team:hover { 
            background: #1e7e34; 
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="text-center mb-4">
            <h1>🗳️ Working Multi-Question Voting</h1>
            <p class="lead">Welcome, <strong><?php echo htmlspecialchars($username); ?></strong>!</p>
            <p>Vote on both questions independently</p>
        </div>
        
        <!-- Question 1: Programming Languages -->
        <div class="question <?php echo $lang_voted ? 'voted' : ''; ?>">
            <h2>📱 Question 1: Favorite Programming Language</h2>
            <p>Choose your favorite programming language from the options below:</p>
            
            <?php if ($lang_voted): ?>
                <div class="alert alert-success">
                    <h4>✅ You have already voted for programming languages!</h4>
                    <p>Thank you for participating in this question.</p>
                </div>
            <?php else: ?>
                <?php
                $result = mysqli_query($con, "SELECT * FROM languages ORDER BY fullname");
                if ($result && mysqli_num_rows($result) > 0) {
                    while ($row = mysqli_fetch_assoc($result)) {
                        echo '<div class="option">';
                        echo '<div>';
                        echo '<h5>' . htmlspecialchars($row['fullname']) . '</h5>';
                        echo '<p class="mb-0 text-muted">' . htmlspecialchars($row['about']) . '</p>';
                        echo '</div>';
                        echo '<form method="post" style="margin: 0;">';
                        echo '<input type="hidden" name="language_id" value="' . $row['lan_id'] . '">';
                        echo '<button type="submit" name="vote_language" class="btn-vote">🗳️ Vote</button>';
                        echo '</form>';
                        echo '</div>';
                    }
                } else {
                    echo '<p style="color: red;">No programming languages found!</p>';
                }
                ?>
            <?php endif; ?>
        </div>
        
        <!-- Question 2: Team Members -->
        <div class="question <?php echo $team_voted ? 'voted' : ''; ?>">
            <h2>👥 Question 2: Best Team Member</h2>
            <p>Vote for the team member you think contributes the most:</p>
            
            <?php if ($team_voted): ?>
                <div class="alert alert-success">
                    <h4>✅ You have already voted for team members!</h4>
                    <p>Thank you for participating in this question.</p>
                </div>
            <?php else: ?>
                <?php
                $result = mysqli_query($con, "SELECT * FROM team_members ORDER BY fullname");
                if ($result && mysqli_num_rows($result) > 0) {
                    while ($row = mysqli_fetch_assoc($result)) {
                        echo '<div class="option team-option">';
                        echo '<div>';
                        echo '<h5>' . htmlspecialchars($row['fullname']) . '</h5>';
                        echo '<p class="mb-0 text-muted">' . htmlspecialchars($row['about']) . '</p>';
                        echo '</div>';
                        echo '<form method="post" style="margin: 0;">';
                        echo '<input type="hidden" name="member_id" value="' . $row['member_id'] . '">';
                        echo '<button type="submit" name="vote_team" class="btn-vote btn-team">🗳️ Vote</button>';
                        echo '</form>';
                        echo '</div>';
                    }
                } else {
                    echo '<div class="alert alert-warning">';
                    echo '<h5>⚠️ No team members found!</h5>';
                    echo '<p>Please contact the administrator to set up team members.</p>';
                    echo '<a href="fix_team_voting.php" class="btn btn-warning">🔧 Fix Team Members</a>';
                    echo '</div>';
                }
                ?>
            <?php endif; ?>
        </div>
        
        <div class="text-center mt-4">
            <a href="multi_question_results.php" class="btn btn-info btn-lg me-3">📊 View Results</a>
            <a href="fix_team_voting.php" class="btn btn-warning me-3">🔧 Fix Issues</a>
            <a href="index.php" class="btn btn-secondary">🏠 Home</a>
        </div>
        
        <div class="text-center mt-3">
            <small class="text-muted">
                Language Voted: <?php echo $lang_voted ? '✅ Yes' : '❌ No'; ?> | 
                Team Voted: <?php echo $team_voted ? '✅ Yes' : '❌ No'; ?>
            </small>
        </div>
    </div>
</body>
</html>