<?php
session_start();
include 'config.php';

// Debug session variables
file_put_contents('C:/xampp/htdocs/sleepwell_coffeeshop/php/debug.log', 
    "admin_dashboard.php accessed at " . date('Y-m-d H:i:s') . "\n" .
    "Session ID: " . session_id() . "\n" .
    "User ID: " . (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'Not set') . "\n" .
    "Account Type: " . (isset($_SESSION['account_type']) ? $_SESSION['account_type'] : 'Not set') . "\n" .
    "Is Verified: " . (isset($_SESSION['is_verified']) ? $_SESSION['is_verified'] : 'Not set') . "\n\n", 
    FILE_APPEND
);

// Check for redirect loop prevention
if (isset($_GET['no_redirect']) && $_GET['no_redirect'] == '1') {
    die("Access denied: Please log in as a verified admin.");
}

// Check if user is logged in, is admin, and is verified
if (!isset($_SESSION['user_id']) || $_SESSION['account_type'] != 'admin' || !$_SESSION['is_verified']) {
    header("Location: /sleepwell_coffeeshop/php/index.php?message=error&text=Access%20denied.%20Please%20log%20in%20as%20a%20verified%20admin.&no_redirect=1");
    exit;
}

ob_start();

// Get notification message from query parameters
$message = isset($_GET['message']) ? $_GET['message'] : '';
$message_text = isset($_GET['text']) ? htmlspecialchars($_GET['text']) : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Sleep Well Coffee Shop</title>
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
        .category-filter button {
            background: #6b4e31;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
            transition: background 0.3s, transform 0.2s, box-shadow 0.3s;
        }
        .category-filter button:hover {
            background: #8b6f47;
            transform: scale(1.05);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
        }
        .category-filter button.active {
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
        .menu-grid.three-column {
            grid-template-columns: 1fr 1fr 1fr;
            gap: 20px;
        }
        .category-column {
            display: flex;
            flex-direction: column;
            animation: fadeIn 1s ease-in-out;
        }
        .category-column h3 {
            color: #6b4e31;
            font-size: 22px;
            margin-bottom: 15px;
            border-bottom: 1px solid #d3b7a0;
            padding-bottom: 5px;
            text-align: left;
        }
        .category-items {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        .menu-item {
            background: linear-gradient(135deg, #ffffff, #f8f1e9);
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            text-align: center;
            transition: transform 0.3s ease, box-shadow 0.3s;
            border: 1px solid #d3b7a0;
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
        .menu-item form {
            display: flex;
            flex-direction: column;
            gap: 10px;
            margin-top: 10px;
        }
        .menu-item input, .menu-item select {
            padding: 12px;
            border: 1px solid #d3b7a0;
            border-radius: 8px;
            font-size: 16px;
            font-family: 'Georgia', serif;
            background: #fff;
            transition: border-color 0.3s, box-shadow 0.3s;
        }
        .menu-item input:focus, .menu-item select:focus {
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
        .menu-item .delete-btn {
            background: #f44336;
        }
        .menu-item .delete-btn:hover {
            background: #d32f2f;
        }
        .no-items {
            color: #6b4e31;
            font-size: 16px;
            text-align: center;
        }
        .form-container {
            width: 100%;
            max-width: 500px;
            background: linear-gradient(135deg, #ffffff, #f8f1e9);
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            margin-top: 30px;
            text-align: center;
            border: 1px solid #d3b7a0;
            animation: fadeIn 1s ease-in-out;
        }
        .form-container form {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        .form-container input, .form-container select {
            padding: 12px;
            border: 1px solid #d3b7a0;
            border-radius: 8px;
            font-size: 16px;
            font-family: 'Georgia', serif;
            background: #fff;
            transition: border-color 0.3s, box-shadow 0.3s;
        }
        .form-container input:focus, .form-container select:focus {
            border-color: #6b4e31;
            outline: none;
            box-shadow: 0 0 8px rgba(107, 78, 49, 0.4);
        }
        .form-container button {
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
        .form-container button:hover {
            background: #8b6f47;
            transform: scale(1.05);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
        }
        .admin-verification, .order-history {
            width: 100%;
            max-width: 500px;
            background: linear-gradient(135deg, #ffffff, #f8f1e9);
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            margin-top: 30px;
            border: 1px solid #d3b7a0;
            animation: fadeIn 1s ease-in-out;
        }
        .admin-verification table, .order-history table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        .admin-verification th, .admin-verification td, .order-history th, .order-history td {
            padding: 10px;
            border: 1px solid #d3b7a0;
            text-align: center;
        }
        .admin-verification th, .order-history th {
            background: #6b4e31;
            color: white;
        }
        .admin-verification button {
            padding: 8px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
            font-family: 'Georgia', serif;
            transition: background 0.3s, transform 0.2s;
        }
        .admin-verification .verify-btn {
            background: #4CAF50;
        }
        .admin-verification .verify-btn:hover {
            background: #45a049;
            transform: scale(1.05);
        }
        .admin-verification .deny-btn {
            background: #f44336;
        }
        .admin-verification .deny-btn:hover {
            background: #d32f2f;
            transform: scale(1.05);
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
            .menu-grid.three-column {
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
            .menu-grid.three-column {
                grid-template-columns: 1fr;
            }
            .form-container, .admin-verification, .order-history {
                padding: 15px;
            }
            .category-filter {
                gap: 8px;
            }
            .category-filter button {
                padding: 8px 16px;
                font-size: 14px;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard">
        <div class="sidebar">
            <h2>Sleep Well Coffee Shop</h2>
            <ul>
                <li><a href="#menu" class="nav-link">View Menu</a></li>
                <li><a href="#add-item" class="nav-link">Add Item</a></li>
                <li><a href="#verify-admins" class="nav-link">Verify Admins</a></li>
                <li><a href="#view-orders" class="nav-link">View Orders</a></li>
                <li><a href="/sleepwell_coffeeshop/php/logout.php">Logout</a></li>
            </ul>
        </div>
        <div class="content">
            <?php if ($message): ?>
                <div class="notification <?php echo $message; ?>">
                    <?php echo $message_text; ?>
                </div>
            <?php endif; ?>
            <div id="menu">
                <h2>Menu</h2>
                <div class="menu-grid" id="menu-grid">
                    <?php
                    $result = $conn->query("SELECT * FROM menu_items ORDER BY category, name");
                    $items_by_category = ['food' => [], 'beverage' => [], 'appetizer' => []];
                    while ($item = $result->fetch_assoc()) {
                        $category = htmlspecialchars(strtolower($item['category']));
                        $items_by_category[$category][] = $item;
                    }
                    foreach (['food', 'beverage', 'appetizer'] as $category) {
                        echo "<div class='category-column' data-category='$category'>";
                        echo "<h3>" . ucfirst($category) . "</h3>";
                        echo "<div class='category-items'>";
                        if (empty($items_by_category[$category])) {
                            echo "<p class='no-items'>No items available</p>";
                        } else {
                            foreach ($items_by_category[$category] as $item) {
                                file_put_contents('C:/xampp/htdocs/sleepwell_coffeeshop/php/debug.log', 
                                    "admin_dashboard.php: Displaying item ID={$item['id']}, name=" . htmlspecialchars($item['name']) . "\n", 
                                    FILE_APPEND
                                );
                                echo "<div class='menu-item' data-category='$category'>
                                    <h3>" . htmlspecialchars($item['name']) . "</h3>
                                    <p>Category: " . ucfirst(htmlspecialchars($item['category'])) . "</p>
                                    <p>Price: $" . number_format($item['price'], 2) . "</p>
                                    <p>Stock: " . ($item['stock'] > 0 ? htmlspecialchars($item['stock']) : 'Out of Stock') . "</p>
                                    <form action='/sleepwell_coffeeshop/php/update_menu.php' method='POST'>
                                        <input type='hidden' name='item_id' value='{$item['id']}'>
                                        <input type='text' name='name' value='" . htmlspecialchars($item['name']) . "' required>
                                        <select name='category' required>
                                            <option value='food' " . ($item['category'] == 'food' ? 'selected' : '') . ">Food</option>
                                            <option value='beverage' " . ($item['category'] == 'beverage' ? 'selected' : '') . ">Beverage</option>
                                            <option value='appetizer' " . ($item['category'] == 'appetizer' ? 'selected' : '') . ">Appetizer</option>
                                        </select>
                                        <input type='number' name='price' value='{$item['price']}' min='0' step='0.01' required>
                                        <input type='number' name='stock' value='{$item['stock']}' min='0' required>
                                        <button type='submit'>Update</button>
                                    </form>
                                    <form action='/sleepwell_coffeeshop/php/delete_menu.php' method='POST'>
                                        <input type='hidden' name='item_id' value='{$item['id']}'>
                                        <button type='submit' class='delete-btn'>Delete</button>
                                    </form>
                                </div>";
                            }
                        }
                        echo "</div></div>";
                    }
                    ?>
                </div>
            </div>
            <div id="add-item" style="display: none;">
                <h2>Add Menu Item</h2>
                <div class="form-container">
                    <form action="/sleepwell_coffeeshop/php/add_menu.php" method="POST">
                        <input type="text" name="name" placeholder="Item Name" required>
                        <select name="category" required>
                            <option value="food">Food</option>
                            <option value="beverage">Beverage</option>
                            <option value="appetizer">Appetizer</option>
                        </select>
                        <input type="number" name="price" placeholder="Price" min="0" step="0.01" required>
                        <input type="number" name="stock" placeholder="Stock" min="0" required>
                        <button type="submit">Add Item</button>
                    </form>
                </div>
            </div>
            <div id="verify-admins" style="display: none;">
                <h2>Verify Admins</h2>
                <div class="admin-verification">
                    <table>
                        <tr>
                            <th>Username</th>
                            <th>Action</th>
                        </tr>
                        <?php
                        $result = $conn->query("SELECT id, username FROM users WHERE account_type = 'admin' AND is_verified = 0");
                        while ($admin = $result->fetch_assoc()) {
                            echo "<tr>
                                <td>" . htmlspecialchars($admin['username']) . "</td>
                                <td>
                                    <form action='/sleepwell_coffeeshop/php/verify_admin.php' method='POST' style='display: inline;'>
                                        <input type='hidden' name='id' value='{$admin['id']}'>
                                        <input type='hidden' name='action' value='verify'>
                                        <button type='submit' class='verify-btn'>Verify</button>
                                    </form>
                                    <form action='/sleepwell_coffeeshop/php/verify_admin.php' method='POST' style='display: inline;'>
                                        <input type='hidden' name='id' value='{$admin['id']}'>
                                        <input type='hidden' name='action' value='deny'>
                                        <button type='submit' class='deny-btn'>Deny</button>
                                    </form>
                                </td>
                            </tr>";
                        }
                        ?>
                    </table>
                </div>
            </div>
            <div id="view-orders" style="display: none;">
                <h2>Order History</h2>
                <div class="order-history">
                    <table>
                        <tr>
                            <th>Username</th>
                            <th>Item</th>
                            <th>Quantity</th>
                            <th>Order Date</th>
                        </tr>
                        <?php
                        $result = $conn->query("SELECT o.*, m.name, u.username FROM orders o JOIN menu_items m ON o.item_id = m.id JOIN users u ON o.user_id = u.id ORDER BY o.order_date DESC");
                        if ($result->num_rows > 0) {
                            while ($order = $result->fetch_assoc()) {
                                echo "<tr>
                                    <td>" . htmlspecialchars($order['username']) . "</td>
                                    <td>" . htmlspecialchars($order['name']) . "</td>
                                    <td>" . htmlspecialchars($order['quantity']) . "</td>
                                    <td>" . htmlspecialchars($order['order_date']) . "</td>
                                </tr>";
                            }
                        } else {
                            echo "<tr><td colspan='4' class='no-items'>No orders available</td></tr>";
                        }
                        ?>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <script>
        // Only apply navigation to links with nav-link class
        document.querySelectorAll('.nav-link').forEach(link => {
            link.addEventListener('click', e => {
                if (e.target.getAttribute('href').startsWith('#')) {
                    e.preventDefault();
                    document.querySelectorAll('.content > div').forEach(div => div.style.display = 'none');
                    document.querySelector(e.target.hash).style.display = 'block';
                }
            });
        });

        // Auto-hide notification after 5 seconds
        setTimeout(() => {
            const notification = document.querySelector('.notification');
            if (notification) {
                notification.style.display = 'none';
            }
        }, 5000);
    </script>
</body>
</html>
<?php
$conn->close();
ob_end_flush();
?>