<?php
session_start();
if (!isset($_SESSION['role']) |

| $_SESSION['role']!== 'STAFF') {
    header("Location:../login.php");
    exit();
}
include '../includes/db_connect.php';

$account_id = $_SESSION['account_id'];
$alert_msg = '';

if ($_SERVER === 'POST' && isset($_POST['phone_number'])) {
    $phone = $_POST['phone_number'];
    $stmt_upd = $conn->prepare("UPDATE staff SET phone_number =? WHERE staff_id =?");
    $stmt_upd->bind_param("si", $phone, $account_id);
    if ($stmt_upd->execute()) {
        $alert_msg = '<div class="alert alert-success fw-bold"><i class="fas fa-check-circle me-2"></i>Phone number updated successfully.</div>';
    } else {
        $alert_msg = '<div class="alert alert-danger fw-bold"><i class="fas fa-times-circle me-2"></i>Failed to update phone number.</div>';
    }
}

$stmt = $conn->prepare("SELECT s.*, a.email FROM staff s JOIN accounts a ON s.staff_id = a.account_id WHERE s.staff_id =?");
$stmt->bind_param("i", $account_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

include '../includes/header.php';
?>
<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow border-0">
                <div class="card-body p-5">
                    <h3 class="fw-bold mb-4"><i class="fas fa-user-tie text-primary me-2"></i>Staff Profile</h3>
                    <?php echo $alert_msg;?>
                    <form method="POST">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Email Address</label>
                                <input type="email" class="form-control bg-light" value="<?php echo htmlspecialchars($user['email']);?>" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Full Name</label>
                                <input type="text" class="form-control bg-light" value="<?php echo htmlspecialchars($user['full_name']);?>" readonly>
                            </div>
                        </div>
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Assigned State</label>
                                <input type="text" class="form-control bg-light" value="<?php echo htmlspecialchars($user['assigned_state']);?>" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Phone Number</label>
                                <input type="text" name="phone_number" class="form-control" value="<?php echo htmlspecialchars($user['phone_number']);?>" required>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary btn-lg fw-bold">Update Profile</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include '../includes/footer.php';?>

