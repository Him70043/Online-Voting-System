<?php
/**
 * Database Security Enhancement Class
 * Implements secure database connections, user management, and integrity checks
 */

class DatabaseSecurity {
    private $host;
    private $username;
    private $password;
    private $database;
    private $connection;
    private $logFile;
    
    public function __construct($host = 'localhost', $database = 'polltest') {
        $this->host = $host;
        $this->database = $database;
        $this->logFile = __DIR__ . '/../logs/database_security.log';
        
        // Ensure logs directory exists
        if (!file_exists(dirname($this->logFile))) {
            mkdir(dirname($this->logFile), 0755, true);
        }
    }
    
    /**
     * Create a dedicated database user with minimal privileges
     */
    public function createVotingUser() {
        try {
            // Connect as root to create user
            $rootConnection = new mysqli($this->host, 'root', '', $this->database);
            
            if ($rootConnection->connect_error) {
                throw new Exception("Root connection failed: " . $rootConnection->connect_error);
            }
            
            // Generate secure password for voting user
            $votingPassword = $this->generateSecurePassword();
            
            // Create voting user with minimal privileges
            // First check if user exists
            $checkUserSQL = "SELECT User FROM mysql.user WHERE User = 'voting_user' AND Host = 'localhost'";
            $userExists = $rootConnection->query($checkUserSQL);
            
            if ($userExists->num_rows == 0) {
                $createUserSQL = "CREATE USER 'voting_user'@'localhost' IDENTIFIED BY ?";
                $stmt = $rootConnection->prepare($createUserSQL);
                $stmt->bind_param("s", $votingPassword);
                $stmt->execute();
                $stmt->close();
            } else {
                // User exists, update password
                $updateUserSQL = "ALTER USER 'voting_user'@'localhost' IDENTIFIED BY ?";
                $stmt = $rootConnection->prepare($updateUserSQL);
                $stmt->bind_param("s", $votingPassword);
                $stmt->execute();
                $stmt->close();
            }
            
            // Grant only necessary privileges
            $privileges = [
                "GRANT SELECT, INSERT, UPDATE ON {$this->database}.loginusers TO 'voting_user'@'localhost'",
                "GRANT SELECT, INSERT, UPDATE ON {$this->database}.voters TO 'voting_user'@'localhost'",
                "GRANT SELECT, UPDATE ON {$this->database}.languages TO 'voting_user'@'localhost'",
                "GRANT SELECT, UPDATE ON {$this->database}.team_members TO 'voting_user'@'localhost'"
            ];
            
            foreach ($privileges as $privilege) {
                $rootConnection->query($privilege);
            }
            
            $rootConnection->query("FLUSH PRIVILEGES");
            
            // Store credentials securely
            $this->storeCredentials('voting_user', $votingPassword);
            
            $this->logSecurityEvent("Database user 'voting_user' created with minimal privileges");
            
            $rootConnection->close();
            return true;
            
        } catch (Exception $e) {
            $this->logSecurityEvent("Failed to create voting user: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Establish secure database connection with SSL
     */
    public function getSecureConnection() {
        try {
            $credentials = $this->loadCredentials();
            
            if (!$credentials) {
                throw new Exception("Database credentials not found");
            }
            
            // Create connection with SSL options
            $this->connection = new mysqli();
            
            // Set SSL options for encrypted connection
            $this->connection->ssl_set(null, null, null, null, null);
            
            // Connect with SSL
            $this->connection->real_connect(
                $this->host,
                $credentials['username'],
                $credentials['password'],
                $this->database,
                3306,
                null,
                MYSQLI_CLIENT_SSL
            );
            
            if ($this->connection->connect_error) {
                throw new Exception("Secure connection failed: " . $this->connection->connect_error);
            }
            
            // Set charset to prevent character set confusion attacks
            $this->connection->set_charset("utf8mb4");
            
            $this->logSecurityEvent("Secure database connection established");
            
            return $this->connection;
            
        } catch (Exception $e) {
            $this->logSecurityEvent("Secure connection failed: " . $e->getMessage());
            
            // Fallback to regular connection if SSL fails
            return $this->getFallbackConnection();
        }
    }
    
    /**
     * Fallback connection without SSL
     */
    private function getFallbackConnection() {
        try {
            $credentials = $this->loadCredentials();
            
            if (!$credentials) {
                // Use root as fallback
                $credentials = ['username' => 'root', 'password' => ''];
            }
            
            $this->connection = new mysqli(
                $this->host,
                $credentials['username'],
                $credentials['password'],
                $this->database
            );
            
            if ($this->connection->connect_error) {
                throw new Exception("Fallback connection failed: " . $this->connection->connect_error);
            }
            
            $this->connection->set_charset("utf8mb4");
            
            $this->logSecurityEvent("Fallback database connection established");
            
            return $this->connection;
            
        } catch (Exception $e) {
            $this->logSecurityEvent("All connection attempts failed: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Create secure database backup
     */
    public function createSecureBackup() {
        try {
            $backupDir = __DIR__ . '/../backups';
            if (!file_exists($backupDir)) {
                mkdir($backupDir, 0755, true);
            }
            
            $timestamp = date('Y-m-d_H-i-s');
            $backupFile = $backupDir . "/polltest_backup_{$timestamp}.sql";
            
            // Create backup using mysqldump
            $mysqldumpPath = $this->findMysqldumpPath();
            $command = "\"{$mysqldumpPath}\" --host={$this->host} --user=root --single-transaction --routines --triggers {$this->database} > \"{$backupFile}\"";
            
            exec($command, $output, $returnCode);
            
            if ($returnCode === 0 && file_exists($backupFile)) {
                // Encrypt backup file
                $this->encryptBackupFile($backupFile);
                
                $this->logSecurityEvent("Secure backup created: " . basename($backupFile));
                return $backupFile;
            } else {
                throw new Exception("Backup creation failed");
            }
            
        } catch (Exception $e) {
            $this->logSecurityEvent("Backup failed: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Perform database integrity checks
     */
    public function performIntegrityChecks() {
        try {
            $connection = $this->getSecureConnection();
            $issues = [];
            
            // Check for orphaned records
            $orphanedVoters = $this->checkOrphanedVoters($connection);
            if ($orphanedVoters > 0) {
                $issues[] = "Found {$orphanedVoters} orphaned voter records";
            }
            
            // Check vote count consistency
            $voteConsistency = $this->checkVoteConsistency($connection);
            if (!$voteConsistency) {
                $issues[] = "Vote count inconsistency detected";
            }
            
            // Check for duplicate usernames
            $duplicateUsers = $this->checkDuplicateUsers($connection);
            if ($duplicateUsers > 0) {
                $issues[] = "Found {$duplicateUsers} duplicate username entries";
            }
            
            // Check table structure integrity
            $structureIssues = $this->checkTableStructure($connection);
            $issues = array_merge($issues, $structureIssues);
            
            $this->logSecurityEvent("Database integrity check completed. Issues found: " . count($issues));
            
            return [
                'status' => empty($issues) ? 'PASS' : 'FAIL',
                'issues' => $issues,
                'timestamp' => date('Y-m-d H:i:s')
            ];
            
        } catch (Exception $e) {
            $this->logSecurityEvent("Integrity check failed: " . $e->getMessage());
            return [
                'status' => 'ERROR',
                'issues' => [$e->getMessage()],
                'timestamp' => date('Y-m-d H:i:s')
            ];
        }
    }
    
    /**
     * Check for orphaned voter records
     */
    private function checkOrphanedVoters($connection) {
        $sql = "SELECT COUNT(*) as count FROM voters v 
                LEFT JOIN loginusers l ON v.username = l.username 
                WHERE l.username IS NULL";
        
        $result = $connection->query($sql);
        $row = $result->fetch_assoc();
        
        return $row['count'];
    }
    
    /**
     * Check vote count consistency
     */
    private function checkVoteConsistency($connection) {
        // Check if total votes match voter records
        $languageVotesSQL = "SELECT SUM(votecount) as total FROM languages";
        $teamVotesSQL = "SELECT SUM(votecount) as total FROM team_members";
        $voterCountSQL = "SELECT COUNT(*) as count FROM voters WHERE status = 'VOTED'";
        
        $langResult = $connection->query($languageVotesSQL);
        $teamResult = $connection->query($teamVotesSQL);
        $voterResult = $connection->query($voterCountSQL);
        
        $langTotal = $langResult->fetch_assoc()['total'];
        $teamTotal = $teamResult->fetch_assoc()['total'];
        $voterCount = $voterResult->fetch_assoc()['count'];
        
        // Allow for some flexibility as voters might vote for only one category
        return ($langTotal <= $voterCount && $teamTotal <= $voterCount);
    }
    
    /**
     * Check for duplicate users
     */
    private function checkDuplicateUsers($connection) {
        $sql = "SELECT username, COUNT(*) as count FROM loginusers 
                GROUP BY username HAVING count > 1";
        
        $result = $connection->query($sql);
        return $result->num_rows;
    }
    
    /**
     * Check table structure integrity
     */
    private function checkTableStructure($connection) {
        $issues = [];
        $requiredTables = ['loginusers', 'voters', 'languages', 'team_members'];
        
        foreach ($requiredTables as $table) {
            $sql = "SHOW TABLES LIKE '{$table}'";
            $result = $connection->query($sql);
            
            if ($result->num_rows === 0) {
                $issues[] = "Required table '{$table}' is missing";
            }
        }
        
        return $issues;
    }
    
    /**
     * Generate secure password
     */
    private function generateSecurePassword($length = 16) {
        $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*';
        $password = '';
        
        for ($i = 0; $i < $length; $i++) {
            $password .= $characters[random_int(0, strlen($characters) - 1)];
        }
        
        return $password;
    }
    
    /**
     * Store database credentials securely
     */
    private function storeCredentials($username, $password) {
        $credentialsFile = __DIR__ . '/../config/db_credentials.php';
        
        // Ensure config directory exists
        if (!file_exists(dirname($credentialsFile))) {
            mkdir(dirname($credentialsFile), 0755, true);
        }
        
        $credentials = [
            'username' => $username,
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'plain_password' => $password // Temporarily store for connection
        ];
        
        $content = "<?php\n// Database credentials - DO NOT COMMIT TO VERSION CONTROL\n";
        $content .= "return " . var_export($credentials, true) . ";\n";
        
        file_put_contents($credentialsFile, $content);
        chmod($credentialsFile, 0600); // Restrict file permissions
    }
    
    /**
     * Load database credentials
     */
    private function loadCredentials() {
        $credentialsFile = __DIR__ . '/../config/db_credentials.php';
        
        if (file_exists($credentialsFile)) {
            $credentials = include $credentialsFile;
            return [
                'username' => $credentials['username'],
                'password' => $credentials['plain_password']
            ];
        }
        
        return false;
    }
    
    /**
     * Find mysqldump executable path
     */
    private function findMysqldumpPath() {
        $possiblePaths = [
            'C:\\xampp\\mysql\\bin\\mysqldump.exe',
            'C:\\wamp\\bin\\mysql\\mysql8.0.31\\bin\\mysqldump.exe',
            'C:\\Program Files\\MySQL\\MySQL Server 8.0\\bin\\mysqldump.exe',
            'mysqldump' // System PATH
        ];
        
        foreach ($possiblePaths as $path) {
            if (file_exists($path) || $path === 'mysqldump') {
                return $path;
            }
        }
        
        throw new Exception("mysqldump executable not found");
    }
    
    /**
     * Encrypt backup file
     */
    private function encryptBackupFile($filePath) {
        $key = hash('sha256', 'voting_system_backup_key_' . date('Y-m-d'));
        $iv = openssl_random_pseudo_bytes(16);
        
        $data = file_get_contents($filePath);
        $encrypted = openssl_encrypt($data, 'AES-256-CBC', $key, 0, $iv);
        
        $encryptedFile = $filePath . '.encrypted';
        file_put_contents($encryptedFile, base64_encode($iv . $encrypted));
        
        // Remove original unencrypted file
        unlink($filePath);
        
        return $encryptedFile;
    }
    
    /**
     * Log security events
     */
    private function logSecurityEvent($message) {
        $timestamp = date('Y-m-d H:i:s');
        $logEntry = "[{$timestamp}] DATABASE_SECURITY: {$message}\n";
        file_put_contents($this->logFile, $logEntry, FILE_APPEND | LOCK_EX);
    }
    
    /**
     * Get database statistics for monitoring
     */
    public function getDatabaseStats() {
        try {
            $connection = $this->getSecureConnection();
            
            $stats = [];
            
            // Get table sizes
            $tables = ['loginusers', 'voters', 'languages', 'team_members'];
            foreach ($tables as $table) {
                $sql = "SELECT COUNT(*) as count FROM {$table}";
                $result = $connection->query($sql);
                $stats['table_counts'][$table] = $result->fetch_assoc()['count'];
            }
            
            // Get connection info
            $stats['connection_info'] = [
                'host' => $connection->host_info,
                'server_version' => $connection->server_info,
                'protocol_version' => $connection->protocol_version
            ];
            
            return $stats;
            
        } catch (Exception $e) {
            $this->logSecurityEvent("Failed to get database stats: " . $e->getMessage());
            return false;
        }
    }
}
?>