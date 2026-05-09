<?php
include '../includes/db_connect.php';
include '../includes/auth_check.php';

protect_customer_page('CUSTOMER', $conn);

$account_id = $_SESSION['account_id'];
$alert_msg = '';
$alert_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // SECTION A: Handle Password Hashing (Task #9)
    if (isset($_POST['update_password'])) {
        $old_pass = $_POST['old_password'];
        $new_pass = $_POST['new_password'];

        $stmt_check = $conn->prepare("SELECT password_hash FROM accounts WHERE account_id = ?");
        $stmt_check->bind_param("i", $account_id);
        $stmt_check->execute();
        $res = $stmt_check->get_result()->fetch_assoc();

        if ($res && password_verify($old_pass, $res['password_hash'])) {
            $hashed_password = password_hash($new_pass, PASSWORD_DEFAULT);
            $update_pass = $conn->prepare("UPDATE accounts SET password_hash = ? WHERE account_id = ?");
            $update_pass->bind_param("si", $hashed_password, $account_id);

            if ($update_pass->execute()) {
                $alert_msg = 'Password successfully updated.';
                $alert_type = 'success';
            }
        } else {
            $alert_msg = 'Current password incorrect.';
            $alert_type = 'danger';
        }
    }

    // SECTION B: Handle Customer Information Update
    if (isset($_POST['save_details'])) {
        $full_name = trim($_POST['full_name']);
        $phone = trim($_POST['phone_number']);
        $marital = $_POST['marital_status'];
        $dependents = intval($_POST['dependents_count']);
        $occupation = trim($_POST['occupation']);
        $income = floatval($_POST['monthly_income']);

        $sql = "INSERT INTO customers (customer_id, full_name, phone_number, marital_status, dependents_count, occupation, monthly_income) 
                VALUES (?, ?, ?, ?, ?, ?, ?) 
                ON DUPLICATE KEY UPDATE 
                full_name = VALUES(full_name), 
                phone_number = VALUES(phone_number), 
                marital_status = VALUES(marital_status), 
                dependents_count = VALUES(dependents_count), 
                occupation = VALUES(occupation), 
                monthly_income = VALUES(monthly_income)";
        
        $update_stmt = $conn->prepare($sql);
        $update_stmt->bind_param("isssisd", $account_id, $full_name, $phone, $marital, $dependents, $occupation, $income);

        if ($update_stmt->execute()) {
            $alert_msg = 'Customer information updated successfully.';
            $alert_type = 'success';
        } else {
            $alert_msg = 'Error updating information: ' . $conn->error;
            $alert_type = 'danger';
        }
    }
}

// Fetch refreshed data
$stmt = $conn->prepare("SELECT c.*, a.email FROM accounts a LEFT JOIN customers c ON a.account_id = c.customer_id WHERE a.account_id = ?");
$stmt->bind_param("i", $account_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

include '../includes/header.php';
?>

<div class="container my-5">
    <?php if ($alert_msg): ?>
        <div class="alert alert-<?php echo $alert_type; ?> shadow-sm"><?php echo htmlspecialchars($alert_msg); ?></div>
    <?php endif; ?>

    <div class="row g-4">
        <!-- Security Section -->
        <div class="col-md-4">
            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    <h4 class="fw-bold mb-4">Security</h4>
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Current Password</label>
                            <input type="password" name="old_password" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">New Password</label>
                            <input type="password" name="new_password" class="form-control" required>
                        </div>
                        <button type="submit" name="update_password" class="btn btn-dark w-100 fw-bold mt-2">Update Password</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Customer Information Section -->
        <div class="col-md-8">
            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    <h4 class="fw-bold mb-4 text-dark border-bottom pb-2">Customer Information</h4>
                    <form method="POST">
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Full Name</label>
                                <input type="text" name="full_name" class="form-control" value="<?php echo htmlspecialchars($user['full_name'] ?? ''); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Phone Number</label>
                                <input type="text" name="phone_number" class="form-control" value="<?php echo htmlspecialchars($user['phone_number'] ?? ''); ?>" required>
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Marital Status</label>
                                <select name="marital_status" class="form-select" required>
                                    <option value="">Select Status</option>
                                    <option value="Single" <?php echo ($user['marital_status'] ?? '') == 'Single' ? 'selected' : ''; ?>>Single</option>
                                    <option value="Married" <?php echo ($user['marital_status'] ?? '') == 'Married' ? 'selected' : ''; ?>>Married</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Dependents Count</label>
                                <input type="number" name="dependents_count" class="form-control" value="<?php echo htmlspecialchars($user['dependents_count'] ?? '0'); ?>" required>
                            </div>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Occupation</label>
                                <input type="text" name="occupation" class="form-control" value="<?php echo htmlspecialchars($user['occupation'] ?? ''); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Monthly Income (RM)</label>
                                <input type="number" step="0.01" name="monthly_income" class="form-control" value="<?php echo htmlspecialchars($user['monthly_income'] ?? '0.00'); ?>" required>
                            </div>
                        </div>

                        <button type="submit" name="save_details" class="btn btn-primary fw-bold px-4">Save Details</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>