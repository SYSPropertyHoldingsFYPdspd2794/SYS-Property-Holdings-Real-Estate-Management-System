<?php
/**
 * PROJECT: SYS Property Holdings
 * FILE: customer/track_status.php
 * DESCRIPTION: US29 - Track application and appointment statuses.
 */

session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'CUSTOMER') {
    header("Location: ../login.php");
    exit();
}
include '../includes/db_connect.php';

$account_id = $_SESSION['account_id'];
$success_msg = isset($_GET['success']) ? trim($_GET['success']) : '';

// Fetch Appointments
$appt_stmt = $conn->prepare("SELECT a.*, p.project_name, p.state FROM appointments a JOIN properties p ON a.property_id = p.property_id WHERE a.customer_id = ? ORDER BY a.appointment_date DESC");
$appt_stmt->bind_param("i", $account_id);
$appt_stmt->execute();
$appointments = $appt_stmt->get_result();

// Fetch Affordable Housing Applications
$app_stmt = $conn->prepare("SELECT ah.*, p.project_name, p.state FROM affordable_housing_applications ah JOIN properties p ON ah.property_id = p.property_id WHERE ah.customer_id = ? ORDER BY ah.application_date DESC");
$app_stmt->bind_param("i", $account_id);
$app_stmt->execute();
$applications = $app_stmt->get_result();

include '../includes/header.php';
?>

<div class="container my-5">
    <div class="mb-5 border-bottom pb-4">
        <h2 class="fw-bold mb-2"><i class="fas fa-route text-primary me-3"></i>Universal Status Tracker</h2>
        <p class="text-muted fs-5">Monitor the real-time progression of your submissions and appointments.</p>
    </div>

    <?php if ($success_msg === 'appointment_booked'): ?>
        <div class="alert alert-success shadow-sm border-0 mb-4 rounded-3"><i class="fas fa-check-circle me-2"></i>Your showroom appointment has been successfully scheduled!</div>
    <?php elseif ($success_msg === 'application_submitted'): ?>
        <div class="alert alert-success shadow-sm border-0 mb-4 rounded-3"><i class="fas fa-check-circle me-2"></i>Your affordable housing application was securely submitted and is awaiting review.</div>
    <?php endif; ?>

    <ul class="nav nav-pills mb-4 fs-5 fw-bold" id="statusTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active px-4 rounded-pill shadow-sm me-2" id="appts-tab" data-bs-toggle="pill" data-bs-target="#appts" type="button" role="tab"><i class="fas fa-calendar-alt me-2"></i>Appointments</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link px-4 rounded-pill shadow-sm" id="housing-tab" data-bs-toggle="pill" data-bs-target="#housing" type="button" role="tab"><i class="fas fa-home me-2"></i>Housing Ballots</button>
        </li>
    </ul>

    <div class="tab-content" id="statusTabsContent">
        
        <div class="tab-pane fade show active" id="appts" role="tabpanel">
            <div class="row">
                <?php if ($appointments->num_rows > 0): ?>
                    <?php while ($row = $appointments->fetch_assoc()): 
                        $badgeClass = 'bg-secondary';
                        if ($row['status'] === 'PENDING') $badgeClass = 'bg-warning text-dark';
                        if ($row['status'] === 'ASSIGNED') $badgeClass = 'bg-primary';
                        if ($row['status'] === 'COMPLETED') $badgeClass = 'bg-success';
                        if ($row['status'] === 'CANCELLED') $badgeClass = 'bg-danger';
                    ?>
                        <div class="col-md-6 mb-4">
                            <div class="card h-100 border-0 shadow-sm rounded-4 border-start border-4 border-primary">
                                <div class="card-body p-4">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <h5 class="fw-bold m-0 text-dark"><?php echo htmlspecialchars($row['project_name']); ?></h5>
                                        <span class="badge <?php echo $badgeClass; ?> px-3 py-2"><?php echo htmlspecialchars($row['status']); ?></span>
                                    </div>
                                    <p class="text-muted mb-2"><i class="fas fa-map-marker-alt text-danger me-2"></i><?php echo htmlspecialchars($row['state']); ?></p>
                                    <p class="text-muted mb-2"><i class="fas fa-cogs text-secondary me-2"></i><?php echo htmlspecialchars(str_replace('_', ' ', $row['service_type'])); ?></p>
                                    
                                    <div class="bg-light p-3 rounded-3 mt-3">
                                        <p class="text-dark fw-bold m-0"><i class="far fa-clock text-primary me-2"></i>Scheduled for: <?php echo htmlspecialchars(date('d M Y, h:i A', strtotime($row['appointment_date'] . ' ' . $row['appointment_time']))); ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="col-12 text-center py-5">
                        <i class="fas fa-calendar-times display-1 text-muted opacity-50 mb-3"></i>
                        <h4 class="text-muted">No appointments scheduled.</h4>
                        <a href="properties.php" class="btn btn-outline-primary mt-3 px-4 rounded-pill">Explore Showrooms</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="tab-pane fade" id="housing" role="tabpanel">
            <div class="row">
                <?php if ($applications->num_rows > 0): ?>
                    <?php while ($row = $applications->fetch_assoc()): 
                        $badgeClass = 'bg-secondary';
                        if ($row['status'] === 'PENDING_REVIEW') $badgeClass = 'bg-warning text-dark';
                        if ($row['status'] === 'APPROVED_FOR_DRAW') $badgeClass = 'bg-info text-dark';
                        if ($row['status'] === 'WINNER') $badgeClass = 'bg-success';
                        if ($row['status'] === 'REJECTED') $badgeClass = 'bg-danger';
                    ?>
                        <div class="col-md-6 mb-4">
                            <div class="card h-100 border-0 shadow-sm rounded-4 border-start border-4 border-success">
                                <div class="card-body p-4">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <h5 class="fw-bold m-0 text-dark"><?php echo htmlspecialchars($row['project_name']); ?></h5>
                                        <span class="badge <?php echo $badgeClass; ?> px-3 py-2"><?php echo htmlspecialchars(str_replace('_', ' ', $row['status'])); ?></span>
                                    </div>
                                    
                                    <p class="text-muted fs-6 mb-3"><i class="far fa-clock text-primary me-2"></i>Submitted on: <?php echo htmlspecialchars(date('d M Y, h:i A', strtotime($row['application_date']))); ?></p>
                                    
                                    <?php if ($row['status'] === 'WINNER'): ?>
                                        <div class="mt-4 p-3 bg-success bg-opacity-10 rounded-3 border border-success border-opacity-50">
                                            <p class="m-0 fw-bold text-success"><i class="fas fa-trophy me-2 fa-lg"></i>Congratulations! You have been selected in the draw. Our staff will contact you shortly.</p>
                                        </div>
                                    <?php elseif ($row['status'] === 'REJECTED'): ?>
                                        <div class="mt-4 p-3 bg-danger bg-opacity-10 rounded-3 border border-danger border-opacity-50">
                                            <p class="m-0 fw-bold text-danger"><i class="fas fa-times-circle me-2"></i>Application did not meet the regulatory criteria.</p>
                                        </div>
                                    <?php else: ?>
                                        <div class="mt-4 p-3 bg-light rounded-3 border">
                                            <p class="m-0 text-muted small"><i class="fas fa-info-circle me-2"></i>Your application is currently in processing. Verification may take up to 3 working days.</p>
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
                        <a href="properties.php?filter_type=AFFORDABLE" class="btn btn-outline-success mt-3 px-4 rounded-pill">View Government Housing</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>

    </div>
</div>

<?php include '../includes/footer.php'; ?>