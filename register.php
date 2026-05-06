<?php
include 'includes/header.php';
include 'includes/db_connect.php'; 

$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Collect and sanitize input data
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $full_name = mysqli_real_escape_string($conn, $_POST['full_name']);
    $phone_number = mysqli_real_escape_string($conn, $_POST['phone_number']);
    $marital_status = $_POST['marital_status'];
    $dependents_count = (int)$_POST['dependents_count'];
    $occupation = mysqli_real_escape_string($conn, $_POST['occupation']);
    $monthly_income = (float)$_POST['monthly_income'];

    if ($password !== $confirm_password) {
        $error_message = 'Passwords do not match.';
    } else {
        $stmt_check = $conn->prepare("SELECT email FROM accounts WHERE email = ?");
        $stmt_check->bind_param("s", $email);
        $stmt_check->execute();
        $stmt_check->store_result();

        if ($stmt_check->num_rows > 0) {
            $error_message = "The email address '$email' is already registered. Please use another one.";
            $stmt_check->close();
        } else {
            $stmt_check->close();
            
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $role = 'CUSTOMER';

            $conn->begin_transaction();

            try {
                $stmt_account = $conn->prepare("INSERT INTO accounts (email, password_hash, role) VALUES (?,?,?)");
                $stmt_account->bind_param("sss", $email, $hashed_password, $role);
                $stmt_account->execute();
                
                $account_id = $conn->insert_id;

                $stmt_customer = $conn->prepare("INSERT INTO customers (customer_id, full_name, phone_number, marital_status, dependents_count, occupation, monthly_income) VALUES (?,?,?,?,?,?,?)");
                $stmt_customer->bind_param("isssisd", $account_id, $full_name, $phone_number, $marital_status, $dependents_count, $occupation, $monthly_income);
                $stmt_customer->execute();

                $conn->commit();
                
                header("Location: login.php?registration=success");
                exit();

            } catch (Exception $e) {
                $conn->rollback();
                $error_message = "Registration failed. Please try again later.";
            }
        }
    }
}
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm border-0">
                <div class="card-body p-5">
                    <h2 class="text-center fw-bold mb-4">Create an Account</h2>

                    <?php if ($error_message !== ''): ?>
                        <div class="alert alert-danger">
                            <?php echo htmlspecialchars($error_message); ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="register.php">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Full Name</label>
                                <input type="text" name="full_name" class="form-control" 
                                       value="<?php echo isset($_POST['full_name']) ? htmlspecialchars($_POST['full_name']) : ''; ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Email Address</label>
                                <input type="email" name="email" class="form-control" 
                                       value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Password</label>
                                <input type="password" name="password" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Confirm Password</label>
                                <input type="password" name="confirm_password" class="form-control" required>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Phone Number</label>
                                <input type="text" name="phone_number" class="form-control" 
                                       value="<?php echo isset($_POST['phone_number']) ? htmlspecialchars($_POST['phone_number']) : ''; ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Marital Status</label>
                                <select name="marital_status" class="form-select" required>
                                    <option value="" disabled <?php echo !isset($_POST['marital_status']) ? 'selected' : ''; ?>>Select Status</option>
                                    <option value="SINGLE" <?php echo (isset($_POST['marital_status']) && $_POST['marital_status'] == 'SINGLE') ? 'selected' : ''; ?>>Single</option>
                                    <option value="MARRIED" <?php echo (isset($_POST['marital_status']) && $_POST['marital_status'] == 'MARRIED') ? 'selected' : ''; ?>>Married</option>
                                </select>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Dependents Count</label>
                                <input type="number" name="dependents_count" class="form-control" min="0" 
                                       value="<?php echo isset($_POST['dependents_count']) ? htmlspecialchars($_POST['dependents_count']) : '0'; ?>" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Occupation</label>
                                <input type="text" name="occupation" class="form-control" 
                                       value="<?php echo isset($_POST['occupation']) ? htmlspecialchars($_POST['occupation']) : ''; ?>" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Monthly Income (RM)</label>
                                <input type="number" step="0.01" name="monthly_income" class="form-control" 
                                       value="<?php echo isset($_POST['monthly_income']) ? htmlspecialchars($_POST['monthly_income']) : ''; ?>" required>
                            </div>
                        </div>

                        <div class="d-grid mt-4">
                            <button type="submit" class="btn btn-primary btn-lg">Register Now</button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>