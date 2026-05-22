<?php
/**
 * PROJECT: SYS Property Holdings
 * FILE: customer/adjust_appointment.php
 * DESCRIPTION: US27, US28 & US29 - Enhanced split-logic context builder with state locks, double-confirmation alerts, and extended applicant profiles.
 */

session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'CUSTOMER') {
    header("Location: ../login.php");
    exit();
}
include '../includes/db_connect.php';
include_once '../includes/functions.php';

$account_id = $_SESSION['account_id'];
$type = isset($_GET['type']) ? trim($_GET['type']) : '';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$error = '';
$success = '';

function delete_customer_document_file($file_path) {
    $path = str_replace('\\', '/', trim((string)$file_path));
    $path = preg_replace('#^\./+#', '', $path);
    $path = preg_replace('#^/+#', '', $path);
    while (strpos($path, '../') === 0) {
        $path = substr($path, 3);
    }

    if ($path === '' || (strpos($path, 'storage/docs/') !== 0 && strpos($path, 'uploads/') !== 0)) {
        return;
    }

    $project_root = realpath(__DIR__ . '/..');
    $full_path = realpath($project_root . '/' . $path);
    if ($project_root && $full_path && strpos($full_path, $project_root) === 0 && is_file($full_path)) {
        unlink($full_path);
    }
}

function customer_owns_record($conn, $type, $id, $account_id) {
    if ($type === 'appointment') {
        $stmt = $conn->prepare("SELECT appointment_id FROM appointments WHERE appointment_id = ? AND customer_id = ? LIMIT 1");
    } elseif ($type === 'housing') {
        $stmt = $conn->prepare("SELECT application_id FROM affordable_housing_applications WHERE application_id = ? AND customer_id = ? LIMIT 1");
    } else {
        return false;
    }

    $stmt->bind_param("ii", $id, $account_id);
    $stmt->execute();
    return $stmt->get_result()->num_rows > 0;
}

