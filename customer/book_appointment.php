<?php
/**
 * PROJECT: SYS Property Holdings
 * FILE: customer/book_appointment.php
 * DESCRIPTION: Book showroom appointments with capacity, hour/half-hour alignment, and time conflict validation.
 */

session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'CUSTOMER') {
    header("Location: ../login.php");
    exit();
}
include '../includes/db_connect.php';

$account_id = $_SESSION['account_id'];
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $prop_id = isset($_POST['property_id']) ? (int)$_POST['property_id'] : 0;
    $service = $_POST['service_type'] ?? '';
    $date = $_POST['appointment_date'] ?? '';
    $time = $_POST['appointment_time'] ?? '';
    $time_for_db = strlen($time) === 5 ? $time . ':00' : $time;

    $appointment_dt = DateTime::createFromFormat('Y-m-d H:i:s', $date . ' ' . $time_for_db);
    $date_errors = DateTime::getLastErrors();
    $valid_datetime = $appointment_dt && ($date_errors === false || ($date_errors['warning_count'] === 0 && $date_errors['error_count'] === 0));
    $today = new DateTime();

    if (!$valid_datetime || $appointment_dt < $today) {
        $error = "Please select a valid future appointment date and time.";
    } elseif ($appointment_dt->format('H:i:s') < '08:00:00' || $appointment_dt->format('H:i:s') > '20:00:00') {
        $error = "Appointments are available from 8:00 AM to 8:00 PM. Please choose a time in this range.";
    } 
    // ENHANCEMENT: Server-side validation to enforce strictly sharp hour or half-hour selection matrices
    elseif ($appointment_dt->format('i') !== '00' && $appointment_dt->format('i') !== '30') {
        $error = "Invalid slot alignment: Appointments must be scheduled strictly on the hour or half-hour mark (e.g., 10:00 or 10:30).";
    } else {
        $count_stmt = $conn->prepare("SELECT COUNT(*) AS total FROM appointments WHERE appointment_date = ? AND status NOT IN ('CANCELLED', 'NO_SHOW')");
        $count_stmt->bind_param("s", $date);
        $count_stmt->execute();
        $day_total = (int)$count_stmt->get_result()->fetch_assoc()['total'];

        if ($day_total >= 3) {
            $error = "Capacity reached: This date already has 3 scheduled appointments. Please choose another day.";
        } else {
            $conflict_stmt = $conn->prepare("SELECT appointment_time FROM appointments WHERE appointment_date = ? AND status NOT IN ('CANCELLED', 'NO_SHOW') AND ABS(TIME_TO_SEC(TIMEDIFF(appointment_time, ?))) < 7200 ORDER BY appointment_time ASC LIMIT 1");
            $conflict_stmt->bind_param("ss", $date, $time_for_db);
            $conflict_stmt->execute();
            $conflict = $conflict_stmt->get_result()->fetch_assoc();

            if ($conflict) {
                $conflicting_time = $conflict['appointment_time'];
                $suggested_time = date('h:i A', strtotime($conflicting_time . ' +2 hours'));
                
                $error = "Scheduling conflict: Another appointment is already active within 2 hours of this time. To avoid a clash, the next available time slot you can book is from " . $suggested_time . " onwards.";
            }
        }
    }

    if ($error === '') {
        $conn->begin_transaction();
        try {
            $insert_appt = $conn->prepare("INSERT INTO appointments (customer_id, property_id, service_type, appointment_date, appointment_time, status) VALUES (?, ?, ?, ?, ?, 'REQUESTED')");
            $insert_appt->bind_param("iisss", $account_id, $prop_id, $service, $date, $time_for_db);
            $insert_appt->execute();
            $appt_id = $conn->insert_id;

            if (isset($_FILES['document']) && $_FILES['document']['error'] === UPLOAD_ERR_OK) {
                $tmp_name = $_FILES['document']['tmp_name'];
                $name = basename($_FILES['document']['name']);
                $size = $_FILES['document']['size'];
                $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));

                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mime = finfo_file($finfo, $tmp_name);
                finfo_close($finfo);

                if ($ext === 'pdf' && $mime === 'application/pdf' && $size <= 5242880) {
                    $upload_dir = '../storage/docs/';
                    if (!is_dir($upload_dir)) {
                        mkdir($upload_dir, 0777, true);
                    }

                    $file_name = "appt_" . $appt_id . "_" . time() . ".pdf";
                    $target_file = $upload_dir . $file_name;
                    $db_path = "storage/docs/" . $file_name;

                    if (move_uploaded_file($tmp_name, $target_file)) {
                        $doc_type = 'PAYSLIP_SUMMARY';
                        $rel_type = 'APPOINTMENT';
                        $doc_stmt = $conn->prepare("INSERT INTO documents (customer_id, related_to_type, related_to_id, document_type, file_path, is_purged) VALUES (?, ?, ?, ?, ?, FALSE)");
                        $doc_stmt->bind_param("isiss", $account_id, $rel_type, $appt_id, $doc_type, $db_path);
                        $doc_stmt->execute();
                    }
                } else {
                    throw new Exception("Invalid document format. Only PDF files under 5MB are allowed.");
                }
            }

            $conn->commit();
            header("Location: track_status.php?success=appointment_booked");
            exit();
        } catch (Exception $e) {
            $conn->rollback();
            $error = "Failed to book appointment. " . $e->getMessage();
        }
    }
}

