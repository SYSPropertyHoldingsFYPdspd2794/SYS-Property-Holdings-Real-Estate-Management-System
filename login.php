<?php
// 1. ALWAYS put session_start() at the very top before any HTML or includes
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include 'includes/db_connect.php'; 

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
            // Save info to session
            $_SESSION['account_id'] = $account['account_id'];
            $_SESSION['role'] = $account['role'];
            
            $role = $account['role'];

            // --- YOUR REQUESTED LOGIC START ---
            if ($role === 'ADMIN') {
                header('Location: admin/dashboard.php');
            } elseif ($role === 'STAFF') {
                header('Location: staff/dashboard.php');
            } else { // CUSTOMER
                header('Location: customer/dashboard.php');
            }
            // --- YOUR REQUESTED LOGIC END ---
            
            exit();

        } else {
            $error_message = 'Invalid email or password.';
        }
    } else {
        $error_message = 'Invalid email or password.';
    }
}

include 'includes/header.php';
?>

<div class="container my-5 py-4">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card shadow-sm border-0 auth-card">
                <div class="card-body p-5">
                    <div class="text-center mb-4">
                        <div class="section-kicker mb-2">Welcome back</div>
                        <h2 class="fw-bold mb-2">Sign In</h2>
                        <p class="text-muted mb-0">Access your dashboard, saved homes, and appointments.</p>
                    </div>
                    
                    <?php if ($error_message !== ''): ?>
                        <div class="alert alert-danger"><?php echo htmlspecialchars($error_message); ?></div>
                    <?php endif; ?>
                    
                    <form method="POST" action="login.php">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Email Address</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                        <div class="mb-2">
                            <label class="form-label fw-bold">Password</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                        <div class="text-end mb-4">
                            <a href="https://wa.link/bzspzh" target="_blank" rel="noopener noreferrer" class="small fw-bold text-decoration-none">
                                Forgot Password?
                            </a>
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

<?php include 'includes/footer.php'; ?>
