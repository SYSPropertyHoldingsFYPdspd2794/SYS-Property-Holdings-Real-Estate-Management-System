<?php
/**
 * Generates a default temporary password string.
 */
function generateDefaultTempPassword($length = 10) {
    $chars = 'abcdefghjkmnpqrstuvwxyzABCDEFGHJKMNPQRSTUVWXYZ23456789!@#$%';
    return substr(str_shuffle($chars), 0, $length);
}

/**
 * Validates password strength:
 * 8+ chars, Upper, Lower, Number, and Special Symbol.
 */
function validate_password_strength($password) {
    $uppercase = preg_match('@[A-Z]@', $password);
    $lowercase = preg_match('@[a-z]@', $password);
    $number    = preg_match('@[0-9]@', $password);
    $special   = preg_match('@[^\w]@', $password);

    if(!$uppercase || !$lowercase || !$number || !$special || strlen($password) < 8) {
        return false;
    }
    return true;
}

function ensure_profile_image_column($conn, $table) {
    if (!in_array($table, ['customers', 'staff'], true)) {
        return false;
    }

    $check = $conn->query("SHOW COLUMNS FROM `$table` LIKE 'profile_image'");
    if ($check && $check->num_rows > 0) {
        return true;
    }

    return (bool)$conn->query("ALTER TABLE `$table` ADD `profile_image` varchar(255) DEFAULT NULL");
}

function upload_profile_image($file, $role_prefix, $account_id, &$error_message) {
    if (!isset($file) || $file['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    if ($file['error'] !== UPLOAD_ERR_OK) {
        $error_message = 'Avatar upload failed. Please choose another image.';
        return false;
    }

    if ($file['size'] > 2 * 1024 * 1024) {
        $error_message = 'Avatar must be 2MB or smaller.';
        return false;
    }

    $tmp_name = $file['tmp_name'];
    $image_info = @getimagesize($tmp_name);
    if ($image_info === false) {
        $error_message = 'Avatar must be a valid image file.';
        return false;
    }

    $allowed_mimes = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    ];

    $mime = $image_info['mime'] ?? '';
    if (!isset($allowed_mimes[$mime])) {
        $error_message = 'Avatar must be JPG, PNG, or WebP.';
        return false;
    }

    $upload_dir = __DIR__ . '/../storage/profile_images/';
    if (!is_dir($upload_dir) && !mkdir($upload_dir, 0777, true)) {
        $error_message = 'Unable to create avatar storage folder.';
        return false;
    }

    $file_name = $role_prefix . '_' . (int)$account_id . '_' . bin2hex(random_bytes(8)) . '.' . $allowed_mimes[$mime];
    $target_file = $upload_dir . $file_name;

    if (!move_uploaded_file($tmp_name, $target_file)) {
        $error_message = 'Unable to save avatar image.';
        return false;
    }

    return '/storage/profile_images/' . $file_name;
}

function document_public_url($file_path, $root_prefix = '') {
    $path = trim((string)$file_path);
    if ($path === '') {
        return '';
    }

    if (preg_match('#^https?://#i', $path)) {
        return $path;
    }

    $path = str_replace('\\', '/', $path);
    $path = preg_replace('#^\./+#', '', $path);
    $path = preg_replace('#^/+#', '', $path);

    while (strpos($path, '../') === 0) {
        $path = substr($path, 3);
    }

    $prefix = trim((string)$root_prefix);
    return $prefix === '' ? $path : rtrim($prefix, '/') . '/' . $path;
}
?>
