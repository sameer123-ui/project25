<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
include 'db_connect.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $role = $_POST['role'];

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Check if username exists
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = :username");
    $stmt->bindValue(':username', $username);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        $error = "Username already exists.";
    } else {
        $stmt = $conn->prepare("INSERT INTO users (username, password, role) VALUES (:username, :password, :role)");
        $stmt->bindValue(':username', $username);
        $stmt->bindValue(':password', $hashed_password);
        $stmt->bindValue(':role', $role);

        if ($stmt->execute()) {
            $_SESSION['username'] = $username;
            $_SESSION['role'] = $role;
            header("Location: login.php");
            exit();
        } else {
            $error = "Failed to register. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Register - Restaurant System</title>
    <style>
        * {
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', sans-serif;
            margin: 0;
            padding: 0;
            background: linear-gradient(135deg, #f6d365, #fda085);
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
        }
        .register-container {
            background: #ffffff;
            padding: 30px 25px;
            border-radius: 12px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
            width: 100%;
            max-width: 400px;
        }
        .register-container h2 {
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
            margin-top: 15px;
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

<div class="register-container">
    <h2>Create Account</h2>
    <?php if ($error): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <form method="POST">
        <input type="text" name="username" placeholder="Choose a username" required />
        <input type="password" name="password" placeholder="Create a password" required />
        <select name="role" required>
            <option value="">Select role</option>
            <option value="admin">Admin</option>
            <option value="staff">Staff</option>
            <option value="user">User</option>
        </select>
        <input type="submit" value="Register" />
    </form>
    <div class="links">
        <p><a href="login.php">Already have an account? Login</a></p>
    </div>
</div>

</body>
</html>
