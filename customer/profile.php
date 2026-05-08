<?php
// 1. Include Database Connection and Security Middleware
include '../includes/db_connect.php';
include '../includes/auth_check.php';

/**
 * TASK: Validate session roles and restrict unauthorized database connections.
 * This function handles the logic: if not a 'CUSTOMER', it kills the $conn and redirects.
 */
protect_customer_page('CUSTOMER', $conn);

$account_id = $_SESSION['account_id'];
$alert_msg = '';
$alert_type = '';

// 2. Handle Profile Update (POST Request)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $phone = trim($_POST['phone_number']);
    $marital = $_POST['marital_status'];
    $dep = intval($_POST['dependents_count']);
    $occ = trim($_POST['occupation']);
    $inc = floatval($_POST['monthly_income']);

    // Prepare statement to prevent SQL Injection
    $update = $conn->prepare("UPDATE customers SET phone_number=?, marital_status=?, dependents_count=?, occupation=?, monthly_income=? WHERE customer_id=?");
    $update->bind_param("ssisdi", $phone, $marital, $dep, $occ, $inc, $account_id);

    if ($update->execute()) {
        $alert_msg = "Profile updated successfully!";
        $alert_type = "success";
    } else {
        $alert_msg = "Error updating profile. Please try again.";
        $alert_type = "danger";
    }
}

// 3. Fetch Latest User Data to display in the form
$stmt = $conn->prepare("SELECT c.*, a.email FROM customers c JOIN accounts a ON c.customer_id = a.account_id WHERE c.customer_id = ?");
$stmt->bind_param("i", $account_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

include '../includes/header.php';
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card shadow-sm border-0">
                <div class="card-body p-5">
                    <h3 class="fw-bold mb-4"><i class="fas fa-user-circle text-primary me-2"></i>My Profile</h3>

                    <?php if ($alert_msg !== ''): ?>
                        <div class="alert alert-<?php echo $alert_type; ?> alert-dismissible fade show shadow-sm" role="alert">
                            <i class="fas <?php echo ($alert_type === 'success') ? 'fa-check-circle' : 'fa-exclamation-triangle'; ?> me-2"></i>
                            <?php echo $alert_msg; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <form method="POST">
                        <div class="row g-4 mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Email Address</label>
                                <input type="email" class="form-control bg-light" value="<?php echo htmlspecialchars($user['email']); ?>" readonly>
                                <small class="text-muted italic">Account email cannot be modified.</small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Full Name</label>
                                <input type="text" class="form-control bg-light" value="<?php echo htmlspecialchars($user['full_name']); ?>" readonly>
                                <small class="text-muted italic">Identity name is verified and locked.</small>
                            </div>
                        </div>

                        <div class="row g-4 mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Phone Number</label>
                                <input type="text" name="phone_number" class="form-control border-primary" value="<?php echo htmlspecialchars($user['phone_number']); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Marital Status</label>
                                <select name="marital_status" class="form-select border-primary" required>
                                    <option value="SINGLE" <?php echo ($user['marital_status'] === 'SINGLE') ? 'selected' : ''; ?>>Single</option>
                                    <option value="MARRIED" <?php echo ($user['marital_status'] === 'MARRIED') ? 'selected' : ''; ?>>Married</option>
                                    <option value="DIVORCED" <?php echo ($user['marital_status'] === 'DIVORCED') ? 'selected' : ''; ?>>Divorced</option>
                                </select>
                            </div>
                        </div>

                        <div class="row g-4 mb-4">
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Dependents Count</label>
                                <input type="number" name="dependents_count" class="form-control border-primary" value="<?php echo htmlspecialchars($user['dependents_count']); ?>" min="0" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Occupation</label>
                                <input type="text" name="occupation" class="form-control border-primary" value="<?php echo htmlspecialchars($user['occupation']); ?>" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Monthly Income (RM)</label>
                                <input type="number" step="0.01" name="monthly_income" class="form-control border-primary" value="<?php echo htmlspecialchars($user['monthly_income']); ?>" required>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end mt-4">
                            <button type="submit" class="btn btn-primary btn-lg fw-bold px-5 shadow-sm">
                                <i class="fas fa-save me-2"></i>Update Profile Information
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>