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

$user_stmt = $conn->prepare("SELECT monthly_income FROM customers WHERE customer_id =?");
$user_stmt->bind_param("i", $account_id);
$user_stmt->execute();
$user_income = $user_stmt->get_result()->fetch_assoc()['monthly_income'];

if ($_SERVER === 'POST') {
    $prop_id = $_POST['property_id'];
    if (!isset($_FILES['document']) |

| $_FILES['document']['error']!== UPLOAD_ERR_OK) {
        $error = "Mandatory income declaration document is missing.";
    } else {
        $tmp_name = $_FILES['document']['tmp_name'];
        $name = basename($_FILES['document']['name']);
        $size = $_FILES['document']['size'];
        $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
        
        if ($ext!== 'pdf' |

| $size > 5242880) {
            $error = "Invalid file. Document must be in PDF format and under 5MB.";
        } else {
            $insert_app = $conn->prepare("INSERT INTO affordable_housing_applications (customer_id, property_id, status) VALUES (?,?, 'PENDING_REVIEW')");
            $insert_app->bind_param("ii", $account_id, $prop_id);
            if ($insert_app->execute()) {
                $app_id = $conn->insert_id;
                if (!is_dir('../uploads')) mkdir('../uploads', 0777, true);
                $file_path = '../uploads/app_'. $app_id. '_'. time(). '.pdf';
                if (move_uploaded_file($tmp_name, $file_path)) {
                    $doc_type = 'INCOME_DECLARATION';
                    $rel_type = 'APPLICATION';
                    $doc_stmt = $conn->prepare("INSERT INTO documents (customer_id, related_to_type, related_to_id, document_type, file_path) VALUES (?,?,?,?,?)");
                    $doc_stmt->bind_param("isiss", $account_id, $rel_type, $app_id, $doc_type, $file_path);
                    $doc_stmt->execute();
                }
                header("Location: track_status.php");
                exit();
            } else {
                $error = "Failed to submit housing application.";
            }
        }
    }
}

$props = $conn->query("SELECT property_id, project_name, state FROM properties WHERE status = 'ACTIVE' AND property_type = 'AFFORDABLE'");
$preselect = isset($_GET['id'])? intval($_GET['id']) : 0;

include '../includes/header.php';
?>
<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow border-0 border-top border-primary border-5">
                <div class="card-body p-5">
                    <h2 class="fw-bold mb-3 text-primary"><i class="fas fa-home me-2"></i>Affordable Housing Application</h2>
                    <p class="text-muted mb-4">Complete your submission for government-subsidized housing. Please ensure your income data aligns with state policies.</p>
                    <?php if ($error!== ''):?>
                        <div class="alert alert-danger fw-bold"><?php echo htmlspecialchars($error);?></div>
                    <?php endif;?>
                    <form method="POST" enctype="multipart/form-data">
                        <div class="mb-4">
                            <label class="form-label fw-bold">Select Government Property</label>
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
                            <label class="form-label fw-bold">Declared Monthly Income (RM)</label>
                            <input type="text" class="form-control form-control-lg bg-light" value="<?php echo number_format($user_income, 2);?>" readonly>
                            <small class="text-danger mt-2 d-block"><i class="fas fa-exclamation-circle me-1"></i>If this value is incorrect, you must update it in your <a href="profile.php">Profile</a> before submitting.</small>
                        </div>
                        <div class="mb-5 p-4 bg-light rounded border border-primary">
                            <label class="form-label fw-bold"><i class="fas fa-file-pdf text-danger me-2"></i>Income Declaration / EPF Abstract *</label>
                            <p class="small text-muted mb-3">This upload is mandatory for eligibility verification. Max size: 5MB (PDF only).</p>
                            <input type="file" name="document" class="form-control form-control-lg" accept="application/pdf" required>
                        </div>
                        <button type="submit" class="btn btn-primary btn-lg w-100 fw-bold py-3 shadow">Submit Application</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include '../includes/footer.php';?>

