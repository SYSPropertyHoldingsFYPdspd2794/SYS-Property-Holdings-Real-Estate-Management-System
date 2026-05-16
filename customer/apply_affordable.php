<?php
/**
 * PROJECT: SYS Property Holdings
 * FILE: customer/apply_affordable.php
 * DESCRIPTION: US27 & US28 - Submit affordable housing application with strict income checks.
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

$user_stmt = $conn->prepare("SELECT monthly_income FROM customers WHERE customer_id = ?");
$user_stmt->bind_param("i", $account_id);
$user_stmt->execute();
$user_income = (float)($user_stmt->get_result()->fetch_assoc()['monthly_income'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $prop_id = isset($_POST['property_id']) ? intval($_POST['property_id']) : 0;
    
    $prop_check = $conn->prepare("SELECT property_id, income_limit_rm FROM properties WHERE property_id = ? AND status IN ('ACTIVE', 'AVAILABLE') AND is_affordable = 1");
    $prop_check->bind_param("i", $prop_id);
    $prop_check->execute();
    $prop_res = $prop_check->get_result();

    if ($prop_res->num_rows === 0) {
        $error = "System Error: Please select a valid government-subsidized property.";
    } else {
        $property_data = $prop_res->fetch_assoc();
        $income_limit = (float)($property_data['income_limit_rm'] ?? 0);

        // Strict Application Eligibility Check
        if ($user_income > $income_limit && $income_limit > 0) {
            $error = "Application Rejected: Your declared monthly income (RM " . number_format($user_income, 2) . ") exceeds the maximum threshold for this subsidized unit.";
        } elseif (!isset($_FILES['document']) || $_FILES['document']['error'] !== UPLOAD_ERR_OK) {
            $error = "Mandatory income declaration document is missing or corrupted.";
        } else {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime = finfo_file($finfo, $_FILES['document']['tmp_name']);
            finfo_close($finfo);

            if ($mime !== 'application/pdf' || $_FILES['document']['size'] > 5242880) {
                $error = "Invalid document format. Only PDF files under 5MB are permitted.";
            } else {
                $conn->begin_transaction();
                try {
                    $stmt = $conn->prepare("INSERT INTO affordable_housing_applications (customer_id, property_id, status) VALUES (?, ?, 'PENDING_REVIEW')");
                    $stmt->bind_param("ii", $account_id, $prop_id);
                    $stmt->execute();
                    $app_id = $conn->insert_id;

                    $target_dir = "../storage/docs/";
                    if(!is_dir($target_dir)) mkdir($target_dir, 0777, true);
                    
                    $file_name = "app_" . $app_id . "_" . time() . ".pdf";
                    $target_file = $target_dir . $file_name;
                    
                    if (move_uploaded_file($_FILES["document"]["tmp_name"], $target_file)) {
                        $db_path = "/storage/docs/" . $file_name;
                        $doc_stmt = $conn->prepare("INSERT INTO documents (customer_id, related_to_type, related_to_id, document_type, file_path, is_purged) VALUES (?, 'APPLICATION', ?, 'EPF_STATEMENT_SUMMARY', ?, FALSE)");
                        $doc_stmt->bind_param("iis", $account_id, $app_id, $db_path);
                        $doc_stmt->execute();
                    }

                    $conn->commit();
                    header("Location: track_status.php?success=application_submitted");
                    exit();
                } catch (Exception $e) {
                    $conn->rollback();
                    $error = "Database failure. Unable to submit application.";
                }
            }
        }
    }
}

// Prepare dropdown options
$all_props = $conn->query("SELECT property_id, project_name, income_limit_rm FROM properties WHERE is_affordable = 1 AND status IN ('ACTIVE', 'AVAILABLE')");

include '../includes/header.php';
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card border-0 shadow-lg rounded-4">
                <div class="card-header bg-success text-white p-4 rounded-top-4">
                    <h3 class="fw-bold mb-0"><i class="fas fa-file-signature me-2"></i>Affordable Housing Registration</h3>
                </div>
                <div class="card-body p-5">
                    <?php if ($error): ?>
                        <div class="alert alert-danger border-0 shadow-sm p-4 mb-4" style="background-color: #fff5f5; border-left: 5px solid #dc3545 !important;">
                            <h5 class="fw-bold text-danger mb-1"><i class="fas fa-ban me-2"></i>Submission Failed</h5>
                            <p class="mb-0 text-dark"><?php echo htmlspecialchars($error); ?></p>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="apply_affordable.php" enctype="multipart/form-data">
                        <div class="mb-4">
                            <label class="form-label fw-bold">Select Subsidized Property</label>
                            <select name="property_id" class="form-select form-select-lg bg-light" required>
                                <option value="" disabled <?php echo ($property_id == 0) ? 'selected' : ''; ?>>-- Please Select --</option>
                                <?php while ($p = $all_props->fetch_assoc()): ?>
                                    <option value="<?php echo $p['property_id']; ?>" <?php echo ($property_id == $p['property_id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($p['project_name']) . " (Limit: RM " . number_format($p['income_limit_rm'] ?? 0) . ")"; ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        
                        <div class="mb-4">
                            <label class="form-label fw-bold">Declared Monthly Income (RM)</label>
                            <input type="text" class="form-control form-control-lg bg-light" value="<?php echo number_format($user_income, 2); ?>" readonly>
                            <small class="text-danger mt-2 d-block"><i class="fas fa-exclamation-circle me-1"></i>If incorrect, please update your <a href="profile.php" class="fw-bold text-decoration-none">Profile</a> prior to submission.</small>
                        </div>
                        
                        <div class="mb-5 p-4 bg-success bg-opacity-10 rounded-4 border border-success border-opacity-25">
                            <label class="form-label fw-bold text-dark"><i class="fas fa-file-pdf text-danger me-2 fs-5"></i>Income Declaration / EPF Abstract *</label>
                            <p class="small text-muted mb-3">This verified document is strictly required by the local ministry for final assessment. Max size: 5MB (PDF format only).</p>
                            <input type="file" name="document" class="form-control form-control-lg" accept="application/pdf" required>
                        </div>
                        
                        <button type="submit" class="btn btn-success btn-lg w-100 fw-bold py-3 rounded-pill shadow-sm">Submit Secure Application</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>