<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['account_type'] != 'admin') {
    error_log("Delete Menu: Unauthorized access attempt");
    header("Location: index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $item_id = intval($_POST['item_id']);

    // Validate input
    if ($item_id <= 0) {
        error_log("Delete Menu: Invalid item ID: $item_id");
        header("Location: admin_dashboard.php?message=error&text=Invalid item ID.");
        exit;
    }

    // Check if item exists
    $stmt = $conn->prepare("SELECT name FROM menu_items WHERE id = ?");
    $stmt->bind_param("i", $item_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows == 0) {
        error_log("Delete Menu: Item ID $item_id not found");
        header("Location: admin_dashboard.php?message=error&text=Item not found.");
        $stmt->close();
        exit;
    }
    $item = $result->fetch_assoc();
    $item_name = $item['name'];
    $stmt->close();

    // Delete item
    $stmt = $conn->prepare("DELETE FROM menu_items WHERE id = ?");
    $stmt->bind_param("i", $item_id);

    if ($stmt->execute()) {
        error_log("Delete Menu: Successfully deleted item '$item_name' (ID: $item_id)");
        header("Location: admin_dashboard.php?message=success&text=Item '$item_name' deleted successfully!");
    } else {
        error_log("Delete Menu: Failed to delete item ID $item_id. Error: " . $stmt->error);
        header("Location: admin_dashboard.php?message=error&text=Failed to delete item. Please try again.");
    }

    $stmt->close();
} else {
    error_log("Delete Menu: Invalid request method");
    header("Location: admin_dashboard.php?message=error&text=Invalid request method.");
}

$conn->close();
?>