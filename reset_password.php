<?php
session_start();
include 'db_connect.php'; // your DB connection file

$error   = '';
$success = '';
$showForm = false;

// 1) Ensure token is provided
if (!isset($_GET['token'])) {
    $error = "No reset token found.";
} else {
    $token = $_GET['token'];

    // 2) Validate token in database
    $stmt = $conn->prepare("SELECT id FROM users WHERE reset_token = ?");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows !== 1) {
        $error = "Invalid or expired reset token.";
    } else {
        // Token is valid; show the form
        $showForm = true;

        // 3) Handle form submission
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $new_password = $_POST['new_password'];
            $hashed_password = hash('sha256', $new_password);

            // Update password and clear token
            $upd = $conn->prepare("
                UPDATE users 
                SET password = ?, reset_token = NULL 
                WHERE reset_token = ?
            ");
            $upd->bind_param("ss", $hashed_password, $token);

            if ($upd->execute()) {
                $success  = "Your password has been successfully reset. You can now <a href='login.php'>log in</a>.";
                $showForm = false;  // hide form after success
            } else {
                $error = "Failed to reset your password. Please try again.";
            }
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
        .container {
            width: 300px; margin: 100px auto; background: #fff;
            padding: 20px; border-radius: 8px; box-shadow: 0 0 10px #ccc;
        }
        input[type="password"], input[type="submit"] {
            width: 100%; padding: 10px; margin-top: 10px;
        }
        input[type="submit"] {
            background: #27ae60; color: #fff; border: none; cursor: pointer;
        }
        .error { color: red; margin-bottom: 10px; }
        .success { color: green; margin-bottom: 10px; }
    </style>
</head>
<body>

<div class="container">
    <h2>Reset Password</h2>

    <?php if ($error): ?>
        <p class="error"><?= $error ?></p>
    <?php endif; ?>

    <?php if ($success): ?>
        <p class="success"><?= $success ?></p>
    <?php endif; ?>

    <?php if ($showForm): ?>
    <form method="POST">
        <input type="password" name="new_password" placeholder="New password" required />
        <input type="submit" value="Reset Password" />
    </form>
    <?php endif; ?>
</div>

</body>
</html>
