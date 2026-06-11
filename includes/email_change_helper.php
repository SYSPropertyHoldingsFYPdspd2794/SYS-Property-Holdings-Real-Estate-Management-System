<?php
include_once __DIR__ . '/email_helper.php';

function ensure_email_change_requests_table($conn) {
    $sql = "CREATE TABLE IF NOT EXISTS email_change_requests (
        request_id INT AUTO_INCREMENT PRIMARY KEY,
        account_id INT NOT NULL,
        old_email VARCHAR(255) NOT NULL,
        new_email VARCHAR(255) NOT NULL,
        otp_code VARCHAR(6) NOT NULL,
        otp_expires_at DATETIME NOT NULL,
        otp_attempts INT NOT NULL DEFAULT 0,
        approve_token_hash CHAR(64) NOT NULL,
        reject_token_hash CHAR(64) NOT NULL,
        old_approved_at DATETIME DEFAULT NULL,
        new_verified_at DATETIME DEFAULT NULL,
        status ENUM('PENDING','COMPLETED','REJECTED','EXPIRED') NOT NULL DEFAULT 'PENDING',
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        completed_at DATETIME DEFAULT NULL,
        INDEX idx_email_change_account_status (account_id, status),
        UNIQUE KEY uq_email_change_approve_token (approve_token_hash),
        UNIQUE KEY uq_email_change_reject_token (reject_token_hash)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

    return (bool)$conn->query($sql);
}

function email_change_base_url() {
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $script_dir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/'));
    $script_dir = trim($script_dir, '/');
    $parts = $script_dir === '' ? [] : explode('/', $script_dir);

    if (!empty($parts) && in_array(end($parts), ['admin', 'staff', 'customer'], true)) {
        array_pop($parts);
    }

    $base_path = empty($parts) ? '' : '/' . implode('/', $parts);
    return $scheme . '://' . $host . $base_path;
}

function email_change_email_exists($conn, $email, $exclude_account_id) {
    $stmt = $conn->prepare("SELECT account_id FROM accounts WHERE email = ? AND account_id <> ? LIMIT 1");
    $stmt->bind_param("si", $email, $exclude_account_id);
    $stmt->execute();
    return $stmt->get_result()->num_rows > 0;
}

