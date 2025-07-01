<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['account_type'] != 'admin') {
    error_log("Update Menu: Unauthorized access attempt");
    header("Location: index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $item_id = intval($_POST['item_id']);
    $price = isset($_POST['price']) && $_POST['price'] !== '' ? floatval($_POST['price']) : null;
    $stock = isset($_POST['stock']) && $_POST['stock'] !== '' ? intval($_POST['stock']) : null;

    // Validate input
    if ($item_id <= 0) {
        error_log("Update Menu: Invalid item ID: $item_id");
        header("Location: admin_dashboard.php?message=error&text=Invalid item ID.");
        exit;
    }

    // Check if item exists
    $stmt = $conn->prepare("SELECT name FROM menu_items WHERE id = ?");
    $stmt->bind_param("i", $item_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows == 0) {
        error_log("Update Menu: Item ID $item_id not found");
        header("Location: admin_dashboard.php?message=error&text=Item not found.");
        $stmt->close();
        exit;
    }
    $item = $result->fetch_assoc();
    $item_name = $item['name'];
    $stmt->close();

    // Build dynamic update query
    $updates = [];
    $params = [];
    $types = '';

    if ($price !== null && $price > 0) {
        $updates[] = "price = ?";
        $params[] = $price;
        $types .= 'd';
    }
    if ($stock !== null && $stock >= 0) {
        $updates[] = "stock = ?";
        $params[] = $stock;
        $types .= 'i';
    }

    if (empty($updates)) {
        error_log("Update Menu: No fields to update for item ID $item_id");
        header("Location: admin_dashboard.php?message=error&text=No fields provided for update.");
        exit;
    }

    $params[] = $item_id;
    $types .= 'i';
    $query = "UPDATE menu_items SET " . implode(', ', $updates) . " WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param($types, ...$params);

    if ($stmt->execute()) {
        error_log("Update Menu: Successfully updated item '$item_name' (ID: $item_id)");
        header("Location: admin_dashboard.php?message=success&text=Item '$item_name' updated successfully!");
    } else {
        error_log("Update Menu: Failed to update item ID $item_id. Error: " . $stmt->error);
        header("Location: admin_dashboard.php?message=error&text=Failed to update item. Please try again.");
    }

    $stmt->close();
} else {
    error_log("Update Menu: Invalid request method");
    header("Location: admin_dashboard.php?message=error&text=Invalid request method.");
}

$conn->close();
?>