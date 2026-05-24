<?php
include 'includes/header.php';
include 'includes/db_connect.php'; 
include 'includes/functions.php'; 

$error_message = '';
$show_duplicate_alert = false; 
$show_success_alert = false; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $full_name = mysqli_real_escape_string($conn, $_POST['full_name']);
    $phone_number = mysqli_real_escape_string($conn, $_POST['phone_number']);
    $marital_status = $_POST['marital_status'] ?? '';
    $dependents_count = (int)$_POST['dependents_count'];
    $occupation = mysqli_real_escape_string($conn, $_POST['occupation']);
    $monthly_income = (float)$_POST['monthly_income'];

    if (!validate_password_strength($password)) {
        $error_message = 'Password is too weak. Please follow the requirements below.';
    } elseif ($password !== $confirm_password) {
        $error_message = 'Passwords do not match.';
    } else {
        $stmt_check = $conn->prepare("SELECT email FROM accounts WHERE email = ?");
        $stmt_check->bind_param("s", $email);
        $stmt_check->execute();
        $stmt_check->store_result();

        if ($stmt_check->num_rows > 0) {
            $show_duplicate_alert = true;
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
                $show_success_alert = true;
            } catch (Exception $e) {
                $conn->rollback();
                $error_message = "Registration failed. Please try again later.";
            }
        }
    }
}
?>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="container my-5 py-4">
    <div class="row justify-content-center">
        <div class="col-md-10 col-lg-8">
            <div class="card shadow-sm border-0 auth-card">
                <div class="card-body p-5">
                    <div class="text-center mb-5">
                        <div class="section-kicker mb-2">Start your home journey</div>
                        <h2 class="fw-bold mb-2">Create an Account</h2>
                        <p class="text-muted mb-0">Save properties, book visits, and track affordable housing applications.</p>
                    </div>

                    <?php if ($error_message !== ''): ?>
                        <div class="alert alert-danger shadow-sm border-0 mb-4">
                            <i class="fas fa-exclamation-circle me-2"></i> <?php echo htmlspecialchars($error_message); ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="register.php">
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Full Name</label>
                                <input type="text" name="full_name" class="form-control py-2" value="<?php echo isset($_POST['full_name']) ? htmlspecialchars($_POST['full_name']) : ''; ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Email Address</label>
                                <input type="email" name="email" class="form-control py-2" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
                            </div>
                        </div>

                        <div class="row mb-2">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Password</label>
                                <input type="password" name="password" class="form-control py-2" required onkeyup="checkPasswordRealtime(this.value, 'reg_')">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Confirm Password</label>
                                <input type="password" name="confirm_password" class="form-control py-2" required>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-12">
                                <div class="p-3 border rounded bg-light shadow-sm">
                                    <ul class="list-unstyled mb-0 d-flex flex-wrap justify-content-between align-items-center">
                                        <li id="reg_length" class="text-danger small mx-2 my-1"><i class="fas fa-times-circle me-1"></i> 8+ Characters</li>
                                        <li id="reg_upper" class="text-danger small mx-2 my-1"><i class="fas fa-times-circle me-1"></i> Uppercase (A-Z)</li>
                                        <li id="reg_lower" class="text-danger small mx-2 my-1"><i class="fas fa-times-circle me-1"></i> Lowercase (a-z)</li>
                                        <li id="reg_number" class="text-danger small mx-2 my-1"><i class="fas fa-times-circle me-1"></i> Number (0-9)</li>
                                        <li id="reg_symbol" class="text-danger small mx-2 my-1"><i class="fas fa-times-circle me-1"></i> Symbol (@#$!)</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Phone Number</label>
                                <input type="text" name="phone_number" class="form-control py-2" value="<?php echo isset($_POST['phone_number']) ? htmlspecialchars($_POST['phone_number']) : ''; ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Marital Status</label>
                                <select name="marital_status" class="form-select py-2" required>
                                    <option value="" disabled <?php echo !isset($_POST['marital_status']) ? 'selected' : ''; ?>>Select Status</option>
                                    <option value="SINGLE" <?php echo (isset($_POST['marital_status']) && $_POST['marital_status'] == 'SINGLE') ? 'selected' : ''; ?>>Single</option>
                                    <option value="MARRIED" <?php echo (isset($_POST['marital_status']) && $_POST['marital_status'] == 'MARRIED') ? 'selected' : ''; ?>>Married</option>
                                </select>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Dependents</label>
                                <input type="number" name="dependents_count" class="form-control py-2" min="0" value="<?php echo isset($_POST['dependents_count']) ? htmlspecialchars($_POST['dependents_count']) : '0'; ?>" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Occupation</label>
                                <input type="text" name="occupation" class="form-control py-2" value="<?php echo isset($_POST['occupation']) ? htmlspecialchars($_POST['occupation']) : ''; ?>" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Income (RM)</label>
                                <input type="number" step="0.01" name="monthly_income" class="form-control py-2" value="<?php echo isset($_POST['monthly_income']) ? htmlspecialchars($_POST['monthly_income']) : ''; ?>" required>
                            </div>
                        </div>

                        <div class="d-grid mt-5">
                            <button type="submit" class="btn btn-primary btn-lg fw-bold shadow-sm">Register Now</button>
                        </div>
                    </form>
                    <div class="text-center mt-4">
                        <span class="text-muted">Already have an account?</span> <a href="login.php" class="fw-bold text-decoration-none">Login here</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if ($show_duplicate_alert): ?>
<script>
    Swal.fire({
        icon: 'error',
        title: 'Email Already Registered',
        text: 'The email "<?php echo $email; ?>" is already in use. Please use another or login.',
        confirmButtonColor: '#0d6efd'
    });
</script>
<?php endif; ?>

<?php if ($show_success_alert): ?>
<script>
    Swal.fire({
        icon: 'success',
        title: 'Registration Successful',
        text: 'Welcome to SYS Property Holdings!',
        confirmButtonColor: '#0d6efd'
    }).then(() => {
        window.location.href = 'login.php?registration=success';
    });
</script>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>
