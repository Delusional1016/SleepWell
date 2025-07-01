<?php
include 'config.php';

$result = $conn->query("SELECT o.*, m.name, u.username FROM orders o JOIN menu_items m ON o.item_id = m.id JOIN users u ON o.user_id = u.id");
while ($order = $result->fetch_assoc()) {
    echo "<p>User {$order['username']} ordered {$order['quantity']} x {$order['name']} on {$order['order_date']}</p>";
}
$conn->close();
?>