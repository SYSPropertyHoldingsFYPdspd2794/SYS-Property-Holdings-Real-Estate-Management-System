<?php
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'CUSTOMER') {
    header("Location: ../login.php");
    exit();
}

require_once '../includes/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: book_appointment.php");
    exit();
}

$customer_id = (int)($_SESSION['account_id'] ?? 0);
$property_id = (int)($_POST['property_id'] ?? 0);
$service_type = trim($_POST['service_type'] ?? '');
$appointment_date = trim($_POST['appointment_date'] ?? '');
$appointment_time = trim($_POST['appointment_time'] ?? '');
$appointment_time_db = strlen($appointment_time) === 5 ? $appointment_time . ':00' : $appointment_time;

if ($customer_id <= 0 || $property_id <= 0 || $service_type === '' || $appointment_date === '' || $appointment_time === '') {
    header("Location: book_appointment.php?error=missing_fields");
    exit();
}

$appointment_dt = DateTime::createFromFormat('Y-m-d H:i:s', $appointment_date . ' ' . $appointment_time_db);
$date_errors = DateTime::getLastErrors();
$valid_datetime = $appointment_dt && ($date_errors === false || ($date_errors['warning_count'] === 0 && $date_errors['error_count'] === 0));

if (!$valid_datetime || $appointment_dt < new DateTime() || $appointment_time_db < '08:00:00' || $appointment_time_db > '20:00:00') {
    header("Location: book_appointment.php?id=" . $property_id . "&error=invalid_slot");
    exit();
}

$stmt = $conn->prepare("INSERT INTO appointments (customer_id, property_id, service_type, appointment_date, appointment_time, status) VALUES (?, ?, ?, ?, ?, 'REQUESTED')");
$stmt->bind_param("iisss", $customer_id, $property_id, $service_type, $appointment_date, $appointment_time_db);

if ($stmt->execute()) {
    header("Location: track_status.php?success=appointment_booked");
    exit();
}

header("Location: book_appointment.php?id=" . $property_id . "&error=booking_failed");
exit();
?>
