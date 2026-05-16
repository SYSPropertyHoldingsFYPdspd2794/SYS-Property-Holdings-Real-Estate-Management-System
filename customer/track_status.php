<?php
/**
 * PROJECT: SYS Property Holdings
 * FILE: customer/track_status.php
 * DESCRIPTION: US29 - Track application and appointment statuses. Fixed plus button routing to properties catalog.
 */

session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'CUSTOMER') {
    header("Location: ../login.php");
    exit();
}
include '../includes/db_connect.php';

$account_id = $_SESSION['account_id'];
$success_msg = isset($_GET['success']) ? trim($_GET['success']) : '';

$appt_stmt = $conn->prepare("SELECT a.*, p.project_name, p.state FROM appointments a JOIN properties p ON a.property_id = p.property_id WHERE a.customer_id = ? ORDER BY a.appointment_date DESC");
$appt_stmt->bind_param("i", $account_id);
$appt_stmt->execute();
$appointments = $appt_stmt->get_result();

$app_stmt = $conn->prepare("SELECT ah.*, p.project_name, p.state FROM affordable_housing_applications ah JOIN properties p ON ah.property_id = p.property_id WHERE ah.customer_id = ? ORDER BY ah.application_date DESC");
$app_stmt->bind_param("i", $account_id);
$app_stmt->execute();
$applications = $app_stmt->get_result();

