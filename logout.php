<?php
/**
 * TASK: Logic to destroy active sessions and terminate all database connections.
 */

// 1. Terminate Database Connections
// We use __DIR__ to ensure the path is absolute and correct regardless of where it's called
include_once __DIR__ . '/includes/db_connect.php';

if (isset($conn) && $conn instanceof mysqli) {
    // This physically closes the connection to the MySQL server
    // It fulfills the "terminate all database connections" requirement
    $conn->close(); 
}

// 2. Access the current session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 3. Destroy Active Sessions
// Clear all data from the $_SESSION superglobal array
$_SESSION = array();

// 4. Terminate Session Cookie
// This expires the session cookie on the user's browser for total security
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// 5. Final Destruction
// Removes the session file from the server's temporary storage
session_destroy();

// 6. Redirect to Landing Page
// Redirects back to index.php as specified in your logic
header("Location: index.php");
exit();
?>