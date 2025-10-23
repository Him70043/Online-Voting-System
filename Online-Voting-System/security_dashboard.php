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
    <title>Security Monitoring Dashboard - Online Voting System</title>
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href='http://fonts.googleapis.com/css?family=Ubuntu' rel='stylesheet' type='text/css'>
    <link href='http://fonts.googleapis.com/css?family=Raleway' rel='stylesheet' type='text/css'>
    <style>
        .security-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        .stat-card {
            background: white;
            border-radius: 10px;
            padding: 15px;
            margin: 10px 0;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .severity-high { border-left: 5px solid #dc3545; }
        .severity-medium { border-left: 5px solid #ffc107; }
        .severity-low { border-left: 5px solid #28a745; }
        .severity-critical { border-left: 5px solid #6f42c1; }
        .log-entry {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 10px;
            margin: 5px 0;
            border-left: 4px solid #007bff;
        }
        .timestamp {
            color: #6c757d;
            font-size: 0.9em;
        }
    </style>
</head>

<body style="background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%); min-height: 100vh;">
    <?php
    require_once __DIR__ . "/includes/SessionSecurity.php";
    require_once __DIR__ . "/includes/SecurityLogger.php";
    include "connection.php";
    include "includes/XSSProtection.php";

    // Check admin authentication
    if (!SessionSecurity::isAdminLoggedIn()) {
        header("Location: admin_login.php");
        exit();
    }

    // Set security headers
    XSSProtection::setSecurityHeaders();

    // Initialize security logger
    SecurityLogger::initialize($con);

    // Get filter parameters
    $days = isset($_GET['days']) ? intval($_GET['days']) : 7;
    $eventType = isset($_GET['event_type']) ? $_GET['event_type'] : '';
    $severity = isset($_GET['severity']) ? $_GET['severity'] : '';

    // Get security statistics
    $stats = SecurityLogger::getSecurityStatistics($days);
    $recentEvents = SecurityLogger::getRecentSecurityEvents(50);
    $authLogs = SecurityLogger::getAuthenticationLogs(100);
    ?>

    <div class="container-fluid" style="padding: 20px;">
        <!-- Header -->
        <div class="row">
            <div class="col-12">
                <div class="security-card text-center">
                    <h1>🔒 Security Monitoring Dashboard</h1>
                    <p>Real-time security monitoring for Online Voting System</p>
                    <p><strong>Administrator:</strong> <?php echo htmlspecialchars($_SESSION['ADMIN_NAME']); ?></p>
                    <div style="margin-top: 15px;">
                        <a href="admin_dashboard.php" class="btn btn-light">← Back to Admin Panel</a>
                        <button onclick="location.reload()" class="btn btn-outline-light">🔄 Refresh</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter Controls -->
        <div class="row">
            <div class="col-12">
                <div class="stat-card">
                    <h5>📊 Filter Options</h5>
                    <form method="GET" class="row">
                        <div class="col-md-3">
                            <label>Time Period:</label>
                            <select name="days" class="form-control">
                                <option value="1" <?php echo $days == 1 ? 'selected' : ''; ?>>Last 24 Hours</option>
                                <option value="7" <?php echo $days == 7 ? 'selected' : ''; ?>>Last 7 Days</option>
                                <option value="30" <?php echo $days == 30 ? 'selected' : ''; ?>>Last 30 Days</option>
                                <option value="90" <?php echo $days == 90 ? 'selected' : ''; ?>>Last 90 Days</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label>Event Type:</label>
                            <select name="event_type" class="form-control">
                                <option value="">All Events</option>
                                <option value="failed_authentication" <?php echo $eventType == 'failed_authentication' ? 'selected' : ''; ?>>Failed Authentication</option>
                                <option value="vote_cast" <?php echo $eventType == 'vote_cast' ? 'selected' : ''; ?>>Vote Cast</option>
                                <option value="admin_action" <?php echo $eventType == 'admin_action' ? 'selected' : ''; ?>>Admin Action</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label>Severity:</label>
                            <select name="severity" class="form-control">
                                <option value="">All Severities</option>
                                <option value="critical" <?php echo $severity == 'critical' ? 'selected' : ''; ?>>Critical</option>
                                <option value="high" <?php echo $severity == 'high' ? 'selected' : ''; ?>>High</option>
                                <option value="medium" <?php echo $severity == 'medium' ? 'selected' : ''; ?>>Medium</option>
                                <option value="low" <?php echo $severity == 'low' ? 'selected' : ''; ?>>Low</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label>&nbsp;</label>
                            <button type="submit" class="form-control btn btn-primary">Apply Filters</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Security Statistics -->
        <div class="row">
            <!-- Authentication Statistics -->
            <div class="col-md-6">
                <div class="stat-card">
                    <h5>🔐 Authentication Statistics (Last <?php echo $days; ?> days)</h5>
                    <?php if (isset($stats['authentication'])): ?>
                        <?php foreach ($stats['authentication'] as $type => $data): ?>
                            <div class="mb-3">
                                <h6><?php echo ucfirst($type); ?> Logins</h6>
                                <div class="progress mb-2">
                                    <div class="progress-bar bg-success" style="width: <?php echo $data['total_attempts'] > 0 ? ($data['successful_logins'] / $data['total_attempts']) * 100 : 0; ?>%">
                                        Success: <?php echo $data['successful_logins']; ?>
                                    </div>
                                    <div class="progress-bar bg-danger" style="width: <?php echo $data['total_attempts'] > 0 ? ($data['failed_attempts'] / $data['total_attempts']) * 100 : 0; ?>%">
                                        Failed: <?php echo $data['failed_attempts']; ?>
                                    </div>
                                </div>
                                <small>Total Attempts: <?php echo $data['total_attempts']; ?></small>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-muted">No authentication data available for this period.</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Voting Statistics -->
            <div class="col-md-6">
                <div class="stat-card">
                    <h5>🗳️ Voting Activity (Last <?php echo $days; ?> days)</h5>
                    <?php if (isset($stats['voting'])): ?>
                        <?php foreach ($stats['voting'] as $type => $count): ?>
                            <div class="mb-2">
                                <strong><?php echo ucfirst($type); ?> Votes:</strong> 
                                <span class="badge badge-primary"><?php echo $count; ?></span>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-muted">No voting activity data available for this period.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Security Events by Severity -->
            <div class="col-md-6">
                <div class="stat-card">
                    <h5>⚠️ Security Events by Severity (Last <?php echo $days; ?> days)</h5>
                    <?php if (isset($stats['security_events'])): ?>
                        <?php 
                        $severityColors = [
                            'critical' => 'danger',
                            'high' => 'warning', 
                            'medium' => 'info',
                            'low' => 'success'
                        ];
                        foreach ($stats['security_events'] as $sev => $count): 
                        ?>
                            <div class="mb-2">
                                <span class="badge badge-<?php echo $severityColors[$sev]; ?>">
                                    <?php echo ucfirst($sev); ?>: <?php echo $count; ?>
                                </span>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-muted">No security events for this period.</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Admin Actions -->
            <div class="col-md-6">
                <div class="stat-card">
                    <h5>👨‍💼 Admin Actions (Last <?php echo $days; ?> days)</h5>
                    <?php if (isset($stats['admin_actions'])): ?>
                        <?php foreach ($stats['admin_actions'] as $action => $count): ?>
                            <div class="mb-2">
                                <strong><?php echo str_replace('_', ' ', ucfirst($action)); ?>:</strong> 
                                <span class="badge badge-info"><?php echo $count; ?></span>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-muted">No admin actions recorded for this period.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Recent Security Events -->
        <div class="row">
            <div class="col-12">
                <div class="stat-card">
                    <h5>📋 Recent Security Events</h5>
                    <div style="max-height: 400px; overflow-y: auto;">
                        <?php if (!empty($recentEvents)): ?>
                            <?php foreach ($recentEvents as $event): ?>
                                <div class="log-entry severity-<?php echo $event['severity']; ?>">
                                    <div class="d-flex justify-content-between">
                                        <strong><?php echo htmlspecialchars($event['event_type']); ?></strong>
                                        <span class="badge badge-<?php echo $event['severity'] == 'high' ? 'danger' : ($event['severity'] == 'medium' ? 'warning' : 'info'); ?>">
                                            <?php echo ucfirst($event['severity']); ?>
                                        </span>
                                    </div>
                                    <p class="mb-1"><?php echo htmlspecialchars($event['description']); ?></p>
                                    <div class="d-flex justify-content-between">
                                        <small class="timestamp">
                                            🕒 <?php echo $event['timestamp']; ?> | 
                                            👤 <?php echo htmlspecialchars($event['username']); ?> | 
                                            🌐 <?php echo htmlspecialchars($event['ip_address']); ?>
                                        </small>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="text-muted">No recent security events found.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Authentication Logs -->
        <div class="row">
            <div class="col-12">
                <div class="stat-card">
                    <h5>🔑 Recent Authentication Attempts</h5>
                    <div style="max-height: 400px; overflow-y: auto;">
                        <?php if (!empty($authLogs)): ?>
                            <?php foreach ($authLogs as $log): ?>
                                <div class="log-entry <?php echo $log['success'] ? 'border-success' : 'border-danger'; ?>">
                                    <div class="d-flex justify-content-between">
                                        <strong><?php echo htmlspecialchars($log['username']); ?></strong>
                                        <span class="badge badge-<?php echo $log['success'] ? 'success' : 'danger'; ?>">
                                            <?php echo $log['success'] ? '✅ Success' : '❌ Failed'; ?>
                                        </span>
                                    </div>
                                    <div class="d-flex justify-content-between">
                                        <span>Type: <?php echo ucfirst($log['login_type']); ?></span>
                                        <?php if (!$log['success'] && $log['failure_reason']): ?>
                                            <span class="text-danger">Reason: <?php echo htmlspecialchars($log['failure_reason']); ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <small class="timestamp">
                                        🕒 <?php echo $log['timestamp']; ?> | 
                                        🌐 <?php echo htmlspecialchars($log['ip_address']); ?>
                                    </small>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="text-muted">No authentication logs found.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- System Information -->
        <div class="row">
            <div class="col-12">
                <div class="stat-card">
                    <h5>ℹ️ System Information</h5>
                    <div class="row">
                        <div class="col-md-4">
                            <strong>Current Time:</strong> <?php echo date('Y-m-d H:i:s'); ?>
                        </div>
                        <div class="col-md-4">
                            <strong>Server IP:</strong> <?php echo $_SERVER['SERVER_ADDR'] ?? 'Unknown'; ?>
                        </div>
                        <div class="col-md-4">
                            <strong>PHP Version:</strong> <?php echo phpversion(); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <script>
        // Auto-refresh every 30 seconds
        setTimeout(function() {
            location.reload();
        }, 30000);
    </script>
</body>
</html>