include '../includes/header.php';
?>
<div class="container my-5">
    <h2 class="fw-bold mb-4"><i class="fas fa-route text-primary me-2"></i>Universal Status Tracker</h2>
    
    <?php if ($success_msg === 'appointment_booked'): ?>
        <div class="alert alert-success shadow-sm border-0 mb-4 rounded-3 fw-bold"><i class="fas fa-check-circle me-2"></i>Your showroom appointment request has been successfully submitted!</div>
    <?php elseif ($success_msg === 'application_submitted'): ?>
        <div class="alert alert-success shadow-sm border-0 mb-4 rounded-3 fw-bold"><i class="fas fa-check-circle me-2"></i>Your affordable housing application and documents were securely submitted.</div>
    <?php endif; ?>

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-5">
        <ul class="nav nav-pills" id="trackerTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active px-4 py-3 fw-bold fs-5 shadow-sm me-3 rounded-pill" id="appt-tab" data-bs-toggle="pill" data-bs-target="#appt" type="button" role="tab"><i class="far fa-calendar-alt me-2"></i>Showroom Appointments</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link px-4 py-3 fw-bold fs-5 shadow-sm rounded-pill" id="housing-tab" data-bs-toggle="pill" data-bs-target="#housing" type="button" role="tab"><i class="fas fa-home me-2"></i>Housing Applications</button>
            </li>
        </ul>
        <a href="properties.php" class="btn btn-outline-primary btn-lg rounded-circle shadow-sm" title="Browse Properties Catalog">
            <i class="fas fa-plus"></i>
        </a>
    </div>

    <div class="tab-content" id="trackerTabsContent">
        <div class="tab-pane fade show active" id="appt" role="tabpanel">
            <div class="row">
                <?php if ($appointments->num_rows > 0): ?>
                    <?php while ($row = $appointments->fetch_assoc()): ?>
                        <div class="col-md-6 mb-4">
                            <div class="card shadow-sm border-0 h-100 rounded-4">
                                <div class="card-body p-4">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <h4 class="fw-bold m-0"><?php echo htmlspecialchars($row['project_name']); ?></h4>
                                        <?php
                                            $bg = 'secondary';
                                            if ($row['status'] === 'REQUESTED' || $row['status'] === 'PENDING') $bg = 'warning text-dark';
                                            if ($row['status'] === 'ASSIGNED') $bg = 'primary';
                                            if ($row['status'] === 'COMPLETED') $bg = 'success';
                                            if ($row['status'] === 'CANCELLED' || $row['status'] === 'NO_SHOW') $bg = 'danger';
                                        ?>
                                        <span class="badge bg-<?php echo $bg; ?> fs-6 px-3 py-2 shadow-sm"><?php echo htmlspecialchars($row['status']); ?></span>
                                    </div>
                                    <p class="text-muted fs-5 mb-2"><i class="fas fa-clipboard-list text-primary me-2"></i><?php echo str_replace('_', ' ', htmlspecialchars($row['service_type'])); ?></p>
                                    <p class="text-muted fs-5 m-0"><i class="far fa-calendar-alt text-danger me-2"></i><?php echo htmlspecialchars(date('d M Y', strtotime($row['appointment_date'])) . ' at ' . date('h:i A', strtotime($row['appointment_time']))); ?></p>
                                    
                                    <?php if (!empty($row['staff_remarks'])): ?>
                                        <div class="mt-4 p-3 bg-light rounded-3 border-start border-warning border-4">
                                            <p class="m-0 small fw-bold text-dark"><i class="fas fa-comment-dots me-2"></i>Staff Remarks:</p>
                                            <p class="m-0 text-muted"><?php echo htmlspecialchars($row['staff_remarks']); ?></p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="col-12 text-center py-5">
                        <i class="far fa-calendar-times display-1 text-muted opacity-50 mb-3"></i>
                        <h4 class="text-muted">No appointments found.</h4>
                        <a href="properties.php" class="btn btn-dark mt-3 px-4 rounded-pill">Browse Properties</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="tab-pane fade" id="housing" role="tabpanel">
            <div class="row">
                <?php if ($applications->num_rows > 0): ?>
                    <?php while ($row = $applications->fetch_assoc()): ?>
                        <div class="col-md-6 mb-4">
                            <div class="card shadow-sm border-0 border-start border-success border-5 h-100 rounded-4">
                                <div class="card-body p-4">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <h4 class="fw-bold m-0"><?php echo htmlspecialchars($row['project_name']); ?></h4>
                                        <?php
                                            $bg = 'secondary';
                                            if ($row['status'] === 'PENDING_REVIEW') $bg = 'warning text-dark';
                                            if ($row['status'] === 'APPROVED_FOR_DRAW') $bg = 'info text-dark';
                                            if ($row['status'] === 'WINNER') $bg = 'success';
                                            if ($row['status'] === 'REJECTED') $bg = 'danger';
                                        ?>
                                        <span class="badge bg-<?php echo $bg; ?> fs-6 px-3 py-2 shadow-sm"><?php echo htmlspecialchars(str_replace('_', ' ', $row['status'])); ?></span>
                                    </div>
                                    <p class="text-muted fs-5 m-0"><i class="far fa-clock text-primary me-2"></i>Applied on: <?php echo htmlspecialchars(date('d M Y, h:i A', strtotime($row['application_date']))); ?></p>
                                    
                                    <?php if ($row['status'] === 'WINNER'): ?>
                                        <div class="mt-4 p-3 bg-success bg-opacity-10 rounded-3 border border-success border-opacity-50">
                                            <p class="m-0 fw-bold text-success"><i class="fas fa-trophy me-2 fa-lg"></i>Congratulations! You have been selected in the draw. Please await offline contract instructions.</p>
                                        </div>
                                    <?php elseif ($row['status'] === 'REJECTED'): ?>
                                        <div class="mt-4 p-3 bg-danger bg-opacity-10 rounded-3 border border-danger border-opacity-50">
                                            <p class="m-0 fw-bold text-danger"><i class="fas fa-times-circle me-2"></i>Application did not meet the regulatory criteria.</p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="col-12 text-center py-5">
                        <i class="fas fa-file-invoice display-1 text-muted opacity-50 mb-3"></i>
                        <h4 class="text-muted">No affordable housing applications found.</h4>
                        <a href="properties.php?filter_type=AFFORDABLE" class="btn btn-success mt-3 px-4 rounded-pill">View Government Housing</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php include '../includes/footer.php'; ?>