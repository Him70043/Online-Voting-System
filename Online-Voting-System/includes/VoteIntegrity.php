<?php
/**
 * Vote Integrity and Audit Trail System
 * 
 * This class provides comprehensive vote integrity verification,
 * audit trail functionality, and anomaly detection while maintaining
 * vote privacy and security.
 */

class VoteIntegrity {
    private static $connection;
    
    /**
     * Initialize the Vote Integrity system
     */
    public static function initialize($dbConnection) {
        self::$connection = $dbConnection;
        self::createAuditTables();
    }
    
    /**
     * Create necessary audit and integrity tables
     */
    private static function createAuditTables() {
        // Create vote_audit table for audit trail
        $auditTable = "
            CREATE TABLE IF NOT EXISTS vote_audit (
                audit_id INT AUTO_INCREMENT PRIMARY KEY,
                username VARCHAR(100) NOT NULL,
                vote_type ENUM('language', 'team', 'both') NOT NULL,
                submission_timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                client_ip VARCHAR(45),
                user_agent TEXT,
                session_id VARCHAR(128),
                integrity_hash VARCHAR(64),
                status ENUM('submitted', 'verified', 'flagged') DEFAULT 'submitted',
                INDEX idx_username (username),
                INDEX idx_timestamp (submission_timestamp),
                INDEX idx_status (status)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ";
        
        // Create vote_integrity table for verification
        $integrityTable = "
            CREATE TABLE IF NOT EXISTS vote_integrity (
                integrity_id INT AUTO_INCREMENT PRIMARY KEY,
                audit_id INT,
                verification_timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                vote_count_snapshot JSON,
                integrity_status ENUM('valid', 'suspicious', 'invalid') DEFAULT 'valid',
                anomaly_score DECIMAL(5,2) DEFAULT 0.00,
                verification_notes TEXT,
                FOREIGN KEY (audit_id) REFERENCES vote_audit(audit_id),
                INDEX idx_verification_timestamp (verification_timestamp),
                INDEX idx_integrity_status (integrity_status)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ";
        
        // Create voting_statistics table for anomaly detection
        $statisticsTable = "
            CREATE TABLE IF NOT EXISTS voting_statistics (
                stat_id INT AUTO_INCREMENT PRIMARY KEY,
                stat_date DATE NOT NULL,
                total_votes INT DEFAULT 0,
                language_votes INT DEFAULT 0,
                team_votes INT DEFAULT 0,
                unique_voters INT DEFAULT 0,
                peak_voting_hour INT,
                average_votes_per_hour DECIMAL(8,2),
                anomaly_flags JSON,
                created_timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY unique_date (stat_date),
                INDEX idx_stat_date (stat_date)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ";
        
        mysqli_query(self::$connection, $auditTable);
        mysqli_query(self::$connection, $integrityTable);
        mysqli_query(self::$connection, $statisticsTable);
    }
    
    /**
     * Record vote submission with timestamp and integrity data
     */
    public static function recordVoteSubmission($username, $voteType, $voteData = null) {
        $clientIP = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
        $sessionId = session_id();
        
        // Generate integrity hash (without revealing vote content)
        $integrityData = [
            'username' => $username,
            'vote_type' => $voteType,
            'timestamp' => time(),
            'session_id' => $sessionId,
            'client_ip' => $clientIP
        ];
        $integrityHash = hash('sha256', json_encode($integrityData));
        
        // Insert audit record
        $stmt = mysqli_prepare(self::$connection, 
            "INSERT INTO vote_audit (username, vote_type, client_ip, user_agent, session_id, integrity_hash) 
             VALUES (?, ?, ?, ?, ?, ?)"
        );
        
        mysqli_stmt_bind_param($stmt, "ssssss", 
            $username, $voteType, $clientIP, $userAgent, $sessionId, $integrityHash
        );
        
        $result = mysqli_stmt_execute($stmt);
        $auditId = mysqli_insert_id(self::$connection);
        mysqli_stmt_close($stmt);
        
        if ($result) {
            // Create integrity verification record
            self::createIntegrityVerification($auditId);
            
            // Update daily statistics
            self::updateDailyStatistics($voteType);
            
            // Perform anomaly detection
            self::performAnomalyDetection($username, $voteType, $clientIP);
        }
        
        return $auditId;
    }
    
    /**
     * Create integrity verification record with vote count snapshot
     */
    private static function createIntegrityVerification($auditId) {
        // Get current vote counts for verification
        $voteCountSnapshot = self::getVoteCountSnapshot();
        
        $stmt = mysqli_prepare(self::$connection,
            "INSERT INTO vote_integrity (audit_id, vote_count_snapshot) VALUES (?, ?)"
        );
        
        $snapshotJson = json_encode($voteCountSnapshot);
        mysqli_stmt_bind_param($stmt, "is", $auditId, $snapshotJson);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }
    
    /**
     * Get current vote count snapshot for integrity verification
     */
    private static function getVoteCountSnapshot() {
        $snapshot = [
            'timestamp' => time(),
            'languages' => [],
            'team_members' => [],
            'total_voters' => 0,
            'voted_count' => 0
        ];
        
        // Get language vote counts
        $langResult = mysqli_query(self::$connection, "SELECT fullname, votecount FROM languages");
        while ($row = mysqli_fetch_assoc($langResult)) {
            $snapshot['languages'][$row['fullname']] = (int)$row['votecount'];
        }
        
        // Get team member vote counts
        $teamResult = mysqli_query(self::$connection, "SELECT fullname, votecount FROM team_members");
        while ($row = mysqli_fetch_assoc($teamResult)) {
            $snapshot['team_members'][$row['fullname']] = (int)$row['votecount'];
        }
        
        // Get voter statistics
        $voterStats = mysqli_query(self::$connection, 
            "SELECT COUNT(*) as total, SUM(CASE WHEN status = 'VOTED' THEN 1 ELSE 0 END) as voted FROM voters"
        );
        $stats = mysqli_fetch_assoc($voterStats);
        $snapshot['total_voters'] = (int)$stats['total'];
        $snapshot['voted_count'] = (int)$stats['voted'];
        
        return $snapshot;
    }
    
    /**
     * Update daily voting statistics
     */
    private static function updateDailyStatistics($voteType) {
        $today = date('Y-m-d');
        $currentHour = (int)date('H');
        
        // Get or create today's statistics
        $stmt = mysqli_prepare(self::$connection,
            "INSERT INTO voting_statistics (stat_date, total_votes, language_votes, team_votes, unique_voters, peak_voting_hour) 
             VALUES (?, 1, ?, ?, 1, ?) 
             ON DUPLICATE KEY UPDATE 
             total_votes = total_votes + 1,
             language_votes = language_votes + ?,
             team_votes = team_votes + ?,
             unique_voters = (SELECT COUNT(DISTINCT username) FROM vote_audit WHERE DATE(submission_timestamp) = ?),
             peak_voting_hour = IF(total_votes > (SELECT MAX(total_votes) FROM voting_statistics WHERE stat_date < ?), ?, peak_voting_hour)"
        );
        
        $langVote = ($voteType === 'language' || $voteType === 'both') ? 1 : 0;
        $teamVote = ($voteType === 'team' || $voteType === 'both') ? 1 : 0;
        
        mysqli_stmt_bind_param($stmt, "siiiiiisi", 
            $today, $langVote, $teamVote, $currentHour, 
            $langVote, $teamVote, $today, $today, $currentHour
        );
        
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }
    
    /**
     * Perform anomaly detection on voting patterns
     */
    private static function performAnomalyDetection($username, $voteType, $clientIP) {
        $anomalies = [];
        $anomalyScore = 0.0;
        
        // Check for rapid successive votes from same IP
        $stmt = mysqli_prepare(self::$connection,
            "SELECT COUNT(*) as count FROM vote_audit 
             WHERE client_ip = ? AND submission_timestamp > DATE_SUB(NOW(), INTERVAL 1 MINUTE)"
        );
        mysqli_stmt_bind_param($stmt, "s", $clientIP);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $rapidVotes = mysqli_fetch_assoc($result)['count'];
        mysqli_stmt_close($stmt);
        
        if ($rapidVotes > 3) {
            $anomalies[] = 'rapid_voting_from_ip';
            $anomalyScore += 0.3;
        }
        
        // Check for unusual voting patterns (voting outside normal hours)
        $currentHour = (int)date('H');
        if ($currentHour < 6 || $currentHour > 23) {
            $anomalies[] = 'unusual_voting_time';
            $anomalyScore += 0.2;
        }
        
        // Check for suspicious user agent patterns
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        if (empty($userAgent) || strlen($userAgent) < 20) {
            $anomalies[] = 'suspicious_user_agent';
            $anomalyScore += 0.25;
        }
        
        // Check for duplicate session attempts
        $sessionId = session_id();
        $stmt = mysqli_prepare(self::$connection,
            "SELECT COUNT(*) as count FROM vote_audit 
             WHERE session_id = ? AND username != ?"
        );
        mysqli_stmt_bind_param($stmt, "ss", $sessionId, $username);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $sessionReuse = mysqli_fetch_assoc($result)['count'];
        mysqli_stmt_close($stmt);
        
        if ($sessionReuse > 0) {
            $anomalies[] = 'session_reuse_detected';
            $anomalyScore += 0.4;
        }
        
        // Flag high anomaly scores
        if ($anomalyScore >= 0.5) {
            self::flagSuspiciousActivity($username, $anomalies, $anomalyScore);
        }
        
        return ['anomalies' => $anomalies, 'score' => $anomalyScore];
    }
    
    /**
     * Flag suspicious voting activity
     */
    private static function flagSuspiciousActivity($username, $anomalies, $score) {
        // Update the most recent audit record for this user
        $stmt = mysqli_prepare(self::$connection,
            "UPDATE vote_audit SET status = 'flagged' 
             WHERE username = ? ORDER BY submission_timestamp DESC LIMIT 1"
        );
        mysqli_stmt_bind_param($stmt, "s", $username);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        
        // Log the suspicious activity
        if (class_exists('SecurityLogger')) {
            SecurityLogger::logSecurityEvent('SUSPICIOUS_VOTING_ACTIVITY', 
                "User: $username, Anomalies: " . implode(', ', $anomalies) . ", Score: $score"
            );
        }
    }
    
    /**
     * Verify vote integrity for a specific time period
     */
    public static function verifyVoteIntegrity($startDate = null, $endDate = null) {
        if (!$startDate) $startDate = date('Y-m-d', strtotime('-1 day'));
        if (!$endDate) $endDate = date('Y-m-d');
        
        $verificationResults = [
            'period' => ['start' => $startDate, 'end' => $endDate],
            'total_votes_audited' => 0,
            'integrity_violations' => 0,
            'suspicious_activities' => 0,
            'verification_timestamp' => date('Y-m-d H:i:s'),
            'details' => []
        ];
        
        // Get audit records for the period
        $stmt = mysqli_prepare(self::$connection,
            "SELECT va.*, vi.integrity_status, vi.anomaly_score 
             FROM vote_audit va 
             LEFT JOIN vote_integrity vi ON va.audit_id = vi.audit_id 
             WHERE DATE(va.submission_timestamp) BETWEEN ? AND ?"
        );
        
        mysqli_stmt_bind_param($stmt, "ss", $startDate, $endDate);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        while ($row = mysqli_fetch_assoc($result)) {
            $verificationResults['total_votes_audited']++;
            
            if ($row['status'] === 'flagged') {
                $verificationResults['suspicious_activities']++;
            }
            
            if ($row['integrity_status'] === 'invalid') {
                $verificationResults['integrity_violations']++;
            }
            
            // Verify integrity hash
            $integrityData = [
                'username' => $row['username'],
                'vote_type' => $row['vote_type'],
                'timestamp' => strtotime($row['submission_timestamp']),
                'session_id' => $row['session_id'],
                'client_ip' => $row['client_ip']
            ];
            $expectedHash = hash('sha256', json_encode($integrityData));
            
            if ($expectedHash !== $row['integrity_hash']) {
                $verificationResults['integrity_violations']++;
                $verificationResults['details'][] = [
                    'audit_id' => $row['audit_id'],
                    'issue' => 'integrity_hash_mismatch',
                    'username' => $row['username']
                ];
            }
        }
        
        mysqli_stmt_close($stmt);
        return $verificationResults;
    }
    
    /**
     * Generate audit trail report (privacy-preserving)
     */
    public static function generateAuditTrail($startDate = null, $endDate = null, $includeDetails = false) {
        if (!$startDate) $startDate = date('Y-m-d', strtotime('-7 days'));
        if (!$endDate) $endDate = date('Y-m-d');
        
        $auditTrail = [
            'report_period' => ['start' => $startDate, 'end' => $endDate],
            'generated_at' => date('Y-m-d H:i:s'),
            'summary' => [],
            'daily_breakdown' => [],
            'anomaly_summary' => []
        ];
        
        // Get summary statistics
        $stmt = mysqli_prepare(self::$connection,
            "SELECT 
                COUNT(*) as total_submissions,
                COUNT(DISTINCT username) as unique_voters,
                SUM(CASE WHEN vote_type = 'language' THEN 1 ELSE 0 END) as language_votes,
                SUM(CASE WHEN vote_type = 'team' THEN 1 ELSE 0 END) as team_votes,
                SUM(CASE WHEN vote_type = 'both' THEN 1 ELSE 0 END) as both_votes,
                SUM(CASE WHEN status = 'flagged' THEN 1 ELSE 0 END) as flagged_submissions
             FROM vote_audit 
             WHERE DATE(submission_timestamp) BETWEEN ? AND ?"
        );
        
        mysqli_stmt_bind_param($stmt, "ss", $startDate, $endDate);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $auditTrail['summary'] = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
        
        // Get daily breakdown
        $stmt = mysqli_prepare(self::$connection,
            "SELECT 
                DATE(submission_timestamp) as vote_date,
                COUNT(*) as daily_votes,
                COUNT(DISTINCT username) as daily_unique_voters,
                COUNT(DISTINCT client_ip) as unique_ips
             FROM vote_audit 
             WHERE DATE(submission_timestamp) BETWEEN ? AND ?
             GROUP BY DATE(submission_timestamp)
             ORDER BY vote_date"
        );
        
        mysqli_stmt_bind_param($stmt, "ss", $startDate, $endDate);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        while ($row = mysqli_fetch_assoc($result)) {
            $auditTrail['daily_breakdown'][] = $row;
        }
        mysqli_stmt_close($stmt);
        
        return $auditTrail;
    }
    
    /**
     * Get voting statistics for anomaly detection dashboard
     */
    public static function getVotingStatistics($days = 7) {
        $stats = [];
        
        $stmt = mysqli_prepare(self::$connection,
            "SELECT * FROM voting_statistics 
             WHERE stat_date >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
             ORDER BY stat_date DESC"
        );
        
        mysqli_stmt_bind_param($stmt, "i", $days);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        while ($row = mysqli_fetch_assoc($result)) {
            $row['anomaly_flags'] = json_decode($row['anomaly_flags'], true) ?: [];
            $stats[] = $row;
        }
        
        mysqli_stmt_close($stmt);
        return $stats;
    }
    
    /**
     * Clean up old audit records (data retention)
     */
    public static function cleanupOldRecords($retentionDays = 365) {
        $cutoffDate = date('Y-m-d', strtotime("-$retentionDays days"));
        
        // Delete old vote_integrity records first (foreign key constraint)
        $stmt1 = mysqli_prepare(self::$connection,
            "DELETE vi FROM vote_integrity vi 
             JOIN vote_audit va ON vi.audit_id = va.audit_id 
             WHERE DATE(va.submission_timestamp) < ?"
        );
        mysqli_stmt_bind_param($stmt1, "s", $cutoffDate);
        $deleted1 = mysqli_stmt_execute($stmt1);
        $integrityDeleted = mysqli_stmt_affected_rows($stmt1);
        mysqli_stmt_close($stmt1);
        
        // Delete old vote_audit records
        $stmt2 = mysqli_prepare(self::$connection,
            "DELETE FROM vote_audit WHERE DATE(submission_timestamp) < ?"
        );
        mysqli_stmt_bind_param($stmt2, "s", $cutoffDate);
        $deleted2 = mysqli_stmt_execute($stmt2);
        $auditDeleted = mysqli_stmt_affected_rows($stmt2);
        mysqli_stmt_close($stmt2);
        
        // Delete old statistics (keep longer retention)
        $statsCutoff = date('Y-m-d', strtotime("-" . ($retentionDays * 2) . " days"));
        $stmt3 = mysqli_prepare(self::$connection,
            "DELETE FROM voting_statistics WHERE stat_date < ?"
        );
        mysqli_stmt_bind_param($stmt3, "s", $statsCutoff);
        $deleted3 = mysqli_stmt_execute($stmt3);
        $statsDeleted = mysqli_stmt_affected_rows($stmt3);
        mysqli_stmt_close($stmt3);
        
        return [
            'audit_records_deleted' => $auditDeleted,
            'integrity_records_deleted' => $integrityDeleted,
            'statistics_deleted' => $statsDeleted,
            'cleanup_date' => date('Y-m-d H:i:s')
        ];
    }
}