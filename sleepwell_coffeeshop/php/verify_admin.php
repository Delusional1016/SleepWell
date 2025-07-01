<?php
session_start();
include 'config.php';

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Log session and POST data
file_put_contents('C:/xampp/htdocs/sleepwell_coffeeshop/php/debug.log', 
    "verify_admin.php accessed at " . date('Y-m-d H:i:s') . "\n" .
    "Session ID: " . session_id() . "\n" .
    "User ID: " . (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'Not set') . "\n" .
    "Account Type: " . (isset($_SESSION['account_type']) ? $_SESSION['account_type'] : 'Not set') . "\n" .
    "Is Verified: " . (isset($_SESSION['is_verified']) ? $_SESSION['is_verified'] : 'Not set') . "\n" .
    "POST Data: " . json_encode($_POST) . "\n\n", 
    FILE_APPEND
);

if (!isset($_SESSION['user_id']) || $_SESSION['account_type'] != 'admin' || $_SESSION['is_verified'] != 1) {
    header("Location: /sleepwell_coffeeshop/php/index.php?message=error&text=Unauthorized access");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!isset($_POST['id']) || !isset($_POST['action'])) {
        header("Location: /sleepwell_coffeeshop/php/admin_dashboard.php?message=error&text=Invalid request");
        exit;
    }

    $user_id = intval($_POST['id']);
    $action = $_POST['action'];

    // Validate user exists and is unverified
    $stmt = $conn->prepare("SELECT id FROM users WHERE id = ? AND account_type = 'admin' AND is_verified = 0");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        header("Location: /sleepwell_coffeeshop/php/admin_dashboard.php?message=error&text=Invalid user ID or user already verified");
        exit;
    }

    if ($action === 'verify') {
        // Verify user
        $stmt = $conn->prepare("UPDATE users SET is_verified = 1 WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        if ($stmt->execute()) {
            header("Location: /sleepwell_coffeeshop/php/admin_dashboard.php?message=success&text=User verified successfully");
        } else {
            header("Location: /sleepwell_coffeeshop/php/admin_dashboard.php?message=error&text=Failed to verify user");
        }
    } elseif ($action === 'deny') {
        // Deny user (delete from database)
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        if ($stmt->execute()) {
            header("Location: /sleepwell_coffeeshop/php/admin_dashboard.php?message=success&text=User denied and removed");
        } else {
            header("Location: /sleepwell_coffeeshop/php/admin_dashboard.php?message=error&text=Failed to deny user");
        }
    } else {
        header("Location: /sleepwell_coffeeshop/php/admin_dashboard.php?message=error&text=Invalid action");
    }

    $stmt->close();
} else {
    header("Location: /sleepwell_coffeeshop/php/admin_dashboard.php?message=error&text=Invalid request method");
}

$conn->close();
exit;
?>