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

    // Prepare and execute PDO statement
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = :username AND role = :role");
    $stmt->bindParam(':username', $username);
    $stmt->bindParam(':role', $role);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        // Use password_verify to check hashed password
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];

            // Redirect based on role
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
        body { font-family: Arial; background: #f5f5f5; }
        .login-container {
            width: 300px; margin: 100px auto; background: #fff;
            padding: 20px; border-radius: 8px; box-shadow: 0 0 10px #ccc;
        }
        input[type="text"], input[type="password"], select {
            width: 100%; padding: 10px; margin-top: 10px;
        }
        input[type="submit"] {
            width: 100%; padding: 10px; background: #27ae60; color: white;
            border: none; cursor: pointer; margin-top: 20px;
        }
        .error { color: red; }
        .links {
            margin-top: 15px;
            text-align: center;
        }
        .links a {
            color: #27ae60;
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
        <p class="error"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>
    <form method="POST">
        <input type="text" name="username" placeholder="Username" required />
        <input type="password" name="password" placeholder="Password" required />
        <select name="role" required>
            <option value="">Select Role</option>
            <option value="admin">Admin</option>
            <option value="staff">Staff</option>
            <option value="user">User</option>
        </select>
        <input type="submit" value="Login" />
    </form>

    <div class="links">
        <p><a href="register.php">Don't have an account? Register here</a></p>
        <p><a href="forgot_password.php">Forgot your password?</a></p>
    </div>
</div>

</body>
</html>
