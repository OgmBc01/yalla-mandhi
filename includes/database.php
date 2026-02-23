<?php
// Set default timezone to UAE
date_default_timezone_set('Asia/Dubai');
define('DB_HOST', 'mysql');
define('DB_USER', 'root');
define('DB_PASS', 'rootpassword'); 
define('DB_NAME', 'yallaalmandhi');

function getDBConnection() {
    static $conn = null;
    
    if ($conn === null) {
        try {
            $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
            
            if ($conn->connect_error) {
                throw new Exception("Connection failed: " . $conn->connect_error);
            }
            
            $conn->set_charset("utf8mb4");
            
            // Set charset to utf8mb4
            $conn->set_charset("utf8mb4");
            // Set MySQL timezone to UAE
            $conn->query("SET time_zone = '+04:00'");
        } catch (Exception $e) {
            error_log("Database connection error: " . $e->getMessage());
            return null;
        }
    }
    
    return $conn;
}