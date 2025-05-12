<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
include 'db_connect.php'; // your DB connection file

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $role = $_POST['role'];

    // Hash the password using password_hash (more secure)
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Check if username already exists
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = :username");
    $stmt->bindValue(':username', $username, PDO::PARAM_STR);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        $error = "Username already exists.";
    } else {
        // Insert new user into database
        $stmt = $conn->prepare("INSERT INTO users (username, password, role) VALUES (:username, :password, :role)");
        $stmt->bindValue(':username', $username, PDO::PARAM_STR);
        $stmt->bindValue(':password', $hashed_password, PDO::PARAM_STR);
        $stmt->bindValue(':role', $role, PDO::PARAM_STR);

        if ($stmt->execute()) {
            $_SESSION['username'] = $username;
            $_SESSION['role'] = $role;
            header("Location: login.php"); // Redirect to login page after successful registration
            exit();
        } else {
            $error = "Failed to register. Please try again. Error: " . $stmt->errorInfo();
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Register - Restaurant System</title>
    <style>
        body { font-family: Arial; background: #f5f5f5; }
        .register-container {
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
    </style>
</head>
<body>

<div class="register-container">
    <h2>Register</h2>
    <?php if ($error): ?>
        <p class="error"><?= $error ?></p>
    <?php endif; ?>
    <form method="POST">
        <input type="text" name="username" placeholder="Username" required />
        <input type="password" name="password" placeholder="Password" required />
        <select name="role" required>
            <option value="admin">Admin</option>
            <option value="staff">Staff</option>
            <option value="user">User</option> <!-- Added the "user" role -->
        </select>
        <input type="submit" value="Register" />
    </form>
</div>

</body>
</html>
