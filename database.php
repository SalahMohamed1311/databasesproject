<?php
// config/database.php

// Define absolute paths

// Database configuration
define('DB_HOST', 'sql3.freesqldatabase.com');
define('DB_USER', 'sql3812747');
define('DB_PASS', '6i4frVDN9T');
define('DB_NAME', 'sql3812747');

// Create connection
function getConnection() {
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    // Check connection
    if (!$conn) {
        die("Connection failed: " . mysqli_connect_error());
    }
    
    // Set charset to UTF-8
    mysqli_set_charset($conn, "utf8");
    
    return $conn;
}

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>