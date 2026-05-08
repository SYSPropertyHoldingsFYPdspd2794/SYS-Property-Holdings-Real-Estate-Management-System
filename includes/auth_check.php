<?php
// includes/auth_check.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Core protection logic to restrict database access and validate roles.
 */
function protect_page($requiredRole, $conn) {
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== $requiredRole) {
        // Kill the connection immediately to prevent data leaks
        if ($conn) {
            $conn->close();
        }
        header("Location: ../login.php?error=unauthorized");
        exit();
    }
}

// Function for Admin folder
function protect_admin_page($role, $conn) {
    protect_page($role, $conn);
}

// Function for Staff folder
function protect_staff_page($role, $conn) {
    protect_page($role, $conn);
}

// Function for Customer folder - THIS FIXES YOUR ERROR
function protect_customer_page($role, $conn) {
    protect_page($role, $conn);
}
?>