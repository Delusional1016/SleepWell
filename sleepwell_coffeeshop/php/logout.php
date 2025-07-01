<?php
// Start output buffering to prevent header issues
ob_start();
session_start();

// Debug: Log session status
error_log("Logout: Session ID before destruction: " . session_id());
if (isset($_SESSION['user_id'])) {
    error_log("Logout: User ID " . $_SESSION['user_id'] . " found in session");
} else {
    error_log("Logout: No user ID found in session");
}

// Clear all session variables
session_unset();

// Destroy the session
if (session_destroy()) {
    error_log("Logout: Session successfully destroyed");
} else {
    error_log("Logout: Session destruction failed");
}

// Clear output buffer and redirect
ob_end_clean();
header("Location: index.php");
exit;
?>