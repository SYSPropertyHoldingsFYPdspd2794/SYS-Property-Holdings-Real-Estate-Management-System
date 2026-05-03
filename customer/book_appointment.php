<?php
session_start();
if (!isset($_SESSION['role']) |

| $_SESSION['role']!== 'CUSTOMER') {
    header("Location:../login.php");
    exit();
}
include '../includes/db_connect.php';

$account_id = $_SESSION['account_id'];
$error = '';

if ($_SERVER === 'POST') {
    $prop_id = $_POST['property_id'];
    $service = $_POST['service_type'];
    $date = $_POST['appointment_date'];
    $time = $_POST['appointment_time'];
    
    $insert_appt = $conn->prepare("INSERT INTO appointments (customer_id, property_id, service_type, appointment_date, appointment_time, status) VALUES (?,?,?,?,?, 'REQUESTED')");
    $insert_appt->bind_param("iisss", $account_id, $prop_id, $service, $date, $time);
    
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
                                <input type="time" name="appointment_time" class="form-control form-control-lg" required>
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

