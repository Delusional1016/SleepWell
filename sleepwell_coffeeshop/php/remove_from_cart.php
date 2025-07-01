<?php
session_start();
include 'config.php';

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Log session data
file_put_contents('C:/xampp/htdocs/sleepwell_coffeeshop/php/debug.log', 
    "remove_from_cart.php accessed at " . date('Y-m-d H:i:s') . "\n" .
    "Session ID: " . session_id() . "\n" .
    "User ID: " . (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'Not set') . "\n" .
    "Account Type: " . (isset($_SESSION['account_type']) ? $_SESSION['account_type'] : 'Not set') . "\n" .
    "Cart: " . (isset($_SESSION['cart']) ? json_encode($_SESSION['cart']) : '[]') . "\n" .
    "POST Data: " . json_encode($_POST) . "\n\n", 
    FILE_APPEND
);

if (!isset($_SESSION['user_id']) || $_SESSION['account_type'] != 'user') {
    header("Location: /sleepwell_coffeeshop/php/index.php?message=error&text=Please log in as a user.");
    exit;
}

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $item_id = $_POST['id'];
    $found = false;
    foreach ($_SESSION['cart'] as $index => $item) {
        if ($item['id'] == $item_id) {
            array_splice($_SESSION['cart'], $index, 1);
            $found = true;
            break;
        }
    }
    if ($found) {
        header("Location: /sleepwell_coffeeshop/php/cart.php?message=success&text=Item removed from cart.");
    } else {
        header("Location: /sleepwell_coffeeshop/php/cart.php?message=error&text=Item not found in cart.");
    }
} else {
    header("Location: /sleepwell_coffeeshop/php/cart.php?message=error&text=Invalid request.");
}

$conn->close();
exit;
?>