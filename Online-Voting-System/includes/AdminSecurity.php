<?php
/**
 * AdminSecurity Class
 * 
 * Provides enhanced security features specifically for admin panel:
 * - Admin session timeout (shorter than regular users)
 * - Admin action confirmation dialogs
 * - Admin activity audit trail
 * - Admin privilege separation
 * 
 * Requirements: 5.1, 5.2, 5.3, 5.4
 */
class AdminSecurity {
    
    // Admin privilege levels
    const PRIVILEGE_SUPER_ADMIN = 'super_admin';
    const PRIVILEGE_ADMIN = 'admin';
    const PRIVILEGE_MODERATOR = 'moderator';
    const PRIVILEGE_VIEWER = 'viewer';
    
    // Admin action types that require confirmation
    const HIGH_RISK_ACTIONS = [
        'delete_user',
        'reset_all_votes',
        'export_data',
        'reset_lang',
        'reset_team',
        'system_reset',
        'backup_database',
        'restore_database'
    ];
    
    // Admin privilege permissions mapping
    const PRIVILEGE_PERMISSIONS = [
        self::PRIVILEGE_SUPER_ADMIN => [
            'delete_user', 'reset_all_votes', 'export_data', 'reset_lang', 
            'reset_team', 'system_reset', 'backup_database', 'restore_database',
            'view_users', 'view_voters', 'view_results', 'view_analytics',
            'manage_admins', 'view_security_logs', 'system_config'
        ],
        self::PRIVILEGE_ADMIN => [
            'delete_user', 'reset_lang', 'reset_team', 'export_data',
            'view_users', 'view_voters', 'view_results', 'view_analytics',
            'view_security_logs'
        ],
        self::PRIVILEGE_MODERATOR => [
            'view_users', 'view_voters', 'view_results', 'view_analytics'
        ],
        self::PRIVILEGE_VIEWER => [
            'view_results', 'view_analytics'
        ]
    ];
    
    /**
     * Initialize admin security and validate session
     * Requirement 5.1: Admin session timeout (shorter than regular users)
     */
    public static function validateAdminSession() {
        require_once 'SessionSecurity.php';
        
        // Initialize secure session
        SessionSecurity::initializeSecureSession();
        
        // Validate admin session with shorter timeout
        if (!SessionSecurity::validateSession(true)) {
            self::logAdminSecurityEvent('session_timeout', 'Admin session timed out');
            return false;
        }
        
        // Check if admin is actually logged in
        if (!isset($_SESSION['ADMIN_LOGGED_IN']) || $_SESSION['ADMIN_LOGGED_IN'] !== true) {
            self::logAdminSecurityEvent('unauthorized_access', 'Attempted admin access without login');
            return false;
        }
        
        // Additional admin session validation
        if (!self::validateAdminSessionIntegrity()) {
            self::logAdminSecurityEvent('session_integrity_failure', 'Admin session integrity check failed');
            SessionSecurity::destroySession();
            return false;
        }
        
        return true;
    }
    
    /**
     * Validate admin session integrity
     */
    private static function validateAdminSessionIntegrity() {
        // Check if admin session has required fields
        if (!isset($_SESSION['ADMIN_NAME']) || !isset($_SESSION['admin_login_time'])) {
            return false;
        }
        
        // Check session age (max 4 hours for admin sessions)
        if (time() - $_SESSION['admin_login_time'] > 14400) { // 4 hours
            return false;
        }
        
        return true;
    }
    
    /**
     * Get admin privilege level
     * Requirement 5.4: Implement admin privilege separation
     */
    public static function getAdminPrivilege() {
        if (!isset($_SESSION['ADMIN_PRIVILEGE'])) {
            // Default privilege for existing admin (backward compatibility)
            return self::PRIVILEGE_ADMIN;
        }
        
        return $_SESSION['ADMIN_PRIVILEGE'];
    }
    
    /**
     * Set admin privilege level
     */
    public static function setAdminPrivilege($privilege) {
        if (in_array($privilege, [
            self::PRIVILEGE_SUPER_ADMIN, 
            self::PRIVILEGE_ADMIN, 
            self::PRIVILEGE_MODERATOR, 
            self::PRIVILEGE_VIEWER
        ])) {
            $_SESSION['ADMIN_PRIVILEGE'] = $privilege;
            self::logAdminSecurityEvent('privilege_change', "Admin privilege set to: $privilege");
        }
    }
    
