<?php
// Enable strict MySQLi error reporting.
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $server_name = $_SERVER['SERVER_NAME'] ?? 'localhost';

    if ($server_name === 'localhost' || $server_name === '127.0.0.1') {
        $host = "localhost";
        $user = "root";
        $password = "";
        $dbname = "sys_property_db";
    } else {
        $host = "sql102.infinityfree.com";
        $user = "if0_41857411";
        $password = "SYSProperty2026";
        $dbname = "if0_41857411_sys_property_db";
    }

    $conn = new mysqli($host, $user, $password, $dbname);
    $conn->set_charset("utf8mb4");
} catch (mysqli_sql_exception $e) {
    if (($server_name ?? 'localhost') === 'localhost' || ($server_name ?? 'localhost') === '127.0.0.1') {
        die("Database Connection Failed (Local Only): " . $e->getMessage());
    }

    die("SYS Property Holdings System is currently under maintenance. Unable to connect to the data center. Please try again later.");
}
?>
