<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['account_type'] != 'user') {
    error_log("Process Payment: Unauthorized access attempt");
    header("Location: index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $cart = json_decode($_POST['cart'], true);
    $user_id = $_SESSION['user_id'];

    if (empty($cart)) {
        error_log("Process Payment: Empty cart for user ID $user_id");
        header("Location: user_dashboard.php?message=error&text=Your cart is empty!");
        exit;
    }

    // Begin transaction
    $conn->begin_transaction();

    try {
        // Aggregate quantities by item_id
        $items = [];
        foreach ($cart as $item) {
            $item_id = intval($item['id']);
            if (!isset($items[$item_id])) {
                $items[$item_id] = ['name' => $item['name'], 'quantity' => 0, 'price' => floatval($item['price'])];
            }
            $items[$item_id]['quantity']++;
        }

        // Validate stock
        foreach ($items as $item_id => $data) {
            $stmt = $conn->prepare("SELECT stock FROM menu_items WHERE id = ?");
            $stmt->bind_param("i", $item_id);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows == 0) {
                throw new Exception("Item ID $item_id not found");
            }
            $item = $result->fetch_assoc();
            if ($item['stock'] < $data['quantity']) {
                throw new Exception("Insufficient stock for {$data['name']}");
            }
            $stmt->close();
        }

        // Insert orders and update stock
        $stmt = $conn->prepare("INSERT INTO orders (user_id, item_id, quantity, order_date) VALUES (?, ?, ?, NOW())");
        $update_stmt = $conn->prepare("UPDATE menu_items SET stock = stock - ? WHERE id = ?");

        foreach ($items as $item_id => $data) {
            // Insert order
            $stmt->bind_param("iii", $user_id, $item_id, $data['quantity']);
            if (!$stmt->execute()) {
                throw new Exception("Failed to save order for item ID $item_id: " . $stmt->error);
            }

            // Update stock
            $update_stmt->bind_param("ii", $data['quantity'], $item_id);
            if (!$update_stmt->execute()) {
                throw new Exception("Failed to update stock for item ID $item_id: " . $update_stmt->error);
            }

            error_log("Process Payment: Processed order for user ID $user_id, item ID $item_id, quantity {$data['quantity']}");
        }

        $stmt->close();
        $update_stmt->close();
        $conn->commit();

        header("Location: user_dashboard.php?message=success&text=Payment processed successfully! Stock updated.");
    } catch (Exception $e) {
        $conn->rollback();
        error_log("Process Payment: Error for user ID $user_id: " . $e->getMessage());
        header("Location: user_dashboard.php?message=error&text=Payment failed: {$e->getMessage()}");
    }
} else {
    error_log("Process Payment: Invalid request method");
    header("Location: user_dashboard.php?message=error&text=Invalid request method.");
}

$conn->close();
?>