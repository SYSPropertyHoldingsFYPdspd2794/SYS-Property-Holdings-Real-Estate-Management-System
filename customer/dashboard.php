<?php

session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'CUSTOMER') {
    header("Location: ../login.php");
    exit();
}

include '../includes/db_connect.php';
include '../includes/header.php';

$account_id = $_SESSION['account_id'];

// Appointments
$stmt1 = $conn->prepare("SELECT COUNT(*) as appt_count FROM appointments WHERE customer_id = ?");
$stmt1->bind_param("i", $account_id);
$stmt1->execute();
$appt_count = $stmt1->get_result()->fetch_assoc()['appt_count'];

// Housing
$stmt2 = $conn->prepare("SELECT COUNT(*) as house_count FROM affordable_housing_applications WHERE customer_id = ?");
$stmt2->bind_param("i", $account_id);
$stmt2->execute();
$house_count = $stmt2->get_result()->fetch_assoc()['house_count'];

// Name
$stmt3 = $conn->prepare("SELECT full_name FROM customers WHERE customer_id = ?");
$stmt3->bind_param("i", $account_id);
$stmt3->execute();
$customer_name = $stmt3->get_result()->fetch_assoc()['full_name'];
?>
<div class="container my-5">
    <h2 class="fw-bold mb-4">Welcome back, <?php echo htmlspecialchars($customer_name);?></h2>
    <div class="row">
        <div class="col-md-6 mb-4">
            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    <h5 class="fw-bold"><i class="fas fa-calendar-check text-primary me-2"></i>Active Appointments</h5>
                    <h2 class="display-4 fw-bold text-dark mt-3"><?php echo $appt_count;?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-6 mb-4">
            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    <h5 class="fw-bold"><i class="fas fa-home text-success me-2"></i>Housing Applications</h5>
                    <h2 class="display-4 fw-bold text-dark mt-3"><?php echo $house_count;?></h2>
                </div>
            </div>
        </div>
    </div>
    <div class="d-flex gap-3 mt-3">
        <a href="properties.php" class="btn btn-primary btn-lg px-4 fw-bold shadow-sm">Browse Properties</a>
        <a href="track_status.php" class="btn btn-outline-dark btn-lg px-4 fw-bold shadow-sm">My Applications</a>
    </div>
</div>
<?php include '../includes/footer.php';?>

