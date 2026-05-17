<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role']!== 'CUSTOMER') {
    header("Location:../login.php");
    exit();
}
include '../includes/db_connect.php';

$account_id = $_SESSION['account_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'cancel_appointment') {
    $appointment_id = (int)($_POST['appointment_id'] ?? 0);

    $cancel_stmt = $conn->prepare("UPDATE appointments SET status = 'CANCELLED' WHERE appointment_id = ? AND customer_id = ? AND status IN ('REQUESTED', 'ASSIGNED') AND TIMESTAMP(appointment_date, appointment_time) > NOW()");
    $cancel_stmt->bind_param("ii", $appointment_id, $account_id);
    $cancel_stmt->execute();

    header("Location: track_status.php?msg=" . ($cancel_stmt->affected_rows > 0 ? "cancelled" : "cancel_failed"));
    exit();
}

$appt_stmt = $conn->prepare("SELECT a.*, p.project_name, p.state FROM appointments a JOIN properties p ON a.property_id = p.property_id WHERE a.customer_id =? ORDER BY a.appointment_date DESC");
$appt_stmt->bind_param("i", $account_id);
$appt_stmt->execute();
$appointments = $appt_stmt->get_result();

$app_stmt = $conn->prepare("SELECT ah.*, p.project_name, p.state FROM affordable_housing_applications ah JOIN properties p ON ah.property_id = p.property_id WHERE ah.customer_id =? ORDER BY ah.application_date DESC");
$app_stmt->bind_param("i", $account_id);
$app_stmt->execute();
$applications = $app_stmt->get_result();

