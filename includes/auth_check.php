<?php
/**
 * PROJECT: SYS Property Holdings
 * FILE: includes/auth_check.php
 * DESCRIPTION: Core protection logic with strict Role-Based Access Control (RBAC).
 * Prevents URL jumping between different user roles.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Base protection logic for single roles
 */
function protect_page($requiredRole, $conn) {
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== $requiredRole) {
        if ($conn) {
            $conn->close();
        }
        // If unauthorized, determine where to kick them based on context
        $redirect = (isset($_SESSION['role'])) ? "dashboard.php" : "../login.php?error=unauthorized";
        header("Location: " . $redirect);
        exit();
    }
}

// Function for Admin exclusive folders
function protect_admin_page($role, $conn) {
    protect_page($role, $conn);
}

// Function for Staff exclusive folders
function protect_staff_page($role, $conn) {
    protect_page($role, $conn);
}

// Function for Customer exclusive folders
function protect_customer_page($role, $conn) {
    protect_page($role, $conn);
}

/**
 * NEW: Function for the Root directory Catalog.
 * Allows ONLY Staff and Admin. Kicks Customers back to their dashboard.
 */
function protect_staff_admin_page($conn) {
    if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['STAFF', 'ADMIN'])) {
        if ($conn) {
            $conn->close();
        }
        // If Customer tries to access the staff/admin catalog via URL, kick them to their own catalog
        if (isset($_SESSION['role']) && $_SESSION['role'] === 'CUSTOMER') {
            header("Location: customer/properties.php");
        } else {
            header("Location: login.php?error=unauthorized");
        }
        exit();
    }
}
?>