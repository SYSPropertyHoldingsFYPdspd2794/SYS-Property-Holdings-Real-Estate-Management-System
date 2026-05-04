<?php
include '../includes/db_connect.php';

$res = $conn->query("SELECT setting_value FROM system_settings WHERE setting_key = 'DATA_RETENTION_DAYS'");
if ($res && $res->num_rows > 0) {
    $retention_days = (int) $res->fetch_assoc()['setting_value'];
    
    $stmt = $conn->prepare("SELECT document_id, file_path FROM documents WHERE is_purged = FALSE AND uploaded_at <= DATE_SUB(NOW(), INTERVAL? DAY)");
    $stmt->bind_param("i", $retention_days);
    $stmt->execute();
    $documents = $stmt->get_result();
    
    while ($doc = $documents->fetch_assoc()) {
        $id = $doc['document_id'];
        $path = $doc['file_path'];
        
        if (file_exists($path)) {
            unlink($path);
        }
        
        $upd = $conn->prepare("UPDATE documents SET is_purged = TRUE, purged_at = CURRENT_TIMESTAMP WHERE document_id =?");
        $upd->bind_param("i", $id);
        $upd->execute();
        
        $log = $conn->prepare("INSERT INTO audit_logs (action_type, entity_type, entity_id) VALUES ('DOCUMENT_PURGED', 'document_id',?)");
        $log->bind_param("i", $id);
        $log->execute();
    }
    
    echo "Purge protocol executed successfully.";
} else {
    echo "Failed to retrieve retention settings.";
}
?>

