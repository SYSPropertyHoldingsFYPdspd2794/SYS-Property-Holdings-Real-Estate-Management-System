<?php
require_once '../includes/db_connect.php';

try {
    $conn->query("ALTER TABLE appointments ADD COLUMN terminal_date DATETIME DEFAULT NULL");
    echo "Added terminal_date to appointments.<br>";
} catch (Exception $e) { echo $e->getMessage() . "<br>"; }

try {
    $conn->query("ALTER TABLE appointments ADD COLUMN customer_deleted_at DATETIME DEFAULT NULL");
    echo "Added customer_deleted_at to appointments.<br>";
} catch (Exception $e) { echo $e->getMessage() . "<br>"; }

try {
    $conn->query("ALTER TABLE affordable_housing_applications ADD COLUMN terminal_date DATETIME DEFAULT NULL");
    echo "Added terminal_date to applications.<br>";
} catch (Exception $e) { echo $e->getMessage() . "<br>"; }
?>
