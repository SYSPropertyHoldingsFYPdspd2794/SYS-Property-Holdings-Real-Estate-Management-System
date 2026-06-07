<?php
require_once '../includes/db_connect.php';
require_once '../includes/auth_check.php';

if (!isset($_SESSION['account_id'])) {
    echo json_encode(['success' => false, 'error' => 'Not authenticated']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['application_id'])) {
    $app_id = (int)$_POST['application_id'];
    
    $stmt = $conn->prepare("UPDATE affordable_housing_applications SET notification_count = notification_count + 1 WHERE application_id = ?");
    $stmt->bind_param("i", $app_id);
    
    if ($stmt->execute()) {
        $res = $conn->query("SELECT notification_count FROM affordable_housing_applications WHERE application_id = $app_id");
        $row = $res->fetch_assoc();
        echo json_encode(['success' => true, 'new_count' => $row['notification_count']]);
    } else {
        echo json_encode(['success' => false, 'error' => $conn->error]);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request']);
}
?>
