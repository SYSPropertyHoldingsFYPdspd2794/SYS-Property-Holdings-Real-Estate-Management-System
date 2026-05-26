<?php
$conn = new mysqli('127.0.0.1', 'root', '', 'sys_property_db');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$conn->query("ALTER TABLE appointments ADD COLUMN status_updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP");
echo "Appointments altered: " . $conn->error . "\n";

$conn->query("ALTER TABLE affordable_housing_applications ADD COLUMN status_updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP");
echo "Applications altered: " . $conn->error . "\n";
?>