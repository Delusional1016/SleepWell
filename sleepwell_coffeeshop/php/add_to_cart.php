<?php
session_start();
include 'config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['account_type'] != 'user') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Handle JSON input (from JavaScript fetch)
$input = json_decode(file_get_contents('php://input'), true);

if ($input && isset($input['id'], $input['name'], $input['price'], $input['quantity'])) {
    $item_id = $input['id'];
    $name = $input['name'];
    $price = $input['price'];
    $quantity = $input['quantity'];

    // Validate item exists in database
    $stmt = $conn->prepare("SELECT stock FROM menu_items WHERE id = ?");
    $stmt->bind_param("i", $item_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Item not found']);
        exit;
    }
    $item = $result->fetch_assoc();
    if ($quantity > $item['stock']) {
        echo json_encode(['success' => false, 'message' => 'Quantity exceeds stock']);
        exit;
    }

    // Check if item exists in cart
    $existing_index = -1;
    foreach ($_SESSION['cart'] as $index => $cart_item) {
        if ($cart_item['id'] == $item_id) {
            $existing_index = $index;
            break;
        }
    }

    if ($existing_index >= 0) {
        // Update quantity
        $_SESSION['cart'][$existing_index]['quantity'] += $quantity;
    } else {
        // Add new item
        $_SESSION['cart'][] = [
            'id' => $item_id,
            'name' => $name,
            'price' => $price,
            'quantity' => $quantity
        ];
    }

    echo json_encode(['success' => true, 'cart' => $_SESSION['cart']]);
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid input']);
}

$conn->close();
?>