include '../includes/header.php';
?>
<div class="container my-5">
    <h2 class="fw-bold mb-5"><i class="fas fa-route text-primary me-2"></i>Universal Status Tracker</h2>

    <?php if (isset($_GET['msg']) && $_GET['msg'] === 'cancelled'): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            Appointment cancelled successfully.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php elseif (isset($_GET['msg']) && $_GET['msg'] === 'cancel_failed'): ?>
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            This appointment cannot be cancelled because the appointment time has passed or the status has already changed.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-5">
        <ul class="nav nav-pills" id="trackerTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active px-4 py-3 fw-bold fs-5 shadow-sm me-3" id="appt-tab" data-bs-toggle="pill" data-bs-target="#appt" type="button" role="tab">Showroom Appointments</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link px-4 py-3 fw-bold fs-5 shadow-sm" id="housing-tab" data-bs-toggle="pill" data-bs-target="#housing" type="button" role="tab">Housing Applications</button>
            </li>
        </ul>
        <a href="book_appointment.php" class="btn btn-outline-primary btn-lg rounded-circle shadow-sm" title="Add appointment" aria-label="Add appointment">
            <i class="fas fa-plus"></i>
        </a>
    </div>

    <div class="tab-content" id="trackerTabsContent">
        <div class="tab-pane fade show active" id="appt" role="tabpanel">
            <div class="row">
                <?php if ($appointments->num_rows > 0):?>
                    <?php while ($row = $appointments->fetch_assoc()):?>
                        <?php
                            $appointmentDateTime = DateTime::createFromFormat('Y-m-d H:i:s', $row['appointment_date'] . ' ' . $row['appointment_time']);
                            $canCancel = in_array($row['status'], ['REQUESTED', 'ASSIGNED'], true) && $appointmentDateTime && $appointmentDateTime > new DateTime();
                        ?>
                        <div class="col-md-6 mb-4">
                            <div class="card shadow-sm border-0 h-100">
                                <div class="card-body p-4">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <h4 class="fw-bold m-0"><?php echo htmlspecialchars($row['project_name']);?></h4>
                                        <?php
                                            $bg = 'secondary';
                                            if ($row['status'] === 'REQUESTED') $bg = 'warning text-dark';
                                            if ($row['status'] === 'ASSIGNED') $bg = 'primary';
                                            if ($row['status'] === 'COMPLETED') $bg = 'success';
                                            if ($row['status'] === 'CANCELLED' || $row['status'] === 'NO_SHOW') $bg = 'danger';
                                       ?>
                                        <div class="text-end">
                                            <span class="badge bg-<?php echo $bg;?> fs-6 px-3 py-2"><?php echo htmlspecialchars($row['status']);?></span>
                                            <?php if ($canCancel):?>
                                                <form method="POST" class="mt-2" onsubmit="return confirm('ARE YOU SURE WANT TO CANCEL?');">
                                                    <input type="hidden" name="action" value="cancel_appointment">
                                                    <input type="hidden" name="appointment_id" value="<?php echo (int)$row['appointment_id'];?>">
                                                    <button type="submit" class="btn btn-sm btn-outline-danger fw-bold">Cancel</button>
                                                </form>
                                            <?php endif;?>
                                        </div>
                                    </div>
                                    <p class="text-muted fs-5 mb-2"><i class="fas fa-clipboard-list text-primary me-2"></i><?php echo str_replace('_', ' ', htmlspecialchars($row['service_type']));?></p>
                                    <p class="text-muted fs-5 m-0"><i class="far fa-calendar-alt text-danger me-2"></i><?php echo htmlspecialchars(date('Y-m-d', strtotime($row['appointment_date'])). ' at '. date('H:i', strtotime($row['appointment_time'])));?></p>
                                    <?php if (!empty($row['staff_remarks'])):?>
                                        <div class="mt-3 p-3 bg-light rounded border">
                                            <p class="m-0 small fw-bold">Staff Remarks:</p>
                                            <p class="m-0 text-muted"><?php echo htmlspecialchars($row['staff_remarks']);?></p>
                                        </div>
                                    <?php endif;?>
                                </div>
                            </div>
                        </div>
                    <?php endwhile;?>
                <?php else:?>
                    <div class="col-12 text-center py-5">
                        <i class="far fa-calendar-times display-1 text-muted mb-3"></i>
                        <h4 class="text-muted">No appointments found.</h4>
                        <a href="properties.php" class="btn btn-dark mt-3 px-4">Browse Properties</a>
                    </div>
                <?php endif;?>
            </div>
        </div>

        <div class="tab-pane fade" id="housing" role="tabpanel">
            <div class="row">
                <?php if ($applications->num_rows > 0):?>
                    <?php while ($row = $applications->fetch_assoc()):?>
                        <div class="col-md-6 mb-4">
                            <div class="card shadow-sm border-0 border-start border-primary border-5 h-100">
                                <div class="card-body p-4">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <h4 class="fw-bold m-0"><?php echo htmlspecialchars($row['project_name']);?></h4>
                                        <?php
                                            $bg = 'secondary';
                                            if ($row['status'] === 'PENDING_REVIEW') $bg = 'warning text-dark';
                                            if ($row['status'] === 'APPROVED_FOR_DRAW') $bg = 'primary';
                                            if ($row['status'] === 'WINNER') $bg = 'success';
                                            if ($row['status'] === 'REJECTED') $bg = 'danger';
                                       ?>
                                        <span class="badge bg-<?php echo $bg;?> fs-6 px-3 py-2"><?php echo htmlspecialchars($row['status']);?></span>
                                    </div>
                                    <p class="text-muted fs-5 m-0"><i class="far fa-clock text-primary me-2"></i>Applied on: <?php echo htmlspecialchars(date('d M Y, h:i A', strtotime($row['application_date'])));?></p>
                                    <?php if ($row['status'] === 'WINNER'):?>
                                        <div class="mt-4 p-3 bg-success bg-opacity-10 rounded border border-success">
                                            <p class="m-0 fw-bold text-success"><i class="fas fa-trophy me-2"></i>Congratulations! You have been selected in the draw. Please await offline contract instructions.</p>
                                        </div>
                                    <?php endif;?>
                                </div>
                            </div>
                        </div>
                    <?php endwhile;?>
                <?php else:?>
                    <div class="col-12 text-center py-5">
                        <i class="fas fa-file-invoice display-1 text-muted mb-3"></i>
                        <h4 class="text-muted">No affordable housing applications found.</h4>
                        <a href="properties.php" class="btn btn-primary mt-3 px-4">View Government Housing</a>
                    </div>
                <?php endif;?>
            </div>
        </div>
    </div>
</div>
<?php include '../includes/footer.php';?>