    /**
     * Check if admin has permission for specific action
     * Requirement 5.4: Implement admin privilege separation
     */
    public static function hasPermission($action) {
        $privilege = self::getAdminPrivilege();
        $permissions = self::PRIVILEGE_PERMISSIONS[$privilege] ?? [];
        
        return in_array($action, $permissions);
    }
    
    /**
     * Require specific permission for action
     */
    public static function requirePermission($action) {
        if (!self::hasPermission($action)) {
            self::logAdminSecurityEvent('permission_denied', "Access denied for action: $action");
            throw new Exception("Insufficient privileges for this action");
        }
    }
    
    /**
     * Check if action requires confirmation dialog
     * Requirement 5.2: Add admin action confirmation dialogs
     */
    public static function requiresConfirmation($action) {
        return in_array($action, self::HIGH_RISK_ACTIONS);
    }
    
    /**
     * Generate confirmation dialog JavaScript
     * Requirement 5.2: Add admin action confirmation dialogs
     */
    public static function generateConfirmationDialog($action, $message = null) {
        if (!$message) {
            $message = self::getDefaultConfirmationMessage($action);
        }
        
        $escapedMessage = addslashes($message);
        
        return "
        <script>
        function confirmAction_{$action}(callback) {
            if (confirm('$escapedMessage')) {
                if (typeof callback === 'function') {
                    callback();
                }
                return true;
            }
            return false;
        }
        </script>";
    }
    
    /**
     * Get default confirmation message for action
     */
    private static function getDefaultConfirmationMessage($action) {
        $messages = [
            'delete_user' => '⚠️ WARNING: This will permanently delete the user and all associated data. This action cannot be undone. Are you sure you want to continue?',
            'reset_all_votes' => '🚨 CRITICAL ACTION: This will reset ALL votes in the system and cannot be undone. Are you absolutely sure?',
            'export_data' => '📊 This will export all voting data including sensitive information. Confirm export?',
            'reset_lang' => '🔄 This will reset all votes for this programming language. Continue?',
            'reset_team' => '🔄 This will reset all votes for this team member. Continue?',
            'system_reset' => '💥 DANGER: This will reset the entire system. This action is irreversible!',
            'backup_database' => '💾 Create a backup of the current database?',
            'restore_database' => '⚠️ WARNING: This will overwrite current data with backup. Continue?'
        ];
        
        return $messages[$action] ?? 'Are you sure you want to perform this action?';
    }
    
    /**
     * Log admin activity for audit trail
     * Requirement 5.3: Create admin activity audit trail
     */
    public static function logAdminActivity($action, $details = '', $targetTable = '', $targetId = null, $oldData = null, $newData = null) {
        require_once 'SecurityLogger.php';
        
        $adminName = $_SESSION['ADMIN_NAME'] ?? 'unknown';
        $privilege = self::getAdminPrivilege();
        $timestamp = date('Y-m-d H:i:s');
        $ip = self::getUserIP();
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
        
        // Enhanced audit trail data
        $auditData = [
            'timestamp' => $timestamp,
            'admin_name' => $adminName,
            'admin_privilege' => $privilege,
            'action' => $action,
            'details' => $details,
            'target_table' => $targetTable,
            'target_id' => $targetId,
            'ip_address' => $ip,
            'user_agent' => $userAgent,
            'session_id' => session_id(),
            'old_data' => $oldData ? json_encode($oldData) : null,
            'new_data' => $newData ? json_encode($newData) : null
        ];
        
        // Log to SecurityLogger if available
        if (class_exists('SecurityLogger')) {
            SecurityLogger::logAdminAction(
                $adminName, 
                $action, 
                $details, 
                $targetTable, 
                $targetId, 
                $oldData, 
                $newData
            );
        }
        
        // Also log to dedicated admin audit file
        self::writeAdminAuditLog($auditData);
        
        return $auditData;
    }
    
    /**
     * Write to dedicated admin audit log file
     */
    private static function writeAdminAuditLog($auditData) {
        $logDir = dirname(__FILE__) . '/../logs';
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        
        $logFile = $logDir . '/admin_audit.log';
        $logEntry = json_encode($auditData) . "\n";
        
        file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
    }
    
