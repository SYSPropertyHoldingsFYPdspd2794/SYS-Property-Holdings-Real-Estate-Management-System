<?php
require '../includes/db_connect.php';
$sql = "ALTER TABLE affordable_housing_applications ADD COLUMN notification_count INT DEFAULT 0";
if ($conn->query($sql)) {
    echo "Success";
} else {
    echo "Failed: " . $conn->error;
}
?>
