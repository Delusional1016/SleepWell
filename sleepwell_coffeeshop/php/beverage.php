<?php
session_start();
include 'config.php';

// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Log session data
file_put_contents('C:/xampp/htdocs/sleepwell_coffeeshop/php/debug.log', 
    "beverage.php accessed at " . date('Y-m-d H:i:s') . "\n" .
    "Session ID: " . session_id() . "\n" .
    "User ID: " . (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'Not set') . "\n" .
    "Account Type: " . (isset($_SESSION['account_type']) ? $_SESSION['account_type'] : 'Not set') . "\n\n", 
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
    <title>Beverage Menu - Sleep Well Coffee Shop</title>
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
        .category-filter {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            flex-wrap: wrap;
            justify-content: center;
            background: linear-gradient(135deg, #ffffff, #f8f1e9);
            padding: 15px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            border: 1px solid #d3b7a0;
        }
        .category-filter a {
            background: #6b4e31;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            text-decoration: none;
            font-family: 'Georgia', serif;
            transition: background 0.3s, transform 0.2s, box-shadow 0.3s;
        }
        .category-filter a:hover {
            background: #8b6f47;
            transform: scale(1.05);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
        }
        .category-filter a.active {
            background: #8b6f47;
            font-weight: bold;
        }
        .menu-grid {
            width: 100%;
            max-width: 1200px;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
        }
        .menu-item {
            background: linear-gradient(135deg, #ffffff, #f8f1e9);
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            text-align: center;
            transition: transform 0.3s ease, box-shadow 0.3s;
            border: 1px solid #d3b7a0;
            animation: fadeIn 1s ease-in-out;
        }
        .menu-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.15);
        }
        .menu-item h3 {
            color: #6b4e31;
            font-size: 20px;
            margin-bottom: 10px;
        }
        .menu-item p {
            margin: 5px 0;
            color: #3c2f2f;
        }
        .menu-item .order-controls {
            display: flex;
            gap: 10px;
            justify-content: center;
            align-items: center;
            margin-top: 10px;
        }
        .menu-item input[type="number"] {
            width: 60px;
            padding: 12px;
            border: 1px solid #d3b7a0;
            border-radius: 8px;
            font-size: 16px;
            font-family: 'Georgia', serif;
            background: #fff;
            text-align: center;
            transition: border-color 0.3s, box-shadow 0.3s;
        }
        .menu-item input[type="number"]:focus {
            border-color: #6b4e31;
            outline: none;
            box-shadow: 0 0 8px rgba(107, 78, 49, 0.4);
        }
        .menu-item button {
            padding: 12px;
            background: #6b4e31;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            font-family: 'Georgia', serif;
            transition: background 0.3s, transform 0.2s, box-shadow 0.3s;
        }
        .menu-item button:hover {
            background: #8b6f47;
            transform: scale(1.05);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
        }
        .menu-item button:disabled {
            background: #ccc;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
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
            .menu-grid {
                grid-template-columns: 1fr;
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
            .menu-grid {
                grid-template-columns: 1fr;
            }
            .category-filter {
                gap: 8px;
            }
            .category-filter a {
                padding: 8px 16px;
                font-size: 14px;
            }
            .menu-item input[type="number"] {
                width: 50px;
                padding: 8px;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard">
        <div class="sidebar">
            <h2>Sleep Well Coffee Shop</h2>
            <ul>
                <li><a href="/sleepwell_coffeeshop/php/user_dashboard.php" class="nav-link">View Menu</a></li>
                <li><a href="/sleepwell_coffeeshop/php/food.php" class="nav-link">Food</a></li>
                <li><a href="/sleepwell_coffeeshop/php/beverage.php" class="nav-link active">Beverage</a></li>
                <li><a href="/sleepwell_coffeeshop/php/appetizer.php" class="nav-link">Appetizer</a></li>
                <li><a href="/sleepwell_coffeeshop/php/cart.php" class="nav-link">Cart</a></li>
                <li><a href="/sleepwell_coffeeshop/php/logout.php">Logout</a></li>
            </ul>
        </div>
        <div class="content">
            <?php if ($message): ?>
                <div class="notification <?php echo $message; ?>">
                    <?php echo $message_text; ?>
                </div>
            <?php endif; ?>
            <h2>Beverage Menu</h2>
            <div class="category-filter">
                <a href="/sleepwell_coffeeshop/php/user_dashboard.php">View All</a>
                <a href="/sleepwell_coffeeshop/php/food.php">Food</a>
                <a href="/sleepwell_coffeeshop/php/beverage.php" class="active">Beverage</a>
                <a href="/sleepwell_coffeeshop/php/appetizer.php">Appetizer</a>
            </div>
            <div class="menu-grid" id="menu-grid">
                <?php
                $result = $conn->query("SELECT * FROM menu_items WHERE category = 'beverage' ORDER BY name") or die("Query failed: " . $conn->error);
                if ($result->num_rows == 0) {
                    echo "<p class='no-items'>No items available</p>";
                } else {
                    while ($item = $result->fetch_assoc()) {
                        echo "<div class='menu-item' data-category='beverage'>";
                        echo "<h3>" . htmlspecialchars($item['name']) . "</h3>";
                        echo "<p>Category: Beverage</p>";
                        echo "<p>Price: $" . number_format($item['price'], 2) . "</p>";
                        echo "<p>Stock: " . ($item['stock'] > 0 ? htmlspecialchars($item['stock']) : 'Out of Stock') . "</p>";
                        echo "<div class='order-controls'>";
                        echo "<input type='number' id='quantity-{$item['id']}' min='1' max='{$item['stock']}' value='1' " . ($item['stock'] > 0 ? '' : 'disabled') . ">";
                        echo "<button onclick='addToCart({id: {$item['id']}, name: \"" . htmlspecialchars($item['name'], ENT_QUOTES) . "\", price: {$item['price']}, quantity: parseInt(document.getElementById(\"quantity-{$item['id']}\").value)})' " . ($item['stock'] > 0 ? '' : 'disabled') . ">Add to Cart</button>";
                        echo "</div>";
                        echo "</div>";
                    }
                }
                ?>
            </div>
        </div>
    </div>
    <script>
        async function addToCart(item) {
            if (item.quantity < 1 || item.quantity > parseInt(document.getElementById(`quantity-${item.id}`).max)) {
                showNotification('Invalid quantity selected.', 'error');
                return;
            }

            try {
                const response = await fetch('/sleepwell_coffeeshop/php/add_to_cart.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(item)
                });
                const result = await response.json();

                if (result.success) {
                    showNotification(`Added ${item.quantity} x ${item.name} to cart!`, 'success');
                    window.location.href = '/sleepwell_coffeeshop/php/cart.php';
                } else {
                    showNotification(result.message || 'Failed to add item to cart.', 'error');
                }
            } catch (error) {
                showNotification('Error adding item to cart.', 'error');
                console.error('Add to cart error:', error);
            }
        }

        function showNotification(message, type) {
            const notification = document.createElement('div');
            notification.className = `notification ${type}`;
            notification.textContent = message;
            document.body.appendChild(notification);
            setTimeout(() => {
                notification.style.opacity = '0';
                setTimeout(() => notification.remove(), 500);
            }, 5000);
        }
    </script>
</body>
</html>
<?php
$conn->close();
ob_end_flush();
?>