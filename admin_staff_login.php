<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'db_connect.php'; // $conn PDO
define('CUSTOM_SALT', 'your-secure-salt-value');

function custom_hash($password) {
    return hash_hmac('sha256', $password, CUSTOM_SALT);
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    // Only allow role = admin or staff
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = :username AND (role = 'admin' OR role = 'staff')");
    $stmt->bindParam(':username', $username);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        if (custom_hash($password) === $user['password']) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];

            if ($user['role'] === 'admin') {
                header("Location: admin_dashboard.php");
            } else {
                header("Location: staff_dashboard.php");
            }
            exit();
        } else {
            $error = "Invalid password.";
        }
    } else {
        $error = "User not found or not authorized.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Admin / Staff Login - Restaurant System</title>
<style>
  @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap');

  * {
    box-sizing: border-box;
  }
  body {
    margin: 0;
    font-family: 'Poppins', sans-serif;
    background: linear-gradient(135deg, #34495e, #2c3e50);
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh;
    color: #ecf0f1;
  }
  .login-container {
    background: #2c3e50;
    padding: 40px 35px;
    border-radius: 12px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.3);
    width: 100%;
    max-width: 380px;
    text-align: center;
  }
  .login-container h2 {
    margin-bottom: 30px;
    font-weight: 600;
    font-size: 28px;
    letter-spacing: 1.1px;
  }
  input[type="text"], input[type="password"] {
    width: 100%;
    padding: 14px 16px;
    margin: 12px 0 20px 0;
    border-radius: 6px;
    border: none;
    font-size: 16px;
    outline: none;
    transition: box-shadow 0.3s ease;
  }
  input[type="text"]:focus, input[type="password"]:focus {
    box-shadow: 0 0 10px #1abc9c;
  }
  input[type="submit"] {
    width: 100%;
    background: #1abc9c;
    border: none;
    color: white;
    font-size: 18px;
    font-weight: 600;
    padding: 14px 0;
    border-radius: 8px;
    cursor: pointer;
    transition: background 0.3s ease;
    margin-top: 10px;
  }
  input[type="submit"]:hover {
    background: #16a085;
  }
  .error {
    background: #e74c3c;
    color: #fff;
    padding: 12px;
    margin-bottom: 25px;
    border-radius: 6px;
    font-weight: 600;
  }
  .links {
    margin-top: 30px;
  }
  .links a {
    color: #1abc9c;
    text-decoration: none;
    font-size: 15px;
    transition: color 0.3s ease;
  }
  .links a:hover {
    color: #16a085;
  }
</style>
</head>
<body>

<div class="login-container">
  <h2>Admin / Staff Login</h2>
  <?php if ($error): ?>
    <div class="error"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>
  <form method="POST" autocomplete="off" novalidate>
    <input type="text" name="username" placeholder="Username" required autofocus />
    <input type="password" name="password" placeholder="Password" required />
    <input type="submit" value="Login" />
  </form>
  <div class="links">
    <p><a href="forgot_password.php">Forgot Password?</a></p>
    <p><a href="user_login.php">User Login</a></p>
  </div>
</div>

</body>
</html>
