<?php
include 'auth_check.php';
include 'db_connect.php'; // Ensure your database connection file is working

// Ensure the user is an admin
if ($_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Fetch dashboard data (add more queries if needed)
$staffCount = $conn->query("SELECT COUNT(*) as total FROM staff")->fetch(PDO::FETCH_ASSOC)['total'];
$menuCount = $conn->query("SELECT COUNT(*) as total FROM menu")->fetch(PDO::FETCH_ASSOC)['total'];
$orderCount = $conn->query("SELECT COUNT(*) as total FROM orders")->fetch(PDO::FETCH_ASSOC)['total'];
$userCount = $conn->query("SELECT COUNT(*) as total FROM users")->fetch(PDO::FETCH_ASSOC)['total'];
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f4f9;
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
            max-width: 1100px;
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

        /* Dashboard Stats Styling */
        .dashboard-stats {
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
            width: 22%;
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

        @media (max-width: 768px) {
            .card {
                width: 45%;
            }
        }

        @media (max-width: 480px) {
            .card {
                width: 100%;
            }
        }
    </style>
</head>
<body>

    <div class="navbar">
        <h1>Admin Panel</h1>
        <ul>
            <li><a href="manage_staff.php">Staff</a></li>
            <li><a href="manage_menu.php">Menu</a></li>
            <li><a href="view_orders.php">Orders</a></li>
            <li><a href="manage_users.php">Users</a></li>
            <li><a class="logout" href="logout.php">Logout</a></li>
        </ul>
    </div>

    <div class="container">
        <h2>Welcome, Admin <?= htmlspecialchars($_SESSION['username']) ?> 👋</h2>

        <!-- Dashboard Stats Section -->
        <div class="dashboard-stats">
            <div class="card">
                <div class="card-icon">👨‍🍳</div>
                <h3>Total Staff</h3>
                <p><?= $staffCount ?></p>
            </div>
            <div class="card">
                <div class="card-icon">🍽️</div>
                <h3>Total Menu Items</h3>
                <p><?= $menuCount ?></p>
            </div>
            <div class="card">
                <div class="card-icon">🧾</div>
                <h3>Total Orders</h3>
                <p><?= $orderCount ?></p>
            </div>
            <div class="card">
                <div class="card-icon">👥</div>
                <h3>Registered Users</h3>
                <p><?= $userCount ?></p>
            </div>
        </div>

        <!-- Quick Links Section -->
        <ul class="links">
            <li><a href="manage_staff.php">👨‍🍳 Manage Staff</a></li>
            <li><a href="manage_menu.php">📋 Manage Menu</a></li>
            <li><a href="view_orders.php">🧾 View All Orders</a></li>
            <li><a href="manage_users.php">👥 Manage Users</a></li>
            <li><a class="logout" href="logout.php">🚪 Logout</a></li>
        </ul>
    </div>

</body>
</html>