$props = $conn->query("SELECT property_id, project_name, state FROM properties WHERE status IN ('ACTIVE', 'AVAILABLE') AND is_affordable = 0 ORDER BY state, project_name");
$preselect = isset($_GET['id']) ? (int)$_GET['id'] : 0;

include '../includes/header.php';
?>
<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow border-0 rounded-4">
                <div class="card-header bg-dark text-white p-4 rounded-top-4">
                    <h3 class="fw-bold mb-0"><i class="far fa-calendar-check text-warning me-2"></i>Book Showroom Appointment</h3>
                </div>
                <div class="card-body p-5">
                    <p class="text-muted mb-4">Schedule your physical offline visit to our showrooms for a personalized experience.</p>

                    <?php if ($error !== ''): ?>
                        <div class="alert alert-danger fw-bold"><i class="fas fa-exclamation-triangle me-2"></i><?php echo htmlspecialchars($error); ?></div>
                    <?php endif; ?>

                    <form method="POST" enctype="multipart/form-data">
                        <div class="mb-4">
                            <label class="form-label fw-bold">Select Property</label>
                            <select name="property_id" class="form-select form-select-lg bg-light" required>
                                <option value="" disabled <?php echo $preselect === 0 ? 'selected' : ''; ?>>Choose a property...</option>
                                <?php while ($p = $props->fetch_assoc()): ?>
                                    <option value="<?php echo $p['property_id']; ?>" <?php echo $preselect === (int)$p['property_id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($p['project_name'] . ' (' . $p['state'] . ')'); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="mb-4">
                            <label class="form-label fw-bold">Service Type</label>
                            <select name="service_type" class="form-select form-select-lg bg-light" required>
                                <option value="SHOWROOM_VIEWING">Showroom Viewing</option>
                                <option value="FINANCIAL_CONSULTATION">Financial Consultation</option>
                            </select>
                        </div>
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Preferred Date</label>
                                <input type="date" name="appointment_date" class="form-control form-control-lg bg-light" min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Preferred Time</label>
                                <input type="time" name="appointment_time" class="form-control form-control-lg bg-light" min="08:00" max="20:00" step="1800" required>
                                <small class="text-muted d-block mt-2">Available time: 8:00 AM to 8:00 PM. Appointments must be structured in hour or half-hour slots and at least 2 hours apart.</small>
                            </div>
                        </div>
                        <div class="mb-5 p-4 bg-light rounded-4 border border-secondary border-opacity-25">
                            <label class="form-label fw-bold"><i class="fas fa-file-upload me-2 text-primary"></i>Payslip / Income Summary (Optional)</label>
                            <p class="small text-muted mb-3">Upload your latest payslip to help our consultants run a quick, early financial assessment before your appointment. PDF only, max size 5MB.</p>
                            <input type="file" name="document" class="form-control" accept="application/pdf">
                        </div>
                        <button type="submit" class="btn btn-dark btn-lg w-100 fw-bold py-3 rounded-pill shadow-sm">Confirm Booking Request</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include '../includes/footer.php'; ?>