function customer_appointment_status($conn, $id, $account_id) {
    $stmt = $conn->prepare("SELECT status FROM appointments WHERE appointment_id = ? AND customer_id = ? LIMIT 1");
    $stmt->bind_param("ii", $id, $account_id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();

    return $row ? $row['status'] : null;
}

function document_meta_for_type($type) {
    if ($type === 'appointment') {
        return ['APPOINTMENT', 'PAYSLIP_SUMMARY', 'appt'];
    }
    if ($type === 'housing') {
        return ['APPLICATION', 'EPF_STATEMENT_SUMMARY', 'app'];
    }
    return [null, null, null];
}

// BACKEND DATA FETCH WITH EXTENDED APPLICANT PROFILE MATRICES
$data = null;
if ($type === 'appointment') {
    $stmt = $conn->prepare("SELECT a.*, p.project_name, p.state, p.price, p.property_code FROM appointments a JOIN properties p ON a.property_id = p.property_id WHERE a.appointment_id = ? AND a.customer_id = ?");
    $stmt->bind_param("ii", $id, $account_id);
    $stmt->execute();
    $data = $stmt->get_result()->fetch_assoc();
} elseif ($type === 'housing') {
    $stmt = $conn->prepare("SELECT ah.*, p.project_name, p.state, p.price, p.property_code, p.income_limit_rm, c.marital_status, c.dependents_count, c.occupation 
                            FROM affordable_housing_applications ah 
                            JOIN properties p ON ah.property_id = p.property_id 
                            JOIN customers c ON ah.customer_id = c.customer_id
                            WHERE ah.application_id = ? AND ah.customer_id = ?");
    $stmt->bind_param("ii", $id, $account_id);
    $stmt->execute();
    $data = $stmt->get_result()->fetch_assoc();
}

if (!$data) {
    header("Location: track_status.php");
    exit();
}

// STATE VALIDATION AND LOCK SECURITY SETUPS
$current_status = $data['status'];
$allow_document_adjustment = false;

if ($type === 'appointment') {
    // Showroom Appointments allow uploads during REQUESTED, PENDING, or ASSIGNED statuses. Locked if COMPLETED/NO_SHOW.
    if (in_array($current_status, ['REQUESTED', 'PENDING', 'ASSIGNED'], true)) {
        $allow_document_adjustment = true;
    }
} elseif ($type === 'housing') {
    // Housing Applications STRICTLY allow document updates only during PENDING_REVIEW status. Locked for APPROVED_FOR_DRAW or REJECTED.
    if ($current_status === 'PENDING_REVIEW') {
        $allow_document_adjustment = true;
    }
}

// HANDLE DOCUMENT MUTATION REQUESTS (UPLOAD / RESEND / RE-UPLOAD)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && in_array($_POST['action_type'] ?? '', ['resend_document', 'delete_document'], true)) {
    [$related_type, $document_type, $file_prefix] = document_meta_for_type($type);

    $appointment_status = null;
    if ($type === 'appointment') {
        $appointment_status = customer_appointment_status($conn, $id, $account_id);
    }

    if (!$related_type || !customer_owns_record($conn, $type, $id, $account_id)) {
        $error = "Document action is not available for this record.";
<<<<<<< HEAD
    } elseif ($type === 'appointment' && !in_array($appointment_status, ['REQUESTED', 'ASSIGNED'], true)) {
        $error = "This appointment document cannot be adjusted because the appointment is already completed or closed.";
=======
    } elseif (!$allow_document_adjustment) {
        $error = "Action Denied: Current record state constraints do not permit modifying document payloads.";
>>>>>>> a569b1048510cbb6f153689057ce94d22d449514
    } elseif ($_POST['action_type'] === 'delete_document') {
        $doc_stmt = $conn->prepare("SELECT document_id, file_path FROM documents WHERE customer_id = ? AND related_to_type = ? AND related_to_id = ? AND is_purged = FALSE");
        $doc_stmt->bind_param("isi", $account_id, $related_type, $id);
        $doc_stmt->execute();
        $docs = $doc_stmt->get_result();

        while ($doc = $docs->fetch_assoc()) {
            delete_customer_document_file($doc['file_path']);
        }

        $delete_stmt = $conn->prepare("DELETE FROM documents WHERE customer_id = ? AND related_to_type = ? AND related_to_id = ?");
        $delete_stmt->bind_param("isi", $account_id, $related_type, $id);
        if ($delete_stmt->execute() && $delete_stmt->affected_rows > 0) {
            $success = "Document removed from system log registry.";
        } else {
            $error = "No active file payload was detected to purge.";
        }
    } (!isset($_FILES['document']) || $_FILES['document']['error'] !== UPLOAD_ERR_OK) {
        $error = "Please attach a valid PDF binary file data stream.";
    } else {
        $tmp_name = $_FILES['document']['tmp_name'];
        $name = basename($_FILES['document']['name']);
        $size = $_FILES['document']['size'];
        $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $tmp_name);
        finfo_close($finfo);

        if ($ext !== 'pdf' || $mime !== 'application/pdf' || $size > 5242880) {
            $error = "Invalid format: Only standard PDF documents under 5MB are accepted.";
        } else {
            $upload_dir = '../storage/docs/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }

            $file_name = $file_prefix . "_" . $id . "_" . time() . ".pdf";
            $target_file = $upload_dir . $file_name;
            $db_path = "storage/docs/" . $file_name;

            $conn->begin_transaction();
            try {
                $old_stmt = $conn->prepare("SELECT file_path FROM documents WHERE customer_id = ? AND related_to_type = ? AND related_to_id = ? AND is_purged = FALSE");
                $old_stmt->bind_param("isi", $account_id, $related_type, $id);
                $old_stmt->execute();
                $old_docs = $old_stmt->get_result();

                if (!move_uploaded_file($tmp_name, $target_file)) {
                    throw new Exception("Unable to save file data to storage arrays.");
                }

                while ($old_doc = $old_docs->fetch_assoc()) {
                    delete_customer_document_file($old_doc['file_path']);
                }

                $delete_old = $conn->prepare("DELETE FROM documents WHERE customer_id = ? AND related_to_type = ? AND related_to_id = ?");
                $delete_old->bind_param("isi", $account_id, $related_type, $id);
                $delete_old->execute();

                $insert_doc = $conn->prepare("INSERT INTO documents (customer_id, related_to_type, related_to_id, document_type, file_path, is_purged) VALUES (?, ?, ?, ?, ?, FALSE)");
                $insert_doc->bind_param("isiss", $account_id, $related_type, $id, $document_type, $db_path);
                $insert_doc->execute();

<<<<<<< HEAD
                if ($type === 'appointment' && $appointment_status === 'ASSIGNED') {
                    $status_stmt = $conn->prepare("UPDATE appointments SET assigned_staff_id = NULL, status = 'REQUESTED', staff_remarks = NULL WHERE appointment_id = ? AND customer_id = ? AND status = 'ASSIGNED'");
                    $status_stmt->bind_param("ii", $id, $account_id);
                    $status_stmt->execute();
                }

                $conn->commit();
                $success = ($type === 'appointment' && $appointment_status === 'ASSIGNED')
                    ? "Document resent successfully. Appointment status has returned to REQUESTED and awaits staff assignment."
                    : "Document resent successfully.";
=======
                // DYNAMIC APPOINTMENT REVERSION: If an ASSIGNED appointment uploads a new file, force revert to REQUESTED
                if ($type === 'appointment' && $current_status === 'ASSIGNED') {
                    $revert_stmt = $conn->prepare("UPDATE appointments SET status = 'REQUESTED', assigned_staff_id = NULL, staff_remarks = NULL WHERE appointment_id = ?");
                    $revert_stmt->bind_param("i", $id);
                    $revert_stmt->execute();
                    $current_status = 'REQUESTED';
                }

                $conn->commit();
                header("Location: adjust_appointment.php?type=" . urlencode($type) . "&id=" . $id . "&success_msg=uploaded");
                exit();
>>>>>>> a569b1048510cbb6f153689057ce94d22d449514
            } catch (Throwable $e) {
                $conn->rollback();
                if (file_exists($target_file)) {
                    unlink($target_file);
                }
                $error = "Transaction aborted. Unable to complete payload synchronization.";
            }
        }
    }
}

