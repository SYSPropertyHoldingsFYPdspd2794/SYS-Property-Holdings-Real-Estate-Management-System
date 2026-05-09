<?php
/**
 * Generates a default temporary password string.
 * Satisfies the task: "Generate a default temporary password string."
 * 
 * @param int $length The length of the password.
 * @return string The plain-text temporary password.
 */
function generateDefaultTempPassword($length = 10) {
    // Pool of easy-to-read characters (Avoiding confusion between 0/O and 1/l)
    $chars = 'abcdefghjkmnpqrstuvwxyzABCDEFGHJKMNPQRSTUVWXYZ23456789';
    
    // Shuffle the characters and take the required amount
    return substr(str_shuffle($chars), 0, $length);
}
?>