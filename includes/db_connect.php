<?php
// Safety Lock 1: Enable strict MySQLi error reporting
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    // Environment Check: Local XAMPP
    if ($_SERVER['SERVER_NAME'] == 'localhost' || $_SERVER['SERVER_NAME'] == '127.0.0.1') {
        $host = "localhost";
        $user = "root";
        $password = "";
        $dbname = "sys_property_db";
    } 
    // Environment Check: Live Server (InfinityFree)
    else {
        $host = "sql102.infinityfree.com"; // 【CRITICAL】Replace this with your InfinityFree MySQL Hostname
        $user = "if0_41857411";
        $password = "SYSProperty2026";
        $dbname = "if0_41857411_sys_property_db";
    }

    // Establish Connection
    $conn = new mysqli($host, $user, $password, $dbname);
    
    // Safety Lock 2: Enforce character set to prevent encoding issues
    $conn->set_charset("utf8mb4");

} catch (mysqli_sql_exception $e) {
    // Safety Lock 3: Graceful error handling
    if ($_SERVER['SERVER_NAME'] == 'localhost' || $_SERVER['SERVER_NAME'] == '127.0.0.1') {
        // Detailed error for local debugging
        die("🚨 Database Connection Failed (Local Only): " . $e->getMessage());
    } else {
        // Safe, generic error for production
        die("🚧 SYS Property Holdings System is currently under maintenance. Unable to connect to the data center. Please try again later.");
    }
}
?>