if (isset($_GET['success_msg']) && $_GET['success_msg'] === 'uploaded') {
    $success = "Document uploaded successfully. System records have been updated.";
}

// ACTION HANDLERS FOR SHOWROOM APPOINTMENTS RESCHEDULING & CANCELLATION
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $type === 'appointment') {
    if (isset($_POST['action_type']) && $_POST['action_type'] === 'reschedule') {
        $new_date = $_POST['reschedule_date'] ?? '';
        $new_time = $_POST['reschedule_time'] ?? '';
        $time_db = strlen($new_time) === 5 ? $new_time . ':00' : $new_time;

        $current_stmt = $conn->prepare("SELECT appointment_date, appointment_time, status FROM appointments WHERE appointment_id = ? AND customer_id = ? LIMIT 1");
        $current_stmt->bind_param("ii", $id, $account_id);
        $current_stmt->execute();
        $current_appt = $current_stmt->get_result()->fetch_assoc();
        
        $appointment_dt = DateTime::createFromFormat('Y-m-d H:i:s', $new_date . ' ' . $time_db);
        $date_errors = DateTime::getLastErrors();
        $valid_datetime = $appointment_dt && ($date_errors === false || ($date_errors['warning_count'] === 0 && $date_errors['error_count'] === 0));
        $today = new DateTime();
        $current_dt = $current_appt ? DateTime::createFromFormat('Y-m-d H:i:s', $current_appt['appointment_date'] . ' ' . $current_appt['appointment_time']) : null;
        
        if (!$current_appt || !in_array($current_appt['status'], ['REQUESTED', 'ASSIGNED'], true) || !$current_dt || $current_dt <= $today) {
            $error = "Reschedule unavailable. This slot has either expired or been closed by administrative actions.";
        } elseif (!$valid_datetime || $appointment_dt < $today) {
            $error = "Invalid parameters: Please choose a valid target scheduling slot timeline.";
        } elseif ($current_appt['appointment_date'] === $new_date && substr($current_appt['appointment_time'], 0, 5) === substr($time_db, 0, 5)) {
            $error = "No modification detected. Please specify a new scheduling vector context.";
        } elseif ($time_db < '10:00:00' || $time_db > '20:00:00') {
            $error = "Outside operations framework. Active booking hours range exclusively between 10:00 AM and 8:00 PM.";
        } else {
            $count_stmt = $conn->prepare("SELECT COUNT(*) AS total FROM appointments WHERE appointment_date = ? AND appointment_id != ? AND status NOT IN ('CANCELLED', 'NO_SHOW')");
            $count_stmt->bind_param("si", $new_date, $id);
            $count_stmt->execute();
            $day_total = (int)$count_stmt->get_result()->fetch_assoc()['total'];

            if ($day_total >= 3) {
                $error = "Capacity threshold reached. Selected calendar date contains 3 active client assignments.";
            } else {
                $conflict_stmt = $conn->prepare("SELECT appointment_time FROM appointments WHERE appointment_date = ? AND appointment_id != ? AND status NOT IN ('CANCELLED', 'NO_SHOW') AND ABS(TIME_TO_SEC(TIMEDIFF(appointment_time, ?))) < 7200 LIMIT 1");
                $conflict_stmt->bind_param("sis", $new_date, $id, $time_db);
                $conflict_stmt->execute();
                $conflict = $conflict_stmt->get_result()->fetch_assoc();

                if ($conflict) {
                    $error = "Buffer clash: Another showroom meeting exists within a 2-hour radial window of this timestamp.";
                }
            }
        }
        
        if ($error === '') {
            $up_stmt = $conn->prepare("UPDATE appointments SET assigned_staff_id = NULL, appointment_date = ?, appointment_time = ?, status = 'REQUESTED', staff_remarks = NULL WHERE appointment_id = ? AND customer_id = ? AND status IN ('REQUESTED', 'ASSIGNED') AND TIMESTAMP(appointment_date, appointment_time) > NOW()");
            $up_stmt->bind_param("ssii", $new_date, $time_db, $id, $account_id);
            if ($up_stmt->execute() && $up_stmt->affected_rows > 0) {
                $success = "Appointment successfully rescheduled. Status reverted to REQUESTED and awaits staff assignment.";
                $current_status = 'REQUESTED';
            } else {
                $error = "System write conflict. Unable to modify appointment lifecycle configuration.";
            }
        }
    } elseif (isset($_POST['action_type']) && $_POST['action_type'] === 'cancel') {
        $can_stmt = $conn->prepare("UPDATE appointments SET status = 'CANCELLED' WHERE appointment_id = ? AND customer_id = ? AND status IN ('REQUESTED', 'ASSIGNED') AND TIMESTAMP(appointment_date, appointment_time) > NOW()");
        $can_stmt->bind_param("ii", $id, $account_id);
        if ($can_stmt->execute() && $can_stmt->affected_rows > 0) {
            $success = "Appointment slot revoked and status flagged as CANCELLED.";
            $current_status = 'CANCELLED';
        } else {
            $error = "Cancellation criteria mismatch. Expired or completed logs cannot be adjusted.";
        }
    }
}

