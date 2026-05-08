<?php
// 1. Include Dependencies
include '../includes/db_connect.php';
include '../includes/auth_check.php';

// 2. Validate session roles and restrict unauthorized database connections.
protect_staff_page('STAFF', $conn);

// REQUIREMENT: Get the ACTIVE SESSION ID
// This ID identifies exactly which staff member is logged in.
$account_id = $_SESSION['account_id']; 

$alert_msg = '';

// 3. TASK LOGIC: Execute an UPDATE statement based on the active session ID.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['phone_number'])) {
    
    // Capture and sanitize input
    $phone = trim($_POST['phone_number']);
    
    // PREPARED STATEMENT: Targets the 'staff' table.
    // The WHERE clause strictly uses the $account_id from the active session.
    $stmt_upd = $conn->prepare("UPDATE staff SET phone_number = ? WHERE staff_id = ?");
    
    // Bind parameters: 's' for string (phone), 'i' for integer (session id)
    $stmt_upd->bind_param("si", $phone, $account_id);
    
    if ($stmt_upd->execute()) {
        $alert_msg = '<div class="alert alert-success fw-bold shadow-sm">
                        <i class="fas fa-check-circle me-2"></i>
                        Update Successful! Record for Session ID: ' . $account_id . ' has been updated.
                      </div>';
    } else {
        $alert_msg = '<div class="alert alert-danger fw-bold shadow-sm">
                        <i class="fas fa-times-circle me-2"></i>
                        Failed to update database: ' . $conn->error . '
                      </div>';
    }
    
    // Close statement to free up database resources
    $stmt_upd->close();
}

// 4. Fetch the refreshed data for display in the form
$stmt = $conn->prepare("SELECT s.*, a.email FROM staff s JOIN accounts a ON s.staff_id = a.account_id WHERE s.staff_id = ?");
$stmt->bind_param("i", $account_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

include '../includes/header.php';
?>

<div class="container my-5">
    <div class="mb-5">
        <h2 class="fw-bold m-0"><i class="fas fa-user-circle text-primary me-2"></i>My Profile</h2>
    </div>

    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm border-0">
                <div class="card-body p-5">
                    <h4 class="fw-bold mb-4 text-dark border-bottom pb-2">Staff Information</h4>
                    
                    <?php echo $alert_msg; ?>
                    
                    <form method="POST">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Email Address</label>
                                <input type="email" class="form-control bg-light" value="<?php echo htmlspecialchars($user['email']); ?>" readonly>
                                <small class="text-muted italic">Managed by HQ</small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Full Name</label>
                                <input type="text" class="form-control bg-light" value="<?php echo htmlspecialchars($user['full_name']); ?>" readonly>
                                <small class="text-muted italic">Managed by HR</small>
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
                                <small class="text-info italic">Update your contact details here.</small>
                            </div>
                        </div>
                        
                        <hr class="my-4">
                        
                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary btn-lg fw-bold px-5 shadow-sm">
                                <i class="fas fa-save me-2"></i>Update Staff Record
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>