    /**
     * Log admin security events
     */
    public static function logAdminSecurityEvent($event, $details = '') {
        $adminName = $_SESSION['ADMIN_NAME'] ?? 'unknown';
        $timestamp = date('Y-m-d H:i:s');
        $ip = self::getUserIP();
        
        $logEntry = "[$timestamp] ADMIN_SECURITY - $event - Admin: $adminName - IP: $ip - Details: $details\n";
        
        $logDir = dirname(__FILE__) . '/../logs';
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        
        file_put_contents($logDir . '/admin_security.log', $logEntry, FILE_APPEND | LOCK_EX);
    }
    
    /**
     * Get user's real IP address
     */
    private static function getUserIP() {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            return $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            return $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            return $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        }
    }
    
    /**
     * Get admin session information
     */
    public static function getAdminSessionInfo() {
        if (!self::validateAdminSession()) {
            return null;
        }
        
        return [
            'admin_name' => $_SESSION['ADMIN_NAME'] ?? 'unknown',
            'privilege' => self::getAdminPrivilege(),
            'login_time' => $_SESSION['admin_login_time'] ?? null,
            'last_activity' => $_SESSION['last_activity'] ?? null,
            'session_id' => session_id(),
            'ip_address' => $_SESSION['ip_address'] ?? 'unknown',
            'timeout_remaining' => SessionSecurity::getTimeoutRemaining(true)
        ];
    }
    
    /**
     * Generate admin privilege badge HTML
     */
    public static function getPrivilegeBadge() {
        $privilege = self::getAdminPrivilege();
        $badges = [
            self::PRIVILEGE_SUPER_ADMIN => '<span class="badge badge-danger">🔴 Super Admin</span>',
            self::PRIVILEGE_ADMIN => '<span class="badge badge-warning">🟡 Admin</span>',
            self::PRIVILEGE_MODERATOR => '<span class="badge badge-info">🔵 Moderator</span>',
            self::PRIVILEGE_VIEWER => '<span class="badge badge-secondary">⚪ Viewer</span>'
        ];
        
        return $badges[$privilege] ?? '<span class="badge badge-dark">❓ Unknown</span>';
    }
    
    /**
     * Get admin activity summary for dashboard
     */
    public static function getAdminActivitySummary($limit = 10) {
        $logDir = dirname(__FILE__) . '/../logs';
        $logFile = $logDir . '/admin_audit.log';
        
        if (!file_exists($logFile)) {
            return [];
        }
        
        $lines = file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $activities = [];
        
        // Get last N activities
        $recentLines = array_slice($lines, -$limit);
        
        foreach ($recentLines as $line) {
            $activity = json_decode($line, true);
            if ($activity) {
                $activities[] = $activity;
            }
        }
        
        return array_reverse($activities); // Most recent first
    }
    
    /**
     * Check if admin session is about to expire
     */
    public static function isSessionNearExpiry($warningMinutes = 5) {
        require_once 'SessionSecurity.php';
        
        $remaining = SessionSecurity::getTimeoutRemaining(true);
        return $remaining <= ($warningMinutes * 60);
    }
    
    /**
     * Generate session timeout warning JavaScript
     */
    public static function generateSessionTimeoutWarning() {
        require_once 'SessionSecurity.php';
        
        $remaining = SessionSecurity::getTimeoutRemaining(true);
        $warningTime = 5 * 60; // 5 minutes warning
        
        return "
        <script>
        var adminSessionTimeout = $remaining;
        var warningShown = false;
        
        function checkAdminSessionTimeout() {
            adminSessionTimeout--;
            
            if (adminSessionTimeout <= $warningTime && !warningShown) {
                warningShown = true;
                var minutes = Math.ceil(adminSessionTimeout / 60);
                alert('⚠️ Admin session will expire in ' + minutes + ' minutes. Please save your work.');
            }
            
            if (adminSessionTimeout <= 0) {
                alert('🔒 Admin session has expired. You will be redirected to login.');
                window.location.href = 'admin_login.php';
                return;
            }
            
            // Update timeout display if element exists
            var timeoutElement = document.getElementById('session-timeout');
            if (timeoutElement) {
                var minutes = Math.floor(adminSessionTimeout / 60);
                var seconds = adminSessionTimeout % 60;
                timeoutElement.textContent = minutes + ':' + (seconds < 10 ? '0' : '') + seconds;
            }
        }
        
        // Check every second
        setInterval(checkAdminSessionTimeout, 1000);
        </script>";
    }
}
?>