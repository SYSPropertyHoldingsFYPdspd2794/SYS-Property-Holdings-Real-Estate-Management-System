<?php
/**
 * PROJECT: SYS Property Holdings
 * FILE: customer/track_status.php
 * DESCRIPTION: US29 - Track application and appointment statuses with context-aware plus button filtering.
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
                                            AND (a.status = 'CANCELLED' OR a.status = 'NO_SHOW')");

        $delete_appt_stmt = $conn->prepare("DELETE FROM appointments 
                                            WHERE customer_id = ? AND appointment_id = ? 
                                            AND (status = 'CANCELLED' OR status = 'NO_SHOW')");

        $deleted_count = 0;
        foreach ($selected_ids as $appt_id) {
            $delete_docs_stmt->bind_param("ii", $account_id, $appt_id);
            $delete_docs_stmt->execute();

            $delete_appt_stmt->bind_param("ii", $account_id, $appt_id);
            $delete_appt_stmt->execute();
            if ($delete_appt_stmt->affected_rows > 0) {
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
                <button class="nav-link active px-4 py-3 fw-bold fs-5 shadow-sm me-3 rounded-pill" id="appt-tab" data-bs-toggle="pill" data-bs-target="#appt" type="button" role="tab"><i class="far fa-calendar-alt me-2"></i>Showroom Appointments</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link px-4 py-3 fw-bold fs-5 shadow-sm rounded-pill" id="housing-tab" data-bs-toggle="pill" data-bs-target="#housing" type="button" role="tab"><i class="fas fa-home me-2"></i>Housing Applications</button>
            </li>
        </ul>
        <a href="properties.php" id="dynamicPlusBtn" class="btn btn-outline-primary btn-lg rounded-circle shadow-sm" title="Browse Properties Catalog">
            <i class="fas fa-plus"></i>
        </a>
    </div>

    <form id="deleteAppointmentsForm" method="POST" action="track_status.php">
        <input type="hidden" name="action_type" value="delete_appointments">
        <div class="tab-content" id="trackerTabsContent">
            
            <div class="tab-pane fade show active" id="appt" role="tabpanel">
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
                                                <?php if (in_array($row['status'], ['CANCELLED', 'COMPLETED', 'NO_SHOW'], true)): ?>
                                                    <input type="checkbox" name="appointment_ids[]" value="<?php echo $row['appointment_id']; ?>" class="form-check-input appointment-delete-check me-1 flex-shrink-0">
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
                                            <span class="badge bg-<?php echo $bg; ?> fs-6 px-3 py-2 shadow-sm"><?php echo htmlspecialchars($row['status']); ?></span>
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
    </form>
</div>

<script { sandbox: 'allow-scripts' }>
// ENHANCEMENT: Listen to Bootstrap pill tab switches to toggle the context-aware plus button target
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
                // Instantly injects the filter parameter when user switches to Housing summary panel
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
<?php include '../includes/footer.php'; ?>