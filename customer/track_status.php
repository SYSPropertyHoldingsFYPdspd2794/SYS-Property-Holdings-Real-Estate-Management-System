<?php
/**
 * PROJECT: SYS Property Holdings
 * FILE: customer/track_status.php
 * DESCRIPTION: US29 - Track application and appointment statuses with context-aware filtering, flat layout cards, and multi-stage workflow timelines.
 */

session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'CUSTOMER') {
    header("Location: ../login.php");
    exit();
}
include '../includes/db_connect.php';

$account_id = $_SESSION['account_id'];
$delete_message = '';
$delete_error = '';

$conn->query("ALTER TABLE appointments ADD COLUMN IF NOT EXISTS customer_deleted_at DATETIME DEFAULT NULL");

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action_type'] ?? '') === 'delete_appointments') {
    $selected_ids = array_values(array_unique(array_filter(array_map('intval', $_POST['appointment_ids'] ?? []), function ($id) {
        return $id > 0;
    })));

    if (empty($selected_ids)) {
        header("Location: track_status.php?delete_msg=none");
        exit();
    }

    $conn->begin_transaction();
    try {
        $hide_appt_stmt = $conn->prepare("UPDATE appointments
                                           SET customer_deleted_at = NOW()
                                           WHERE customer_id = ? && appointment_id = ?
                                           AND customer_deleted_at IS NULL
                                           AND (
                                               status IN ('CANCELLED', 'NO_SHOW')
                                               OR (status IN ('REQUESTED', 'ASSIGNED') AND TIMESTAMP(appointment_date, appointment_time) <= NOW())
                                           )");

        $deleted_count = 0;
        foreach ($selected_ids as $appt_id) {
            $hide_appt_stmt->bind_param("ii", $account_id, $appt_id);
            $hide_appt_stmt->execute();
            if ($hide_appt_stmt->affected_rows > 0) {
                $deleted_count++;
            }
        }

        if ($deleted_count > 0) {
            $conn->commit();
            header("Location: track_status.php?delete_msg=success&count=" . $deleted_count);
            exit();
        } else {
            $conn->rollback();
            header("Location: track_status.php?delete_msg=not_eligible");
            exit();
        }
    } catch (Exception $e) {
        $conn->rollback();
        $delete_error = "System error occurred while deleting appointments.";
    }
}

if (isset($_GET['delete_msg'])) {
    if ($_GET['delete_msg'] === 'success') {
        $count = intval($_GET['count'] ?? 1);
        $delete_message = "Successfully cleared " . $count . " archived appointment record(s) from your history tracking.";
    } elseif ($_GET['delete_msg'] === 'none') {
        $delete_error = "No appointment selections detected. Please select entries via checkboxes.";
    } elseif ($_GET['delete_msg'] === 'not_eligible') {
        $delete_error = "Selected records cannot be cleared. Only CANCELLED or EXPIRED records can be deleted.";
    }
}

$success_msg = isset($_GET['success']) ? trim($_GET['success']) : '';

$active_tab = 'appt';
if ((isset($_GET['tab']) && $_GET['tab'] === 'housing') || $success_msg === 'application_submitted') {
    $active_tab = 'housing';
}

