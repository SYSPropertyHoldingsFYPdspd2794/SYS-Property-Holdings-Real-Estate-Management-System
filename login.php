<?php
include 'includes/header.php';
include 'includes/db_connect.php'; // make sure ada connect database
session_start(); //  for help session can save something

$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') { 
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT account_id, password_hash, role FROM accounts WHERE email = ?");

    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $account = $result->fetch_assoc();

        if (password_verify($password, $account['password_hash'])) {

            $_SESSION['account_id'] = $account['account_id'];
            $_SESSION['role'] = $account['role'];

            switch ($account['role']) {
                case 'CUSTOMER':
                    header("Location: customer/dashboard.php");
                    break;
                case 'STAFF':
                    header("Location: staff/dashboard.php");
                    break;
                case 'ADMIN':
                    header("Location: admin/dashboard.php");
                    break;
                default:
                    header("Location: login.php");
            }
            exit();

        } else {
            $error_message = 'Invalid email or password.';
        }

    } else {
        $error_message = 'Invalid email or password.';
    }
}
?>
    <div class="container my-5">
        <div class="row justify-content-center">
            <div class="col-md-5">
                <div class="card shadow-sm border-0">
                    <div class="card-body p-5">
                        <h2 class="text-center fw-bold mb-4">Sign In</h2>
                        <?php if ($error_message!== ''):?>
                            <div class="alert alert-danger"><?php echo htmlspecialchars($error_message);?></div>
                        <?php endif;?>
                        <form method="POST" action="login.php">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Email Address</label>
                                <input type="email" name="email" class="form-control" required>
                            </div>
                            <div class="mb-4">
                                <label class="form-label fw-bold">Password</label>
                                <input type="password" name="password" class="form-control" required>
                            </div>
                            <div class="d-grid mb-3">
                                <button type="submit" class="btn btn-primary btn-lg">Login</button>
                            </div>
                            <div class="text-center">
                                <span>Don't have an account? </span>
                                <a href="register.php" class="text-decoration-none">Register here</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php include 'includes/footer.php';?>

