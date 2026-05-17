<?php
/**
 * PROJECT: SYS Property Holdings
 * FILE: customer/apply_affordable.php
 * DESCRIPTION: US27 & US28 - Affordable application with exact server-side submission timestamping.
 */

session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'CUSTOMER') {
    header("Location: ../login.php");
    exit();
}
include '../includes/db_connect.php';

$account_id = $_SESSION['account_id'];
$error = '';

$user_stmt = $conn->prepare("SELECT monthly_income FROM customers WHERE customer_id = ?");
$user_stmt->bind_param("i", $account_id);
$user_stmt->execute();
$user = $user_stmt->get_result()->fetch_assoc();
$user_income = (float)($user['monthly_income'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $prop_id = isset($_POST['property_id']) ? (int)$_POST['property_id'] : 0;
    
    $prop_check = $conn->prepare("SELECT property_id, income_limit_rm FROM properties WHERE property_id = ? AND status IN ('ACTIVE', 'AVAILABLE') AND is_affordable = 1");
    $prop_check->bind_param("i", $prop_id);
    $prop_check->execute();
    $prop_res = $prop_check->get_result();

    if ($prop_res->num_rows === 0) {
        $error = "Please select a valid government property.";
    } else {
        $property_data = $prop_res->fetch_assoc();
        $income_limit = (float)($property_data['income_limit_rm'] ?? 0);

        if ($user_income > $income_limit && $income_limit > 0) {
            $error = "Application Rejected: Your declared monthly income (RM " . number_format($user_income, 2) . ") exceeds the maximum threshold for this subsidized unit.";
        } elseif (!isset($_FILES['document']) || $_FILES['document']['error'] !== UPLOAD_ERR_OK) {
            $error = "Mandatory income declaration document is missing or corrupted.";
        } else {
            $tmp_name = $_FILES['document']['tmp_name'];
            $name = basename($_FILES['document']['name']);
            $size = $_FILES['document']['size'];
            $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
            
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime = finfo_file($finfo, $tmp_name);
            finfo_close($finfo);
            
            if ($ext !== 'pdf' || $mime !== 'application/pdf' || $size > 5242880) {
                $error = "Invalid file. Document must be in PDF format and strictly under 5MB.";
            } else {
                $conn->begin_transaction();
                try {
                    // FIX US26: Explicitly bind NOW() timestamp to capture the exact current submission period
                    $insert_app = $conn->prepare("INSERT INTO affordable_housing_applications (customer_id, property_id, status, application_date) VALUES (?, ?, 'PENDING_REVIEW', NOW())");
                    $insert_app->bind_param("ii", $account_id, $prop_id);
                    $insert_app->execute();
                    $app_id = $conn->insert_id;
                    
                    $upload_dir = '../storage/docs/';
                    if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
                    
                    $file_name = "app_" . $app_id . "_" . time() . ".pdf";
                    $target_file = $upload_dir . $file_name;
                    $db_path = "/storage/docs/" . $file_name;
                    
                    if (move_uploaded_file($tmp_name, $target_file)) {
                        $doc_type = 'EPF_STATEMENT_SUMMARY';
                        $rel_type = 'APPLICATION';
                        $doc_stmt = $conn->prepare("INSERT INTO documents (customer_id, related_to_type, related_to_id, document_type, file_path, is_purged) VALUES (?, ?, ?, ?, ?, FALSE)");
                        $doc_stmt->bind_param("isiss", $account_id, $rel_type, $app_id, $doc_type, $db_path);
                        $doc_stmt->execute();
                        
                        $conn->commit();
                        header("Location: track_status.php?success=application_submitted");
                        exit();
                    } else {
                        throw new Exception("File system error: Unable to save uploaded document.");
                    }
                } catch (Exception $e) {
                    $conn->rollback();
                    $error = "Failed to submit housing application. " . $e->getMessage();
                }
            }
        }
    }
}

$props = $conn->query("SELECT property_id, project_name, state, income_limit_rm FROM properties WHERE status IN ('ACTIVE', 'AVAILABLE') AND is_affordable = 1 ORDER BY state, project_name");
$preselect = isset($_GET['id']) ? intval($_GET['id']) : 0;

include '../includes/header.php';
?>
<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow border-0 border-top border-primary border-5 rounded-3">
                <div class="card-body p-5">
                    <h2 class="fw-bold mb-3 text-primary"><i class="fas fa-home me-2"></i>Affordable Housing Application</h2>
                    <p class="text-muted mb-4">Complete your submission for government-subsidized housing. Please ensure your income data aligns with state policies.</p>
                    
                    <?php if ($error !== ''): ?>
                        <div class="alert alert-danger fw-bold"><i class="fas fa-exclamation-triangle me-2"></i><?php echo htmlspecialchars($error); ?></div>
                    <?php endif; ?>
                    
                    <form method="POST" enctype="multipart/form-data">
                        <div class="mb-4">
                            <label class="form-label fw-bold">Select Government Property</label>
                            <select name="property_id" class="form-select form-select-lg bg-light" required>
                                <option value="" <?php echo $preselect === 0 ? 'selected' : ''; ?>>None</option>
                                <?php while ($p = $props->fetch_assoc()): ?>
                                    <option value="<?php echo $p['property_id']; ?>" <?php echo $preselect === (int)$p['property_id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($p['project_name'] . ' (' . $p['state'] . ') - Limit: RM ' . number_format($p['income_limit_rm'] ?? 0)); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="mb-4">
                            <label class="form-label fw-bold">Declared Monthly Income (RM)</label>
                            <input type="text" class="form-control form-control-lg bg-light" value="<?php echo number_format($user_income, 2); ?>" readonly>
                            <small class="text-danger mt-2 d-block"><i class="fas fa-exclamation-circle me-1"></i>If this value is incorrect, you must update it in your <a href="profile.php" class="fw-bold text-decoration-none">Profile</a> before submitting.</small>
                        </div>
                        <div class="mb-5 p-4 bg-light rounded border border-primary">
                            <label class="form-label fw-bold"><i class="fas fa-file-pdf text-danger me-2"></i>Income Declaration / EPF Abstract *</label>
                            <p class="small text-muted mb-3">This upload is mandatory for eligibility verification. Max size: 5MB (PDF only).</p>
                            <input type="file" name="document" class="form-control form-control-lg" accept="application/pdf" required>
                        </div>
                        <button type="submit" class="btn btn-primary btn-lg w-100 fw-bold py-3 shadow rounded-pill">Submit Secure Application</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include '../includes/footer.php'; ?>