$appt_stmt = $conn->prepare("SELECT a.*, p.project_name, p.state FROM appointments a JOIN properties p ON a.property_id = p.property_id WHERE a.customer_id = ? AND a.customer_deleted_at IS NULL ORDER BY a.appointment_date DESC");
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
    <h2 class="fw-bold mb-5"><i class="fas fa-route text-primary me-2"></i>Universal Status Tracker</h2>
    
    <?php if ($success_msg === 'appointment_booked'): ?>
        <div class="alert alert-success shadow-sm border-0 mb-4 rounded-3 fw-bold"><i class="fas fa-check-circle me-2"></i>Your showroom appointment request has been successfully submitted!</div>
    <?php elseif ($success_msg === 'application_submitted'): ?>
        <div class="alert alert-success shadow-sm border-0 mb-4 rounded-3 fw-bold"><i class="fas fa-check-circle me-2"></i>Your affordable housing application and documents were securely submitted.</div>
    <?php endif; ?>

    <?php if ($delete_message !== ''): ?>
        <div class="alert alert-success shadow-sm border-0 mb-4 rounded-3 fw-bold"><i class="fas fa-trash-alt me-2"></i><?php echo htmlspecialchars($delete_message); ?></div>
    <?php endif; ?>
    <?php if ($delete_error !== ''): ?>
        <div class="alert alert-danger shadow-sm border-0 mb-4 rounded-3 fw-bold"><i class="fas fa-exclamation-triangle me-2"></i><?php echo htmlspecialchars($delete_error); ?></div>
    <?php endif; ?>

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-5">
        <ul class="nav nav-pills" id="trackerTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link <?php echo $active_tab === 'appt' ? 'active' : ''; ?> px-4 py-3 fw-bold fs-5 shadow-sm me-3 rounded-pill" id="appt-tab" data-bs-toggle="pill" data-bs-target="#appt" type="button" role="tab"><i class="far fa-calendar-alt me-2"></i>Showroom Appointments</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link <?php echo $active_tab === 'housing' ? 'active' : ''; ?> px-4 py-3 fw-bold fs-5 shadow-sm rounded-pill" id="housing-tab" data-bs-toggle="pill" data-bs-target="#housing" type="button" role="tab"><i class="fas fa-home me-2"></i>Housing Applications</button>
            </li>
        </ul>
        <a href="<?php echo $active_tab === 'housing' ? 'properties.php?filter_type=AFFORDABLE' : 'properties.php'; ?>" id="dynamicPlusBtn" class="btn btn-outline-primary btn-lg rounded-circle shadow-sm" title="Browse Properties Catalog">
            <i class="fas fa-plus"></i>
        </a>
    </div>

    <form id="deleteAppointmentsForm" method="POST" action="track_status.php">
        <input type="hidden" name="action_type" value="delete_appointments">
        <div class="tab-content" id="trackerTabsContent">
            
            <div class="tab-pane fade <?php echo $active_tab === 'appt' ? 'show active' : ''; ?>" id="appt" role="tabpanel">
                <div class="mb-3 d-flex justify-content-end">
                    <button type="button" id="deleteSelectedAppointmentsBtn" class="btn btn-danger btn-sm rounded-pill px-3 fw-bold shadow-sm" disabled>
                        <i class="fas fa-trash-alt me-1"></i>Delete Selected
                    </button>
                </div>
                <div class="row">
                    <?php if ($appointments->num_rows > 0): ?>
                        <?php while ($row = $appointments->fetch_assoc()): ?>
                            <div class="col-md-6 mb-4">
                                <div class="card shadow-sm border-0 h-100 rounded-4">
                                    <div class="card-body p-4 d-flex flex-column">
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <div class="d-flex align-items-center gap-2" style="max-width: 70%;">
                                                <?php
                                                    $appointmentDateTime = DateTime::createFromFormat('Y-m-d H:i:s', $row['appointment_date'] . ' ' . $row['appointment_time']);
                                                    $isExpiredAppointment = in_array($row['status'], ['REQUESTED', 'ASSIGNED'], true) && $appointmentDateTime && $appointmentDateTime <= new DateTime();
                                                    $displayStatus = $isExpiredAppointment ? 'EXPIRED' : $row['status'];
                                                ?>
                                                <?php if (in_array($row['status'], ['CANCELLED', 'NO_SHOW'], true) || $isExpiredAppointment): ?>
                                                    <input type="checkbox" name="appointment_ids[]" value="<?php echo $row['appointment_id']; ?>" class="form-check-input appointment-delete-check me-1 flex-shrink-0">
                                                <?php endif; ?>
                                                <h4 class="fw-bold m-0 text-truncate"><?php echo htmlspecialchars($row['project_name']); ?></h4>
                                            </div>
                                            <?php
                                                $bg = 'secondary';
                                                if ($displayStatus === 'REQUESTED' || $displayStatus === 'PENDING') $bg = 'warning text-dark';
                                                if ($displayStatus === 'ASSIGNED') $bg = 'primary';
                                                if ($displayStatus === 'COMPLETED') $bg = 'success';
                                                if (in_array($displayStatus, ['CANCELLED', 'NO_SHOW', 'EXPIRED'], true)) $bg = 'danger';
                                            ?>
                                            <span class="badge bg-<?php echo $bg; ?> fs-6 px-3 py-2 shadow-sm"><?php echo htmlspecialchars($displayStatus); ?></span>
                                        </div>
                                        <p class="text-muted fs-5 mb-2"><i class="fas fa-clipboard-list text-primary me-2"></i><?php echo str_replace('_', ' ', htmlspecialchars($row['service_type'])); ?></p>
                                        <p class="text-muted fs-5 mb-3"><i class="far fa-calendar-alt text-danger me-2"></i><?php echo htmlspecialchars(date('d M Y', strtotime($row['appointment_date'])) . ' at ' . date('h:i A', strtotime($row['appointment_time']))); ?></p>
                                        
                                        <div class="mt-auto pt-2">
                                            <a href="adjust_appointment.php?type=appointment&id=<?php echo $row['appointment_id']; ?>" class="btn btn-outline-dark btn-sm w-100 rounded-pill fw-bold py-2"><i class="fas fa-sliders-h me-2"></i>Manage Appointment Details</a>
                                        </div>
                                        
                                        <?php if (!empty($row['staff_remarks'])): ?>
                                            <div class="mt-3 p-3 bg-light rounded-3 border-start border-warning border-4">
                                                <p class="m-0 small fw-bold text-dark"><i class="fas fa-comment-dots me-2"></i>Staff Remarks:</p>
                                                <p class="m-0 text-muted small"><?php echo htmlspecialchars($row['staff_remarks']); ?></p>
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

            <div class="tab-pane fade <?php echo $active_tab === 'housing' ? 'show active' : ''; ?>" id="housing" role="tabpanel">
                <div class="row">
                    <?php if ($applications->num_rows > 0): ?>
                        <?php while ($row = $applications->fetch_assoc()): ?>
                            <div class="col-12 mb-4">
                                <div class="card shadow-sm border-0 border-start border-success border-5 rounded-4">
                                    <div class="card-body p-4 p-md-5">
                                        
                                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
                                            <div>
                                                <h3 class="fw-bold text-dark mb-1"><?php echo htmlspecialchars($row['project_name']); ?></h3>
                                                <p class="text-muted fs-5 m-0"><i class="fas fa-map-marker-alt text-danger me-2"></i><?php echo htmlspecialchars($row['state']); ?></p>
                                            </div>
                                            <?php
                                                $bg = 'secondary';
                                                if ($row['status'] === 'PENDING_REVIEW') $bg = 'warning text-dark';
                                                if ($row['status'] === 'APPROVED_FOR_DRAW') $bg = 'info text-dark';
                                                if ($row['status'] === 'WINNER') $bg = 'success';
                                                if ($row['status'] === 'REJECTED') $bg = 'danger';
                                            ?>
                                            <span class="badge bg-<?php echo $bg; ?> fs-5 px-4 py-2.5 shadow-sm rounded-pill text-uppercase"><?php echo htmlspecialchars(str_replace('_', ' ', $row['status'])); ?></span>
                                        </div>

                                        <div class="py-4 my-2 border-top border-bottom border-light">
                                            <h6 class="text-uppercase tracking-wider fw-bold text-secondary small mb-4"><i class="fas fa-tasks me-2"></i>Application Pipeline Progress Matrix</h6>
                                            <div class="timeline-container d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center position-relative gap-4 gap-md-2">
                                                <div class="timeline-line d-none d-md-block"></div>
                                                
                                                <div class="timeline-step text-md-center position-relative z-index-2">
                                                    <div class="step-icon bg-success text-white shadow"><i class="fas fa-file-import"></i></div>
                                                    <div class="fw-bold text-dark mt-2 small">1. Documents Submitted</div>
                                                    <div class="text-muted font-monospace tiny-time"><?php echo htmlspecialchars(date('d M Y, h:i A', strtotime($row['application_date']))); ?></div>
                                                </div>

                                                <div class="timeline-step text-md-center position-relative z-index-2">
                                                    <?php if ($row['status'] !== 'PENDING_REVIEW'): ?>
                                                        <div class="step-icon bg-success text-white shadow"><i class="fas fa-user-check"></i></div>
                                                        <div class="fw-bold text-dark mt-2 small">2. Regional Verification</div>
                                                        <div class="text-success font-monospace tiny-time fw-bold"><i class="fas fa-check me-1"></i>Verified Complete</div>
                                                    <?php else: ?>
                                                        <div class="step-icon bg-warning text-dark shadow"><i class="fas fa-spinner fa-spin"></i></div>
                                                        <div class="fw-bold text-muted mt-2 small">2. Regional Verification</div>
                                                        <div class="text-warning font-monospace tiny-time fw-bold">Awaiting Review...</div>
                                                    <?php endif; ?>
                                                </div>

                                                <div class="timeline-step text-md-center position-relative z-index-2">
                                                    <?php if ($row['status'] === 'WINNER'): ?>
                                                        <div class="step-icon bg-success text-white shadow"><i class="fas fa-trophy"></i></div>
                                                        <div class="fw-bold text-dark mt-2 small">3. Ballot Allocation</div>
                                                        <div class="text-success font-monospace tiny-time fw-bold"><i class="fas fa-star me-1"></i>Selected in Draw!</div>
                                                    <?php elseif ($row['status'] === 'REJECTED'): ?>
                                                        <div class="step-icon bg-danger text-white shadow"><i class="fas fa-times"></i></div>
                                                        <div class="fw-bold text-dark mt-2 small">3. Ballot Allocation</div>
                                                        <div class="text-danger font-monospace tiny-time fw-bold">Disqualified</div>
                                                    <?php elseif ($row['status'] === 'APPROVED_FOR_DRAW'): ?>
                                                        <div class="step-icon bg-info text-dark shadow"><i class="fas fa-ticket-alt"></i></div>
                                                        <div class="fw-bold text-dark mt-2 small">3. Ballot Allocation</div>
                                                        <div class="text-info font-monospace tiny-time fw-bold">Awaiting Draw Runs</div>
                                                    <?php else: ?>
                                                        <div class="step-icon bg-light text-muted border"><i class="fas fa-hourglass-start"></i></div>
                                                        <div class="fw-bold text-muted mt-2 small">3. Ballot Allocation</div>
                                                        <div class="text-muted font-monospace tiny-time">Pending Stage 2</div>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 pt-3">
                                            <p class="text-muted m-0 small"><i class="far fa-clock text-primary me-2"></i>Submission Registered: <?php echo htmlspecialchars(date('d M Y, h:i A', strtotime($row['application_date']))); ?></p>
                                            <div>
                                                <a href="adjust_appointment.php?type=housing&id=<?php echo $row['application_id']; ?>" class="btn btn-outline-success rounded-pill fw-bold px-4 py-2"><i class="fas fa-file-invoice me-2"></i>View Application Summary / Uploads</a>
                                            </div>
                                        </div>

                                        <?php if ($row['status'] === 'REJECTED'): ?>
                                            <div class="alert alert-danger border-0 shadow-sm p-4 mt-4 mb-0 rounded-3" style="background-color: #fff5f5; border-left: 5px solid #dc3545 !important;">
                                                <div class="d-flex align-items-start">
                                                    <i class="fas fa-times-circle text-danger fa-lg me-3 mt-1"></i>
                                                    <div>
                                                        <h6 class="fw-bold text-danger mb-1">Application Request Disqualified</h6>
                                                        <p class="mb-0 text-dark font-monospace fw-bold small">Sorry you’re dont match the application requirement. You still can try other Affodable House Application</p>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endif; ?>

                                        <?php if ($row['status'] === 'WINNER'): ?>
                                            <div class="mt-4 p-4 bg-success bg-opacity-10 rounded-3 border border-success border-opacity-50 mb-0">
                                                <p class="m-0 fw-bold text-success"><i class="fas fa-trophy me-2 fa-lg animate-bounce"></i>Congratulations! Your application has been successfully drawn in the state ballot allocation pool. Our housing officers will contact you shortly for contract signing parameters.</p>
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
    </form>
