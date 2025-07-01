<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['account_type'] != 'admin') {
    error_log("Add Menu: Unauthorized access attempt");
    header("Location: index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $category = trim($_POST['category']);
    $price = floatval($_POST['price']);
    $stock = intval($_POST['stock']);

    // Validate input
    if (empty($name) || empty($category) || $price <= 0 || $stock < 0) {
        error_log("Add Menu: Invalid input - Name: $name, Category: $category, Price: $price, Stock: $stock");
        header("Location: admin_dashboard.php?message=error&text=Invalid input. Please check all fields.");
        exit;
    }

    // Prepare and execute query
    $stmt = $conn->prepare("INSERT INTO menu_items (name, category, price, stock) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssdi", $name, $category, $price, $stock);

    if ($stmt->execute()) {
        error_log("Add Menu: Successfully added item '$name'");
        header("Location: admin_dashboard.php?message=success&text=Item '$name' added successfully!");
    } else {
        error_log("Add Menu: Failed to add item '$name'. Error: " . $stmt->error);
        header("Location: admin_dashboard.php?message=error&text=Failed to add item. Please try again.");
    }

    $stmt->close();
} else {
    error_log("Add Menu: Invalid request method");
    header("Location: admin_dashboard.php?message=error&text=Invalid request method.");
}

$conn->close();
?>