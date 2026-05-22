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
                                            AND (a.status = 'CANCELLED' OR a.appointment_date < CURDATE())");
        
        $delete_appt_stmt = $conn->prepare("DELETE FROM appointments 
                                            WHERE customer_id = ? AND appointment_id = ? 
                                            AND (status = 'CANCELLED' OR appointment_date < CURDATE())");

        foreach ($selected_ids as $id) {
            $delete_docs_stmt->bind_param("ii", $account_id, $id);
            $delete_docs_stmt->execute();

            $delete_appt_stmt->bind_param("ii", $account_id, $id);
            $delete_appt_stmt->execute();
        }

        $conn->commit();
        header("Location: track_status.php?delete_msg=success");
        exit();
    } catch (Exception $e) {
        $conn->rollback();
        header("Location: track_status.php?delete_msg=error");
        exit();
    }
}

if (isset($_GET['delete_msg'])) {
    if ($_GET['delete_msg'] === 'success') {
        $delete_message = "Selected historical records purged successfully.";
    } elseif ($_GET['delete_msg'] === 'error') {
        $delete_error = "Database integrity exception during batch purge.";
    }
}

// FETCH HOUSING APPLICATIONS
$apps_query = "SELECT aha.application_id, aha.status, aha.application_date, p.project_name, p.property_code, p.state, p.price, p.image_filename 
               FROM affordable_housing_applications aha
               JOIN properties p ON aha.property_id = p.property_id
               WHERE aha.customer_id = ?
               ORDER BY aha.application_date DESC";
$apps_stmt = $conn->prepare($apps_query);
$apps_stmt->bind_param("i", $account_id);
$apps_stmt->execute();
$apps_result = $apps_stmt->get_result();

// FETCH APPOINTMENTS
$appts_query = "SELECT a.appointment_id, a.service_type, a.appointment_date, a.appointment_time, a.status, a.staff_remarks, p.project_name, p.property_code
                FROM appointments a
                JOIN properties p ON a.property_id = p.property_id
                WHERE a.customer_id = ?
                ORDER BY a.appointment_date DESC, a.appointment_time DESC";
$appts_stmt = $conn->prepare($appts_query);
$appts_stmt->bind_param("i", $account_id);
$appts_stmt->execute();
$appts_result = $appts_stmt->get_result();

include '../includes/header.php';
?>

