<?php
session_start();
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $account_type = trim($_POST['account_type']);
    
    // Validate input
    if (empty($username) || empty($password) || empty($account_type)) {
        header("Location: register.php?message=error&text=All fields are required");
        exit;
    }
    
    // Check if username exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        header("Location: register.php?message=error&text=Username already exists");
        exit;
    }
    
    // Hash password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    // Set verification status (0 for admin, 1 for user)
    $is_verified = ($account_type == 'admin') ? 0 : 1;
    
    // Insert user
    $stmt = $conn->prepare("INSERT INTO users (username, password, account_type, is_verified) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("sssi", $username, $hashed_password, $account_type, $is_verified);
    
    if ($stmt->execute()) {
        // Set notification based on account type
        $message_text = ($account_type == 'admin') ? 'Registered successfully. Waiting for verification' : 'Registered successfully';
        header("Location: index.php?message=success&text=" . urlencode($message_text));
    } else {
        header("Location: register.php?message=error&text=Registration failed");
    }
    
    $stmt->close();
    $conn->close();
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Sleep Well Coffee Shop</title>
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
            display: flex;
            justify-content: center;
            align-items: center;
            background-image: linear-gradient(rgba(255, 245, 238, 0.9), rgba(255, 245, 238, 0.9)), url('https://images.unsplash.com/photo-1512568400610-62da28bc8a13?ixlib=rb-4.0.3&auto=format&fit=crop&w=1350&q=80');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
        }
        .register-container {
            background: linear-gradient(135deg, #ffffff, #f8f1e9);
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
            text-align: center;
            border: 1px solid #d3b7a0;
            animation: fadeIn 1s ease-in-out;
        }
        h2 {
            color: #6b4e31;
            margin-bottom: 20px;
            font-size: 26px;
        }
        form {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        label {
            font-size: 16px;
            color: #3c2f2f;
            text-align: left;
        }
        input, select {
            padding: 12px;
            border: 1px solid #d3b7a0;
            border-radius: 8px;
            font-size: 16px;
            font-family: 'Georgia', serif;
            background: #fff;
            transition: border-color 0.3s, box-shadow 0.3s;
        }
        input:focus, select:focus {
            border-color: #6b4e31;
            outline: none;
            box-shadow: 0 0 8px rgba(107, 78, 49, 0.4);
        }
        button {
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
        button:hover {
            background: #8b6f47;
            transform: scale(1.05);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
        }
        .login-link {
            margin-top: 20px;
            font-size: 14px;
        }
        .login-link a {
            color: #6b4e31;
            text-decoration: none;
            font-weight: bold;
            transition: color 0.3s;
        }
        .login-link a:hover {
            color: #8b6f47;
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
        @media (max-width: 576px) {
            .register-container {
                padding: 20px;
                margin: 15px;
            }
            button {
                padding: 10px;
                font-size: 14px;
            }
        }
    </style>
</head>
<body>
    <div class="register-container">
        <h2>Register</h2>
        <?php if (isset($_GET['message'])): ?>
            <div class="notification <?php echo htmlspecialchars($_GET['message']); ?>">
                <?php echo htmlspecialchars($_GET['text']); ?>
            </div>
        <?php endif; ?>
        <form action="register.php" method="POST">
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" required>
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>
            <label for="account_type">Account Type:</label>
            <select id="account_type" name="account_type" required>
                <option value="user">User</option>
                <option value="admin">Admin</option>
            </select>
            <button type="submit">Register</button>
        </form>
        <div class="login-link">
            Already have an account? <a href="index.php">Login here</a>
        </div>
    </div>
    <script>
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
?>