<?php
/**
 * Security Logger Class
 * Comprehensive security logging system for the Online Voting System
 * 
 * Features:
 * - Authentication attempt logging
 * - Voting activity logging (privacy-preserving)
 * - Admin action logging
 * - Security event monitoring
 * 
 * @author Himanshu Kumar
 * @version 1.0
 */

class SecurityLogger {
    private static $logDirectory = 'logs/security/';
    private static $dbConnection = null;
    
    /**
     * Initialize the security logger
     */
    public static function initialize($connection) {
        self::$dbConnection = $connection;
        self::createLogDirectory();
        self::createLogTables();
    }
    
    /**
     * Create log directory if it doesn't exist
     */
    private static function createLogDirectory() {
        if (!file_exists(self::$logDirectory)) {
            mkdir(self::$logDirectory, 0755, true);
        }
    }
    
    /**
     * Create database tables for security logs
     */
    private static function createLogTables() {
        if (self::$dbConnection === null) return;
        
        // Authentication logs table
        $authLogTable = "
            CREATE TABLE IF NOT EXISTS security_auth_logs (
                id INT AUTO_INCREMENT PRIMARY KEY,
                timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
                username VARCHAR(100),
                ip_address VARCHAR(45),
                user_agent TEXT,
                login_type ENUM('user', 'admin') DEFAULT 'user',
                success BOOLEAN,
                failure_reason VARCHAR(255),
                session_id VARCHAR(255),
                INDEX idx_timestamp (timestamp),
                INDEX idx_username (username),
                INDEX idx_ip (ip_address)
            )
        ";
        
        // Voting activity logs table
        $voteLogTable = "
            CREATE TABLE IF NOT EXISTS security_vote_logs (
                id INT AUTO_INCREMENT PRIMARY KEY,
                timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
                username VARCHAR(100),
                ip_address VARCHAR(45),
                user_agent TEXT,
                vote_type ENUM('language', 'team', 'both'),
                session_id VARCHAR(255),
                INDEX idx_timestamp (timestamp),
                INDEX idx_username (username)
            )
        ";
        
        // Admin action logs table
        $adminLogTable = "
            CREATE TABLE IF NOT EXISTS security_admin_logs (
                id INT AUTO_INCREMENT PRIMARY KEY,
                timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
                admin_username VARCHAR(100),
                ip_address VARCHAR(45),
                user_agent TEXT,
                action_type VARCHAR(100),
                action_description TEXT,
                affected_table VARCHAR(100),
                affected_record_id INT,
                old_values JSON,
                new_values JSON,
                session_id VARCHAR(255),
                INDEX idx_timestamp (timestamp),
                INDEX idx_admin (admin_username),
                INDEX idx_action (action_type)
            )
        ";
        
        // System security events table
        $securityEventTable = "
            CREATE TABLE IF NOT EXISTS security_events (
                id INT AUTO_INCREMENT PRIMARY KEY,
                timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
                event_type VARCHAR(100),
                severity ENUM('low', 'medium', 'high', 'critical') DEFAULT 'medium',
                ip_address VARCHAR(45),
                user_agent TEXT,
                username VARCHAR(100),
                description TEXT,
                additional_data JSON,
                INDEX idx_timestamp (timestamp),
                INDEX idx_severity (severity),
                INDEX idx_event_type (event_type)
            )
        ";
        
        mysqli_query(self::$dbConnection, $authLogTable);
        mysqli_query(self::$dbConnection, $voteLogTable);
        mysqli_query(self::$dbConnection, $adminLogTable);
        mysqli_query(self::$dbConnection, $securityEventTable);
    }
    