$appointmentDateTime = null;
$canCancelAppointment = false;
$canRescheduleAppointment = false;
if ($type === 'appointment') {
    $appointmentDateTime = DateTime::createFromFormat('Y-m-d H:i:s', $data['appointment_date'] . ' ' . $data['appointment_time']);
    $canCancelAppointment = in_array($current_status, ['REQUESTED', 'ASSIGNED'], true) && $appointmentDateTime && $appointmentDateTime > new DateTime();
    $canRescheduleAppointment = $canCancelAppointment;
}

[$current_related_type] = document_meta_for_type($type);
$current_document = null;
if ($current_related_type) {
    $doc_stmt = $conn->prepare("SELECT document_id, document_type, file_path, uploaded_at FROM documents WHERE customer_id = ? AND related_to_type = ? AND related_to_id = ? AND is_purged = FALSE ORDER BY uploaded_at DESC, document_id DESC LIMIT 1");
    $doc_stmt->bind_param("isi", $account_id, $current_related_type, $id);
    $doc_stmt->execute();
    $current_document = $doc_stmt->get_result()->fetch_assoc();
}

$canManageDocument = true;
if ($type === 'appointment') {
    $canManageDocument = in_array($data['status'], ['REQUESTED', 'ASSIGNED'], true);
}

include '../includes/header.php';
?>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
                    <i class="<?php echo $type === 'appointment' ? 'fas fa-file-contract' : 'fas fa-home'; ?> fa-3x text-dark mb-3"></i>
                    <h3 class="fw-bold text-uppercase tracking-wider m-0"><?php echo $type === 'appointment' ? 'Booking Voucher' : 'Application Summary'; ?></h3>
                    <small class="text-muted text-uppercase font-monospace d-block mt-2">ID Reference: #<?php echo $type === 'appointment' ? 'APT-'.$data['appointment_id'] : 'HOU-'.$data['application_id']; ?></small>
                </div>

                <div class="p-2 font-monospace fs-6">
                    <h5 class="fw-bold mb-3 text-secondary text-uppercase border-bottom pb-2 small"><i class="fas fa-hotel me-2"></i>Property Blueprint Details</h5>
                    <div class="d-flex justify-content-between mb-3">
                        <span class="text-muted">Project Asset:</span>
                        <span class="fw-bold text-dark text-end" style="max-width: 60%;"><?php echo htmlspecialchars($data['project_name']); ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-3">
                        <span class="text-muted">Property Code:</span>
                        <span class="fw-bold text-dark"><?php echo htmlspecialchars($data['property_code']); ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-3">
                        <span class="text-muted">State Boundary:</span>
                        <span class="fw-bold text-dark"><?php echo htmlspecialchars($data['state']); ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-3">
                        <span class="text-muted">Valuation Price:</span>
                        <span class="fw-bold text-success">RM <?php echo number_format($data['price'], 2); ?></span>
                    </div>
                    
                    <?php if ($type === 'housing'): ?>
                        <h5 class="fw-bold mb-3 text-secondary text-uppercase border-bottom pb-2 pt-3 small"><i class="fas fa-user-tie me-2"></i>Applicant Demographics</h5>
                        <div class="d-flex justify-content-between mb-3">
                            <span class="text-muted">Marital Profile:</span>
                            <span class="fw-bold text-dark"><?php echo htmlspecialchars($data['marital_status']); ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-3">
                            <span class="text-muted">Dependents:</span>
                            <span class="fw-bold text-dark"><?php echo htmlspecialchars($data['dependents_count']); ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-3">
                            <span class="text-muted">Occupation:</span>
                            <span class="fw-bold text-dark"><?php echo htmlspecialchars($data['occupation']); ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-3">
                            <span class="text-muted">Income Limit Ceiling:</span>
                            <span class="fw-bold text-danger">RM <?php echo number_format($data['income_limit_rm'], 2); ?></span>
                        </div>
                    <?php endif; ?>

                    <h5 class="fw-bold mb-3 text-secondary text-uppercase border-bottom pb-2 pt-3 small"><i class="fas fa-exchange-alt me-2"></i>Transaction Parameters</h5>
                    <?php if ($type === 'appointment'): ?>
                        <div class="d-flex justify-content-between mb-3">
                            <span class="text-muted">Service Vector:</span>
                            <span class="fw-bold text-dark"><?php echo str_replace('_', ' ', htmlspecialchars($data['service_type'])); ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-3">
                            <span class="text-muted">Execution Date:</span>
                            <span class="fw-bold text-dark"><?php echo htmlspecialchars(date('d M Y', strtotime($data['appointment_date']))); ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-3">
                            <span class="text-muted">Execution Time:</span>
                            <span class="fw-bold text-dark"><?php echo htmlspecialchars(date('h:i A', strtotime($data['appointment_time']))); ?></span>
                        </div>
                    <?php else: ?>
                        <div class="d-flex justify-content-between mb-3">
                            <span class="text-muted">Logged Timestamp:</span>
                            <span class="fw-bold text-dark"><?php echo htmlspecialchars(date('d M Y, h:i A', strtotime($data['application_date']))); ?></span>
                        </div>
                    <?php endif; ?>

                    <div class="d-flex justify-content-between align-items-center mb-3 mt-4 p-3 bg-light rounded border">
                        <span class="text-muted fw-bold m-0"><i class="fas fa-info-circle me-2"></i>Current Status:</span>
                        <?php
                            $badge = 'secondary';
                            if (in_array($current_status, ['REQUESTED', 'PENDING', 'PENDING_REVIEW'])) $badge = 'warning text-dark';
                            if (in_array($current_status, ['ASSIGNED', 'APPROVED_FOR_DRAW'])) $badge = 'primary';
                            if (in_array($current_status, ['COMPLETED', 'WINNER'])) $badge = 'success';
                            if (in_array($current_status, ['CANCELLED', 'REJECTED', 'NO_SHOW'])) $badge = 'danger';
                        ?>
                        <span class="badge bg-<?php echo $badge; ?> fs-6 px-3 py-2 text-uppercase"><?php echo htmlspecialchars(str_replace('_', ' ', $current_status)); ?></span>
                    </div>

                    <div class="mt-4 p-3 bg-light rounded border">
                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
                            <div>
                                <span class="text-muted fw-bold d-block"><i class="fas fa-file-pdf me-2"></i>Customer Document Abstract</span>
                                <?php if ($current_document): ?>
                                    <small class="text-muted">Uploaded <?php echo htmlspecialchars(date('d M Y, h:i A', strtotime($current_document['uploaded_at']))); ?></small>
                                <?php else: ?>
                                    <small class="text-muted">No document currently attached.</small>
                                <?php endif; ?>
                            </div>
                            <?php if ($current_document): ?>
                                <a href="<?php echo htmlspecialchars(document_public_url($current_document['file_path'], '../')); ?>" target="_blank" class="btn btn-outline-primary btn-sm rounded-pill fw-bold px-3">
                                    <i class="fas fa-eye me-2"></i>View
                                </a>
                            <?php endif; ?>
                        </div>

