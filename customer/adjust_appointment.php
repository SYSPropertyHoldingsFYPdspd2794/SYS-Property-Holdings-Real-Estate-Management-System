<?php
/**
 * PROJECT: SYS Property Holdings
 * FILE: customer/adjust_appointment.php
 * DESCRIPTION: US27, US28 & US29 - Enhanced appointment adjustment with absolute validation logic and root-level modal rendering.
 */

session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'CUSTOMER') {
    header("Location: ../login.php");
    exit();
}
include '../includes/db_connect.php';


$account_id = $_SESSION['account_id'];
$type = isset($_GET['type']) ? trim($_GET['type']) : '';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$error = '';
$success = '';

// ACTION HANDLERS FOR SHOWROOM APPOINTMENTS (US28 & US29)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $type === 'appointment') {
    if (isset($_POST['action_type']) && $_POST['action_type'] === 'reschedule') {
        $new_date = $_POST['reschedule_date'] ?? '';
        $new_time = $_POST['reschedule_time'] ?? '';
        $time_db = strlen($new_time) === 5 ? $new_time . ':00' : $new_time;
        
        $appointment_dt = DateTime::createFromFormat('Y-m-d H:i:s', $new_date . ' ' . $time_db);
        $date_errors = DateTime::getLastErrors();
        $valid_datetime = $appointment_dt && ($date_errors === false || ($date_errors['warning_count'] === 0 && $date_errors['error_count'] === 0));
        $today = new DateTime();
        
        // RULE 1: Cannot select a past timestamp configuration
        if (!$valid_datetime || $appointment_dt < $today) {
            $error = "Invalid parameters: Please specify a valid future date and time configuration.";
        } 
        // RULE 2: Operational boundary check (Strictly 10:00 AM - 8:00 PM)
        elseif ($time_db < '10:00:00' || $time_db > '20:00:00') {
            $error = "Outside operation hours: Showroom slots are only open between 10:00 AM and 8:00 PM.";
        } else {
            // RULE 3: Centralized Capacity Check - Max 3 active slots per single date calendar
            $count_stmt = $conn->prepare("SELECT COUNT(*) AS total FROM appointments WHERE appointment_date = ? AND appointment_id != ? AND status NOT IN ('CANCELLED', 'NO_SHOW')");
            $count_stmt->bind_param("si", $new_date, $id);
            $count_stmt->execute();
            $day_total = (int)$count_stmt->get_result()->fetch_assoc()['total'];

            if ($day_total >= 3) {
                $error = "Capacity limit reached: This target date already contains 3 booked sessions. Please opt for another day.";
            } else {
                // RULE 4: Dynamic Buffer Check - Must be strictly spaced at least 2 hours apart
                $conflict_stmt = $conn->prepare("SELECT appointment_time FROM appointments WHERE appointment_date = ? AND appointment_id != ? AND status NOT IN ('CANCELLED', 'NO_SHOW') AND ABS(TIME_TO_SEC(TIMEDIFF(appointment_time, ?))) < 7200 LIMIT 1");
                $conflict_stmt->bind_param("sis", $new_date, $id, $time_db);
                $conflict_stmt->execute();
                $conflict = $conflict_stmt->get_result()->fetch_assoc();

                if ($conflict) {
                    $error = "Scheduling conflict: Another active consultation exists within a 2-hour buffer of your choice.";
                }
            }
        }
        
        // Execute state modification only if all business rules pass inspection safely
        if ($error === '') {
            $up_stmt = $conn->prepare("UPDATE appointments SET appointment_date = ?, appointment_time = ?, status = 'REQUESTED' WHERE appointment_id = ? AND customer_id = ?");
            $up_stmt->bind_param("ssii", $new_date, $time_db, $id, $account_id);
            if ($up_stmt->execute()) {
                $success = "Appointment successfully rescheduled. Status reverted to REQUESTED.";
            } else {
                $error = "Database failure: Unable to modify appointment records.";
            }
        }
    } elseif (isset($_POST['action_type']) && $_POST['action_type'] === 'cancel') {
        $can_stmt = $conn->prepare("UPDATE appointments SET status = 'CANCELLED' WHERE appointment_id = ? AND customer_id = ? AND status IN ('REQUESTED', 'ASSIGNED') AND TIMESTAMP(appointment_date, appointment_time) > NOW()");
        $can_stmt->bind_param("ii", $id, $account_id);
        if ($can_stmt->execute() && $can_stmt->affected_rows > 0) {
            $success = "Appointment successfully terminated and flagged as CANCELLED.";
        } else {
            $error = "This appointment cannot be cancelled because the appointment time has passed or the status has changed.";
        }
    }
}

