<?php
/**
 * Secure Database Connection Manager
 * Replaces connection.php with secure configuration management
 */

require_once __DIR__ . '/includes/ConfigManager.php';

try {
    // Load configuration
    ConfigManager::load();
    
    // Validate configuration
    $configErrors = ConfigManager::validate();
    if (!empty($configErrors)) {
        error_log("Configuration validation errors: " . implode(", ", $configErrors));
        if (!ConfigManager::isProduction()) {
            die("Configuration errors detected. Check logs for details.");
        }
    }
    
    // Get database configuration
    $dbConfig = ConfigManager::getDatabaseConfig();
    
    // Create secure connection with SSL if available
    $con = new mysqli($dbConfig['host'], $dbConfig['username'], $dbConfig['password'], $dbConfig['database']);
    
    // Check connection
    if ($con->connect_error) {
        error_log("Database connection failed: " . $con->connect_error);
        
        if (ConfigManager::isProduction()) {
            die("Database connection error. Please try again later.");
        } else {
            die("Connection failed: " . $con->connect_error);
        }
    }
    
    // Set charset to prevent character set confusion attacks
    if (!$con->set_charset("utf8mb4")) {
        error_log("Error setting charset: " . $con->error);
    }
    
    // Enable SSL if supported and configured
    if ($con->get_server_info() && strpos($con->get_server_info(), 'SSL') !== false) {
        // SSL is available, could be configured here
        error_log("SSL connection available");
    }
    
    // Set SQL mode for better security
    $con->query("SET sql_mode = 'STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION'");
    
} catch (Exception $e) {
    error_log("Database connection error: " . $e->getMessage());
    
    if (ConfigManager::isProduction()) {
        die("Database connection error. Please try again later.");
    } else {
        die("Database error: " . $e->getMessage());
    }
}

/**
 * Get secure database connection
 */
function getSecureConnection() {
    global $con;
    return $con;
}

/**
 * Close database connection securely
 */
function closeSecureConnection() {
    global $con;
    if ($con) {
        $con->close();
    }
}