<<<<<<< HEAD
                        <?php if ($canManageDocument): ?>
                            <form method="POST" enctype="multipart/form-data" id="resendDocumentForm" class="mb-3">
                                <input type="hidden" name="action_type" value="resend_document">
                                <label class="form-label fw-bold"><?php echo $current_document ? 'Resend / Replace Document' : 'Send Document'; ?></label>
=======
                        <?php if ($allow_document_adjustment): ?>
                            <form method="POST" enctype="multipart/form-data" id="resendDocumentForm" class="mb-3">
                                <input type="hidden" name="action_type" value="resend_document">
                                <label class="form-label fw-bold"><?php echo $current_document ? 'Resend / Replace Document' : 'Upload Document'; ?></label>
>>>>>>> a569b1048510cbb6f153689057ce94d22d449514
                                <div class="input-group">
                                    <input type="file" name="document" class="form-control" accept="application/pdf" required>
                                    <button type="submit" class="btn btn-dark fw-bold">
                                        <i class="fas fa-paper-plane me-2"></i><?php echo $current_document ? 'Resend' : 'Upload'; ?>
                                    </button>
                                </div>
<<<<<<< HEAD
                                <small class="text-muted d-block mt-2">PDF only, max 5MB.</small>
                            </form>
                        <?php else: ?>
                            <div class="alert alert-secondary fw-bold mb-3">
                                <i class="fas fa-lock me-2"></i>This appointment is completed or closed. Customer document changes are no longer allowed.
                            </div>
                        <?php endif; ?>

                        <?php if ($current_document && $canManageDocument): ?>
                            <form method="POST" id="deleteDocumentForm" class="m-0">
                                <input type="hidden" name="action_type" value="delete_document">
                                <button type="button" id="deleteDocumentBtn" class="btn btn-outline-danger btn-sm rounded-pill fw-bold px-4">
                                    <i class="fas fa-trash-alt me-2"></i>Delete Document
                                </button>