// FETCH REAL-TIME UPDATED DATA DATA 
$data = null;
if ($type === 'appointment') {
    $stmt = $conn->prepare("SELECT a.*, p.project_name, p.state, p.price, p.property_code FROM appointments a JOIN properties p ON a.property_id = p.property_id WHERE a.appointment_id = ? AND a.customer_id = ?");
    $stmt->bind_param("ii", $id, $account_id);
    $stmt->execute();
    $data = $stmt->get_result()->fetch_assoc();
} elseif ($type === 'housing') {
    $stmt = $conn->prepare("SELECT ah.*, p.project_name, p.state, p.price, p.property_code, p.income_limit_rm FROM affordable_housing_applications ah JOIN properties p ON ah.property_id = p.property_id WHERE ah.application_id = ? AND ah.customer_id = ?");
    $stmt->bind_param("ii", $id, $account_id);
    $stmt->execute();
    $data = $stmt->get_result()->fetch_assoc();
}

if (!$data) {
    header("Location: track_status.php");
    exit();
}

$appointmentDateTime = null;
$canCancelAppointment = false;
if ($type === 'appointment') {
    $appointmentDateTime = DateTime::createFromFormat('Y-m-d H:i:s', $data['appointment_date'] . ' ' . $data['appointment_time']);
    $canCancelAppointment = in_array($data['status'], ['REQUESTED', 'ASSIGNED'], true) && $appointmentDateTime && $appointmentDateTime > new DateTime();
}

include '../includes/header.php';
?>
<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-7">
            
            <?php if ($error !== ''): ?>
                <div class="alert alert-danger fw-bold shadow-sm mb-4"><i class="fas fa-exclamation-triangle me-2"></i><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            <?php if ($success !== ''): ?>
                <div class="alert alert-success fw-bold shadow-sm mb-4"><i class="fas fa-check-circle me-2"></i><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>

            <div class="card border-0 shadow-lg rounded-4 overflow-hidden position-relative p-4 reveal-card" style="background: #fff; border-top: 8px solid #212529 !important;">
                <div class="text-center my-4 border-bottom pb-4">
                    <i class="fas fa-file-contract fa-3x text-dark mb-3"></i>
                    <h3 class="fw-bold text-uppercase tracking-wider m-0"><?php echo $type === 'appointment' ? 'Booking Voucher' : 'Application Summary'; ?></h3>
                    <small class="text-muted text-uppercase font-monospace d-block mt-2">ID Reference: #<?php echo $type === 'appointment' ? 'APT-'.$data['appointment_id'] : 'HOU-'.$data['application_id']; ?></small>
                </div>

                <div class="p-2 font-monospace fs-6">
                    <div class="d-flex justify-content-between mb-3">
                        <span class="text-muted"><i class="fas fa-building me-2"></i>Project Asset:</span>
                        <span class="fw-bold text-dark text-end" style="max-width: 60%;"><?php echo htmlspecialchars($data['project_name']); ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-3">
                        <span class="text-muted"><i class="fas fa-fingerprint me-2"></i>Property Code:</span>
                        <span class="fw-bold text-dark"><?php echo htmlspecialchars($data['property_code']); ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-3">
                        <span class="text-muted"><i class="fas fa-map-marker-alt me-2"></i>State Boundary:</span>
                        <span class="fw-bold text-dark"><?php echo htmlspecialchars($data['state']); ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-3">
                        <span class="text-muted"><i class="fas fa-tags me-2"></i>Valuation Price:</span>
                        <span class="fw-bold text-success">RM <?php echo number_format($data['price'], 2); ?></span>
                    </div>
                    
                    <hr class="border-secondary my-4 border-dashed">

                    <?php if ($type === 'appointment'): ?>
                        <div class="d-flex justify-content-between mb-3">
                            <span class="text-muted"><i class="fas fa-concierge-bell me-2"></i>Service Vector:</span>
                            <span class="fw-bold text-dark"><?php echo str_replace('_', ' ', htmlspecialchars($data['service_type'])); ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-3">
                            <span class="text-muted"><i class="far fa-calendar me-2"></i>Execution Date:</span>
                            <span class="fw-bold text-dark"><?php echo htmlspecialchars(date('d M Y', strtotime($data['appointment_date']))); ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-3">
                            <span class="text-muted"><i class="far fa-clock me-2"></i>Execution Time:</span>
                            <span class="fw-bold text-dark"><?php echo htmlspecialchars(date('h:i A', strtotime($data['appointment_time']))); ?></span>
                        </div>
                    <?php else: ?>
                        <div class="d-flex justify-content-between mb-3">
                            <span class="text-muted"><i class="fas fa-id-card me-2"></i>Income Cap Limit:</span>
                            <span class="fw-bold text-danger">RM <?php echo number_format($data['income_limit_rm'], 2); ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-3">
                            <span class="text-muted"><i class="far fa-clock me-2"></i>Logged Timestamp:</span>
                            <span class="fw-bold text-dark"><?php echo htmlspecialchars(date('d M Y, h:i A', strtotime($data['application_date']))); ?></span>
                        </div>
                    <?php endif; ?>

                    <div class="d-flex justify-content-between align-items-center mb-3 mt-4 p-3 bg-light rounded border">
                        <span class="text-muted fw-bold m-0"><i class="fas fa-info-circle me-2"></i>Current Status:</span>
                        <?php
                            $badge = 'secondary';
                            if (in_array($data['status'], ['REQUESTED', 'PENDING', 'PENDING_REVIEW'])) $badge = 'warning text-dark';
                            if (in_array($data['status'], ['ASSIGNED', 'APPROVED_FOR_DRAW'])) $badge = 'primary';
                            if (in_array($data['status'], ['COMPLETED', 'WINNER'])) $badge = 'success';
                            if (in_array($data['status'], ['CANCELLED', 'REJECTED', 'NO_SHOW'])) $badge = 'danger';
                        ?>
                        <span class="badge bg-<?php echo $badge; ?> fs-6 px-3 py-2"><?php echo htmlspecialchars(str_replace('_', ' ', $data['status'])); ?></span>
                    </div>
                </div>

                <?php if ($type === 'appointment' && !in_array($data['status'], ['CANCELLED', 'COMPLETED', 'NO_SHOW'])): ?>
                    <div class="row g-3 mt-4 pt-2 border-top">
                        <div class="<?php echo $canCancelAppointment ? 'col-6' : 'col-12'; ?>">
                            <button type="button" class="btn btn-outline-dark btn-lg w-100 fw-bold py-3 fs-6 rounded-pill" data-bs-toggle="modal" data-bs-target="#rescheduleModal">
                                <i class="fas fa-clock-rotate-left me-2"></i>Reschedule
                            </button>
                        </div>
                        <?php if ($canCancelAppointment): ?>
                            <div class="col-6">
                                <form method="POST" onsubmit="return confirm('ARE YOU SURE WANT TO CANCEL?');" class="m-0">
                                    <input type="hidden" name="action_type" value="cancel">
                                    <button type="submit" class="btn btn-danger btn-lg w-100 fw-bold py-3 fs-6 rounded-pill text-white shadow-sm">
                                        <i class="fas fa-ban me-2"></i>Cancel Slot
                                    </button>
                                </form>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <div class="text-center mt-5 pt-3 border-top border-dashed">
                    <a href="track_status.php" class="btn btn-sm btn-link text-decoration-none text-muted fw-bold"><i class="fas fa-arrow-left me-2"></i>Back to Tracker Matrix</a>
                </div>
            </div>

        </div>
    </div>
