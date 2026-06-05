<?php
require_once '../includes/db_connect.php';
require_once '../includes/auth_check.php';

protect_admin_page('ADMIN', $conn);

$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['run_purge'])) {
        ob_start();
        include 'cron_purge.php';
        $msg = ob_get_clean();
    } elseif (isset($_POST['delete_doc_id'])) {
        $id = (int)$_POST['delete_doc_id'];
        $stmt = $conn->prepare("SELECT file_path FROM documents WHERE document_id = ? AND is_purged = FALSE");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $doc = $stmt->get_result()->fetch_assoc();
        
        if ($doc) {
            $path = $doc['file_path'];
            $base_dir = realpath(__DIR__ . '/..');
            $real_path = $base_dir . '/' . ltrim($path, '/');
            
            if (file_exists($real_path)) {
                unlink($real_path);
            } elseif (file_exists($path)) {
                // Fallback for absolute paths or current working dir logic
                unlink($path);
            }
            
            $upd = $conn->prepare("UPDATE documents SET is_purged = TRUE, purged_at = CURRENT_TIMESTAMP WHERE document_id = ?");
            $upd->bind_param("i", $id);
            $upd->execute();
            
            $log = $conn->prepare("INSERT INTO audit_logs (action_type, entity_type, entity_id) VALUES ('DOCUMENT_PURGED', 'document_id', ?)");
            $log->bind_param("i", $id);
            $log->execute();
            
            $msg = "Document #$id purged successfully.";
        } else {
            $msg = "Document already purged or not found.";
        }
    }
}

// Fetch retention days
$res = $conn->query("SELECT setting_value FROM system_settings WHERE setting_key = 'DATA_RETENTION_DAYS'");
$retention_days = $res && $res->num_rows > 0 ? (int)$res->fetch_assoc()['setting_value'] : 7;

// Fetch documents
$query = "
    SELECT d.*, c.full_name,
           COALESCE(p1.project_name, p2.project_name) AS project_name,
           COALESCE(p1.state, p2.state) AS property_state,
           COALESCE(appt.status, aha.status) AS entity_status,
           appt.appointment_date,
           appt.appointment_time
    FROM documents d
    JOIN customers c ON d.customer_id = c.customer_id
    LEFT JOIN appointments appt ON d.related_to_type = 'APPOINTMENT' AND d.related_to_id = appt.appointment_id
    LEFT JOIN properties p1 ON appt.property_id = p1.property_id
    LEFT JOIN affordable_housing_applications aha ON d.related_to_type = 'APPLICATION' AND d.related_to_id = aha.application_id
    LEFT JOIN properties p2 ON aha.property_id = p2.property_id
    ORDER BY d.uploaded_at DESC
";
$documents = $conn->query($query);

include '../includes/header.php';
?>

<div class="container my-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold">PDPA Document Management</h2>
        <form method="POST" class="confirm-action-form" data-confirm-title="Execute Purge Script" data-confirm-message="Are you sure you want to execute the purge script? This action cannot be undone.">
            <button type="submit" name="run_purge" class="btn btn-danger btn-lg fw-bold">Execute Purge Script (Cron)</button>
        </form>
    </div>

    <?php if ($msg): ?>
        <div class="alert alert-info"><?php echo htmlspecialchars($msg); ?></div>
    <?php endif; ?>
    
    <div class="alert alert-warning">
        <div class="text-center">
            <strong>PDPA POLICY</strong><br>
            Sensitive documents are securely retained while the relevant request is active.<br>
            These documents will be deleted 7 days after the status changes.
        </div>
        <br>
        <div class="text-start">
            Appointments: COMPLETED / CANCELLED / NO SHOW / EXPIRED<br>
            Applications: REJECTED / WON
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Uploaded By</th>
                            <th>Purpose</th>
                            <th>Property Name</th>
                            <th>STATE</th>
                            <th>Upload At</th>
                            <th>Deletion Date</th>
                            <th style="width: 80px;">Status</th>
                            <th style="width: 120px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($doc = $documents->fetch_assoc()): 
                            $entity_status = strtoupper($doc['entity_status'] ?? 'UNKNOWN');
                            $appointment_time = !empty($doc['appointment_time']) ? $doc['appointment_time'] : '00:00:00';
                            $appointment_timestamp = !empty($doc['appointment_date']) ? strtotime($doc['appointment_date'] . ' ' . $appointment_time) : false;
                            $is_appointment_expired = $doc['related_to_type'] === 'APPOINTMENT'
                                && in_array($entity_status, ['REQUESTED', 'ASSIGNED'], true)
                                && $appointment_timestamp
                                && $appointment_timestamp <= time();
                            if ($is_appointment_expired) {
                                $entity_status = 'EXPIRED';
                            }
                            $is_terminal = in_array($entity_status, ['COMPLETED', 'CANCELLED', 'NO_SHOW', 'EXPIRED', 'REJECTED', 'WINNER']);
                            
                            $base_time = strtotime($doc['uploaded_at']);
                            if ($doc['related_to_type'] === 'APPOINTMENT' && $appointment_timestamp && in_array($entity_status, ['COMPLETED', 'NO_SHOW', 'EXPIRED'])) {
                                $base_time = $appointment_timestamp;
                            }
                            
                            $upload_time = strtotime($doc['uploaded_at']);
                            $delete_time = strtotime("+$retention_days days", $base_time);
                            $is_expired = $is_terminal && (time() >= $delete_time);
                        ?>
                        <tr>
                            <td>#<?php echo $doc['document_id']; ?></td>
                            <td><?php echo htmlspecialchars($doc['full_name']); ?></td>
                            <td><span class="badge bg-secondary"><?php echo htmlspecialchars($doc['related_to_type']); ?></span></td>
                            <td><?php echo htmlspecialchars($doc['project_name'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($doc['property_state'] ?? 'N/A'); ?></td>
                            <td>
                                <?php echo date('Y-m-d', $upload_time); ?><br>
                                <?php echo date('H:i', $upload_time); ?>
                            </td>
                            <td>
                                <?php if (!$is_terminal): ?>
                                    <?php echo htmlspecialchars($entity_status); ?>
                                <?php else: ?>
                                    <?php echo date('Y-m-d', $delete_time); ?><br>
                                    <?php echo date('H:i', $delete_time); ?>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($doc['is_purged']): ?>
                                    <span class="badge bg-secondary">Not Found</span>
                                <?php elseif ($is_expired): ?>
                                    <span class="badge bg-danger">Expired</span>
                                <?php elseif ($is_terminal): ?>
                                    <span class="badge bg-warning text-dark">Countdown</span>
                                <?php else: ?>
                                    <span class="badge bg-success">Active</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (!$doc['is_purged']): ?>
                                    <a href="../<?php echo ltrim($doc['file_path'], '/'); ?>" target="_blank" class="btn btn-sm btn-outline-primary">View</a>
                                    <form method="POST" class="d-inline confirm-action-form" data-confirm-title="Delete Document" data-confirm-message="Are you sure you want to prematurely delete this document? This action cannot be undone.">
                                        <input type="hidden" name="delete_doc_id" value="<?php echo $doc['document_id']; ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                    </form>
                                <?php else: ?>
                                    <button class="btn btn-sm btn-secondary" disabled>View</button>
                                    <button class="btn btn-sm btn-secondary" disabled>Delete</button>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
