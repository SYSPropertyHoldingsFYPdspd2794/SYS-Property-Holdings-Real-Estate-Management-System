<?php
/**
 * PROJECT: SYS Property Holdings
 * FILE: customer/book_appointment.php
 * DESCRIPTION: US26 & US28 - Book showroom appointments and securely upload abstract PDF.
 */

session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'CUSTOMER') {
    header("Location: ../login.php");
    exit();
}

include '../includes/db_connect.php';

$account_id = $_SESSION['account_id'];
$error = '';
$property_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') { 
    $prop_id = isset($_POST['property_id']) ? intval($_POST['property_id']) : 0;
    $service = $_POST['service_type'] ?? '';
    $date = $_POST['appointment_date'] ?? '';
    $time = $_POST['appointment_time'] ?? '';
    $time_for_db = (strlen($time) === 5) ? $time . ':00' : $time;

    $appointment_dt = DateTime::createFromFormat('Y-m-d H:i:s', $date . ' ' . $time_for_db);
    $date_errors = DateTime::getLastErrors();
    $valid_datetime = $appointment_dt && ($date_errors === false || ($date_errors['warning_count'] === 0 && $date_errors['error_count'] === 0));

    $today = new DateTime();
    if (!$valid_datetime || $appointment_dt < $today) {
        $error = "Please select a valid future appointment date and time.";
    } elseif ($appointment_dt->format('H:i:s') < '10:00:00' || $appointment_dt->format('H:i:s') > '20:00:00') {
        $error = "Appointments must be scheduled between 10:00 AM and 8:00 PM.";
    } else {
        $conn->begin_transaction();
        try {
            $stmt = $conn->prepare("INSERT INTO appointments (customer_id, property_id, service_type, appointment_date, appointment_time, status) VALUES (?, ?, ?, ?, ?, 'PENDING')");
            $stmt->bind_param("iisss", $account_id, $prop_id, $service, $date, $time_for_db);
            $stmt->execute();
            $appt_id = $conn->insert_id;

            // Handle optional PDF document upload securely
            if (isset($_FILES['document']) && $_FILES['document']['error'] === UPLOAD_ERR_OK) {
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mime = finfo_file($finfo, $_FILES['document']['tmp_name']);
                finfo_close($finfo);

                if ($mime === 'application/pdf' && $_FILES['document']['size'] <= 5242880) {
                    $target_dir = "../storage/docs/";
                    if(!is_dir($target_dir)) mkdir($target_dir, 0777, true);
                    
                    $file_name = "appt_" . $appt_id . "_" . time() . ".pdf";
                    $target_file = $target_dir . $file_name;
                    
                    if (move_uploaded_file($_FILES["document"]["tmp_name"], $target_file)) {
                        $db_path = "/storage/docs/" . $file_name;
                        $doc_stmt = $conn->prepare("INSERT INTO documents (customer_id, related_to_type, related_to_id, document_type, file_path, is_purged) VALUES (?, 'APPOINTMENT', ?, 'PAYSLIP_SUMMARY', ?, FALSE)");
                        $doc_stmt->bind_param("iis", $account_id, $appt_id, $db_path);
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
            $error = $e->getMessage() ?: "System error occurred during booking.";
        }
    }
}

include '../includes/header.php';
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card border-0 shadow-lg rounded-4">
                <div class="card-header bg-dark text-white p-4 rounded-top-4">
                    <h3 class="fw-bold mb-0"><i class="fas fa-calendar-alt me-2 text-warning"></i>Schedule Viewing</h3>
                </div>
                <div class="card-body p-5">
                    <?php if ($error): ?>
                        <div class="alert alert-danger border-0 shadow-sm"><i class="fas fa-exclamation-triangle me-2"></i><?php echo htmlspecialchars($error); ?></div>
                    <?php endif; ?>
                    
                    <form method="POST" action="book_appointment.php" enctype="multipart/form-data">
                        <input type="hidden" name="property_id" value="<?php echo htmlspecialchars($property_id); ?>">
                        
                        <div class="mb-4">
                            <label class="form-label fw-bold">Service Type</label>
                            <select name="service_type" class="form-select form-select-lg bg-light" required>
                                <option value="SHOWROOM_VIEWING">Showroom Viewing</option>
                                <option value="FINANCIAL_CONSULTATION">Financial Consultation</option>
                            </select>
                        </div>
                        
                        <div class="row g-4 mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Preferred Date</label>
                                <input type="date" name="appointment_date" class="form-control form-control-lg bg-light" required min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Preferred Time</label>
                                <input type="time" name="appointment_time" class="form-control form-control-lg bg-light" min="10:00" max="20:00" step="1800" required>
                                <small class="text-muted d-block mt-2">Available: 10:00 AM - 8:00 PM</small>
                            </div>
                        </div>
                        
                        <div class="mb-5 p-4 bg-light rounded-4 border border-secondary border-opacity-25">
                            <label class="form-label fw-bold text-dark"><i class="fas fa-file-upload me-2 text-primary"></i>Optional Financial Abstract</label>
                            <p class="small text-muted mb-3">Uploading your payslip summary allows our consultants to perform an early financial pre-check. Max size: 5MB (PDF only).</p>
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