</div>

<?php if ($type === 'appointment' && !in_array($data['status'], ['CANCELLED', 'COMPLETED', 'NO_SHOW'])): ?>
<div class="modal fade" id="rescheduleModal" tabindex="-1" aria-labelledby="rescheduleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow-lg">
            <div class="modal-header bg-dark text-white p-4">
                <h5 class="modal-title fw-bold m-0" id="rescheduleModalLabel"><i class="fas fa-calendar-alt me-2"></i>Reschedule Selection Matrix</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" class="m-0">
                <input type="hidden" name="action_type" value="reschedule">
                <div class="modal-body p-4">
                    <div class="mb-4">
                        <label class="form-label fw-bold">Select New Execution Date</label>
                        <input type="date" name="reschedule_date" class="form-control form-control-lg bg-light" required min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>" value="<?php echo htmlspecialchars($data['appointment_date'] ?? ''); ?>">
                    </div>
                    <div class="mb-2">
                        <label class="form-label fw-bold">Select New Execution Time</label>
                        <input type="time" name="reschedule_time" class="form-control form-control-lg bg-light" required min="10:00" max="20:00" step="1800" value="<?php echo htmlspecialchars(substr($data['appointment_time'] ?? '', 0, 5)); ?>">
                        <small class="text-muted d-block mt-2">Permitted operations framework: 10:00 AM - 8:00 PM. System forces 2-hour conflict resolution buffers.</small>
                    </div>
                </div>
                <div class="modal-footer bg-light p-3 border-top rounded-bottom-4">
                    <button type="button" class="btn btn-outline-secondary px-4 rounded-pill fw-bold" data-bs-dismiss="modal">Dismiss</button>
                    <button type="submit" class="btn btn-dark px-4 rounded-pill fw-bold">Save Configuration</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<style>
    @keyframes driftUp {
        0% { opacity: 0; transform: translateY(30px); }
        100% { opacity: 1; transform: translateY(0); }
    }
    .reveal-card { animation: driftUp 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
    .border-dashed { border-style: dashed !important; }
</style>
