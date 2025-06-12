<?php
session_start();
include 'auth_check.php';
include 'db_connect.php';

if ($_SESSION['role'] !== 'user') {
    header("Location: login.php");
    exit();
}

$success = $error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = $_SESSION['user_id'];
    $module = $_POST['module'] ?? 'general';
    $message = trim($_POST['message']);

    if (!empty($message)) {
        $stmt = $conn->prepare("INSERT INTO feedback (user_id, module, message) VALUES (?, ?, ?)");
        $stmt->execute([$userId, $module, $message]);
        $success = "Thank you for your feedback!";
    } else {
        $error = "Please enter your feedback message.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Submit Feedback</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet" />
    <style>
        /* Reuse your dashboard styles for consistency */
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

        .navbar h1 {
            margin: 0;
            font-size: 26px;
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
            max-width: 900px;
            margin: 40px auto;
            background-color: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 6px 20px rgba(0,0,0,0.1);
        }

        h2 {
            color: #2c3e50;
            margin-bottom: 20px;
            text-align: center;
        }

        form {
            max-width: 600px;
            margin: 0 auto;
        }

        label {
            font-weight: 600;
            display: block;
            margin-top: 20px;
            margin-bottom: 6px;
            color: #2c3e50;
        }

        select, textarea {
            width: 100%;
            padding: 10px;
            font-size: 15px;
            border-radius: 8px;
            border: 1px solid #ccc;
            resize: vertical;
            font-family: 'Inter', sans-serif;
        }

        button {
            margin-top: 25px;
            background-color: #34495e;
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s ease;
            display: block;
            width: 100%;
        }

        button:hover {
            background-color: #1abc9c;
        }

        .message {
            text-align: center;
            font-weight: 600;
            margin-top: 15px;
        }

        .message.success {
            color: green;
        }

        .message.error {
            color: red;
        }

        footer {
            background-color: #2c3e50;
            color: white;
            padding: 20px 0;
            text-align: center;
            margin-top: 120px;
        }
        footer .container {
            max-width: 1100px;
            margin: auto;
        }
        footer p {
            font-size: 14px;
            color: #bdc3c7;
            margin-top: 0;
        }

        @media (max-width: 600px) {
            .container {
                margin: 20px 15px;
                padding: 20px;
            }
            button {
                font-size: 14px;
            }
        }
    </style>
</head>
<body>

<div class="navbar">
    <h1>User Panel</h1>
    <ul>
        <li><a href="user_dashboard.php">Home</a></li>
        <li><a href="menu.php">View Menu</a></li>
        <li><a href="order_menu.php">Place an Order</a></li>
        <li><a href="my_orders.php">My Orders</a></li>
        <li><a href="order_history.php">Order History</a></li>
        <li><a href="book_table.php">Booking</a></li>
        <li><a href="my_bookings.php">My Bookings</a></li>
        <li><a href="feedback.php">Feedback</a></li>
        <li><a href="profile.php">Manage Profile</a></li>
        <li><a class="logout" href="logout.php">Logout</a></li>
    </ul>
</div>

<div class="container">
    <h2>Submit Your Feedback</h2>

    <?php if ($success): ?>
        <p class="message success"><?= htmlspecialchars($success) ?></p>
    <?php elseif ($error): ?>
        <p class="message error"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <form method="POST" action="">
        <label for="module">Feedback about:</label>
        <select name="module" id="module" required>
            <option value="general">General</option>
            <option value="orders">Orders</option>
            <option value="table_booking">Table Booking</option>
            <option value="menu">Menu</option>
            <option value="payment">Payment</option>
        </select>

        <label for="message">Your Feedback:</label>
        <textarea name="message" id="message" rows="6" placeholder="Write your feedback here..." required></textarea>

        <button type="submit">Submit Feedback</button>
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
