<?php
session_start();
include 'config.php';

// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Log session data
file_put_contents('C:/xampp/htdocs/sleepwell_coffeeshop/php/debug.log', 
    "cart.php accessed at " . date('Y-m-d H:i:s') . "\n" .
    "Session ID: " . session_id() . "\n" .
    "User ID: " . (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'Not set') . "\n" .
    "Account Type: " . (isset($_SESSION['account_type']) ? $_SESSION['account_type'] : 'Not set') . "\n" .
    "Cart: " . (isset($_SESSION['cart']) ? json_encode($_SESSION['cart']) : '[]') . "\n\n", 
    FILE_APPEND
);

if (!isset($_SESSION['user_id']) || $_SESSION['account_type'] != 'user') {
    header("Location: /sleepwell_coffeeshop/php/index.php?message=error&text=Please log in as a user.");
    exit;
}

$user_id = $_SESSION['user_id'];
ob_start();

// Initialize cart in session if not set
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Get notification message
$message = isset($_GET['message']) ? $_GET['message'] : '';
$message_text = isset($_GET['text']) ? htmlspecialchars($_GET['text']) : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cart - Sleep Well Coffee Shop</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Georgia', serif;
            background: #fff5ee;
            color: #3c2f2f;
            min-height: 100vh;
            background-image: linear-gradient(rgba(255, 245, 238, 0.9), rgba(255, 245, 238, 0.9)), url('https://images.unsplash.com/photo-1512568400610-62da28bc8a13?ixlib=rb-4.0.3&auto=format&fit=crop&w=1350&q=80');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
        }
        .dashboard {
            display: flex;
            min-height: 100vh;
        }
        .sidebar {
            width: 280px;
            background: linear-gradient(135deg, #3c2f2f, #4a3b3b);
            color: white;
            padding: 30px;
            position: fixed;
            height: 100%;
            overflow-y: auto;
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.2);
            border-right: 2px solid #d3b7a0;
            animation: fadeIn 1s ease-in-out;
        }
        .sidebar h2 {
            color: #d3b7a0;
            margin-bottom: 30px;
            font-size: 24px;
            text-align: center;
            font-weight: bold;
        }
        .sidebar ul {
            list-style: none;
            padding: 0;
        }
        .sidebar ul li {
            margin: 15px 0;
        }
        .sidebar ul li a {
            color: white;
            text-decoration: none;
            display: block;
            padding: 12px;
            border-radius: 8px;
            font-size: 18px;
            transition: background 0.3s, padding-left 0.3s, transform 0.2s;
        }
        .sidebar ul li a:hover {
            background: #6b4e31;
            padding-left: 20px;
            transform: scale(1.02);
        }
        .sidebar ul li a.active {
            background: #8b6f47;
            font-weight: bold;
        }
        .content {
            margin-left: 300px;
            padding: 40px;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            min-height: 100vh;
        }
        .content h2 {
            color: #6b4e31;
            font-size: 26px;
            margin-bottom: 20px;
            border-bottom: 2px solid #d3b7a0;
            padding-bottom: 10px;
            text-align: center;
        }
        .cart {
            width: 100%;
            max-width: 500px;
            background: linear-gradient(135deg, #ffffff, #f8f1e9);
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            margin-top: 30px;
            border: 1px solid #d3b7a0;
        }
        .cart-item {
            background: #f9e9d9;
            padding: 15px;
            margin: 10px 0;
            border-radius: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .cart-item form {
            margin: 0;
        }
        .cart-item button {
            background: #f44336;
            padding: 8px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
            font-family: 'Georgia', serif;
            transition: background 0.3s, transform 0.2s;
        }
        .cart-item button:hover {
            background: #d32f2f;
            transform: scale(1.05);
        }
        .cart-total {
            margin-top: 20px;
            font-size: 18px;
            font-weight: bold;
            text-align: right;
            color: #3c2f2f;
        }
        .cart form.checkout-form {
            display: flex;
            flex-direction: column;
            gap: 20px;
            width: 100%;
        }
        .cart button[type="submit"] {
            width: 100%;
            padding: 12px;
            background: #6b4e31;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            font-family: 'Georgia', serif;
            margin-top: 20px;
            transition: background 0.3s, transform 0.2s, box-shadow 0.3s;
        }
        .cart button[type="submit"]:hover {
            background: #8b6f47;
            transform: scale(1.05);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
        }
        .no-items {
            color: #6b4e31;
            font-size: 16px;
            text-align: center;
        }
        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px;
            border-radius: 8px;
            color: white;
            font-size: 16px;
            z-index: 1000;
            max-width: 300px;
            font-family: 'Georgia', serif;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
            transition: opacity 0.5s;
        }
        .notification.success {
            background: #4CAF50;
        }
        .notification.error {
            background: #f44336;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @media (max-width: 768px) {
            .sidebar {
                width: 220px;
            }
            .content {
                margin-left: 240px;
                padding: 20px;
            }
        }
        @media (max-width: 576px) {
            .sidebar {
                width: 100%;
                position: relative;
                height: auto;
            }
            .content {
                margin-left: 0;
                padding: 15px;
            }
            .cart {
                padding: 15px;
            }
        }
    </style>
    <script src="/sleepwell_coffeeshop/js/script.js"></script>
</head>
<body>
    <div class="dashboard">
        <div class="sidebar">
            <h2>Sleep Well Coffee Shop</h2>
            <ul>
                <li><a href="/sleepwell_coffeeshop/php/user_dashboard.php" class="nav-link">View Menu</a></li>
                <li><a href="/sleepwell_coffeeshop/php/food.php" class="nav-link">Food</a></li>
                <li><a href="/sleepwell_coffeeshop/php/beverage.php" class="nav-link">Beverage</a></li>
                <li><a href="/sleepwell_coffeeshop/php/appetizer.php" class="nav-link">Appetizer</a></li>
                <li><a href="/sleepwell_coffeeshop/php/cart.php" class="nav-link active">Cart</a></li>
                <li><a href="/sleepwell_coffeeshop/php/logout.php">Logout</a></li>
            </ul>
        </div>
        <div class="content">
            <?php if ($message): ?>
                <div class="notification <?php echo $message; ?>">
                    <?php echo $message_text; ?>
                </div>
            <?php endif; ?>
            <h2>Your Cart</h2>
            <div class="cart">
                <?php
                $total = 0;
                if (empty($_SESSION['cart'])) {
                    echo "<p class='no-items'>Your cart is empty</p>";
                } else {
                    foreach ($_SESSION['cart'] as $item) {
                        $item_total = $item['price'] * $item['quantity'];
                        $total += $item_total;
                        ?>
                        <div class="cart-item">
                            <span><?php echo htmlspecialchars($item['quantity']) . " x " . htmlspecialchars($item['name']) . " - $" . number_format($item_total, 2); ?></span>
                            <form action="/sleepwell_coffeeshop/php/remove_from_cart.php" method="POST">
                                <input type="hidden" name="id" value="<?php echo htmlspecialchars($item['id']); ?>">
                                <button type="submit">Remove</button>
                            </form>
                        </div>
                        <?php
                    }
                }
                ?>
                <div class="cart-total">
                    Total: $<?php echo number_format($total, 2); ?>
                </div>
                <?php if (!empty($_SESSION['cart'])): ?>
                    <form action="/sleepwell_coffeeshop/php/process_payment.php" method="POST" class="checkout-form">
                        <input type="hidden" name="cart" value="<?php echo htmlspecialchars(json_encode($_SESSION['cart'])); ?>">
                        <button type="submit">Checkout</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
<?php
$conn->close();
ob_end_flush();
?>