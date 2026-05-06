<?php
session_start();

/** 
 * DIRECT ACCESS BYPASS
 * Manually setting session variables to bypass login and show header buttons.
 */
$_SESSION['role'] = 'STAFF'; 
$_SESSION['account_id'] = 1; 
$_SESSION['full_name'] = 'Staff Member';
$_SESSION['region'] = 'Johor';

include '../includes/db_connect.php';

$account_id = $_SESSION['account_id'];
$alert_msg = '';

// BUG FIX: Changed $_SERVER to $_SERVER['REQUEST_METHOD']
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['phone_number'])) {
    $phone = $_POST['phone_number'];
    
    // Using staff_id as the primary key for the update
    $stmt_upd = $conn->prepare("UPDATE staff SET phone_number = ? WHERE staff_id = ?");
    $stmt_upd->bind_param("si", $phone, $account_id);
    
    if ($stmt_upd->execute()) {
        $alert_msg = '<div class="alert alert-success fw-bold shadow-sm"><i class="fas fa-check-circle me-2"></i>Phone number updated successfully.</div>';
    } else {
        $alert_msg = '<div class="alert alert-danger fw-bold shadow-sm"><i class="fas fa-times-circle me-2"></i>Failed to update phone number.</div>';
    }
}

// Fetch user details
$stmt = $conn->prepare("SELECT s.*, a.email FROM staff s JOIN accounts a ON s.staff_id = a.account_id WHERE s.staff_id = ?");
$stmt->bind_param("i", $account_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

include '../includes/header.php';
?>

<div class="container my-5">
    <!-- Header Row consistent with other pages -->
    <div class="mb-5">
        <h2 class="fw-bold m-0"><i class="fas fa-user-circle text-primary me-2"></i>My Profile</h2>
    </div>

    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm border-0">
                <div class="card-body p-5">
                    <h4 class="fw-bold mb-4 text-dark">Personal Information</h4>
                    
                    <?php echo $alert_msg; ?>
                    
                    <form method="POST">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Email Address</label>
                                <input type="email" class="form-control bg-light" value="<?php echo htmlspecialchars($user['email']); ?>" readonly>
                                <small class="text-muted">Email cannot be changed.</small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Full Name</label>
                                <input type="text" class="form-control bg-light" value="<?php echo htmlspecialchars($user['full_name']); ?>" readonly>
                                <small class="text-muted">Contact HR to change name.</small>
                            </div>
                        </div>
                        
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Assigned State</label>
                                <input type="text" class="form-control bg-light" value="<?php echo htmlspecialchars($user['assigned_state']); ?>" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-primary">Phone Number</label>
                                <input type="text" name="phone_number" class="form-control border-primary" value="<?php echo htmlspecialchars($user['phone_number']); ?>" required>
                                <small class="text-info italic">This is your only editable field.</small>
                            </div>
                        </div>
                        
                        <hr class="my-4">
                        
                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary btn-lg fw-bold px-5">
                                <i class="fas fa-save me-2"></i>Save Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>