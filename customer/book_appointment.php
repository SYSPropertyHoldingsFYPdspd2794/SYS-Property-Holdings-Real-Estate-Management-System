<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role']!== 'CUSTOMER') {
    header("Location:../login.php");
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

    if (!$valid_datetime) {
        $error = "Please select a valid appointment date and time.";
    } elseif ($appointment_dt->format('H:i:s') < '10:00:00' || $appointment_dt->format('H:i:s') > '20:00:00') {
        $error = "Appointments are available from 10:00 AM to 8:00 PM. Please choose a time in this range.";
    } else {
        $count_stmt = $conn->prepare("SELECT COUNT(*) AS total FROM appointments WHERE appointment_date = ? AND status NOT IN ('CANCELLED', 'NO_SHOW')");
        $count_stmt->bind_param("s", $date);
        $count_stmt->execute();
        $day_total = (int)$count_stmt->get_result()->fetch_assoc()['total'];

        if ($day_total >= 3) {
            $error = "This date already has 3 appointments. Please choose another day.";
        } else {
            $conflict_stmt = $conn->prepare("SELECT appointment_time FROM appointments WHERE appointment_date = ? AND status NOT IN ('CANCELLED', 'NO_SHOW') AND ABS(TIME_TO_SEC(TIMEDIFF(appointment_time, ?))) < 7200 LIMIT 1");
            $conflict_stmt->bind_param("ss", $date, $time_for_db);
            $conflict_stmt->execute();
            $conflict = $conflict_stmt->get_result()->fetch_assoc();

            if ($conflict) {
                $error = "Another appointment is already scheduled within 2 hours of this time. Please choose a different time.";
            }
        }
    }

    if ($error === '') {
        $insert_appt = $conn->prepare("INSERT INTO appointments (customer_id, property_id, service_type, appointment_date, appointment_time, status) VALUES (?,?,?,?,?, 'REQUESTED')");
        $insert_appt->bind_param("iisss", $account_id, $prop_id, $service, $date, $time_for_db);
        
        if ($insert_appt->execute()) {
            $appt_id = $conn->insert_id;
            if (isset($_FILES['document']) && $_FILES['document']['error'] === UPLOAD_ERR_OK) {
                $tmp_name = $_FILES['document']['tmp_name'];
                $name = basename($_FILES['document']['name']);
                $size = $_FILES['document']['size'];
                $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
                if ($ext === 'pdf' && $size <= 5242880) {
                    if (!is_dir('../uploads')) mkdir('../uploads', 0777, true);
                    $file_path = '../uploads/appt_'. $appt_id. '_'. time(). '.pdf';
                    if (move_uploaded_file($tmp_name, $file_path)) {
                        $doc_type = 'PAYSLIP_SUMMARY';
                        $rel_type = 'APPOINTMENT';
                        $doc_stmt = $conn->prepare("INSERT INTO documents (customer_id, related_to_type, related_to_id, document_type, file_path) VALUES (?,?,?,?,?)");
                        $doc_stmt->bind_param("isiss", $account_id, $rel_type, $appt_id, $doc_type, $file_path);
                        $doc_stmt->execute();
                    }
                }
            }
            header("Location: track_status.php");
            exit();
        } else {
            $error = "Failed to book appointment. Please try again.";
        }
    }
}

$props = $conn->query("SELECT property_id, project_name, state FROM properties WHERE status = 'ACTIVE' AND property_type!= 'AFFORDABLE'");
$preselect = isset($_GET['id'])? intval($_GET['id']) : 0;

include '../includes/header.php';
?>
<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow border-0">
                <div class="card-body p-5">
                    <h2 class="fw-bold mb-4 text-dark"><i class="far fa-calendar-check me-2"></i>Book Showroom Appointment</h2>
                    <p class="text-muted mb-4">Schedule your physical offline visit to our showrooms for a personalized experience.</p>
                    <?php if ($error!== ''):?>
                        <div class="alert alert-danger fw-bold"><?php echo htmlspecialchars($error);?></div>
                    <?php endif;?>
                    <form method="POST" enctype="multipart/form-data">
                        <div class="mb-4">
                            <label class="form-label fw-bold">Select Property</label>
                            <select name="property_id" class="form-select form-select-lg" required>
                                <option value="" disabled <?php echo $preselect === 0? 'selected' : '';?>>Choose a property...</option>
                                <?php while ($p = $props->fetch_assoc()):?>
                                    <option value="<?php echo $p['property_id'];?>" <?php echo $preselect === (int)$p['property_id']? 'selected' : '';?>>
                                        <?php echo htmlspecialchars($p['project_name']. ' ('. $p['state']. ')');?>
                                    </option>
                                <?php endwhile;?>
                            </select>
                        </div>
                        <div class="mb-4">
                            <label class="form-label fw-bold">Service Type</label>
                            <select name="service_type" class="form-select form-select-lg" required>
                                <option value="SHOWROOM_VIEWING">Showroom Viewing</option>
                                <option value="FINANCIAL_CONSULTATION">Financial Consultation</option>
                            </select>
                        </div>
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Preferred Date</label>
                                <input type="date" name="appointment_date" class="form-control form-control-lg" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Preferred Time</label>
                                <input type="time" name="appointment_time" class="form-control form-control-lg" min="10:00" max="20:00" step="1800" required>
                                <small class="text-muted d-block mt-2">Available time: 10:00 AM to 8:00 PM. Appointments must be at least 2 hours apart.</small>
                            </div>
                        </div>
                        <div class="mb-5 p-4 bg-light rounded border">
                            <label class="form-label fw-bold"><i class="fas fa-file-upload me-2"></i>Optional Financial Abstract (Payslip Summary)</label>
                            <p class="small text-muted mb-3">Uploading a document allows our consultants to perform an early financial pre-check before your visit. Max size: 5MB (PDF only).</p>
                            <input type="file" name="document" class="form-control" accept="application/pdf">
                        </div>
                        <button type="submit" class="btn btn-dark btn-lg w-100 fw-bold py-3">Confirm Booking</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include '../includes/footer.php';?>

