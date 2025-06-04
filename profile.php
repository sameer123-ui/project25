<?php
session_start();
include 'auth_check.php';
include 'db_connect.php';

$user_id = $_SESSION['user_id'];

// Handle profile update
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST['username']);

    $stmt = $conn->prepare("UPDATE users SET username = ? WHERE id = ?");
    $stmt->execute([$username, $user_id]);

    $_SESSION['username'] = $username;
    $success = "Profile updated successfully!";
}

// Fetch current user info
$stmt = $conn->prepare("SELECT username FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Profile</title>
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f0f2f5;
            margin: 0;
            padding: 0;
        }

        .navbar {
            background: linear-gradient(to right, #2c3e50, #34495e);
            padding: 20px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            color: white;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }

        .navbar ul {
            list-style: none;
            display: flex;
            margin: 0;
            padding: 0;
        }

        .navbar li {
            margin-left: 25px;
        }

        .navbar a {
            color: white;
            text-decoration: none;
            font-weight: 600;
            transition: 0.3s;
        }

        .navbar a:hover,
        .navbar a.logout:hover {
            color: #1abc9c;
        }

        .navbar .logout {
            color: #e74c3c;
        }

        .container {
            max-width: 600px;
            margin: 40px auto;
            background-color: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 6px 20px rgba(0,0,0,0.1);
        }

        h2 {
            margin-bottom: 20px;
            color: #2c3e50;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            font-weight: 600;
            margin-bottom: 8px;
        }

        input[type="text"] {
            width: 100%;
            padding: 10px 15px;
            border-radius: 8px;
            border: 1px solid #ccc;
            font-size: 16px;
        }

        .btn {
            background-color: #2980b9;
            color: white;
            padding: 10px 20px;
            font-weight: bold;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .btn:hover {
            background-color: #1abc9c;
        }

        .success {
            background-color: #dff0d8;
            color: #3c763d;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 8px;
        }
    </style>
</head>
<body>

<div class="navbar">
    <div>Welcome, <?= htmlspecialchars($_SESSION['username']) ?></div>
    <ul>
        <li><a href="user_dashboard.php">Home</a></li>
        <li><a href="menu.php">View Menu</a></li>
        <li><a href="order_menu.php">Place an Order</a></li>
        <li><a href="my_orders.php">My Orders</a></li>
        <li><a href="order_history.php">Order History</a></li>
          <li><a href="book_table.php">Booking</a></li>
           <li><a href="my_bookings.php">My bookings</a></li>
          <li><a href="profile.php">Manage Profile</a></li>
        <li><a class="logout" href="logout.php">Logout</a></li>
    </ul>
</div>

<div class="container">
    <h2>Manage Profile</h2>

    <?php if (!empty($success)): ?>
        <div class="success"><?= $success ?></div>
    <?php endif; ?>

    <form method="post">
        <div class="form-group">
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" value="<?= htmlspecialchars($user['username']) ?>" required />
        </div>
        <button type="submit" class="btn">Update Profile</button>
    </form>
</div>
    <footer style="background-color: #2c3e50; color: white; padding: 20px 0; text-align: center; margin-top: 400px;">
    <div style="max-width: 1100px; margin: auto;">
        <p style="margin-bottom: 10px; font-size: 16px;">Quick Links</p>
        <div style="display: flex; justify-content: center; flex-wrap: wrap; gap: 20px;">
          
            <a href="logout.php" style="color: #e74c3c; text-decoration: none;">🚪 Logout</a>
        </div>
        <p style="margin-top: 15px; font-size: 14px; color: #bdc3c7;">&copy; <?= date("Y") ?> Restaurant Customer Panel</p>
    </div>
</footer>

</body>
</html>
