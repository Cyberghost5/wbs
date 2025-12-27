<?php
/**
 * Database Configuration
 */

define('DB_HOST', 'localhost');
define('DB_USER', 'yexnecom_wbssummit');           // Default XAMPP user
define('DB_PASS', 'yexnecom_wbssummit');               // Default XAMPP password (empty)
define('DB_NAME', 'yexnecom_wbssummit');     // Database name

/**
 * Get database connection
 */
function getDBConnection() {
    $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    if ($mysqli->connect_error) {
        error_log("Database connection failed: " . $mysqli->connect_error);
        return null;
    }
    
    $mysqli->set_charset("utf8mb4");
    return $mysqli;
}
