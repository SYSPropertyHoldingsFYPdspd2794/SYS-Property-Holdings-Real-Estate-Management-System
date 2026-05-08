<?php
/**
 * TASK: Logic to execute an UPDATE statement on the customers table 
 * based on the active session ID.
 */

// 1. Include Database Connection and Security Middleware
include '../includes/db_connect.php';
include '../includes/auth_check.php';

// 2. Validate session roles and restrict unauthorized database connections.
// This ensures ONLY a CUSTOMER can reach this logic.
protect_customer_page('CUSTOMER', $conn);

// 3. GET THE ACTIVE SESSION ID
// This is the core requirement of your task.
$account_id = $_SESSION['account_id']; 

$alert_msg = '';
$alert_type = '';

// 4. EXECUTE UPDATE LOGIC (Triggered when the form is submitted)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Collect and sanitize data from the form
    $phone = trim($_POST['phone_number']);
    $marital = $_POST['marital_status'];
    $dep = intval($_POST['dependents_count']);
    $occ = trim($_POST['occupation']);
    $inc = floatval($_POST['monthly_income']);

    // LOGIC: The UPDATE statement using the ACTIVE SESSION ID ($account_id)
    $sql = "UPDATE customers SET 
                phone_number = ?, 
                marital_status = ?, 
                dependents_count = ?, 
                occupation = ?, 
                monthly_income = ? 
            WHERE customer_id = ?";

    $update_stmt = $conn->prepare($sql);
    
    // Bind parameters to the query 
    // ssisdi = string, string, integer, string, double(float), integer (ID)
    $update_stmt->bind_param("ssisdi", $phone, $marital, $dep, $occ, $inc, $account_id);

    if ($update_stmt->execute()) {
        $alert_msg = "Profile successfully updated for Session ID: " . $account_id;
        $alert_type = "success";
    } else {
        $alert_msg = "Error updating database: " . $conn->error;
        $alert_type = "danger";
    }
    
    // Terminate statement to free resources
    $update_stmt->close();
}

// 5. FETCH REFRESHED DATA
// Fetch the latest data after the update so the user sees the changes in the form.
$stmt = $conn->prepare("SELECT c.*, a.email FROM customers c JOIN accounts a ON c.customer_id = a.account_id WHERE c.customer_id = ?");
$stmt->bind_param("i", $account_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// 6. Include Page Header
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
                                <label class="form-label fw-bold">Email (Read Only)</label>
                                <input type="email" class="form-control bg-light" value="<?php echo htmlspecialchars($user['email']); ?>" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Full Name (Read Only)</label>
                                <input type="text" class="form-control bg-light" value="<?php echo htmlspecialchars($user['full_name']); ?>" readonly>
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
                                <i class="fas fa-save me-2"></i>Update Profile
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>