<?php
session_start();
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Prevent SQL injection
    $username = $conn->real_escape_string($username);
    $query = "SELECT id, username, password, account_type, is_verified FROM users WHERE username = '$username'";
    $result = $conn->query($query);

    error_log("login.php: Query executed for username=$username, rows=" . ($result ? $result->num_rows : 'query failed'));

    if ($result && $result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['account_type'] = $user['account_type'];
            $_SESSION['is_verified'] = (bool)$user['is_verified'];

            error_log("login.php: Successful login, user_id={$user['id']}, account_type={$user['account_type']}, is_verified={$user['is_verified']}");

            if ($user['account_type'] == 'admin' && $user['is_verified'] && !isset($_GET['no_redirect'])) {
                header("Location: admin_dashboard.php");
            } else {
                header("Location: user_dashboard.php");
            }
            exit;
        } else {
            error_log("login.php: Invalid password for username=$username");
            header("Location: login.php?message=error&text=Invalid%20password.&no_redirect=1");
            exit;
        }
    } else {
        error_log("login.php: User not found for username=$username");
        header("Location: login.php?message=error&text=User%20not%20found.&no_redirect=1");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sleep Well Coffee Shop</title>
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
        .login-container {
            background: linear-gradient(135deg, #ffffff, #f8f1e9);
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
            width: 100%;
            max-width: 400px;
            text-align: center;
            border: 2px solid #d3b7a0;
            animation: fadeIn 1s ease-in-out;
        }
        .login-container h2 {
            color: #6b4e31;
            font-size: 28px;
            margin-bottom: 20px;
            border-bottom: 2px solid #d3b7a0;
            padding-bottom: 10px;
        }
        .login-container form {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        .login-container input {
            padding: 14px;
            border: 1px solid #d3b7a0;
            border-radius: 8px;
            font-size: 16px;
            font-family: 'Georgia', serif;
            background: #fff;
            transition: border-color 0.3s, box-shadow 0.3s;
        }
        .login-container input:focus {
            border-color: #6b4e31;
            outline: none;
            box-shadow: 0 0 8px rgba(107, 78, 49, 0.4);
        }
        .button-container {
            display: flex;
            flex-direction: column;
            gap: 15px;
            margin-top: 10px;
        }
        .login-container button, .login-container .register-btn {
            padding: 14px;
            background: #6b4e31;
            color: white;
            border: 1px solid #d3b7a0;
            border-radius: 8px;
            font-size: 18px;
            font-family: 'Georgia', serif;
            text-decoration: none;
            display: block;
            text-align: center;
            cursor: pointer;
            transition: background 0.3s, transform 0.2s, box-shadow 0.3s;
        }
        .login-container button:hover, .login-container .register-btn:hover {
            background: #8b6f47;
            transform: scale(1.05);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
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
        .notification.error {
            background: #f44336;
        }
        .notification.success {
            background: #4CAF50;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @media (max-width: 576px) {
            .login-container {
                padding: 20px;
                margin: 15px;
            }
            .login-container h2 {
                font-size: 24px;
            }
            .login-container input, .login-container button, .login-container .register-btn {
                font-size: 16px;
                padding: 12px;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h2>Login</h2>
        <?php
        $message = isset($_GET['message']) ? $_GET['message'] : '';
        $message_text = isset($_GET['text']) ? htmlspecialchars($_GET['text']) : '';
        if ($message): ?>
            <div class="notification <?php echo $message; ?>">
                <?php echo $message_text; ?>
            </div>
        <?php endif; ?>
        <form action="login.php" method="POST">
            <input type="text" name="username" placeholder="Username" required>
            <input type="password" name="password" placeholder="Password" required>
            <div class="button-container">
                <button type="submit">Login</button>
                <a href="register.php" class="register-btn">Register</a>
            </div>
        </form>
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