=======
                                <small class="text-muted d-block mt-2">PDF formats only, maximum weight boundary: 5MB.</small>
>>>>>>> a569b1048510cbb6f153689057ce94d22d449514
                            </form>

                            <?php if ($current_document): ?>
                                <form method="POST" id="deleteDocumentForm" class="m-0">
                                    <input type="hidden" name="action_type" value="delete_document">
                                    <button type="button" id="deleteDocumentBtn" class="btn btn-outline-danger btn-sm rounded-pill fw-bold px-4">
                                        <i class="fas fa-trash-alt me-2"></i>Delete Document
                                    </button>
                                </form>
                            <?php endif; ?>
                        <?php else: ?>
                            <div class="p-2 text-center text-muted bg-white border rounded">
                                <i class="fas fa-lock me-2 text-warning"></i>Adjustments locked for current tracking status.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <?php if ($type === 'appointment' && ($canRescheduleAppointment || $canCancelAppointment)): ?>
                    <div class="row g-3 mt-4 pt-2 border-top">
                        <?php if ($canRescheduleAppointment): ?>
                            <div class="<?php echo $canCancelAppointment ? 'col-6' : 'col-12'; ?>">
                                <button type="button" class="btn btn-outline-dark btn-lg w-100 fw-bold py-3 fs-6 rounded-pill" data-bs-toggle="modal" data-bs-target="#rescheduleModal">
                                    <i class="fas fa-clock-rotate-left me-2"></i>Reschedule
                                </button>
                            </div>
                        <?php endif; ?>
                        <?php if ($canCancelAppointment): ?>
                            <div class="col-6">
                                <form method="POST" id="cancelAppointmentForm" class="m-0">
                                    <input type="hidden" name="action_type" value="cancel">
                                    <button type="button" id="cancelAppointmentBtn" class="btn btn-danger btn-lg w-100 fw-bold py-3 fs-6 rounded-pill text-white shadow-sm">
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

