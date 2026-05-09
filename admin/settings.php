<?php
include '../includes/db_connect.php';
include '../includes/auth_check.php';

// Ensure only ADMIN can access global configuration
protect_admin_page('ADMIN', $conn);

$alert = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Handle Global System Settings Update
    if (isset($_POST['save_config'])) {
        $rate = $_POST['BASE_INTEREST_RATE'];
        $days = $_POST['DATA_RETENTION_DAYS'];

        // Update Base Interest Rate
        $stmt1 = $conn->prepare("UPDATE system_settings SET setting_value = ? WHERE setting_key = 'BASE_INTEREST_RATE'");
        $stmt1->bind_param("s", $rate);
        $stmt1->execute();

        // Update Data Retention Days
        $stmt2 = $conn->prepare("UPDATE system_settings SET setting_value = ? WHERE setting_key = 'DATA_RETENTION_DAYS'");
        $stmt2->bind_param("s", $days);
        $stmt2->execute();

        $alert = '<div class="alert alert-success fw-bold shadow-sm"><i class="fas fa-check-circle me-2"></i>System settings updated successfully.</div>';
    }
}

// Fetch current settings for the form fields
$settings = [];
$res = $conn->query("SELECT * FROM system_settings");
while ($row = $res->fetch_assoc()) {
    $settings[$row['setting_key']] = $row;
}

include '../includes/header.php';
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            
            <?php echo $alert; ?>

            <!-- Global System Settings Card -->
            <div class="card shadow border-0">
                <div class="card-header bg-dark text-white p-4">
                    <h3 class="fw-bold m-0"><i class="fas fa-cogs me-2"></i>Global System Settings</h3>
                </div>
                <div class="card-body p-5">
                    <form method="POST">
                        <div class="mb-4">
                            <label class="form-label fw-bold">Base Interest Rate (%)</label>
                            <input type="text" name="BASE_INTEREST_RATE" class="form-control form-control-lg" 
                                   value="<?php echo htmlspecialchars($settings['BASE_INTEREST_RATE']['setting_value'] ?? '3.85'); ?>" required>
                            <small class="text-muted">Universal rate used for property loan calculations.</small>
                        </div>
                        
                        <div class="mb-5">
                            <label class="form-label fw-bold">Data Retention Days (PDPA)</label>
                            <input type="number" name="DATA_RETENTION_DAYS" class="form-control form-control-lg" 
                                   value="<?php echo htmlspecialchars($settings['DATA_RETENTION_DAYS']['setting_value'] ?? '7'); ?>" required>
                            <small class="text-muted">Number of days to store user data before purging for legal compliance.</small>
                        </div>

                        <div class="d-flex justify-content-between align-items-center">
                            <a href="profile.php" class="text-decoration-none text-muted">
                                <i class="fas fa-user-shield me-1"></i> Go to Profile Page
                            </a>
                            <button type="submit" name="save_config" class="btn btn-primary btn-lg fw-bold px-5 shadow-sm">
                                Save Configuration
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
            <div class="text-center mt-4">
                <small class="text-muted italic">Note: Security and password management are moved to the Admin Profile page.</small>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>