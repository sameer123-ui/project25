<?php
include 'auth_check.php';

if ($_SESSION['role'] !== 'user') {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f7f7f7;
            margin: 0;
            padding: 0;
        }

        .navbar {
            background-color: #2c3e50;
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            color: white;
        }

        .navbar h1 {
            margin: 0;
            font-size: 24px;
        }

        .navbar ul {
            list-style: none;
            margin: 0;
            padding: 0;
            display: flex;
        }

        .navbar li {
            margin-left: 20px;
        }

        .navbar a {
            color: white;
            text-decoration: none;
            font-weight: 500;
        }

        .navbar a:hover {
            text-decoration: underline;
        }

        .container {
            max-width: 900px;
            margin: 40px auto;
            background-color: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        h2 {
            margin-top: 0;
            color: #2c3e50;
        }

        ul.links {
            list-style: none;
            padding: 0;
        }

        ul.links li {
            margin: 15px 0;
        }

        ul.links a {
            text-decoration: none;
            color: #2980b9;
            font-size: 18px;
            transition: color 0.2s ease;
        }

        ul.links a:hover {
            color: #1abc9c;
        }

        .logout {
            color: red;
        }

        /* User Dashboard Styling */
        .dashboard {
            display: flex;
            justify-content: space-around;
            margin-top: 30px;
            flex-wrap: wrap;
        }

        .card {
            background-color: #ecf0f1;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            text-align: center;
            width: 250px;
            margin: 10px;
        }

        .card h3 {
            font-size: 20px;
            color: #2980b9;
        }

        .card p {
            font-size: 24px;
            font-weight: bold;
            color: #2c3e50;
        }

        .card-icon {
            font-size: 30px;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>

<div class="navbar">
    <h1>User Panel</h1>
    <ul>
        <li><a href="menu.php">View Menu</a></li>
        <li><a href="order_history.php">Order History</a></li>
        <li><a href="profile.php">Manage Profile</a></li>
        <li><a class="logout" href="logout.php">Logout</a></li>
    </ul>
</div>

<div class="container">
    <h2>Welcome, <?= htmlspecialchars($_SESSION['username']) ?> 👋</h2>

    <!-- Dashboard Section -->
    <div class="dashboard">
        <div class="card">
            <div class="card-icon">🍽️</div>
            <h3>View Menu</h3>
            <p>Browse & Order</p>
        </div>
        <div class="card">
            <div class="card-icon">🧾</div>
            <h3>Order History</h3>
            <p>Track Past Orders</p>
        </div>
        <div class="card">
            <div class="card-icon">👤</div>
            <h3>Manage Profile</h3>
            <p>Edit Info</p>
        </div>
    </div>

    <!-- Quick Links Section -->
    <ul class="links">
        <li><a href="menu.php">🍽️ View Menu / Place Order</a></li>
        <li><a href="order_history.php">🧾 View Order History</a></li>
        <li><a href="profile.php">👤 Manage Profile</a></li>
        <li><a class="logout" href="logout.php">🚪 Logout</a></li>
    </ul>
</div>

</body>
</html>