<?php if ($type === 'appointment' && $canRescheduleAppointment): ?>
<div class="modal fade" id="rescheduleModal" tabindex="-1" aria-labelledby="rescheduleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow-lg">
            <div class="modal-header bg-dark text-white p-4">
                <h5 class="modal-title fw-bold m-0" id="rescheduleModalLabel"><i class="fas fa-calendar-alt me-2"></i>Reschedule Selection Matrix</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" id="rescheduleAppointmentForm" class="m-0">
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

<script { sandbox: 'allow-scripts' }>
const currentRecordType = <?php echo json_encode($type); ?>;
const appointmentStatus = <?php echo json_encode($current_status); ?>;
const documentHasCurrent = <?php echo $current_document ? 'true' : 'false'; ?>;

const resendDocumentForm = document.getElementById('resendDocumentForm');
if (resendDocumentForm) {
    resendDocumentForm.addEventListener('submit', function (event) {
        event.preventDefault();
        
        // CUSTOM ENHANCEMENT Matrix logic tracking for Showroom Appointment double confirmations
        if (currentRecordType === 'appointment' && appointmentStatus === 'ASSIGNED') {
            Swal.fire({
                icon: 'warning',
                title: 'Re-upload Document File?',
                text: 'Warning: Uploading a new document will reset your appointment status to REQUESTED. Your currently assigned staff member will be unlinked, and you must wait for a re-assignment.',
                showCancelButton: true,
                confirmButtonText: 'Yes, upload & reset',
                cancelButtonText: 'Cancel',
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    resendDocumentForm.submit();
                }
            });
        } else {
            // Default safe modal prompt loop
            Swal.fire({
                icon: 'question',
                title: 'Confirm File Upload?',
                text: documentHasCurrent ? 'This action will replace your current submitted tracking document.' : 'This document will be securely attached to this record.',
                showCancelButton: true,
                confirmButtonText: 'Proceed Upload',
                cancelButtonText: 'Dismiss',
                confirmButtonColor: '#212529',
                cancelButtonColor: '#6c757d',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    resendDocumentForm.submit();
                }
            });
        }
    });
}

