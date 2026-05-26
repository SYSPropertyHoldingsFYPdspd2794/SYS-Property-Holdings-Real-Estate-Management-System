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

// Fetch expired documents violating PDPA retention policy
$retention_days = (int) ($settings['DATA_RETENTION_DAYS']['setting_value'] ?? 7);
$stmt_expired = $conn->prepare("SELECT document_id, document_type, file_path, uploaded_at FROM documents WHERE is_purged = FALSE AND uploaded_at <= DATE_SUB(NOW(), INTERVAL ? DAY)");
$stmt_expired->bind_param("i", $retention_days);
$stmt_expired->execute();
$expired_docs_res = $stmt_expired->get_result();
$expired_docs = [];
while ($doc = $expired_docs_res->fetch_assoc()) {
    $expired_docs[] = $doc;
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
            
            <div class="text-center mt-4 mb-5">
                <small class="text-muted fst-italic">Note: Security and password management are moved to the Admin Profile page.</small>
            </div>

            <!-- PDPA Compliance Card -->
            <div class="card shadow border-0 mt-5 mb-5">
                <div class="card-header bg-danger text-white p-4 d-flex justify-content-between align-items-center">
                    <h3 class="fw-bold m-0"><i class="fas fa-file-excel me-2"></i>PDPA Compliance - Expired Documents</h3>
                    <?php if (count($expired_docs) > 0): ?>
                        <button id="btnExecutePurge" class="btn btn-warning fw-bold text-dark shadow-sm">
                            <i class="fas fa-trash-alt me-2"></i> Execute Purge Script
                        </button>
                    <?php else: ?>
                        <span class="badge bg-success fs-6"><i class="fas fa-check-circle me-1"></i> Fully Compliant</span>
                    <?php endif; ?>
                </div>
                <div class="card-body p-4">
                    <p class="text-muted">Documents older than <strong><?php echo $retention_days; ?> days</strong> violating the retention policy.</p>
                    
                    <div id="purgeAlertContainer"></div>

                    <div class="table-responsive">
                        <table class="table table-bordered table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Document ID</th>
                                    <th>Type</th>
                                    <th>File Path</th>
                                    <th>Uploaded At</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($expired_docs) > 0): ?>
                                    <?php foreach ($expired_docs as $doc): ?>
                                        <tr>
                                            <td class="fw-bold text-danger">#<?php echo htmlspecialchars($doc['document_id']); ?></td>
                                            <td><?php echo htmlspecialchars($doc['document_type']); ?></td>
                                            <td class="font-monospace small"><?php echo htmlspecialchars($doc['file_path']); ?></td>
                                            <td><?php echo htmlspecialchars($doc['uploaded_at']); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="4" class="text-center py-4 text-muted">
                                            No expired documents found. The system is compliant.
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const btnExecutePurge = document.getElementById('btnExecutePurge');
    if (btnExecutePurge) {
        btnExecutePurge.addEventListener('click', function() {
            window.showConfirmModal({
                title: 'Execute Purge Script',
                message: 'Are you sure you want to permanently delete all expired documents? This action cannot be undone.',
                confirmText: 'Execute Purge',
                confirmClass: 'btn-danger',
                onConfirm: function () {
                btnExecutePurge.disabled = true;
                btnExecutePurge.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Purging...';
                
                fetch('cron_purge.php', { method: 'POST' })
                    .then(response => response.text())
                    .then(data => {
                        const alertContainer = document.getElementById('purgeAlertContainer');
                        alertContainer.innerHTML = '<div class="alert alert-success fw-bold"><i class="fas fa-check-circle me-2"></i>' + data + ' Redirecting...</div>';
                        setTimeout(() => {
                            window.location.reload();
                        }, 2000);
                    })
                    .catch(error => {
                        const alertContainer = document.getElementById('purgeAlertContainer');
                        alertContainer.innerHTML = '<div class="alert alert-danger fw-bold"><i class="fas fa-exclamation-triangle me-2"></i>Error executing purge.</div>';
                        btnExecutePurge.disabled = false;
                        btnExecutePurge.innerHTML = '<i class="fas fa-trash-alt me-2"></i> Execute Purge Script';
                    });
                }
            });
        });
    }
});
</script>

<?php include '../includes/footer.php'; ?>
