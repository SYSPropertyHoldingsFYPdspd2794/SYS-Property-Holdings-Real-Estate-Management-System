<?php
/**
 * PROJECT: SYS Property Holdings
 * FILE: customer/dashboard.php
 * DESCRIPTION: US25 - Customer Dashboard overview.
 */

session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'CUSTOMER') {
    header("Location: ../login.php");
    exit();
}

include '../includes/db_connect.php';
include '../includes/header.php';

$account_id = $_SESSION['account_id'];

// Fetch Appointments Count
$stmt1 = $conn->prepare("SELECT COUNT(*) as appt_count FROM appointments WHERE customer_id = ?");
$stmt1->bind_param("i", $account_id);
$stmt1->execute();
$appt_count = $stmt1->get_result()->fetch_assoc()['appt_count'];

// Fetch Housing Applications Count
$stmt2 = $conn->prepare("SELECT COUNT(*) as house_count FROM affordable_housing_applications WHERE customer_id = ?");
$stmt2->bind_param("i", $account_id);
$stmt2->execute();
$house_count = $stmt2->get_result()->fetch_assoc()['house_count'];

// Fetch Customer Name
$stmt3 = $conn->prepare("SELECT full_name FROM customers WHERE customer_id = ?");
$stmt3->bind_param("i", $account_id);
$stmt3->execute();
$customer_res = $stmt3->get_result()->fetch_assoc();
$customer_name = $customer_res ? $customer_res['full_name'] : 'Valued Customer';
?>

<div class="container my-5">
    <div class="d-flex justify-content-between align-items-center mb-5">
        <div>
            <h2 class="fw-bold mb-1">Welcome back, <?php echo htmlspecialchars($customer_name); ?>!</h2>
            <p class="text-muted">Here is the overview of your recent property activities.</p>
        </div>
        <a href="properties.php" class="btn btn-primary btn-lg shadow-sm rounded-pill px-4">
            <i class="fas fa-search me-2"></i>Browse Properties
        </a>
    </div>

    <div class="row g-4 mb-5">
        <div class="col-md-6">
            <div class="card shadow-sm border-0 rounded-4 h-100 bg-light bg-gradient">
                <div class="card-body p-5 text-center">
                    <div class="d-inline-block bg-primary bg-opacity-10 p-3 rounded-circle mb-3">
                        <i class="fas fa-calendar-check fa-3x text-primary"></i>
                    </div>
                    <h5 class="fw-bold text-dark">Active Appointments</h5>
                    <h1 class="display-3 fw-bold text-dark mt-2"><?php echo $appt_count; ?></h1>
                    <p class="text-muted mt-3">Scheduled showroom viewings and consultations.</p>
                    <a href="track_status.php" class="btn btn-outline-primary mt-2 rounded-pill px-4">View Schedule</a>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card shadow-sm border-0 rounded-4 h-100 bg-light bg-gradient">
                <div class="card-body p-5 text-center">
                    <div class="d-inline-block bg-success bg-opacity-10 p-3 rounded-circle mb-3">
                        <i class="fas fa-home fa-3x text-success"></i>
                    </div>
                    <h5 class="fw-bold text-dark">Housing Applications</h5>
                    <h1 class="display-3 fw-bold text-dark mt-2"><?php echo $house_count; ?></h1>
                    <p class="text-muted mt-3">Government affordable housing digital ballots.</p>
                    <a href="track_status.php" class="btn btn-outline-success mt-2 rounded-pill px-4">Track Status</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>