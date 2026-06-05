<?php
require_once '../includes/db_connect.php';

$res = $conn->query("SELECT setting_value FROM system_settings WHERE setting_key = 'DATA_RETENTION_DAYS'");
if ($res && $res->num_rows > 0) {
    $retention_days = (int) $res->fetch_assoc()['setting_value'];
    
    $stmt = $conn->prepare("
        SELECT d.document_id, d.file_path
        FROM documents d
        LEFT JOIN appointments appt ON d.related_to_type = 'APPOINTMENT' AND d.related_to_id = appt.appointment_id
        LEFT JOIN affordable_housing_applications aha ON d.related_to_type = 'APPLICATION' AND d.related_to_id = aha.application_id
        WHERE d.is_purged = FALSE
        AND (
            (
                d.related_to_type = 'APPOINTMENT'
                AND (
                    (appt.status IN ('COMPLETED', 'NO_SHOW') AND TIMESTAMP(appt.appointment_date, appt.appointment_time) <= DATE_SUB(NOW(), INTERVAL ? DAY))
                    OR (appt.status = 'CANCELLED' AND d.uploaded_at <= DATE_SUB(NOW(), INTERVAL ? DAY))
                    OR (appt.status IN ('REQUESTED', 'ASSIGNED') AND TIMESTAMP(appt.appointment_date, appt.appointment_time) <= DATE_SUB(NOW(), INTERVAL ? DAY))
                )
            )
            OR (
                d.related_to_type = 'APPLICATION'
                AND aha.status IN ('REJECTED', 'WINNER')
                AND d.uploaded_at <= DATE_SUB(NOW(), INTERVAL ? DAY)
            )
        )
    ");
    $stmt->bind_param("iiii", $retention_days, $retention_days, $retention_days, $retention_days);
    $stmt->execute();
    $documents = $stmt->get_result();
    
    while ($doc = $documents->fetch_assoc()) {
        $id = $doc['document_id'];
        $path = $doc['file_path'];
        
        $base_dir = realpath(__DIR__ . '/..');
        $real_path = $base_dir ? $base_dir . '/' . ltrim($path, '/') : $path;

        if (file_exists($real_path)) {
            unlink($real_path);
        } elseif (file_exists($path)) {
            unlink($path);
        }
        
        $upd = $conn->prepare("UPDATE documents SET is_purged = TRUE, purged_at = CURRENT_TIMESTAMP WHERE document_id = ?");
        $upd->bind_param("i", $id);
        $upd->execute();
        
        $log = $conn->prepare("INSERT INTO audit_logs (action_type, entity_type, entity_id) VALUES ('DOCUMENT_PURGED', 'document_id', ?)");
        $log->bind_param("i", $id);
        $log->execute();
    }
    
    echo "Purge protocol executed successfully.";
} else {
    echo "Error: Data retention setting not found.";
}
?>
