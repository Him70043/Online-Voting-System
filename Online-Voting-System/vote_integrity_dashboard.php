<?php
/**
 * Vote Integrity Dashboard
 * 
 * Administrative dashboard for monitoring vote integrity,
 * audit trails, and anomaly detection.
 */

require_once "connection.php";
require_once "includes/VoteIntegrity.php";

// Check admin authentication
session_start();
if (!isset($_SESSION['ADMIN_LOGGED_IN']) || $_SESSION['ADMIN_LOGGED_IN'] !== true) {
    header("Location: admin_login.php");
    exit();
}

VoteIntegrity::initialize($con);

// Handle actions
$action = $_GET['action'] ?? 'dashboard';
$message = '';

if ($_POST) {
    switch ($_POST['action']) {
        case 'verify_integrity':
            $startDate = $_POST['start_date'] ?? date('Y-m-d', strtotime('-7 days'));
            $endDate = $_POST['end_date'] ?? date('Y-m-d');
            $verificationResult = VoteIntegrity::verifyVoteIntegrity($startDate, $endDate);
            $message = "Integrity verification completed for period $startDate to $endDate";
            break;
            
        case 'cleanup_records':
            $retentionDays = (int)($_POST['retention_days'] ?? 365);
            $cleanupResult = VoteIntegrity::cleanupOldRecords($retentionDays);
            $message = "Cleanup completed: " . $cleanupResult['audit_records_deleted'] . " audit records removed";
            break;
    }
}

