<?php
// test_db.php
echo "<h2>Testing Database Connection</h2>";

// Try to connect to MySQL
$host = 'localhost';
$user = 'root';
$pass = '';

$conn = @mysqli_connect($host, $user, $pass);

if ($conn) {
    echo "<p style='color:green'>✓ MySQL Connection Successful!</p>";
    
    // Try to select database
    $db_selected = mysqli_select_db($conn, 'car_rental_db');
    
    if ($db_selected) {
        echo "<p style='color:green'>✓ Database 'car_rental_db' Selected!</p>";
        
        // Check tables
        $result = mysqli_query($conn, "SHOW TABLES");
        $tables = [];
        while ($row = mysqli_fetch_array($result)) {
            $tables[] = $row[0];
        }
        
        echo "<p>Tables found: " . implode(', ', $tables) . "</p>";
    } else {
        echo "<p style='color:orange'>⚠ Database 'car_rental_db' doesn't exist. You need to create it first.</p>";
        echo "<p><a href='http://localhost/phpmyadmin'>Go to phpMyAdmin</a></p>";
    }
    
    mysqli_close($conn);
} else {
    echo "<p style='color:red'>✗ MySQL Connection Failed: " . mysqli_connect_error() . "</p>";
    echo "<p>Make sure MySQL is running in XAMPP Control Panel</p>";
}

echo "<hr>";
echo "<h3>PHP Information:</h3>";
echo "<p>PHP Version: " . phpversion() . "</p>";
echo "<p>Session Status: " . session_status() . "</p>";
?>