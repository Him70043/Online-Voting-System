<?php
// Initialize HTTP Security Headers
if (file_exists(__DIR__ . '/includes/HTTPSecurityHeaders.php')) {
    require_once __DIR__ . '/includes/HTTPSecurityHeaders.php';
    if (class_exists('HTTPSecurityHeaders') && method_exists('HTTPSecurityHeaders', 'applySecurityHeaders')) {
        HTTPSecurityHeaders::applySecurityHeaders();
    }
}

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
require_once "includes/SecurityLogger.php";

// Set security headers
XSSProtection::setSecurityHeaders();

// Initialize security logger
SecurityLogger::initialize($con);

// Verify CSRF token for admin actions
if (isset($_GET['action']) || isset($_POST['action'])) {
    $csrf_token = $_GET['csrf_token'] ?? $_POST['csrf_token'] ?? null;
    if (!$csrf_token || !CSRFProtection::validateToken($csrf_token)) {
        $_SESSION['admin_error'] = "Security token validation failed. Please refresh and try again.";
        header("Location: admin_dashboard.php");
        exit();
    }
}

if (isset($_GET['action'])) {
    $action = $_GET['action'];
    
    switch ($action) {
        case 'delete_user':
            // Check permission for this action
            try {
                AdminSecurity::requirePermission('delete_user');
            } catch (Exception $e) {
                $_SESSION['admin_error'] = "Access denied: " . $e->getMessage();
                break;
            }
            
            if (isset($_GET['id'])) {
                $user_id = intval($_GET['id']);
                
                // Get username before deleting using prepared statement
                $stmt_get_user = mysqli_prepare($con, "SELECT username FROM loginusers WHERE id = ?");
                mysqli_stmt_bind_param($stmt_get_user, "i", $user_id);
                mysqli_stmt_execute($stmt_get_user);
                $result = mysqli_stmt_get_result($stmt_get_user);
                
                if ($user_row = mysqli_fetch_assoc($result)) {
                    $username = $user_row['username'];
                    $oldData = $user_row;
                    mysqli_stmt_close($stmt_get_user);
                    
                    // Enhanced admin activity logging
                    AdminSecurity::logAdminActivity(
                        'delete_user', 
                        "Deleted user: $username (ID: $user_id)", 
                        'loginusers', 
                        $user_id,
                        $oldData,
                        null
                    );
                    
                    // Delete from loginusers table using prepared statement
                    $stmt_del_login = mysqli_prepare($con, "DELETE FROM loginusers WHERE id = ?");
                    mysqli_stmt_bind_param($stmt_del_login, "i", $user_id);
                    mysqli_stmt_execute($stmt_del_login);
                    mysqli_stmt_close($stmt_del_login);
                    
                    // Delete from voters table using prepared statement
                    $stmt_del_voter = mysqli_prepare($con, "DELETE FROM voters WHERE username = ?");
                    mysqli_stmt_bind_param($stmt_del_voter, "s", $username);
                    mysqli_stmt_execute($stmt_del_voter);
                    mysqli_stmt_close($stmt_del_voter);
                    
                    $_SESSION['admin_message'] = "User deleted successfully!";
                } else {
                    mysqli_stmt_close($stmt_get_user);
                    $_SESSION['admin_error'] = "User not found!";
                }
            }
            break;
            
        case 'reset_lang':
            // Check permission for this action
            try {
                AdminSecurity::requirePermission('reset_lang');
            } catch (Exception $e) {
                $_SESSION['admin_error'] = "Access denied: " . $e->getMessage();
                break;
            }
            
            if (isset($_GET['id'])) {
                $lang_id = intval($_GET['id']);
                
                // Get current data before reset
                $stmt_get_lang = mysqli_prepare($con, "SELECT * FROM languages WHERE lan_id = ?");
                mysqli_stmt_bind_param($stmt_get_lang, "i", $lang_id);
                mysqli_stmt_execute($stmt_get_lang);
                $result = mysqli_stmt_get_result($stmt_get_lang);
                $oldData = mysqli_fetch_assoc($result);
                mysqli_stmt_close($stmt_get_lang);
                
                // Enhanced admin activity logging
                AdminSecurity::logAdminActivity(
                    'reset_lang_votes', 
                    "Reset language votes for: " . ($oldData['fullname'] ?? "ID $lang_id"), 
                    'languages', 
                    $lang_id,
                    $oldData,
                    ['votecount' => 0]
                );
                
                $stmt_reset_lang = mysqli_prepare($con, "UPDATE languages SET votecount = 0 WHERE lan_id = ?");
                mysqli_stmt_bind_param($stmt_reset_lang, "i", $lang_id);
                mysqli_stmt_execute($stmt_reset_lang);
                mysqli_stmt_close($stmt_reset_lang);
                $_SESSION['admin_message'] = "Language votes reset successfully!";
            }
            break;
            
        case 'reset_team':
            // Check permission for this action
            try {
                AdminSecurity::requirePermission('reset_team');
            } catch (Exception $e) {
                $_SESSION['admin_error'] = "Access denied: " . $e->getMessage();
                break;
            }
            
            if (isset($_GET['id'])) {
                $member_id = intval($_GET['id']);
                
                // Get current data before reset
                $stmt_get_member = mysqli_prepare($con, "SELECT * FROM team_members WHERE member_id = ?");
                mysqli_stmt_bind_param($stmt_get_member, "i", $member_id);
                mysqli_stmt_execute($stmt_get_member);
                $result = mysqli_stmt_get_result($stmt_get_member);
                $oldData = mysqli_fetch_assoc($result);
                mysqli_stmt_close($stmt_get_member);
                
                // Enhanced admin activity logging
                AdminSecurity::logAdminActivity(
                    'reset_team_votes', 
                    "Reset team member votes for: " . ($oldData['fullname'] ?? "ID $member_id"), 
                    'team_members', 
                    $member_id,
                    $oldData,
                    ['votecount' => 0]
                );
                
                $stmt_reset_team = mysqli_prepare($con, "UPDATE team_members SET votecount = 0 WHERE member_id = ?");
                mysqli_stmt_bind_param($stmt_reset_team, "i", $member_id);
                mysqli_stmt_execute($stmt_reset_team);
                mysqli_stmt_close($stmt_reset_team);
                $_SESSION['admin_message'] = "Team member votes reset successfully!";
            }
            break;
            
        case 'reset_all_votes':
            // Log admin action
            SecurityLogger::logAdminAction(
                $_SESSION['ADMIN_NAME'], 
                'reset_votes', 
                "Reset all votes in the system", 
                'multiple_tables', 
                null
            );
            
            // Reset all votes using prepared statements
            $stmt_reset_lang_all = mysqli_prepare($con, "UPDATE languages SET votecount = 0");
            mysqli_stmt_execute($stmt_reset_lang_all);
            mysqli_stmt_close($stmt_reset_lang_all);
            
            $stmt_reset_team_all = mysqli_prepare($con, "UPDATE team_members SET votecount = 0");
            mysqli_stmt_execute($stmt_reset_team_all);
            mysqli_stmt_close($stmt_reset_team_all);
            
            $stmt_reset_voters = mysqli_prepare($con, "UPDATE voters SET status = 'NOTVOTED', voted = NULL, team_voted = NULL");
            mysqli_stmt_execute($stmt_reset_voters);
            mysqli_stmt_close($stmt_reset_voters);
            
            $_SESSION['admin_message'] = "All votes reset successfully!";
            break;
            
        case 'export_data':
            // Log admin action
            SecurityLogger::logAdminAction(
                $_SESSION['ADMIN_NAME'], 
                'export_data', 
                "Exported all voting data to CSV", 
                'multiple_tables', 
                null
            );
            
            // Export all data to CSV
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="voting_data_' . date('Y-m-d_H-i-s') . '.csv"');
            
            $output = fopen('php://output', 'w');
            
            // Export users using prepared statements
            fputcsv($output, array('=== USERS ==='));
            fputcsv($output, array('ID', 'Username', 'Role', 'Status'));
            $stmt_export_users = mysqli_prepare($con, "SELECT id, username, rank, status FROM loginusers");
            mysqli_stmt_execute($stmt_export_users);
            $users = mysqli_stmt_get_result($stmt_export_users);
            while ($user = mysqli_fetch_assoc($users)) {
                fputcsv($output, $user);
            }
            mysqli_stmt_close($stmt_export_users);
            
            fputcsv($output, array(''));
            
            // Export voters using prepared statements
            fputcsv($output, array('=== VOTERS ==='));
            fputcsv($output, array('First Name', 'Last Name', 'Username', 'Status', 'Language Vote', 'Team Vote'));
            $stmt_export_voters = mysqli_prepare($con, "SELECT firstname, lastname, username, status, voted, team_voted FROM voters");
            mysqli_stmt_execute($stmt_export_voters);
            $voters = mysqli_stmt_get_result($stmt_export_voters);
            while ($voter = mysqli_fetch_assoc($voters)) {
                fputcsv($output, $voter);
            }
            mysqli_stmt_close($stmt_export_voters);
            
            fputcsv($output, array(''));
            
            // Export language results using prepared statements
            fputcsv($output, array('=== LANGUAGE RESULTS ==='));
            fputcsv($output, array('Language', 'Description', 'Vote Count'));
            $stmt_export_lang = mysqli_prepare($con, "SELECT fullname, about, votecount FROM languages ORDER BY votecount DESC");
            mysqli_stmt_execute($stmt_export_lang);
            $languages = mysqli_stmt_get_result($stmt_export_lang);
            while ($lang = mysqli_fetch_assoc($languages)) {
                fputcsv($output, $lang);
            }
            mysqli_stmt_close($stmt_export_lang);
            
            fputcsv($output, array(''));
            
            // Export team results using prepared statements
            fputcsv($output, array('=== TEAM RESULTS ==='));
            fputcsv($output, array('Team Member', 'Role', 'Vote Count'));
            $stmt_export_team = mysqli_prepare($con, "SELECT fullname, about, votecount FROM team_members ORDER BY votecount DESC");
            mysqli_stmt_execute($stmt_export_team);
            $team = mysqli_stmt_get_result($stmt_export_team);
            while ($member = mysqli_fetch_assoc($team)) {
                fputcsv($output, $member);
            }
            mysqli_stmt_close($stmt_export_team);
            
            fclose($output);
            exit();
            break;
            
        case 'export_audit_log':
            // Check permission for this action
            try {
                AdminSecurity::requirePermission('export_data');
            } catch (Exception $e) {
                $_SESSION['admin_error'] = "Access denied: " . $e->getMessage();
                break;
            }
            
            // Log admin action
            AdminSecurity::logAdminActivity(
                'export_audit_log', 
                "Exported admin audit log", 
                'admin_audit_log', 
                null
            );
            
            // Export audit log to CSV
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="admin_audit_log_' . date('Y-m-d_H-i-s') . '.csv"');
            
            $output = fopen('php://output', 'w');
            
            // CSV headers
            fputcsv($output, array('Timestamp', 'Admin Name', 'Privilege', 'Action', 'Details', 'Target Table', 'Target ID', 'IP Address', 'Session ID'));
            
            // Get audit activities
            $activities = AdminSecurity::getAdminActivitySummary(1000); // Export up to 1000 records
            foreach ($activities as $activity) {
                fputcsv($output, array(
                    $activity['timestamp'],
                    $activity['admin_name'],
                    $activity['admin_privilege'],
                    $activity['action'],
                    $activity['details'],
                    $activity['target_table'],
                    $activity['target_id'],
                    $activity['ip_address'],
                    $activity['session_id']
                ));
            }
            
            fclose($output);
            exit();
            break;
    }
}

header("Location: admin_dashboard.php");
exit();
?>