function email_change_get_current_account($conn, $account_id) {
    $stmt = $conn->prepare("SELECT account_id, email, role FROM accounts WHERE account_id = ? LIMIT 1");
    $stmt->bind_param("i", $account_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

function email_change_complete_if_ready($conn, $request_id) {
    $stmt = $conn->prepare("SELECT request_id, account_id, new_email, old_approved_at, new_verified_at, status FROM email_change_requests WHERE request_id = ? LIMIT 1");
    $stmt->bind_param("i", $request_id);
    $stmt->execute();
    $request = $stmt->get_result()->fetch_assoc();

    if (!$request || $request['status'] !== 'PENDING' || empty($request['old_approved_at']) || empty($request['new_verified_at'])) {
        return false;
    }

    if (email_change_email_exists($conn, $request['new_email'], (int)$request['account_id'])) {
        $reject = $conn->prepare("UPDATE email_change_requests SET status = 'REJECTED', completed_at = NOW() WHERE request_id = ?");
        $reject->bind_param("i", $request_id);
        $reject->execute();
        return false;
    }

    $conn->begin_transaction();
    try {
        $update_account = $conn->prepare("UPDATE accounts SET email = ? WHERE account_id = ?");
        $update_account->bind_param("si", $request['new_email'], $request['account_id']);
        $update_account->execute();

        $update_request = $conn->prepare("UPDATE email_change_requests SET status = 'COMPLETED', completed_at = NOW() WHERE request_id = ?");
        $update_request->bind_param("i", $request_id);
        $update_request->execute();

        $conn->commit();

        if (isset($_SESSION['account_id']) && (int)$_SESSION['account_id'] === (int)$request['account_id']) {
            $_SESSION['user_email'] = $request['new_email'];
        }

        return true;
    } catch (Exception $e) {
        $conn->rollback();
        return false;
    }
}

function email_change_start_request($conn, $account_id, $new_email, $full_name, &$error_message, &$success_message, &$show_otp_modal) {
    ensure_email_change_requests_table($conn);

    $account = email_change_get_current_account($conn, $account_id);
    if (!$account) {
        $error_message = 'Account not found.';
        return false;
    }

    $old_email = trim($account['email'] ?? '');
    $new_email = trim($new_email);

    if (!filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
        $error_message = 'Please enter a valid email address.';
        return false;
    }

    if (strcasecmp($old_email, $new_email) === 0) {
        return true;
    }

    if (email_change_email_exists($conn, $new_email, $account_id)) {
        $error_message = 'This email is already registered. Please use another email.';
        return false;
    }

    $expire_old = $conn->prepare("UPDATE email_change_requests SET status = 'EXPIRED', completed_at = NOW() WHERE account_id = ? AND status = 'PENDING'");
    $expire_old->bind_param("i", $account_id);
    $expire_old->execute();

    $otp = (string)random_int(100000, 999999);
    $approve_token = bin2hex(random_bytes(32));
    $reject_token = bin2hex(random_bytes(32));
    $approve_hash = hash('sha256', $approve_token);
    $reject_hash = hash('sha256', $reject_token);
    $otp_expires_at = date('Y-m-d H:i:s', time() + 600);

    $stmt = $conn->prepare("INSERT INTO email_change_requests (account_id, old_email, new_email, otp_code, otp_expires_at, approve_token_hash, reject_token_hash) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("issssss", $account_id, $old_email, $new_email, $otp, $otp_expires_at, $approve_hash, $reject_hash);
    $stmt->execute();

    $base_url = email_change_base_url();
    $approve_url = $base_url . '/email_change_confirm.php?action=approve&token=' . urlencode($approve_token);
    $reject_url = $base_url . '/email_change_confirm.php?action=reject&token=' . urlencode($reject_token);

    if (!send_email_change_approval($old_email, $full_name, $old_email, $new_email, $approve_url, $reject_url)) {
        $conn->query("UPDATE email_change_requests SET status = 'EXPIRED', completed_at = NOW() WHERE request_id = " . (int)$conn->insert_id);
        $error_message = 'Unable to send approval email to your old email address. Please check the mail server settings.';
        return false;
    }

    if (!send_email_change_otp($new_email, $full_name, $otp)) {
        $conn->query("UPDATE email_change_requests SET status = 'EXPIRED', completed_at = NOW() WHERE request_id = " . (int)$conn->insert_id);
        $error_message = 'Unable to send OTP to the new email address. Please check the mail server settings.';
        return false;
    }

    $success_message = 'Email change requested. Please approve it from your old email and verify the OTP sent to your new email.';
    $show_otp_modal = true;
    return true;
}

function email_change_verify_otp($conn, $account_id, $otp, &$error_message, &$success_message, &$show_otp_modal) {
    ensure_email_change_requests_table($conn);

    $stmt = $conn->prepare("SELECT request_id, otp_code, otp_expires_at, otp_attempts, old_approved_at FROM email_change_requests WHERE account_id = ? AND status = 'PENDING' ORDER BY request_id DESC LIMIT 1");
    $stmt->bind_param("i", $account_id);
    $stmt->execute();
    $request = $stmt->get_result()->fetch_assoc();

    if (!$request) {
        $error_message = 'No pending email change request was found.';
        $show_otp_modal = true;
        return false;
    }

    $request_id = (int)$request['request_id'];
    if (strtotime($request['otp_expires_at']) < time()) {
        $expire = $conn->prepare("UPDATE email_change_requests SET status = 'EXPIRED', completed_at = NOW() WHERE request_id = ?");
        $expire->bind_param("i", $request_id);
        $expire->execute();
        $error_message = 'This OTP has expired. Please submit the email change again.';
        $show_otp_modal = true;
        return false;
    }

    if ((int)$request['otp_attempts'] >= 5) {
        $expire = $conn->prepare("UPDATE email_change_requests SET status = 'EXPIRED', completed_at = NOW() WHERE request_id = ?");
        $expire->bind_param("i", $request_id);
        $expire->execute();
        $error_message = 'Too many incorrect attempts. Please submit the email change again.';
        $show_otp_modal = true;
        return false;
    }

    if (!preg_match('/^\d{6}$/', $otp) || $otp !== $request['otp_code']) {
        $fail = $conn->prepare("UPDATE email_change_requests SET otp_attempts = otp_attempts + 1 WHERE request_id = ?");
        $fail->bind_param("i", $request_id);
        $fail->execute();
        $error_message = 'Invalid OTP. Please check the 6-digit code and try again.';
        $show_otp_modal = true;
        return false;
    }

    $verify = $conn->prepare("UPDATE email_change_requests SET new_verified_at = NOW() WHERE request_id = ?");
    $verify->bind_param("i", $request_id);
    $verify->execute();

    if (email_change_complete_if_ready($conn, $request_id)) {
        $success_message = 'Email address updated successfully.';
        $show_otp_modal = false;
        return true;
    }

    $success_message = 'New email verified. Please approve the change from your old email to complete it.';
    $show_otp_modal = false;
    return true;
}

function email_change_resend_otp($conn, $account_id, $full_name, &$error_message, &$success_message, &$show_otp_modal) {
    ensure_email_change_requests_table($conn);

    $stmt = $conn->prepare("SELECT request_id, new_email FROM email_change_requests WHERE account_id = ? AND status = 'PENDING' ORDER BY request_id DESC LIMIT 1");
    $stmt->bind_param("i", $account_id);
    $stmt->execute();
    $request = $stmt->get_result()->fetch_assoc();

    if (!$request) {
        $error_message = 'No pending email change request was found.';
        $show_otp_modal = true;
        return false;
    }

    $otp = (string)random_int(100000, 999999);
    $expires = date('Y-m-d H:i:s', time() + 600);
    $request_id = (int)$request['request_id'];

    $update = $conn->prepare("UPDATE email_change_requests SET otp_code = ?, otp_expires_at = ?, otp_attempts = 0, new_verified_at = NULL WHERE request_id = ?");
    $update->bind_param("ssi", $otp, $expires, $request_id);
    $update->execute();

    if (!send_email_change_otp($request['new_email'], $full_name, $otp)) {
        $error_message = 'Unable to resend the OTP. Please try again later.';
        $show_otp_modal = true;
        return false;
    }

    $success_message = 'A new verification code has been sent to ' . $request['new_email'] . '.';
    $show_otp_modal = true;
    return true;
}

function email_change_pending_notice($conn, $account_id) {
    ensure_email_change_requests_table($conn);
    $stmt = $conn->prepare("SELECT old_email, new_email, old_approved_at, new_verified_at, otp_expires_at FROM email_change_requests WHERE account_id = ? AND status = 'PENDING' ORDER BY request_id DESC LIMIT 1");
    $stmt->bind_param("i", $account_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}
?>