const deleteDocumentBtn = document.getElementById('deleteDocumentBtn');
if (deleteDocumentBtn) {
    deleteDocumentBtn.addEventListener('click', function () {
        Swal.fire({
            icon: 'warning',
            title: 'Delete Document?',
            text: 'Are you sure you want to permanently clear this file data from the system database registries?',
            showCancelButton: true,
            confirmButtonText: 'Yes, delete',
            cancelButtonText: 'No',
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('deleteDocumentForm').submit();
            }
        });
    });
}

const rescheduleAppointmentForm = document.getElementById('rescheduleAppointmentForm');
if (rescheduleAppointmentForm) {
    rescheduleAppointmentForm.addEventListener('submit', function (event) {
        event.preventDefault();
        Swal.fire({
            icon: 'question',
            title: 'Reschedule Appointment?',
            text: 'Your appointment will return to REQUESTED status and wait for staff assignment again.',
            showCancelButton: true,
            confirmButtonText: 'Yes, reschedule',
            cancelButtonText: 'No',
            confirmButtonColor: '#212529',
            cancelButtonColor: '#6c757d',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                rescheduleAppointmentForm.submit();
            }
        });
    });
}

const cancelAppointmentBtn = document.getElementById('cancelAppointmentBtn');
if (cancelAppointmentBtn) {
    cancelAppointmentBtn.addEventListener('click', function () {
        Swal.fire({
            icon: 'warning',
            title: 'Cancel Appointment?',
            text: 'Are you sure you want to cancel this showroom appointment?',
            showCancelButton: true,
            confirmButtonText: 'Yes, I want to cancel',
            cancelButtonText: 'No',
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('cancelAppointmentForm').submit();
            }
        });
    });
}
</script>
<<<<<<< HEAD
<?php endif; ?>
<script>
const documentHasCurrent = <?php echo $current_document ? 'true' : 'false'; ?>;
const appointmentStatus = '<?php echo $type === 'appointment' ? htmlspecialchars($data['status'], ENT_QUOTES) : ''; ?>';
const resendDocumentForm = document.getElementById('resendDocumentForm');
if (resendDocumentForm) {
    resendDocumentForm.addEventListener('submit', function (event) {
        event.preventDefault();
        const assignedStatusText = 'This will replace your current submitted document. Your appointment status will change from ASSIGNED to REQUESTED and wait for staff assignment again.';
        const defaultStatusText = documentHasCurrent ? 'This will replace your current submitted document.' : 'This document will be submitted for this record.';
        Swal.fire({
            icon: 'question',
            title: 'Resend Document?',
            text: appointmentStatus === 'ASSIGNED' ? assignedStatusText : defaultStatusText,
            showCancelButton: true,
            confirmButtonText: appointmentStatus === 'ASSIGNED' ? 'Yes, resend and request again' : 'Yes, resend',
            cancelButtonText: 'No',
            confirmButtonColor: '#212529',
            cancelButtonColor: '#6c757d',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                resendDocumentForm.submit();
            }
        });
    });
}

const deleteDocumentBtn = document.getElementById('deleteDocumentBtn');
if (deleteDocumentBtn) {
    deleteDocumentBtn.addEventListener('click', function () {
        Swal.fire({
            icon: 'warning',
            title: 'Delete Document?',
            text: 'Are you sure you want to delete this document?',
            showCancelButton: true,
            confirmButtonText: 'Yes, delete',
            cancelButtonText: 'No',
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('deleteDocumentForm').submit();
            }
        });
    });
}
</script>
<?php include '../includes/footer.php'; ?>
=======
<?php include '../includes/footer.php'; ?>
>>>>>>> a569b1048510cbb6f153689057ce94d22d449514
