<?php
// Initialize HTTP Security Headers
if (file_exists(__DIR__ . '/includes/HTTPSecurityHeaders.php')) {
    require_once __DIR__ . '/includes/HTTPSecurityHeaders.php';
    if (class_exists('HTTPSecurityHeaders')) {
        HTTPSecurityHeaders::initialize();
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Dashboard - Online Voting System by Himanshu Kumar</title>
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href='http://fonts.googleapis.com/css?family=Ubuntu' rel='stylesheet' type='text/css'>
    <link href='http://fonts.googleapis.com/css?family=Raleway' rel='stylesheet' type='text/css'>
    <link href='http://fonts.googleapis.com/css?family=Oswald' rel='stylesheet' type='text/css'>
    <link href='http://fonts.googleapis.com/css?family=Roboto+Condensed' rel='stylesheet' type='text/css'>
</head>

<body style="background: #f8f9fa; min-height: 100vh;">
    <?php
    require_once "includes/SessionSecurity.php";
    require_once "includes/AdminSecurity.php";
    
    // Enhanced admin authentication with AdminSecurity
    if (!AdminSecurity::validateAdminSession()) {
        header("Location: admin_login.php");
        exit();
    }
    
    include "connection.php";
    include "includes/CSRFProtection.php";
    include "includes/XSSProtection.php";
    
    // Set security headers
    XSSProtection::setSecurityHeaders();
    
    // Generate CSRF token for admin actions
    $csrf_token = CSRFProtection::generateToken();
    
    // Log admin dashboard access
    AdminSecurity::logAdminActivity('dashboard_access', 'Admin accessed dashboard');
    
    // Get admin session info for display
    $adminInfo = AdminSecurity::getAdminSessionInfo();
    ?>

    <!-- Admin Navigation -->
    <nav class="navbar navbar-expand-lg" style="background: linear-gradient(45deg, #667eea, #764ba2); box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
        <div class="container">
            <a class="navbar-brand" href="#" style="color: white; font-weight: bold; font-size: 24px;">
                🔐 Admin Dashboard - Online Voting System
            </a>
            <div class="navbar-nav ml-auto">
                <span style="color: white; margin-right: 15px;">
                    Welcome, <?php echo XSSProtection::escapeHtml($_SESSION['ADMIN_NAME']); ?>! 
                    <?php echo AdminSecurity::getPrivilegeBadge(); ?>
                </span>
                <span style="color: white; margin-right: 15px; font-size: 12px;">
                    ⏱️ Session: <span id="session-timeout"><?php 
                        $remaining = SessionSecurity::getTimeoutRemaining(true);
                        $minutes = floor($remaining / 60);
                        $seconds = $remaining % 60;
                        echo $minutes . ':' . ($seconds < 10 ? '0' : '') . $seconds;
                    ?></span>
                </span>
                <?php if (AdminSecurity::hasPermission('view_users')): ?>
                <a href="admin_database.php" class="btn btn-info mr-2">🗄️ Database</a>
                <?php endif; ?>
                <?php if (AdminSecurity::hasPermission('view_security_logs')): ?>
                <a href="security_dashboard.php" class="btn btn-warning mr-2">🔒 Security</a>
                <?php endif; ?>
                <a href="admin_logout.php" class="btn btn-danger">🚪 Logout</a>
            </div>
        </div>
    </nav>

    <div class="container-fluid" style="padding: 30px;">
        
        <!-- Statistics Cards -->
        <div class="row" style="margin-bottom: 30px;">
            <?php
            // Get statistics using prepared statements
            $stmt1 = mysqli_prepare($con, "SELECT COUNT(*) as count FROM loginusers");
            mysqli_stmt_execute($stmt1);
            $result1 = mysqli_stmt_get_result($stmt1);
            $total_users = mysqli_fetch_assoc($result1)['count'];
            mysqli_stmt_close($stmt1);
            
            $stmt2 = mysqli_prepare($con, "SELECT COUNT(*) as count FROM voters");
            mysqli_stmt_execute($stmt2);
            $result2 = mysqli_stmt_get_result($stmt2);
            $total_voters = mysqli_fetch_assoc($result2)['count'];
            mysqli_stmt_close($stmt2);
            
            $stmt3 = mysqli_prepare($con, "SELECT COUNT(*) as count FROM voters WHERE status='VOTED'");
            mysqli_stmt_execute($stmt3);
            $result3 = mysqli_stmt_get_result($stmt3);
            $voted_users = mysqli_fetch_assoc($result3)['count'];
            mysqli_stmt_close($stmt3);
            
            $pending_users = $total_voters - $voted_users;
            
            $stmt4 = mysqli_prepare($con, "SELECT SUM(votecount) as count FROM languages");
            mysqli_stmt_execute($stmt4);
            $result4 = mysqli_stmt_get_result($stmt4);
            $total_lang_votes = mysqli_fetch_assoc($result4)['count'];
            mysqli_stmt_close($stmt4);
            
            $stmt5 = mysqli_prepare($con, "SELECT SUM(votecount) as count FROM team_members");
            mysqli_stmt_execute($stmt5);
            $result5 = mysqli_stmt_get_result($stmt5);
            $total_team_votes = mysqli_fetch_assoc($result5)['count'];
            mysqli_stmt_close($stmt5);
            ?>
            
            <div class="col-md-2">
                <div style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); padding: 20px; border-radius: 15px; color: white; text-align: center; box-shadow: 0 5px 15px rgba(0,0,0,0.2);">
                    <h3 style="margin: 0; font-size: 32px;"><?php echo $total_users; ?></h3>
                    <p style="margin: 5px 0 0 0;">👥 Total Users</p>
                </div>
            </div>
            
            <div class="col-md-2">
                <div style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%); padding: 20px; border-radius: 15px; color: white; text-align: center; box-shadow: 0 5px 15px rgba(0,0,0,0.2);">
                    <h3 style="margin: 0; font-size: 32px;"><?php echo $voted_users; ?></h3>
                    <p style="margin: 5px 0 0 0;">✅ Voted</p>
                </div>
            </div>
            
            <div class="col-md-2">
                <div style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); padding: 20px; border-radius: 15px; color: white; text-align: center; box-shadow: 0 5px 15px rgba(0,0,0,0.2);">
                    <h3 style="margin: 0; font-size: 32px;"><?php echo $pending_users; ?></h3>
                    <p style="margin: 5px 0 0 0;">⏳ Pending</p>
                </div>
            </div>
            
            <div class="col-md-2">
                <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 20px; border-radius: 15px; color: white; text-align: center; box-shadow: 0 5px 15px rgba(0,0,0,0.2);">
                    <h3 style="margin: 0; font-size: 32px;"><?php echo $total_lang_votes; ?></h3>
                    <p style="margin: 5px 0 0 0;">🚀 Lang Votes</p>
                </div>
            </div>
            
            <div class="col-md-2">
                <div style="background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%); padding: 20px; border-radius: 15px; color: #333; text-align: center; box-shadow: 0 5px 15px rgba(0,0,0,0.2);">
                    <h3 style="margin: 0; font-size: 32px;"><?php echo $total_team_votes; ?></h3>
                    <p style="margin: 5px 0 0 0;">👥 Team Votes</p>
                </div>
            </div>
            
            <div class="col-md-2">
                <div style="background: linear-gradient(135deg, #ffecd2 0%, #fcb69f 100%); padding: 20px; border-radius: 15px; color: #333; text-align: center; box-shadow: 0 5px 15px rgba(0,0,0,0.2);">
                    <h3 style="margin: 0; font-size: 32px;"><?php echo number_format((($voted_users/$total_voters)*100), 1); ?>%</h3>
                    <p style="margin: 5px 0 0 0;">📊 Turnout</p>
                </div>
            </div>
        </div>

        <!-- Navigation Tabs -->
        <div class="row">
            <div class="col-12">
                <ul class="nav nav-tabs" style="border-bottom: 3px solid #667eea; margin-bottom: 20px;">
                    <li class="nav-item">
                        <a class="nav-link active" href="#users" data-toggle="tab" style="color: #667eea; font-weight: bold;">👥 All Users</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#voters" data-toggle="tab" style="color: #667eea; font-weight: bold;">🗳️ Voters Data</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#languages" data-toggle="tab" style="color: #667eea; font-weight: bold;">🚀 Language Results</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#team" data-toggle="tab" style="color: #667eea; font-weight: bold;">👑 Team Results</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#analytics" data-toggle="tab" style="color: #667eea; font-weight: bold;">📊 Analytics</a>
                    </li>
                    <?php if (AdminSecurity::hasPermission('view_security_logs')): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="#audit" data-toggle="tab" style="color: #667eea; font-weight: bold;">🔍 Admin Audit</a>
                    </li>
                    <?php endif; ?>
                </ul>

                <div class="tab-content">
                    <!-- All Users Tab -->
                    <div class="tab-pane fade show active" id="users">
                        <div style="background: white; padding: 25px; border-radius: 15px; box-shadow: 0 5px 15px rgba(0,0,0,0.1);">
                            <h4 style="color: #333; margin-bottom: 20px;">👥 All Registered Users</h4>
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead style="background: linear-gradient(45deg, #667eea, #764ba2); color: white;">
                                        <tr>
                                            <th>ID</th>
                                            <th>Username</th>
                                            <th>Password Hash</th>
                                            <th>Role</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $stmt_users = mysqli_prepare($con, "SELECT * FROM loginusers ORDER BY id DESC");
                                        mysqli_stmt_execute($stmt_users);
                                        $users = mysqli_stmt_get_result($stmt_users);
                                        while ($user = mysqli_fetch_assoc($users)) {
                                            echo "<tr>";
                                            echo "<td><strong>" . XSSProtection::escapeHtml($user['id']) . "</strong></td>";
                                            echo "<td>" . XSSProtection::escapeHtml($user['username']) . "</td>";
                                            echo "<td><code>" . XSSProtection::escapeHtml(substr($user['password'], 0, 10)) . "...</code></td>";
                                            echo "<td><span class='badge badge-primary'>" . XSSProtection::escapeHtml($user['rank']) . "</span></td>";
                                            echo "<td><span class='badge badge-" . XSSProtection::escapeAttribute($user['status'] == 'ACTIVE' ? 'success' : 'danger') . "'>" . XSSProtection::escapeHtml($user['status']) . "</span></td>";
                                            if (AdminSecurity::hasPermission('delete_user')) {
                                echo "<td><button class='btn btn-sm btn-danger' onclick='confirmDeleteUser(" . XSSProtection::sanitizeInt($user['id']) . ")'>🗑️ Delete</button></td>";
                            } else {
                                echo "<td><span class='text-muted'>No Permission</span></td>";
                            }
                                            echo "</tr>";
                                        }
                                        mysqli_stmt_close($stmt_users);
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Voters Data Tab -->
                    <div class="tab-pane fade" id="voters">
                        <div style="background: white; padding: 25px; border-radius: 15px; box-shadow: 0 5px 15px rgba(0,0,0,0.1);">
                            <h4 style="color: #333; margin-bottom: 20px;">🗳️ Detailed Voting Records</h4>
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead style="background: linear-gradient(45deg, #f093fb, #f5576c); color: white;">
                                        <tr>
                                            <th>First Name</th>
                                            <th>Last Name</th>
                                            <th>Username</th>
                                            <th>Voting Status</th>
                                            <th>Language Vote</th>
                                            <th>Team Vote</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $stmt_voters = mysqli_prepare($con, "SELECT * FROM voters ORDER BY status DESC, firstname ASC");
                                        mysqli_stmt_execute($stmt_voters);
                                        $voters = mysqli_stmt_get_result($stmt_voters);
                                        while ($voter = mysqli_fetch_assoc($voters)) {
                                            echo "<tr>";
                                            echo "<td>" . XSSProtection::escapeHtml($voter['firstname']) . "</td>";
                                            echo "<td>" . XSSProtection::escapeHtml($voter['lastname']) . "</td>";
                                            echo "<td><strong>" . XSSProtection::escapeHtml($voter['username']) . "</strong></td>";
                                            echo "<td><span class='badge badge-" . XSSProtection::escapeAttribute($voter['status'] == 'VOTED' ? 'success' : 'warning') . "'>" . XSSProtection::escapeHtml($voter['status']) . "</span></td>";
                                            echo "<td>" . ($voter['voted'] ? "<span class='badge badge-info'>" . XSSProtection::escapeHtml($voter['voted']) . "</span>" : "<em>No vote</em>") . "</td>";
                                            echo "<td>" . ($voter['team_voted'] ? "<span class='badge badge-secondary'>" . XSSProtection::escapeHtml($voter['team_voted']) . "</span>" : "<em>No vote</em>") . "</td>";
                                            echo "</tr>";
                                        }
                                        mysqli_stmt_close($stmt_voters);
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Language Results Tab -->
                    <div class="tab-pane fade" id="languages">
                        <div style="background: white; padding: 25px; border-radius: 15px; box-shadow: 0 5px 15px rgba(0,0,0,0.1);">
                            <h4 style="color: #333; margin-bottom: 20px;">🚀 Programming Language Voting Results</h4>
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead style="background: linear-gradient(45deg, #4facfe, #00f2fe); color: white;">
                                        <tr>
                                            <th>Rank</th>
                                            <th>Language</th>
                                            <th>Description</th>
                                            <th>Vote Count</th>
                                            <th>Percentage</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $stmt_languages = mysqli_prepare($con, "SELECT * FROM languages ORDER BY votecount DESC");
                                        mysqli_stmt_execute($stmt_languages);
                                        $languages = mysqli_stmt_get_result($stmt_languages);
                                        $rank = 1;
                                        while ($lang = mysqli_fetch_assoc($languages)) {
                                            $percentage = $total_lang_votes > 0 ? round(($lang['votecount'] / $total_lang_votes) * 100, 1) : 0;
                                            echo "<tr>";
                                            echo "<td><strong>" . XSSProtection::escapeHtml(($rank == 1 ? "🏆 " : "") . $rank) . "</strong></td>";
                                            echo "<td><strong>" . XSSProtection::escapeHtml($lang['fullname']) . "</strong></td>";
                                            echo "<td>" . XSSProtection::escapeHtml($lang['about']) . "</td>";
                                            echo "<td><span class='badge badge-primary' style='font-size: 14px;'>" . XSSProtection::escapeHtml($lang['votecount']) . "</span></td>";
                                            echo "<td><span class='badge badge-success'>" . XSSProtection::escapeHtml($percentage) . "%</span></td>";
                                            if (AdminSecurity::hasPermission('reset_lang')) {
                                echo "<td><button class='btn btn-sm btn-warning' onclick='confirmResetLangVotes(" . XSSProtection::sanitizeInt($lang['lan_id']) . ")'>🔄 Reset</button></td>";
                            } else {
                                echo "<td><span class='text-muted'>No Permission</span></td>";
                            }
                                            echo "</tr>";
                                            $rank++;
                                        }
                                        mysqli_stmt_close($stmt_languages);
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Team Results Tab -->
                    <div class="tab-pane fade" id="team">
                        <div style="background: white; padding: 25px; border-radius: 15px; box-shadow: 0 5px 15px rgba(0,0,0,0.1);">
                            <h4 style="color: #333; margin-bottom: 20px;">👑 Team Member Voting Results</h4>
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead style="background: linear-gradient(45deg, #fa709a, #fee140); color: white;">
                                        <tr>
                                            <th>Rank</th>
                                            <th>Team Member</th>
                                            <th>Role & Expertise</th>
                                            <th>Vote Count</th>
                                            <th>Percentage</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $stmt_team = mysqli_prepare($con, "SELECT * FROM team_members ORDER BY votecount DESC");
                                        mysqli_stmt_execute($stmt_team);
                                        $team_members = mysqli_stmt_get_result($stmt_team);
                                        $rank = 1;
                                        while ($member = mysqli_fetch_assoc($team_members)) {
                                            $percentage = $total_team_votes > 0 ? round(($member['votecount'] / $total_team_votes) * 100, 1) : 0;
                                            echo "<tr>";
                                            echo "<td><strong>" . XSSProtection::escapeHtml(($rank == 1 ? "👑 " : "") . $rank) . "</strong></td>";
                                            echo "<td><strong>" . XSSProtection::escapeHtml($member['fullname']) . "</strong></td>";
                                            echo "<td>" . XSSProtection::escapeHtml($member['about']) . "</td>";
                                            echo "<td><span class='badge badge-primary' style='font-size: 14px;'>" . XSSProtection::escapeHtml($member['votecount']) . "</span></td>";
                                            echo "<td><span class='badge badge-success'>" . XSSProtection::escapeHtml($percentage) . "%</span></td>";
                                            if (AdminSecurity::hasPermission('reset_team')) {
                                echo "<td><button class='btn btn-sm btn-warning' onclick='confirmResetTeamVotes(" . XSSProtection::sanitizeInt($member['member_id']) . ")'>🔄 Reset</button></td>";
                            } else {
                                echo "<td><span class='text-muted'>No Permission</span></td>";
                            }
                                            echo "</tr>";
                                            $rank++;
                                        }
                                        mysqli_stmt_close($stmt_team);
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Analytics Tab -->
                    <div class="tab-pane fade" id="analytics">
                        <div style="background: white; padding: 25px; border-radius: 15px; box-shadow: 0 5px 15px rgba(0,0,0,0.1);">
                            <h4 style="color: #333; margin-bottom: 20px;">📊 Advanced Analytics</h4>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <h5>🚀 Programming Language Distribution</h5>
                                    <?php
                                    $stmt_lang_analytics = mysqli_prepare($con, "SELECT * FROM languages ORDER BY votecount DESC");
                                    mysqli_stmt_execute($stmt_lang_analytics);
                                    $languages = mysqli_stmt_get_result($stmt_lang_analytics);
                                    while ($lang = mysqli_fetch_assoc($languages)) {
                                        $percentage = $total_lang_votes > 0 ? round(($lang['votecount'] / $total_lang_votes) * 100, 1) : 0;
                                        echo "<div style='margin-bottom: 15px;'>";
                                        echo "<div style='display: flex; justify-content: space-between; margin-bottom: 5px;'>";
                                        echo "<span><strong>" . XSSProtection::escapeHtml($lang['fullname']) . "</strong></span>";
                                        echo "<span>" . XSSProtection::escapeHtml($lang['votecount']) . " votes (" . XSSProtection::escapeHtml($percentage) . "%)</span>";
                                        echo "</div>";
                                        echo "<div style='background: #e0e0e0; height: 20px; border-radius: 10px; overflow: hidden;'>";
                                        echo "<div style='background: linear-gradient(45deg, #667eea, #764ba2); height: 100%; width: " . XSSProtection::escapeAttribute($percentage) . "%; transition: all 0.3s;'></div>";
                                        echo "</div>";
                                        echo "</div>";
                                    }
                                    mysqli_stmt_close($stmt_lang_analytics);
                                    ?>
                                </div>
                                
                                <div class="col-md-6">
                                    <h5>👥 Team Member Distribution</h5>
                                    <?php
                                    $stmt_team_analytics = mysqli_prepare($con, "SELECT * FROM team_members ORDER BY votecount DESC");
                                    mysqli_stmt_execute($stmt_team_analytics);
                                    $team_members = mysqli_stmt_get_result($stmt_team_analytics);
                                    while ($member = mysqli_fetch_assoc($team_members)) {
                                        $percentage = $total_team_votes > 0 ? round(($member['votecount'] / $total_team_votes) * 100, 1) : 0;
                                        echo "<div style='margin-bottom: 15px;'>";
                                        echo "<div style='display: flex; justify-content: space-between; margin-bottom: 5px;'>";
                                        echo "<span><strong>" . XSSProtection::escapeHtml($member['fullname']) . "</strong></span>";
                                        echo "<span>" . XSSProtection::escapeHtml($member['votecount']) . " votes (" . XSSProtection::escapeHtml($percentage) . "%)</span>";
                                        echo "</div>";
                                        echo "<div style='background: #e0e0e0; height: 20px; border-radius: 10px; overflow: hidden;'>";
                                        echo "<div style='background: linear-gradient(45deg, #f093fb, #f5576c); height: 100%; width: " . XSSProtection::escapeAttribute($percentage) . "%; transition: all 0.3s;'></div>";
                                        echo "</div>";
                                        echo "</div>";
                                    }
                                    mysqli_stmt_close($stmt_team_analytics);
                                    ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Admin Audit Trail Tab -->
                    <?php if (AdminSecurity::hasPermission('view_security_logs')): ?>
                    <div class="tab-pane fade" id="audit">
                        <div style="background: white; padding: 25px; border-radius: 15px; box-shadow: 0 5px 15px rgba(0,0,0,0.1);">
                            <h4 style="color: #333; margin-bottom: 20px;">🔍 Admin Activity Audit Trail</h4>
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <div style="background: #f8f9fa; padding: 15px; border-radius: 10px;">
                                        <h6>Current Session Info</h6>
                                        <p><strong>Admin:</strong> <?php echo XSSProtection::escapeHtml($adminInfo['admin_name']); ?></p>
                                        <p><strong>Privilege:</strong> <?php echo AdminSecurity::getPrivilegeBadge(); ?></p>
                                        <p><strong>Login Time:</strong> <?php echo date('Y-m-d H:i:s', $adminInfo['login_time']); ?></p>
                                        <p><strong>IP Address:</strong> <?php echo XSSProtection::escapeHtml($adminInfo['ip_address']); ?></p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div style="background: #fff3cd; padding: 15px; border-radius: 10px; border: 1px solid #ffeaa7;">
                                        <h6>⚠️ Security Notice</h6>
                                        <p>All admin activities are logged and monitored for security purposes.</p>
                                        <p><strong>Session Timeout:</strong> <span id="session-timeout-audit"><?php 
                                            $remaining = SessionSecurity::getTimeoutRemaining(true);
                                            $minutes = floor($remaining / 60);
                                            $seconds = $remaining % 60;
                                            echo $minutes . ':' . ($seconds < 10 ? '0' : '') . $seconds;
                                        ?></span></p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead style="background: linear-gradient(45deg, #6c5ce7, #a29bfe); color: white;">
                                        <tr>
                                            <th>Timestamp</th>
                                            <th>Admin</th>
                                            <th>Action</th>
                                            <th>Details</th>
                                            <th>IP Address</th>
                                            <th>Privilege</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $activities = AdminSecurity::getAdminActivitySummary(20);
                                        if (empty($activities)) {
                                            echo "<tr><td colspan='6' class='text-center text-muted'>No admin activities recorded yet.</td></tr>";
                                        } else {
                                            foreach ($activities as $activity) {
                                                echo "<tr>";
                                                echo "<td><small>" . XSSProtection::escapeHtml($activity['timestamp']) . "</small></td>";
                                                echo "<td><strong>" . XSSProtection::escapeHtml($activity['admin_name']) . "</strong></td>";
                                                echo "<td><span class='badge badge-info'>" . XSSProtection::escapeHtml($activity['action']) . "</span></td>";
                                                echo "<td>" . XSSProtection::escapeHtml($activity['details']) . "</td>";
                                                echo "<td><code>" . XSSProtection::escapeHtml($activity['ip_address']) . "</code></td>";
                                                echo "<td>" . AdminSecurity::getPrivilegeBadge() . "</td>";
                                                echo "</tr>";
                                            }
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                            
                            <?php if (AdminSecurity::hasPermission('export_data')): ?>
                            <div class="text-center mt-3">
                                <button class="btn btn-primary" onclick="confirmExportAuditLog()">
                                    📊 Export Audit Log
                                </button>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer style="background: linear-gradient(45deg, #667eea, #764ba2); color: white; text-align: center; padding: 20px; margin-top: 50px;">
        <p style="margin: 0;">© 2025 Online Voting System - Admin Panel | Developed by <strong>Himanshu Kumar</strong></p>
    </footer>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
    
    <script>
    var csrfToken = <?php echo XSSProtection::escapeJs($csrf_token); ?>;
    
    // Enhanced confirmation dialogs with detailed warnings
    function confirmDeleteUser(userId) {
        var message = '⚠️ WARNING: This will permanently delete the user and all associated data.\n\n' +
                     '• User account will be removed from loginusers table\n' +
                     '• Voter record will be deleted from voters table\n' +
                     '• This action CANNOT be undone\n\n' +
                     'Are you absolutely sure you want to continue?';
        
        if (confirm(message)) {
            // Second confirmation for critical action
            if (confirm('🚨 FINAL CONFIRMATION: Delete user permanently?')) {
                window.location.href = 'admin_actions.php?action=delete_user&id=' + encodeURIComponent(userId) + '&csrf_token=' + encodeURIComponent(csrfToken);
            }
        }
    }
    
    function confirmResetLangVotes(langId) {
        var message = '🔄 This will reset all votes for this programming language.\n\n' +
                     '• Vote count will be set to 0\n' +
                     '• This action cannot be undone\n\n' +
                     'Continue with reset?';
        
        if (confirm(message)) {
            window.location.href = 'admin_actions.php?action=reset_lang&id=' + encodeURIComponent(langId) + '&csrf_token=' + encodeURIComponent(csrfToken);
        }
    }
    
    function confirmResetTeamVotes(memberId) {
        var message = '🔄 This will reset all votes for this team member.\n\n' +
                     '• Vote count will be set to 0\n' +
                     '• This action cannot be undone\n\n' +
                     'Continue with reset?';
        
        if (confirm(message)) {
            window.location.href = 'admin_actions.php?action=reset_team&id=' + encodeURIComponent(memberId) + '&csrf_token=' + encodeURIComponent(csrfToken);
        }
    }
    
    function confirmExportAuditLog() {
        var message = '📊 Export admin audit log?\n\n' +
                     '• This will download all admin activity records\n' +
                     '• File contains sensitive administrative data\n' +
                     '• Ensure secure handling of exported data\n\n' +
                     'Proceed with export?';
        
        if (confirm(message)) {
            window.location.href = 'admin_actions.php?action=export_audit_log&csrf_token=' + encodeURIComponent(csrfToken);
        }
    }
    
    // Legacy functions for backward compatibility
    function deleteUser(userId) {
        confirmDeleteUser(userId);
    }
    
    function resetLangVotes(langId) {
        confirmResetLangVotes(langId);
    }
    
    function resetTeamVotes(memberId) {
        confirmResetTeamVotes(memberId);
    }
    </script>
    
    <?php echo AdminSecurity::generateSessionTimeoutWarning(); ?>
</body>
</html>