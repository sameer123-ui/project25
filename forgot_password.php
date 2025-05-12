<?php
session_start();
include 'db_connect.php'; // Assumes $conn is a PDO instance

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // Validate passwords match
    if ($new_password !== $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        // Check if username exists
        $stmt = $conn->prepare("SELECT * FROM users WHERE username = :username");
        $stmt->bindParam(':username', $username);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // Hash new password
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

            // Update in DB
            $stmt = $conn->prepare("UPDATE users SET password = :password WHERE username = :username");
            $stmt->bindParam(':password', $hashed_password);
            $stmt->bindParam(':username', $username);
            $stmt->execute();

            $success = "Password has been successfully updated.";
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
        body { font-family: Arial; background: #f5f5f5; }
        .reset-container {
            width: 320px; margin: 100px auto; background: #fff;
            padding: 20px; border-radius: 8px; box-shadow: 0 0 10px #ccc;
        }
        input[type="text"], input[type="password"] {
            width: 100%; padding: 10px; margin-top: 10px;
        }
        input[type="submit"] {
            width: 100%; padding: 10px; background: #27ae60; color: white;
            border: none; cursor: pointer; margin-top: 20px;
        }
        .error { color: red; }
        .success { color: green; }
    </style>
</head>
<body>

<div class="reset-container">
    <h2>Reset Password</h2>
    <?php if ($error): ?>
        <p class="error"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>
    <?php if ($success): ?>
        <p class="success"><?= htmlspecialchars($success) ?></p>
    <?php endif; ?>
    <form method="POST">
        <input type="text" name="username" placeholder="Enter your username" required />
        <input type="password" name="new_password" placeholder="New password" required />
        <input type="password" name="confirm_password" placeholder="Confirm new password" required />
        <input type="submit" value="Reset Password" />
    </form>
</div>

</body>
</html>
