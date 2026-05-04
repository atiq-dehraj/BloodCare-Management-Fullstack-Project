<?php
// Enable strict error reporting for MySQLi to help us debug
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$host = "localhost";
$username = "root";      // Default XAMPP username
$password = "";          // Default XAMPP password is empty
$database = "bloodcare_db"; // Your database name

try {
    // Attempt the connection
    $conn = new mysqli($host, $username, $password, $database);
    $conn->set_charset("utf8mb4");
    
    // Uncomment the line below ONLY for testing, then delete it!
    // echo "<h2>Success! The bridge is working.</h2>";

} catch (mysqli_sql_exception $e) {
    // If it fails, print the exact error to the screen
    die("<h2>Database Connection Failed:</h2><p>" . $e->getMessage() . "</p>");
}
?>