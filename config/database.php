<?php
/**
 * Database Configuration & Connection
 * 
 * Configured for both local and remote MySQL servers.
 * Settings can be adjusted here or via Environment Variables / .env
 */

define('DB_HOST', getenv('DB_HOST') ?: '127.0.0.1'); // Remote server IP or hostname (e.g., '192.168.1.100' or 'db.yourdomain.com')
define('DB_PORT', getenv('DB_PORT') ?: '3306');
define('DB_NAME', getenv('DB_NAME') ?: 'project_tracker');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');
define('DB_CHARSET', 'utf8mb4');

function getDBConnection() {
    static $pdo = null;
    
    if ($pdo === null) {
        $dsn = sprintf(
            "mysql:host=%s;port=%s;dbname=%s;charset=%s",
            DB_HOST,
            DB_PORT,
            DB_NAME,
            DB_CHARSET
        );
        
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
            PDO::ATTR_TIMEOUT            => 5, // 5 seconds timeout for remote database connections
        ];
        
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            // Return null so the UI can gracefully render a Database Setup Warning message
            return null;
        }
    }
    
    return $pdo;
}
