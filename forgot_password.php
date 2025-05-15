<?php
session_start();
include 'db_connect.php'; // Assumes $conn is a PDO instance

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    if ($new_password !== $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        $stmt = $conn->prepare("SELECT * FROM users WHERE username = :username");
        $stmt->bindParam(':username', $username);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

            $stmt = $conn->prepare("UPDATE users SET password = :password WHERE username = :username");
            $stmt->bindParam(':password', $hashed_password);
            $stmt->bindParam(':username', $username);
            $stmt->execute();

            header("Location: login.php?message=reset_success");
            exit;
        } else {
            $error = "Username not found.";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Reset Password - Restaurant System</title>
    <style>
        * {
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', sans-serif;
            margin: 0;
            padding: 0;
            background: linear-gradient(135deg, #74ebd5, #9face6);
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
        }
        .reset-container {
            background: #ffffff;
            padding: 30px 25px;
            border-radius: 12px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
            width: 100%;
            max-width: 400px;
        }
        .reset-container h2 {
            text-align: center;
            margin-bottom: 25px;
            color: #333;
        }
        input[type="text"], input[type="password"] {
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
        .error, .success {
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 5px;
        }
        .error {
            color: #e74c3c;
            background: #fceaea;
            border-left: 4px solid #e74c3c;
        }
        .success {
            color: #2ecc71;
            background: #eafaf1;
            border-left: 4px solid #2ecc71;
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

<div class="reset-container">
    <h2>Reset Password</h2>
    <?php if ($error): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div class="success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>
    <form method="POST">
        <input type="text" name="username" placeholder="Enter your username" required />
        <input type="password" name="new_password" placeholder="New password" required />
        <input type="password" name="confirm_password" placeholder="Confirm new password" required />
        <input type="submit" value="Reset Password" />
    </form>
    <div class="links">
        <p><a href="login.php">Back to Login</a></p>
    </div>
</div>

</body>
</html>
