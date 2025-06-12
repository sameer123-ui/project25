<?php
session_start();
include 'auth_check.php';
include 'db_connect.php'; // Your PDO connection as $conn

if ($_SESSION['role'] !== 'staff') {
    header("Location: login.php");
    exit();
}

$staffId = $_SESSION['user_id']; // Assuming you store staff user ID in session

try {
    // Fetch count of assigned orders for this staff
    $stmtOrders = $conn->prepare("SELECT COUNT(*) FROM orders WHERE assigned_staff_id = :staffId");
    $stmtOrders->execute(['staffId' => $staffId]);
    $assignedOrdersCount = $stmtOrders->fetchColumn();

    // Fetch count of active table bookings — consider both 'pending' and 'confirmed' as active statuses
    $stmtBookings = $conn->prepare("SELECT COUNT(*) FROM table_bookings WHERE status IN ('pending', 'confirmed')");
    $stmtBookings->execute();
    $tableBookingsCount = $stmtBookings->fetchColumn();
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Staff Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet" />
    <style>
        /* Same CSS as before, for brevity only showing your existing style */
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
        }

        .dashboard {
            display: flex;
            gap: 20px;
            justify-content: center;
            flex-wrap: wrap;
            margin-bottom: 30px;
        }

        .card {
            flex: 1 1 250px;
            background: linear-gradient(to top right, #74ebd5, #ACB6E5);
            border-radius: 10px;
            color: #2c3e50;
            padding: 20px;
            text-align: center;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            transition: transform 0.2s ease;
            cursor: default;
        }

        .card:hover {
            transform: translateY(-5px);
        }

        .card-icon {
            font-size: 32px;
            margin-bottom: 10px;
        }

        .card h3 {
            font-size: 18px;
            margin: 10px 0 5px;
        }

        .card p {
            font-size: 26px;
            font-weight: bold;
        }

        .links {
            list-style: none;
            padding: 0;
            margin: 0;
            text-align: center;
        }

        .links li {
            margin: 10px 0;
        }

        .links a {
            font-size: 16px;
            color: #2980b9;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.2s ease;
        }

        .links a:hover {
            color: #1abc9c;
        }

        @media (max-width: 600px) {
            .dashboard {
                flex-direction: column;
                align-items: center;
            }
        }

        footer {
            background-color: #2c3e50;
            color: white;
            padding: 20px 0;
            text-align: center;
            margin-top: 100px;
        }
        footer .container {
            max-width: 1100px;
            margin: auto;
        }
        footer .quick-links {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 20px;
            margin-bottom: 10px;
        }
        footer .quick-links a {
            color: #ecf0f1;
            text-decoration: none;
            font-size: 16px;
        }
        footer .quick-links a.logout {
            color: #e74c3c;
        }
        footer .quick-links a:hover {
            color: #1abc9c;
        }
        footer p {
            font-size: 14px;
            color: #bdc3c7;
            margin-top: 0;
        }
    </style>
</head>
<body>

<div class="navbar">
    <h1>Staff Panel</h1>
    <ul>
          <li> <a href="staff_dashboard.php">Home</a></li>
          <li><a href="staff_orders.php">My Orders</a></li>
        <li><a href="assigned_orders.php">Assigned Orders</a></li>
        <li><a href="table_bookings.php">Table Bookings</a></li>
         <li><a href="view_feedback2.php">See feedback</a></li>
        <li><a class="logout" href="logout.php">Logout</a></li>
    </ul>
</div>

<div class="container">
    <h2>Welcome, Staff <?= htmlspecialchars($_SESSION['username']) ?> 👋</h2>

    <div class="dashboard">
      <div class="card">
        <a href="assigned_orders.php" class="card-link">
            <div class="card-icon">📝</div>
            <h3>Assigned Orders</h3>
            <p><?= $assignedOrdersCount ?></p>
        </a>
    </div>

    <div class="card">
        <a href="table_bookings.php" class="card-link">
            <div class="card-icon">📅</div>
            <h3>Table Bookings</h3>
            <p><?= $tableBookingsCount ?></p>
        </a>
    </div>

    </div>
</div>

  <footer style="background-color: #2c3e50; color: white; padding: 20px 0; text-align: center; margin-top: 400px;">
    <div style="max-width: 1100px; margin: auto;">
        <p style="margin-bottom: 10px; font-size: 16px;">Quick Links</p>
        <div style="display: flex; justify-content: center; flex-wrap: wrap; gap: 20px;">
           
            <a href="staff_orders.php" style="color: #ecf0f1; text-decoration: none;">🧾 Orders</a>

            <a href="logout.php" style="color: #e74c3c; text-decoration: none;">🚪 Logout</a>
        </div>
        <p style="margin-top: 15px; font-size: 14px; color: #bdc3c7;">&copy; <?= date("Y") ?> Restaurant Staff Panel</p>
    </div>
</footer>


</body>
</html>
