<?php

function property_catalog_image_path(array $property, string $url_prefix = '', string $filesystem_prefix = ''): string
{
    $base_url = $url_prefix . 'SYS Property Catalog/';
    $base_path = $filesystem_prefix . 'SYS Property Catalog/';
    $placeholder = $base_url . 'placeholder.jpg';

    $custom_image = trim((string)($property['image_filename'] ?? ''));
    if ($custom_image !== '') {
        $custom_image = ltrim(str_replace('\\', '/', $custom_image), '/');
        if (strpos($custom_image, '..') === false && file_exists($base_path . $custom_image)) {
            return $base_url . $custom_image;
        }
    }

    $db_type = strtolower(trim((string)($property['property_type'] ?? '')));
    $raw_state = trim((string)($property['state'] ?? ''));
    if (strtoupper($raw_state) === 'PENANG') {
        $raw_state = 'Pulau Pinang';
    }
    if (strtoupper($raw_state) === 'MALACCA') {
        $raw_state = 'Melaka';
    }

    $state_name = ucwords(strtolower($raw_state));
    $folder = 'Apartment/';
    if ($db_type === 'commercial') {
        $folder = 'Commercial/';
    } elseif ($db_type === 'terrace') {
        $folder = 'Terrace/';
    } elseif ($db_type === 'bungalow') {
        $folder = 'Bungalow/';
    }

    $file_name = ucfirst($db_type) . ' - ' . $state_name;
    foreach (['jpg', 'jpeg', 'png', 'webp'] as $ext) {
        $relative_path = $folder . $file_name . '.' . $ext;
        if (file_exists($base_path . $relative_path)) {
            return $base_url . $relative_path;
        }
    }

    return $placeholder;
}

function save_property_image_upload(string $field_name, string $property_code, string $catalog_directory): ?string
{
    if (empty($_FILES[$field_name]) || ($_FILES[$field_name]['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    if ($_FILES[$field_name]['error'] !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Image upload failed. Please choose another image.');
    }

    if (($_FILES[$field_name]['size'] ?? 0) > 5 * 1024 * 1024) {
        throw new RuntimeException('Image upload failed. Maximum file size is 5MB.');
    }

    $image_info = @getimagesize($_FILES[$field_name]['tmp_name']);
    $mime = $image_info['mime'] ?? '';
    $allowed_mimes = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp'
    ];

    if (!isset($allowed_mimes[$mime])) {
        throw new RuntimeException('Image upload failed. Only JPG, PNG, and WEBP images are allowed.');
    }

    $custom_directory = rtrim($catalog_directory, "\\/") . DIRECTORY_SEPARATOR . 'Custom';
    if (!is_dir($custom_directory) && !mkdir($custom_directory, 0775, true)) {
        throw new RuntimeException('Image upload failed. Unable to create image folder.');
    }

    $safe_code = preg_replace('/[^A-Za-z0-9_-]/', '_', $property_code);
    $filename = $safe_code . '_' . date('YmdHis') . '_' . bin2hex(random_bytes(4)) . '.' . $allowed_mimes[$mime];
    $target_path = $custom_directory . DIRECTORY_SEPARATOR . $filename;

    if (!move_uploaded_file($_FILES[$field_name]['tmp_name'], $target_path)) {
        throw new RuntimeException('Image upload failed. Unable to save selected image.');
    }

    return 'Custom/' . $filename;
}
