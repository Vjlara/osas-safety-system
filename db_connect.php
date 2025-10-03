<?php
// ====== DATABASE CONNECTION ====== //
$servername = "127.0.0.1";       // Host
$username   = "root";            // MySQL username
$password   = "rattlerad123";    // MySQL password
$dbname     = "sms";             // Database name
$port       = 3307;              // MySQL port

// Enable MySQLi error reporting (optional, for debugging)
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    // Create connection
    $conn = new mysqli($servername, $username, $password, $dbname, $port);
    
    // Set character set
    $conn->set_charset('utf8mb4');

    // Optional: echo "Connected successfully!";
} catch (mysqli_sql_exception $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>