<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
        <div>
            <h2 class="fw-bold text-dark mb-1">Asset Pipeline Tracking</h2>
            <p class="text-muted small mb-0">Real-time status updates for housing registrations and showroom appointments.</p>
        </div>
        <a href="properties.php" class="btn btn-dark rounded-pill px-4 fw-bold shadow-sm">
            <i class="fas fa-plus me-1"></i> New Registration
        </a>
    </div>

    <?php if ($delete_message): ?>
        <div class="alert alert-success fw-bold small shadow-sm"><?php echo htmlspecialchars($delete_message); ?></div>
    <?php endif; ?>
    <?php if ($delete_error): ?>
        <div class="alert alert-danger fw-bold small shadow-sm"><?php echo htmlspecialchars($delete_error); ?></div>
    <?php endif; ?>

    <ul class="nav nav-pills mb-4 bg-light p-1 rounded-3 d-inline-flex" id="pipelineTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active fw-bold px-4 rounded-3" id="housing-tab" data-bs-toggle="tab" data-bs-target="#housing" type="button" role="tab" aria-controls="housing" aria-selected="true">
                <i class="fas fa-home me-2"></i>Housing Applications (<?php echo $apps_result->num_rows; ?>)
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link fw-bold px-4 rounded-3" id="appointments-tab" data-bs-toggle="tab" data-bs-target="#appointments" type="button" role="tab" aria-controls="appointments" aria-selected="false">
                <i class="fas fa-calendar-check me-2"></i>Showroom Appointments (<?php echo $appts_result->num_rows; ?>)
            </button>
        </li>
    </ul>

    <div class="tab-content" id="pipelineTabsContent">
        
        <div class="tab-pane fade show active" id="housing" role="tablist" aria-labelledby="housing-tab">
            <?php if ($apps_result->num_rows === 0): ?>
                <div class="text-center py-5 bg-white rounded-4 border shadow-sm">
                    <i class="fas fa-folder-open fa-3x text-muted mb-3"></i>
                    <h5 class="fw-bold text-secondary">No Active Schemes Registered</h5>
                    <p class="text-muted small">Explore our database to register for affordable housing options.</p>
                </div>
            <?php else: ?>
                <div class="row g-4">
                    <?php while ($app = $apps_result->fetch_assoc()): ?>
                        <div class="col-12">
                            <div class="card border shadow-sm rounded-4 overflow-hidden bg-white">
                                <div class="row g-0 align-items-center">
                                    <div class="col-md-2 bg-light text-center py-4 border-end">
                                        <?php 
                                        $img_url = "../storage/properties/" . (!empty($app['image_filename']) ? $app['image_filename'] : 'default.jpg');
                                        if (!file_exists($img_url)) $img_url = "https://images.unsplash.com/photo-1564013799919-ab600027ffc6?q=80&w=600&auto=format&fit=crop";
                                        ?>
                                        <img src="<?php echo $img_url; ?>" class="img-fluid rounded-3 shadow-sm mx-auto" style="width: 100px; height: 100px; object-fit: cover;" alt="Scheme Asset">
                                    </div>
                                    <div class="col-md-6 p-4">
                                        <div class="d-flex align-items-center mb-2">
                                            <span class="badge bg-secondary px-2 py-1 rounded small me-2"><?php echo htmlspecialchars($app['property_code']); ?></span>
                                            <small class="text-muted"><i class="fas fa-map-marker-alt me-1"></i><?php echo htmlspecialchars($app['state']); ?></small>
                                        </div>
                                        <h5 class="fw-bold text-dark mb-2"><?php echo htmlspecialchars($app['project_name']); ?></h5>
                                        <div class="text-success fw-bold mb-3">RM <?php echo number_format($app['price'], 2); ?></div>
                                        
                                        <div class="position-relative mt-4 pt-2">
                                            <div class="progress" style="height: 4px;">
                                                <?php 
                                                $pct = 25;
                                                if ($app['status'] === 'APPROVED_FOR_DRAW') $pct = 60;
                                                if ($app['status'] === 'WINNER') $pct = 100;
                                                if ($app['status'] === 'REJECTED') $pct = 100;
                                                ?>
                                                <div class="progress-bar <?php echo $app['status'] === 'REJECTED' ? 'bg-danger' : 'bg-dark'; ?>" role="progressbar" style="width: <?php echo $pct; ?>%;"></div>
                                            </div>
                                            <div class="d-flex justify-content-between position-absolute top-0 w-100" style="margin-top: -6px;">
                                                <div class="text-center">
                                                    <span class="d-block bg-dark text-white rounded-circle shadow-sm" style="width:16px; height:16px; margin:0 auto;"></span>
                                                    <small class="d-block text-muted tiny mt-1 fw-bold">Submitted</small>
                                                </div>
                                                <div class="text-center">
                                                    <span class="d-block <?php echo ($pct >= 60 && $app['status'] !== 'REJECTED') ? 'bg-dark' : 'bg-secondary'; ?> text-white rounded-circle shadow-sm" style="width:16px; height:16px; margin:0 auto;"></span>
                                                    <small class="d-block text-muted tiny mt-1 <?php echo ($pct >= 60) ? 'fw-bold text-dark' : ''; ?>">Review Status</small>
                                                </div>
                                                <div class="text-center">
                                                    <span class="d-block <?php echo ($pct === 100) ? ($app['status'] === 'REJECTED' ? 'bg-danger' : 'bg-success') : 'bg-secondary'; ?> text-white rounded-circle shadow-sm" style="width:16px; height:16px; margin:0 auto;"></span>
                                                    <small class="d-block text-muted tiny mt-1 <?php echo ($pct === 100) ? 'fw-bold text-dark' : ''; ?>">Resolution</small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4 p-4 border-start text-center bg-light-subtle h-100">
                                        <div class="mb-3">
                                            <span class="small text-muted d-block mb-1">Current Matrix State</span>
                                            <?php if ($app['status'] === 'PENDING_REVIEW'): ?>
                                                <span class="badge bg-warning text-dark fw-bold px-3 py-2 rounded-pill"><i class="fas fa-hourglass-half me-1"></i> PENDING REVIEW</span>
                                            <?php elseif ($app['status'] === 'APPROVED_FOR_DRAW'): ?>
                                                <span class="badge bg-info text-dark fw-bold px-3 py-2 rounded-pill"><i class="fas fa-ticket-alt me-1"></i> APPROVED FOR DRAW</span>
                                            <?php elseif ($app['status'] === 'WINNER'): ?>
                                                <span class="badge bg-success fw-bold px-3 py-2 rounded-pill"><i class="fas fa-trophy me-1"></i> CONGRATULATIONS: WINNER</span>
                                            <?php else: ?>
                                                <span class="badge bg-danger fw-bold px-3 py-2 rounded-pill"><i class="fas fa-times-circle me-1"></i> APPLICATION REJECTED</span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="small text-muted mb-3"><i class="fas fa-clock me-1"></i> Logged: <?php echo date('Y-m-d', strtotime($app['application_date'])); ?></div>
                                        
                                        <a href="adjust_appointment.php?type=application&id=<?php echo $app['application_id']; ?>" class="btn btn-sm btn-outline-dark fw-bold rounded-pill px-4">
                                            <i class="fas fa-sliders-h me-1"></i> Manage Application
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="tab-pane fade" id="appointments" role="tabpanel" aria-labelledby="appointments-tab">
            <form id="deleteAppointmentsForm" method="POST">
                <input type="hidden" name="action_type" value="delete_appointments">
                
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="small text-muted">To drop an expired or cancelled schedule, select the card option framework.</span>
                    <button type="button" id="deleteSelectedAppointmentsBtn" class="btn btn-sm btn-outline-danger fw-bold px-3 rounded-pill" disabled>
                        <i class="fas fa-trash-alt me-1"></i> Purge Selected History
                    </button>
                </div>

                <?php if ($appts_result->num_rows === 0): ?>
                    <div class="text-center py-5 bg-white rounded-4 border shadow-sm">
                        <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                        <h5 class="fw-bold text-secondary">No Showroom Visits Scheduled</h5>
                        <p class="text-muted small">You haven't requested any site allocations or direct corporate consults.</p>
                    </div>
                <?php else: ?>
                    <div class="row g-3">
                        <?php while ($appt = $appts_result->fetch_assoc()): ?>
                            <?php 
                            $is_historical = ($appt['status'] === 'CANCELLED' || strtotime($appt['appointment_date']) < strtotime(date('Y-m-d')));
                            ?>
                            <div class="col-md-6">
                                <div class="card border shadow-sm rounded-4 bg-white position-relative hover-card h-100">
                                    <div class="card-body p-4">
                                        <div class="d-flex justify-content-between align-items-start mb-3">
                                            <div>
                                                <span class="badge bg-light text-dark border px-2 py-1 small rounded mb-2"><?php echo htmlspecialchars($appt['property_code']); ?></span>
                                                <h5 class="fw-bold text-dark mb-1"><?php echo htmlspecialchars($appt['project_name']); ?></h5>
                                                <small class="text-muted"><i class="fas fa-concierge-bell me-1"></i><?php echo str_replace('_', ' ', $appt['service_type']); ?></small>
                                            </div>
                                            <div class="text-end">
                                                <?php if ($appt['status'] === 'REQUESTED'): ?>
                                                    <span class="badge bg-warning text-dark px-2 py-1 rounded small fw-bold"><i class="fas fa-spinner fa-spin me-1"></i> REQUESTED</span>
                                                <?php elseif ($appt['status'] === 'ASSIGNED'): ?>
                                                    <span class="badge bg-dark px-2 py-1 rounded small fw-bold"><i class="fas fa-user-check me-1"></i> AGENT ASSIGNED</span>
                                                <?php elseif ($appt['status'] === 'COMPLETED'): ?>
                                                    <span class="badge bg-success px-2 py-1 rounded small fw-bold"><i class="fas fa-check-circle me-1"></i> COMPLETED</span>
                                                <?php else: ?>
                                                    <span class="badge bg-danger px-2 py-1 rounded small fw-bold"><i class="fas fa-ban me-1"></i> CANCELLED</span>
                                                <?php endif; ?>
                                            </div>
                                        </div>

                                        <div class="bg-light p-3 rounded-3 mb-3 small border-0">
                                            <div class="row text-center text-md-start">
                                                <div class="col-6 mb-2 mb-md-0">
                                                    <span class="text-muted d-block">Target Date</span>
                                                    <strong class="text-dark"><i class="far fa-calendar me-1"></i><?php echo htmlspecialchars($appt['appointment_date']); ?></strong>
                                                </div>
                                                <div class="col-6">
                                                    <span class="text-muted d-block">Allocation Window</span>
                                                    <strong class="text-dark"><i class="far fa-clock me-1"></i><?php echo date('h:i A', strtotime($appt['appointment_time'])); ?></strong>
                                                </div>
                                            </div>
                                        </div>

                                        <?php if (!empty($appt['staff_remarks'])): ?>
                                            <div class="alert alert-secondary border-0 small p-2 mb-3 bg-light text-dark">
                                                <i class="fas fa-comment-dots me-1 text-secondary"></i> <strong>Agent Executive Note:</strong> 
                                                <span class="text-muted italic">"<?php echo htmlspecialchars($appt['staff_remarks']); ?>"</span>
                                            </div>
                                        <?php endif; ?>

                                        <div class="d-flex justify-content-between align-items-center mt-3 pt-2 border-top">
                                            <div>
                                                <?php if ($is_historical): ?>
                                                    <div class="form-check m-0">
                                                        <input class="form-check-input appointment-delete-check" type="checkbox" name="appointment_ids[]" value="<?php echo $appt['appointment_id']; ?>" id="chk_<?php echo $appt['appointment_id']; ?>">
                                                        <label class="form-check-label text-danger tiny fw-bold cursor-pointer" style="user-select: none;" for="chk_<?php echo $appt['appointment_id']; ?>">
                                                            Purge Record
                                                        </label>
                                                    </div>
                                                <?php else: ?>
                                                    <small class="text-success tiny fw-bold"><i class="fas fa-lock me-1"></i> Action Sequence Open</small>
                                                <?php endif; ?>
                                            </div>
                                            
                                            <a href="adjust_appointment.php?type=appointment&id=<?php echo $appt['appointment_id']; ?>" class="btn btn-sm btn-dark fw-bold rounded-pill px-3">
                                                <i class="fas fa-eye me-1"></i> View Details
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php endif; ?>
            </form>
        </div>
    </div>
</div>

<style>
.tiny { font-size: 0.72rem; }
.hover-card { transition: transform 0.2s, box-shadow 0.2s; }
.hover-card:hover { transform: translateY(-2px); box-shadow: 0 .5rem 1.5rem rgba(0,0,0,.08)!important; }
.cursor-pointer { cursor: pointer; }
</style>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
<?php include '../includes/footer.php'; ?>