    /**
     * Log authentication attempts
     */
    public static function logAuthenticationAttempt($username, $success, $loginType = 'user', $failureReason = null) {
        $timestamp = date('Y-m-d H:i:s');
        $ipAddress = self::getClientIP();
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
        $sessionId = session_id();
        
        // Database logging
        if (self::$dbConnection !== null) {
            $stmt = mysqli_prepare(self::$dbConnection, 
                "INSERT INTO security_auth_logs (username, ip_address, user_agent, login_type, success, failure_reason, session_id) 
                 VALUES (?, ?, ?, ?, ?, ?, ?)"
            );
            mysqli_stmt_bind_param($stmt, "sssssss", $username, $ipAddress, $userAgent, $loginType, $success, $failureReason, $sessionId);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }
        
        // File logging
        $logEntry = [
            'timestamp' => $timestamp,
            'type' => 'authentication',
            'username' => $username,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
            'login_type' => $loginType,
            'success' => $success,
            'failure_reason' => $failureReason,
            'session_id' => $sessionId
        ];
        
        self::writeToLogFile('auth_' . date('Y-m-d') . '.log', $logEntry);
        
        // Log security event for failed attempts
        if (!$success) {
            self::logSecurityEvent(
                'failed_authentication',
                'medium',
                "Failed login attempt for user: $username" . ($failureReason ? " - Reason: $failureReason" : ""),
                ['username' => $username, 'login_type' => $loginType]
            );
        }
    }
    
    /**
     * Log voting activities (privacy-preserving)
     */
    public static function logVotingActivity($username, $voteType) {
        $timestamp = date('Y-m-d H:i:s');
        $ipAddress = self::getClientIP();
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
        $sessionId = session_id();
        
        // Database logging (no vote content stored)
        if (self::$dbConnection !== null) {
            $stmt = mysqli_prepare(self::$dbConnection,
                "INSERT INTO security_vote_logs (username, ip_address, user_agent, vote_type, session_id) 
                 VALUES (?, ?, ?, ?, ?)"
            );
            mysqli_stmt_bind_param($stmt, "sssss", $username, $ipAddress, $userAgent, $voteType, $sessionId);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }
        
        // File logging
        $logEntry = [
            'timestamp' => $timestamp,
            'type' => 'voting_activity',
            'username' => $username,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
            'vote_type' => $voteType,
            'session_id' => $sessionId
        ];
        
        self::writeToLogFile('voting_' . date('Y-m-d') . '.log', $logEntry);
        
        // Log security event for voting
        self::logSecurityEvent(
            'vote_cast',
            'low',
            "Vote cast by user: $username",
            ['username' => $username, 'vote_type' => $voteType]
        );
    }
    
    /**
     * Log admin actions and system changes
     */
    public static function logAdminAction($adminUsername, $actionType, $actionDescription, $affectedTable = null, $affectedRecordId = null, $oldValues = null, $newValues = null) {
        $timestamp = date('Y-m-d H:i:s');
        $ipAddress = self::getClientIP();
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
        $sessionId = session_id();
        
        // Convert arrays to JSON
        $oldValuesJson = $oldValues ? json_encode($oldValues) : null;
        $newValuesJson = $newValues ? json_encode($newValues) : null;
        
        // Database logging
        if (self::$dbConnection !== null) {
            $stmt = mysqli_prepare(self::$dbConnection,
                "INSERT INTO security_admin_logs (admin_username, ip_address, user_agent, action_type, action_description, affected_table, affected_record_id, old_values, new_values, session_id) 
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
            );
            mysqli_stmt_bind_param($stmt, "ssssssssss", $adminUsername, $ipAddress, $userAgent, $actionType, $actionDescription, $affectedTable, $affectedRecordId, $oldValuesJson, $newValuesJson, $sessionId);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }
        
        // File logging
        $logEntry = [
            'timestamp' => $timestamp,
            'type' => 'admin_action',
            'admin_username' => $adminUsername,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
            'action_type' => $actionType,
            'action_description' => $actionDescription,
            'affected_table' => $affectedTable,
            'affected_record_id' => $affectedRecordId,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'session_id' => $sessionId
        ];
        
        self::writeToLogFile('admin_' . date('Y-m-d') . '.log', $logEntry);
        
        // Log security event for admin actions
        $severity = self::getAdminActionSeverity($actionType);
        self::logSecurityEvent(
            'admin_action',
            $severity,
            "Admin action: $actionDescription by $adminUsername",
            ['admin_username' => $adminUsername, 'action_type' => $actionType]
        );
    }
    
