<?php
include 'includes/header.php';
include 'includes/db_connect.php'; // make sure ada connect database

$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $full_name = $_POST['full_name'];
    $phone_number = $_POST['phone_number'];
    $marital_status = $_POST['marital_status'];
    $dependents_count = $_POST['dependents_count'];
    $occupation = $_POST['occupation'];
    $monthly_income = $_POST['monthly_income'];

    if ($password !== $confirm_password) {
        $error_message = 'Passwords do not match.';
    } else {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $role = 'CUSTOMER';

        // Insert into accounts
        $stmt_account = $conn->prepare("INSERT INTO accounts (email, password_hash, role) VALUES (?,?,?)");

        if (!$stmt_account) {
            die("Prepare failed: " . $conn->error); 
        }

        $stmt_account->bind_param("sss", $email, $hashed_password, $role);

        if ($stmt_account->execute()) {
            $account_id = $conn->insert_id;

            
            $stmt_customer = $conn->prepare("INSERT INTO customers (customer_id, full_name, phone_number, marital_status, dependents_count, occupation, monthly_income) VALUES (?,?,?,?,?,?,?)");

            if (!$stmt_customer) {
                die("Prepare failed: " . $conn->error); 
            }

            $stmt_customer->bind_param("isssisd", $account_id, $full_name, $phone_number, $marital_status, $dependents_count, $occupation, $monthly_income);

            if ($stmt_customer->execute()) {
                header("Location: login.php");
                exit();
            } else {
                $error_message = "Customer Error: " . $stmt_customer->error; 
            }

        } else {
            $error_message = "Account Error: " . $stmt_account->error;
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
                                <input type="text" name="full_name" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Email Address</label>
                                <input type="email" name="email" class="form-control" required>
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
                                <input type="text" name="phone_number" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Marital Status</label>
                                <select name="marital_status" class="form-select" required>
                                    <option value="" disabled selected>Select Status</option>
                                    <option value="SINGLE">Single</option>
                                    <option value="MARRIED">Married</option>
                                </select>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Dependents Count</label>
                                <input type="number" name="dependents_count" class="form-control" min="0" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Occupation</label>
                                <input type="text" name="occupation" class="form-control" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Monthly Income (RM)</label>
                                <input type="number" step="0.01" name="monthly_income" class="form-control" required>
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