// Get current statistics
$stats = VoteIntegrity::getVotingStatistics(30);
$auditTrail = VoteIntegrity::generateAuditTrail(date('Y-m-d', strtotime('-7 days')), date('Y-m-d'));
$integrityCheck = VoteIntegrity::verifyVoteIntegrity(date('Y-m-d', strtotime('-1 day')), date('Y-m-d'));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vote Integrity Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-2 bg-dark text-white p-3">
                <h5><i class="fas fa-shield-alt"></i> Vote Integrity</h5>
                <nav class="nav flex-column">
                    <a class="nav-link text-white" href="?action=dashboard">
                        <i class="fas fa-tachometer-alt"></i> Dashboard
                    </a>
                    <a class="nav-link text-white" href="?action=audit_trail">
                        <i class="fas fa-list-alt"></i> Audit Trail
                    </a>
                    <a class="nav-link text-white" href="?action=anomalies">
                        <i class="fas fa-exclamation-triangle"></i> Anomalies
                    </a>
                    <a class="nav-link text-white" href="?action=statistics">
                        <i class="fas fa-chart-bar"></i> Statistics
                    </a>
                    <a class="nav-link text-white" href="admin_dashboard.php">
                        <i class="fas fa-arrow-left"></i> Back to Admin
                    </a>
                </nav>
            </div>
            
            <div class="col-md-10 p-4">
                <?php if ($message): ?>
                    <div class="alert alert-success alert-dismissible fade show">
                        <?= htmlspecialchars($message) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <?php if ($action === 'dashboard'): ?>
                    <h2><i class="fas fa-shield-alt"></i> Vote Integrity Dashboard</h2>
                    
                    <!-- Summary Cards -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card bg-primary text-white">
                                <div class="card-body">
                                    <h5><i class="fas fa-vote-yea"></i> Total Audited Votes</h5>
                                    <h3><?= $auditTrail['summary']['total_submissions'] ?></h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-success text-white">
                                <div class="card-body">
                                    <h5><i class="fas fa-users"></i> Unique Voters</h5>
                                    <h3><?= $auditTrail['summary']['unique_voters'] ?></h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-warning text-white">
                                <div class="card-body">
                                    <h5><i class="fas fa-flag"></i> Flagged Submissions</h5>
                                    <h3><?= $auditTrail['summary']['flagged_submissions'] ?></h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-danger text-white">
                                <div class="card-body">
                                    <h5><i class="fas fa-exclamation"></i> Integrity Violations</h5>
                                    <h3><?= $integrityCheck['integrity_violations'] ?></h3>
                                </div>
                            </div>
                        </div>
                    </div>      
              
                    <!-- Quick Actions -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5><i class="fas fa-check-circle"></i> Verify Vote Integrity</h5>
                                </div>
                                <div class="card-body">
                                    <form method="POST">
                                        <input type="hidden" name="action" value="verify_integrity">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <label>Start Date:</label>
                                                <input type="date" name="start_date" class="form-control" 
                                                       value="<?= date('Y-m-d', strtotime('-7 days')) ?>">
                                            </div>
                                            <div class="col-md-6">
                                                <label>End Date:</label>
                                                <input type="date" name="end_date" class="form-control" 
                                                       value="<?= date('Y-m-d') ?>">
                                            </div>
                                        </div>
                                        <button type="submit" class="btn btn-primary mt-2">
                                            <i class="fas fa-search"></i> Run Verification
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5><i class="fas fa-trash"></i> Data Cleanup</h5>
                                </div>
                                <div class="card-body">
                                    <form method="POST">
                                        <input type="hidden" name="action" value="cleanup_records">
                                        <div class="mb-2">
                                            <label>Retention Period (days):</label>
                                            <input type="number" name="retention_days" class="form-control" 
                                                   value="365" min="30" max="3650">
                                        </div>
                                        <button type="submit" class="btn btn-warning" 
                                                onclick="return confirm('Are you sure you want to cleanup old records?')">
                                            <i class="fas fa-broom"></i> Cleanup Old Records
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Recent Activity -->
                    <div class="card">
                        <div class="card-header">
                            <h5><i class="fas fa-clock"></i> Recent Voting Activity (Last 7 Days)</h5>
                        </div>
                        <div class="card-body">
                            <?php if (!empty($auditTrail['daily_breakdown'])): ?>
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>Date</th>
                                                <th>Total Votes</th>
                                                <th>Unique Voters</th>
                                                <th>Unique IPs</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($auditTrail['daily_breakdown'] as $day): ?>
                                                <tr>
                                                    <td><?= htmlspecialchars($day['vote_date']) ?></td>
                                                    <td><?= $day['daily_votes'] ?></td>
                                                    <td><?= $day['daily_unique_voters'] ?></td>
                                                    <td><?= $day['unique_ips'] ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <p class="text-muted">No voting activity in the last 7 days.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                <?php elseif ($action === 'audit_trail'): ?>
                    <h2><i class="fas fa-list-alt"></i> Audit Trail</h2>
                    
                    <?php
                    // Get detailed audit records
                    $stmt = mysqli_prepare($con, 
                        "SELECT va.*, vi.integrity_status, vi.anomaly_score 
                         FROM vote_audit va 
                         LEFT JOIN vote_integrity vi ON va.audit_id = vi.audit_id 
                         WHERE DATE(va.submission_timestamp) >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
                         ORDER BY va.submission_timestamp DESC 
                         LIMIT 100"
                    );
                    mysqli_stmt_execute($stmt);
                    $auditRecords = mysqli_stmt_get_result($stmt);
                    ?>
                    
                    <div class="card">
                        <div class="card-header">
                            <h5>Recent Audit Records (Last 30 Days)</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped table-sm">
                                    <thead>
                                        <tr>
                                            <th>Timestamp</th>
                                            <th>Username</th>
                                            <th>Vote Type</th>
                                            <th>Client IP</th>
                                            <th>Status</th>
                                            <th>Integrity</th>
                                            <th>Anomaly Score</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($record = mysqli_fetch_assoc($auditRecords)): ?>
                                            <tr class="<?= $record['status'] === 'flagged' ? 'table-warning' : '' ?>">
                                                <td><?= date('Y-m-d H:i:s', strtotime($record['submission_timestamp'])) ?></td>
                                                <td><?= htmlspecialchars($record['username']) ?></td>
                                                <td>
                                                    <span class="badge bg-info"><?= $record['vote_type'] ?></span>
                                                </td>
                                                <td><?= htmlspecialchars($record['client_ip']) ?></td>
                                                <td>
                                                    <?php if ($record['status'] === 'flagged'): ?>
                                                        <span class="badge bg-warning">Flagged</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-success">Normal</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if ($record['integrity_status']): ?>
                                                        <span class="badge bg-<?= $record['integrity_status'] === 'valid' ? 'success' : 'danger' ?>">
                                                            <?= ucfirst($record['integrity_status']) ?>
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="badge bg-secondary">Pending</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if ($record['anomaly_score']): ?>
                                                        <span class="badge bg-<?= $record['anomaly_score'] > 0.5 ? 'danger' : 'success' ?>">
                                                            <?= number_format($record['anomaly_score'], 2) ?>
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="badge bg-secondary">0.00</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    
                <?php elseif ($action === 'statistics'): ?>
                    <h2><i class="fas fa-chart-bar"></i> Voting Statistics</h2>
                    
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5>Daily Statistics (Last 30 Days)</h5>
                                </div>
                                <div class="card-body">
                                    <?php if (!empty($stats)): ?>
                                        <div class="table-responsive">
                                            <table class="table table-striped">
                                                <thead>
                                                    <tr>
                                                        <th>Date</th>
                                                        <th>Total Votes</th>
                                                        <th>Language Votes</th>
                                                        <th>Team Votes</th>
                                                        <th>Unique Voters</th>
                                                        <th>Peak Hour</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($stats as $stat): ?>
                                                        <tr>
                                                            <td><?= $stat['stat_date'] ?></td>
                                                            <td><?= $stat['total_votes'] ?></td>
                                                            <td><?= $stat['language_votes'] ?></td>
                                                            <td><?= $stat['team_votes'] ?></td>
                                                            <td><?= $stat['unique_voters'] ?></td>
                                                            <td><?= $stat['peak_voting_hour'] ? $stat['peak_voting_hour'] . ':00' : 'N/A' ?></td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    <?php else: ?>
                                        <p class="text-muted">No statistics available yet.</p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>