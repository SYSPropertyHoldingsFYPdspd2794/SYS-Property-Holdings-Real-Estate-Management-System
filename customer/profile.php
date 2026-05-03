<?php
session_start();
if (!isset($_SESSION['role']) |

| $_SESSION['role']!== 'CUSTOMER') {
    header("Location:../login.php");
    exit();
}
include '../includes/db_connect.php';

$account_id = $_SESSION['account_id'];
$alert_msg = '';
$alert_type = '';

if ($_SERVER === 'POST') {
    $phone = $_POST['phone_number'];
    $marital = $_POST['marital_status'];
    $dep = $_POST['dependents_count'];
    $occ = $_POST['occupation'];
    $inc = $_POST['monthly_income'];
    $update = $conn->prepare("UPDATE customers SET phone_number=?, marital_status=?, dependents_count=?, occupation=?, monthly_income=? WHERE customer_id=?");
    $update->bind_param("ssisdi", $phone, $marital, $dep, $occ, $inc, $account_id);
    if ($update->execute()) {
        $alert_msg = "Profile updated successfully.";
        $alert_type = "success";
    } else {
        $alert_msg = "Update failed.";
        $alert_type = "danger";
    }
}

$stmt = $conn->prepare("SELECT c.*, a.email FROM customers c JOIN accounts a ON c.customer_id = a.account_id WHERE c.customer_id =?");
$stmt->bind_param("i", $account_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

include '../includes/header.php';
?>
<div class="container my-5">
    <div class="card shadow-sm border-0">
        <div class="card-body p-5">
            <h3 class="fw-bold mb-4">My Profile</h3>
            <?php if ($alert_msg!== ''):?>
                <div class="alert alert-<?php echo $alert_type;?>"><?php echo $alert_msg;?></div>
            <?php endif;?>
            <form method="POST">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Email</label>
                        <input type="email" class="form-control bg-light" value="<?php echo htmlspecialchars($user['email']);?>" readonly>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Full Name</label>
                        <input type="text" class="form-control bg-light" value="<?php echo htmlspecialchars($user['full_name']);?>" readonly>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Phone Number</label>
                        <input type="text" name="phone_number" class="form-control" value="<?php echo htmlspecialchars($user['phone_number']);?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Marital Status</label>
                        <select name="marital_status" class="form-select" required>
                            <option value="SINGLE" <?php echo $user['marital_status'] === 'SINGLE'? 'selected' : '';?>>Single</option>
                            <option value="MARRIED" <?php echo $user['marital_status'] === 'MARRIED'? 'selected' : '';?>>Married</option>
                        </select>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Dependents Count</label>
                        <input type="number" name="dependents_count" class="form-control" value="<?php echo htmlspecialchars($user['dependents_count']);?>" min="0" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Occupation</label>
                        <input type="text" name="occupation" class="form-control" value="<?php echo htmlspecialchars($user['occupation']);?>" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Monthly Income (RM)</label>
                        <input type="number" step="0.01" name="monthly_income" class="form-control" value="<?php echo htmlspecialchars($user['monthly_income']);?>" required>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary mt-3 px-4 fw-bold">Update Profile</button>
            </form>
        </div>
    </div>
</div>
<?php include '../includes/footer.php';?>

