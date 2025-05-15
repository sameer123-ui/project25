<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'db_connect.php'; // assumes $conn is a PDO object

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $role = $_POST['role'];

    $stmt = $conn->prepare("SELECT * FROM users WHERE username = :username AND role = :role");
    $stmt->bindParam(':username', $username);
    $stmt->bindParam(':role', $role);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];

            if ($user['role'] === 'admin') {
                header("Location: admin_dashboard.php");
            } elseif ($user['role'] === 'staff') {
                header("Location: staff_dashboard.php");
            } else {
                header("Location: user_dashboard.php");
            }
            exit();
        } else {
            $error = "Invalid password.";
        }
    } else {
        $error = "User not found or incorrect role.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login - Restaurant System</title>
    <style>
        * {
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', sans-serif;
            margin: 0;
            padding: 0;
            background: linear-gradient(135deg, #74ebd5, #acb6e5);
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
        }
        .login-container {
            background: #ffffff;
            padding: 30px 25px;
            border-radius: 12px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
            width: 100%;
            max-width: 400px;
        }
        .login-container h2 {
            text-align: center;
            margin-bottom: 25px;
            color: #333;
        }
        input[type="text"], input[type="password"], select {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 16px;
        }
        input[type="submit"] {
            width: 100%;
            padding: 12px;
            background: #27ae60;
            border: none;
            color: white;
            font-size: 16px;
            border-radius: 6px;
            margin-top: 20px;
            cursor: pointer;
            transition: background 0.3s ease;
        }
        input[type="submit"]:hover {
            background: #219150;
        }
        .error {
            color: #e74c3c;
            background: #fceae9;
            padding: 10px;
            border-left: 4px solid #e74c3c;
            border-radius: 5px;
            margin-bottom: 15px;
        }
        .links {
            text-align: center;
            margin-top: 20px;
        }
        .links a {
            color: #2980b9;
            text-decoration: none;
            font-size: 14px;
        }
        .links a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

<div class="login-container">
    <h2>Login</h2>
    <?php if ($error): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <form method="POST">
        <input type="text" name="username" placeholder="Enter your username" required />
        <input type="password" name="password" placeholder="Enter your password" required />
        <select name="role" required>
            <option value="">Select your role</option>
            <option value="admin">Admin</option>
            <option value="staff">Staff</option>
            <option value="user">User</option>
        </select>
        <input type="submit" value="Login" />
    </form>

    <div class="links">
        <p><a href="register.php">Don't have an account? Register</a></p>
        <p><a href="forgot_password.php">Forgot Password?</a></p>
    </div>
</div>

</body>
</html>
