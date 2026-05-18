<?php
/**
 * PROJECT: SYS Property Holdings
 * FILE: customer/track_status.php
 * DESCRIPTION: US29 - Track application and appointment statuses. Linked to adjust_appointment.php.
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
        $delete_docs_stmt = $conn->prepare("DELETE d FROM documents d
                                            JOIN appointments a ON d.related_to_type = 'APPOINTMENT' AND d.related_to_id = a.appointment_id
                                            WHERE a.customer_id = ? AND a.appointment_id = ?
                                            AND (a.status = 'CANCELLED' OR TIMESTAMP(a.appointment_date, a.appointment_time) < NOW())");
        $delete_appts_stmt = $conn->prepare("DELETE FROM appointments
                                             WHERE customer_id = ? AND appointment_id = ?
                                             AND (status = 'CANCELLED' OR TIMESTAMP(appointment_date, appointment_time) < NOW())");

        $deleted_count = 0;
        foreach ($selected_ids as $appointment_id) {
            $delete_docs_stmt->bind_param("ii", $account_id, $appointment_id);
            $delete_docs_stmt->execute();

            $delete_appts_stmt->bind_param("ii", $account_id, $appointment_id);
            $delete_appts_stmt->execute();
            $deleted_count += $delete_appts_stmt->affected_rows;
        }

        $conn->commit();
        header("Location: track_status.php?delete_msg=" . ($deleted_count > 0 ? 'success' : 'none') . "&deleted=" . $deleted_count);
        exit();
    } catch (Throwable $e) {
        $conn->rollback();
        $delete_error = "Unable to delete selected appointments. Please try again.";
    }
}

if (isset($_GET['delete_msg'])) {
    if ($_GET['delete_msg'] === 'success') {
        $deleted_count = max(0, (int)($_GET['deleted'] ?? 0));
        $delete_message = $deleted_count . " appointment" . ($deleted_count === 1 ? "" : "s") . " deleted successfully.";
    } elseif ($_GET['delete_msg'] === 'none') {
        $delete_error = "Please select at least one cancelled or expired appointment to delete.";
    }
}

$appt_stmt = $conn->prepare("SELECT a.*, p.project_name, p.state FROM appointments a JOIN properties p ON a.property_id = p.property_id WHERE a.customer_id =? ORDER BY a.appointment_date DESC");
$appt_stmt->bind_param("i", $account_id);
$appt_stmt->execute();
$appointments = $appt_stmt->get_result();

$app_stmt = $conn->prepare("SELECT ah.*, p.project_name, p.state FROM affordable_housing_applications ah JOIN properties p ON ah.property_id = p.property_id WHERE ah.customer_id = ? ORDER BY ah.application_date DESC");
$app_stmt->bind_param("i", $account_id);
$app_stmt->execute();
$applications = $app_stmt->get_result();

include '../includes/header.php';
?>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<div class="container my-5">
    <h2 class="fw-bold mb-5"><i class="fas fa-route text-primary me-2"></i>Universal Status Tracker</h2>
    <?php if ($delete_message !== ''): ?>
        <div class="alert alert-success alert-dismissible fade show fw-bold shadow-sm mb-4" role="alert">
            <i class="fas fa-check-circle me-2"></i><?php echo htmlspecialchars($delete_message); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    <?php if ($delete_error !== ''): ?>
        <div class="alert alert-danger alert-dismissible fade show fw-bold shadow-sm mb-4" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i><?php echo htmlspecialchars($delete_error); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
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
            <form method="POST" id="deleteAppointmentsForm">
                <input type="hidden" name="action_type" value="delete_appointments">
                <div class="d-flex justify-content-end mb-3">
                    <button type="button" id="deleteSelectedAppointmentsBtn" class="btn btn-outline-danger rounded-pill fw-bold px-4" disabled>
                        <i class="fas fa-trash-alt me-2"></i>Delete Selected
                    </button>
                </div>
            <div class="row">
                <?php if ($appointments->num_rows > 0):?>
                    <?php while ($row = $appointments->fetch_assoc()):?>
                        <?php
                            $appointment_ts = strtotime($row['appointment_date'] . ' ' . $row['appointment_time']);
                            $can_delete_appointment = $row['status'] === 'CANCELLED' || ($appointment_ts !== false && $appointment_ts < time());
                        ?>
                        <div class="col-md-6 mb-4">
                            <div class="card shadow-sm border-0 h-100 rounded-4">
                                <div class="card-body p-4 d-flex flex-column">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <div class="d-flex align-items-center gap-2" style="max-width: 70%;">
                                            <?php if ($can_delete_appointment): ?>
                                                <input class="form-check-input appointment-delete-check flex-shrink-0 m-0" type="checkbox" name="appointment_ids[]" value="<?php echo (int)$row['appointment_id']; ?>" aria-label="Select appointment for deletion">
                                            <?php endif; ?>
                                            <h4 class="fw-bold m-0 text-truncate"><?php echo htmlspecialchars($row['project_name']); ?></h4>
                                        </div>
                                        <?php
                                            $bg = 'secondary';
                                            if ($row['status'] === 'REQUESTED' || $row['status'] === 'PENDING') $bg = 'warning text-dark';
                                            if ($row['status'] === 'ASSIGNED') $bg = 'primary';
                                            if ($row['status'] === 'COMPLETED') $bg = 'success';
                                            if ($row['status'] === 'CANCELLED' || $row['status'] === 'NO_SHOW') $bg = 'danger';
                                       ?>
                                        <span class="badge bg-<?php echo $bg;?> fs-6 px-3 py-2"><?php echo htmlspecialchars($row['status']);?></span>
                                    </div>

                                    <div class="text-muted fs-6 mb-2">
                                        <i class="fas fa-map-marker-alt text-danger me-2"></i><?php echo htmlspecialchars($row['state']); ?>
                                    </div>
                                    <div class="text-muted fs-6 mb-2">
                                        <i class="far fa-calendar text-primary me-2"></i><?php echo htmlspecialchars(date('d M Y', strtotime($row['appointment_date']))); ?>
                                        <span class="mx-2">|</span>
                                        <i class="far fa-clock text-primary me-2"></i><?php echo htmlspecialchars(date('h:i A', strtotime($row['appointment_time']))); ?>
                                    </div>
                                    <div class="text-muted fs-6 mb-3">
                                        <i class="fas fa-concierge-bell text-primary me-2"></i><?php echo htmlspecialchars(str_replace('_', ' ', $row['service_type'])); ?>
                                    </div>
                                    
                                    <?php if (!empty($row['staff_remarks'])): ?>
                                        <div class="mt-3 p-3 bg-light rounded-3 border-start border-warning border-4">
                                            <p class="m-0 small fw-bold text-dark"><i class="fas fa-comment-dots me-2"></i>Staff Remarks:</p>
                                            <p class="m-0 text-muted small"><?php echo htmlspecialchars($row['staff_remarks']); ?></p>
                                        </div>
                                    <?php endif; ?>

                                    <div class="mt-auto pt-3">
                                        <a href="adjust_appointment.php?type=appointment&id=<?php echo $row['appointment_id']; ?>" class="btn btn-outline-dark btn-sm w-100 rounded-pill fw-bold py-2">
                                            <i class="fas fa-sliders-h me-2"></i>Manage Appointment
                                        </a>
                                    </div>
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
            </form>
        </div>

        <div class="tab-pane fade" id="housing" role="tabpanel">
            <div class="row">
                <?php if ($applications->num_rows > 0): ?>
                    <?php while ($row = $applications->fetch_assoc()): ?>
                        <div class="col-md-6 mb-4">
                            <div class="card shadow-sm border-0 border-start border-success border-5 h-100 rounded-4">
                                <div class="card-body p-4 d-flex flex-column">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <h4 class="fw-bold m-0 text-truncate" style="max-width: 65%;"><?php echo htmlspecialchars($row['project_name']); ?></h4>
                                        <?php
                                            $bg = 'secondary';
                                            if ($row['status'] === 'PENDING_REVIEW') $bg = 'warning text-dark';
                                            if ($row['status'] === 'APPROVED_FOR_DRAW') $bg = 'info text-dark';
                                            if ($row['status'] === 'WINNER') $bg = 'success';
                                            if ($row['status'] === 'REJECTED') $bg = 'danger';
                                        ?>
                                        <span class="badge bg-<?php echo $bg; ?> fs-6 px-3 py-2 shadow-sm"><?php echo htmlspecialchars(str_replace('_', ' ', $row['status'])); ?></span>
                                    </div>
                                    <p class="text-muted fs-5 mb-3"><i class="far fa-clock text-primary me-2"></i>Applied on: <?php echo htmlspecialchars(date('d M Y, h:i A', strtotime($row['application_date']))); ?></p>
                                    
                                    <div class="mt-auto pt-2">
                                        <a href="adjust_appointment.php?type=housing&id=<?php echo $row['application_id']; ?>" class="btn btn-outline-success btn-sm w-100 rounded-pill fw-bold py-2"><i class="fas fa-file-alt me-2"></i>View Application Summary</a>
                                    </div>

                                    <?php if ($row['status'] === 'WINNER'): ?>
                                        <div class="mt-3 p-3 bg-success bg-opacity-10 rounded-3 border border-success border-opacity-50">
                                            <p class="m-0 small fw-bold text-success"><i class="fas fa-trophy me-2 fa-lg"></i>Congratulations! Selected in ballots. Awaiting verification instructions.</p>
                                        </div>
                                    <?php elseif ($row['status'] === 'REJECTED'): ?>
                                        <div class="mt-3 p-3 bg-danger bg-opacity-10 rounded-3 border border-danger border-opacity-50">
                                            <p class="m-0 small fw-bold text-danger"><i class="fas fa-times-circle me-2"></i>Application did not meet state DDL parameters.</p>
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
<script>
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

        Swal.fire({
            icon: 'warning',
            title: 'Delete Appointment?',
            text: 'Are you sure you want to delete the selected cancelled or expired appointment' + (selectedCount === 1 ? '?' : 's?'),
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
    });
}
</script>
<?php include '../includes/footer.php';?>