    /**
     * Log general security events
     */
    public static function logSecurityEvent($eventType, $severity, $description, $additionalData = null) {
        $timestamp = date('Y-m-d H:i:s');
        $ipAddress = self::getClientIP();
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
        $username = $_SESSION['SESS_NAME'] ?? $_SESSION['ADMIN_NAME'] ?? 'anonymous';
        $additionalDataJson = $additionalData ? json_encode($additionalData) : null;
        
        // Database logging
        if (self::$dbConnection !== null) {
            $stmt = mysqli_prepare(self::$dbConnection,
                "INSERT INTO security_events (event_type, severity, ip_address, user_agent, username, description, additional_data) 
                 VALUES (?, ?, ?, ?, ?, ?, ?)"
            );
            mysqli_stmt_bind_param($stmt, "sssssss", $eventType, $severity, $ipAddress, $userAgent, $username, $description, $additionalDataJson);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }
        
        // File logging
        $logEntry = [
            'timestamp' => $timestamp,
            'type' => 'security_event',
            'event_type' => $eventType,
            'severity' => $severity,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
            'username' => $username,
            'description' => $description,
            'additional_data' => $additionalData
        ];
        
        self::writeToLogFile('security_events_' . date('Y-m-d') . '.log', $logEntry);
    }
    
    /**
     * Get security statistics for dashboard
     */
    public static function getSecurityStatistics($days = 7) {
        if (self::$dbConnection === null) return null;
        
        $stats = [];
        $dateLimit = date('Y-m-d H:i:s', strtotime("-$days days"));
        
        // Authentication statistics
        $authQuery = "SELECT 
            COUNT(*) as total_attempts,
            SUM(success) as successful_logins,
            COUNT(*) - SUM(success) as failed_attempts,
            login_type
            FROM security_auth_logs 
            WHERE timestamp >= ? 
            GROUP BY login_type";
        
        $stmt = mysqli_prepare(self::$dbConnection, $authQuery);
        mysqli_stmt_bind_param($stmt, "s", $dateLimit);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        while ($row = mysqli_fetch_assoc($result)) {
            $stats['authentication'][$row['login_type']] = $row;
        }
        mysqli_stmt_close($stmt);
        
        // Voting statistics
        $voteQuery = "SELECT COUNT(*) as total_votes, vote_type FROM security_vote_logs WHERE timestamp >= ? GROUP BY vote_type";
        $stmt = mysqli_prepare(self::$dbConnection, $voteQuery);
        mysqli_stmt_bind_param($stmt, "s", $dateLimit);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        while ($row = mysqli_fetch_assoc($result)) {
            $stats['voting'][$row['vote_type']] = $row['total_votes'];
        }
        mysqli_stmt_close($stmt);
        
        // Security events by severity
        $eventQuery = "SELECT COUNT(*) as count, severity FROM security_events WHERE timestamp >= ? GROUP BY severity";
        $stmt = mysqli_prepare(self::$dbConnection, $eventQuery);
        mysqli_stmt_bind_param($stmt, "s", $dateLimit);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        while ($row = mysqli_fetch_assoc($result)) {
            $stats['security_events'][$row['severity']] = $row['count'];
        }
        mysqli_stmt_close($stmt);
        
        // Admin actions
        $adminQuery = "SELECT COUNT(*) as total_actions, action_type FROM security_admin_logs WHERE timestamp >= ? GROUP BY action_type";
        $stmt = mysqli_prepare(self::$dbConnection, $adminQuery);
        mysqli_stmt_bind_param($stmt, "s", $dateLimit);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        while ($row = mysqli_fetch_assoc($result)) {
            $stats['admin_actions'][$row['action_type']] = $row['total_actions'];
        }
        mysqli_stmt_close($stmt);
        
        return $stats;
    }
    
