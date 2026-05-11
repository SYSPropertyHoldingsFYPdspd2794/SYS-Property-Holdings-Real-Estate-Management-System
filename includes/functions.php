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
?>