</div>

<style>
    .timeline-container { position: relative; width: 100%; }
    .timeline-line { position: absolute; top: 20px; left: 5%; width: 90%; height: 4px; background-color: #e9ecef; z-index: 1; }
    .step-icon { width: 45px; height: 45px; border-radius: 50%; background-color: #fff; border: 3px solid #e9ecef; display: flex; align-items: center; justify-content: center; margin: 0 auto; font-size: 16px; transition: all 0.3s ease; }
    .tiny-time { font-size: 11px; margin-top: 2px; display: block; }
    .appointment-delete-check {
        border: 2px solid #000;
    }
    .appointment-delete-check:checked {
        background-color: #000;
        border-color: #000;
    }
    @media (max-width: 767.98px) {
        .step-icon { margin: 0; display: inline-flex; }
        .timeline-step { padding-left: 60px; text-align: left !important; width: 100%; }
        .timeline-step .step-icon { position: absolute; left: 0; top: 0; }
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const plusBtn = document.getElementById('dynamicPlusBtn');
    const apptTab = document.getElementById('appt-tab');
    const housingTab = document.getElementById('housing-tab');

    if (plusBtn) {
        if (apptTab) {
            apptTab.addEventListener('shown.bs.tab', function () {
                plusBtn.setAttribute('href', 'properties.php');
            });
        }
        if (housingTab) {
            housingTab.addEventListener('shown.bs.tab', function () {
                plusBtn.setAttribute('href', 'properties.php?filter_type=AFFORDABLE');
            });
        }
    }
});

const deleteAppointmentsForm = document.getElementById('deleteAppointmentsForm');
const deleteSelectedAppointmentsBtn = document.getElementById('deleteSelectedAppointmentsBtn');
const appointmentDeleteChecks = document.querySelectorAll('.appointment-delete-check');

function updateDeleteSelectedState() {
    if (!deleteSelectedAppointmentsBtn) return;
    deleteSelectedAppointmentsBtn.disabled = !Array.from(appointmentDeleteChecks).some((checkbox) => checkbox.checked);
}

appointmentDeleteChecks.forEach((checkbox) => {
    checkbox.addEventListener('change', updateDeleteSelectedState);
});

if (deleteSelectedAppointmentsBtn && deleteAppointmentsForm) {
    deleteSelectedAppointmentsBtn.addEventListener('click', function () {
        const selectedCount = Array.from(appointmentDeleteChecks).filter((checkbox) => checkbox.checked).length;
        if (selectedCount === 0) {
            updateDeleteSelectedState();
            return;
        }

        const message = 'Are you sure you want to delete the selected cancelled or expired appointment' + (selectedCount === 1 ? '?' : 's?');

        if (window.Swal) {
            Swal.fire({
                icon: 'warning',
                title: 'Delete Appointment?',
                text: message,
                showCancelButton: true,
                confirmButtonText: 'Yes, delete',
                cancelButtonText: 'No',
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    deleteAppointmentsForm.submit();
                }
            });
            return;
        }

        if (window.showConfirmModal) {
            window.showConfirmModal({
                title: 'Delete Appointment?',
                message: message,
                confirmText: 'Yes, delete',
                confirmClass: 'btn-danger',
                onConfirm: function () {
                    deleteAppointmentsForm.submit();
                }
            });
            return;
        }

        if (window.confirm(message)) {
            deleteAppointmentsForm.submit();
        }
    });
}
</script>
<?php include '../includes/footer.php'; ?>