    /**
     * Get recent security events for monitoring
     */
    public static function getRecentSecurityEvents($limit = 50) {
        if (self::$dbConnection === null) return [];
        
        $query = "SELECT * FROM security_events ORDER BY timestamp DESC LIMIT ?";
        $stmt = mysqli_prepare(self::$dbConnection, $query);
        mysqli_stmt_bind_param($stmt, "i", $limit);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        $events = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $events[] = $row;
        }
        mysqli_stmt_close($stmt);
        
        return $events;
    }
    
    /**
     * Get authentication logs with filtering
     */
    public static function getAuthenticationLogs($limit = 100, $username = null, $success = null) {
        if (self::$dbConnection === null) return [];
        
        $query = "SELECT * FROM security_auth_logs WHERE 1=1";
        $params = [];
        $types = "";
        
        if ($username !== null) {
            $query .= " AND username = ?";
            $params[] = $username;
            $types .= "s";
        }
        
        if ($success !== null) {
            $query .= " AND success = ?";
            $params[] = $success;
            $types .= "i";
        }
        
        $query .= " ORDER BY timestamp DESC LIMIT ?";
        $params[] = $limit;
        $types .= "i";
        
        $stmt = mysqli_prepare(self::$dbConnection, $query);
        if (!empty($params)) {
            mysqli_stmt_bind_param($stmt, $types, ...$params);
        }
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        $logs = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $logs[] = $row;
        }
        mysqli_stmt_close($stmt);
        
        return $logs;
    }
    
    /**
     * Write log entry to file
     */
    private static function writeToLogFile($filename, $logEntry) {
        $logFile = self::$logDirectory . $filename;
        $logLine = date('Y-m-d H:i:s') . " | " . json_encode($logEntry) . "\n";
        file_put_contents($logFile, $logLine, FILE_APPEND | LOCK_EX);
    }
    
    /**
     * Get client IP address
     */
    private static function getClientIP() {
        $ipKeys = ['HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'];
        foreach ($ipKeys as $key) {
            if (array_key_exists($key, $_SERVER) === true) {
                foreach (explode(',', $_SERVER[$key]) as $ip) {
                    $ip = trim($ip);
                    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                        return $ip;
                    }
                }
            }
        }
        return $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    }
    
    /**
     * Determine severity level for admin actions
     */
    private static function getAdminActionSeverity($actionType) {
        $highSeverityActions = ['delete_user', 'reset_votes', 'system_config', 'database_modify'];
        $mediumSeverityActions = ['view_users', 'export_data', 'user_management'];
        
        if (in_array($actionType, $highSeverityActions)) {
            return 'high';
        } elseif (in_array($actionType, $mediumSeverityActions)) {
            return 'medium';
        } else {
            return 'low';
        }
    }
    
    /**
     * Clean old log files (retention policy)
     */
    public static function cleanOldLogs($retentionDays = 90) {
        $cutoffDate = date('Y-m-d', strtotime("-$retentionDays days"));
        
        // Clean database logs
        if (self::$dbConnection !== null) {
            $tables = ['security_auth_logs', 'security_vote_logs', 'security_admin_logs', 'security_events'];
            foreach ($tables as $table) {
                $query = "DELETE FROM $table WHERE timestamp < ?";
                $stmt = mysqli_prepare(self::$dbConnection, $query);
                mysqli_stmt_bind_param($stmt, "s", $cutoffDate);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
            }
        }
        
        // Clean log files
        if (is_dir(self::$logDirectory)) {
            $files = glob(self::$logDirectory . '*.log');
            foreach ($files as $file) {
                if (filemtime($file) < strtotime("-$retentionDays days")) {
                    unlink($file);
                }
            }
        }
    }
}
?>