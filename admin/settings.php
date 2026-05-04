<?php
session_start();
if (!isset($_SESSION['role']) |

| $_SESSION['role']!== 'ADMIN') {
    header("Location:../login.php");
    exit();
}
include '../includes/db_connect.php';

$alert = '';

if ($_SERVER === 'POST') {
    $rate = $_POST;
    $days = $_POST;

    $stmt1 = $conn->prepare("UPDATE system_settings SET setting_value =? WHERE setting_key = 'BASE_INTEREST_RATE'");
    $stmt1->bind_param("s", $rate);
    $stmt1->execute();

    $stmt2 = $conn->prepare("UPDATE system_settings SET setting_value =? WHERE setting_key = 'DATA_RETENTION_DAYS'");
    $stmt2->bind_param("s", $days);
    $stmt2->execute();

    $alert = '<div class="alert alert-success fw-bold">System settings updated successfully.</div>';
}

$settings =;
$res = $conn->query("SELECT * FROM system_settings");
while ($row = $res->fetch_assoc()) {
    $settings[$row['setting_key']] = $row;
}

include '../includes/header.php';
?>
<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow border-0">
                <div class="card-header bg-dark text-white p-4">
                    <h3 class="fw-bold m-0"><i class="fas fa-cogs me-2"></i>Global System Settings</h3>
                </div>
                <div class="card-body p-5">
                    <?php echo $alert;?>
                    <form method="POST">
                        <div class="mb-4">
                            <label class="form-label fw-bold fs-5">Base Interest Rate (%)</label>
                            <input type="text" name="BASE_INTEREST_RATE" class="form-control form-control-lg" value="<?php echo htmlspecialchars($settings['setting_value']);?>" required>
                            <small class="text-muted"><?php echo htmlspecialchars($settings['description']);?></small>
                        </div>
                        <div class="mb-5">
                            <label class="form-label fw-bold fs-5">Data Retention Days (PDPA)</label>
                            <input type="number" name="DATA_RETENTION_DAYS" class="form-control form-control-lg" value="<?php echo htmlspecialchars($settings['setting_value']);?>" required>
                            <small class="text-muted"><?php echo htmlspecialchars($settings['description']);?></small>
                        </div>
                        <button type="submit" class="btn btn-primary btn-lg w-100 fw-bold">Save Configuration</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include '../includes/footer.php';?>

