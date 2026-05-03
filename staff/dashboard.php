<?php
session_start();
if (!isset($_SESSION['role']) |

| $_SESSION['role']!== 'STAFF') {
    header("Location:../login.php");
    exit();
}
include '../includes/db_connect.php';

$account_id = $_SESSION['account_id'];

$stmt_staff = $conn->prepare("SELECT staff_id, assigned_state FROM staff WHERE staff_id =?");
$stmt_staff->bind_param("i", $account_id);
$stmt_staff->execute();
$staff_data = $stmt_staff->get_result()->fetch_assoc();
$staff_id = $staff_data['staff_id'];
$assigned_state = $staff_data['assigned_state'];

$stmt_appt = $conn->prepare("SELECT COUNT(*) as pending_appt FROM appointments WHERE assigned_staff_id =? AND status = 'ASSIGNED'");
$stmt_appt->bind_param("i", $staff_id);
$stmt_appt->execute();
$pending_appt = $stmt_appt->get_result()->fetch_assoc()['pending_appt'];

$stmt_app = $conn->prepare("SELECT COUNT(*) as pending_app FROM affordable_housing_applications a JOIN properties p ON a.property_id = p.property_id WHERE p.state =? AND a.status = 'PENDING_REVIEW'");
$stmt_app->bind_param("s", $assigned_state);
$stmt_app->execute();
$pending_app = $stmt_app->get_result()->fetch_assoc()['pending_app'];

include '../includes/header.php';
?>
<div class="container my-5">
    <h2 class="fw-bold mb-4">Staff Dashboard</h2>
    <div class="alert alert-info fw-bold mb-5">
        <i class="fas fa-map-marker-alt me-2"></i>Assigned Region: <?php echo htmlspecialchars($assigned_state);?>
    </div>
    <div class="row">
        <div class="col-md-6 mb-4">
            <div class="card shadow-sm border-0 border-start border-primary border-5">
                <div class="card-body p-4">
                    <h5 class="fw-bold"><i class="fas fa-calendar-alt text-primary me-2"></i>My Pending Appointments</h5>
                    <h1 class="display-4 fw-bold text-dark mt-3"><?php echo $pending_appt;?></h1>
                    <a href="appointments.php" class="btn btn-primary mt-3 fw-bold">Manage Appointments</a>
                </div>
            </div>
        </div>
        <div class="col-md-6 mb-4">
            <div class="card shadow-sm border-0 border-start border-success border-5">
                <div class="card-body p-4">
                    <h5 class="fw-bold"><i class="fas fa-file-signature text-success me-2"></i>Regional Housing Applications</h5>
                    <h1 class="display-4 fw-bold text-dark mt-3"><?php echo $pending_app;?></h1>
                    <a href="verifications.php" class="btn btn-success mt-3 fw-bold">Review Applications</a>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include '../includes/footer.php';?>

