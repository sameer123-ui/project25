<?php
session_start();
include 'auth_check.php';
include 'db_connect.php';

if ($_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

try {
    $staffCount = $conn->query("SELECT COUNT(*) FROM staff")->fetchColumn();
    $menuCount = $conn->query("SELECT COUNT(*) FROM menu")->fetchColumn();
    $orderCount = $conn->query("SELECT COUNT(*) FROM orders")->fetchColumn();
    $userCount = $conn->query("SELECT COUNT(*) FROM users")->fetchColumn();
    $bookingCount = $conn->query("SELECT COUNT(*) FROM table_bookings")->fetchColumn();

    // Fetch recent bookings for details section
    $stmt = $conn->prepare("SELECT id, table_number, booking_date, status FROM table_bookings ORDER BY booking_date DESC LIMIT 10");
    $stmt->execute();
    $recentBookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<title>Admin Dashboard</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet" />
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
        max-width: 1100px;
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
    .dashboard-stats {
        display: flex;
        flex-wrap: wrap;
        gap: 20px;
        margin-bottom: 30px;
    }
    .card {
        flex: 1;
        min-width: 220px;
        padding: 20px;
        background: linear-gradient(to top right, #74ebd5, #ACB6E5);
        border-radius: 10px;
        color: #2c3e50;
        text-align: center;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        transition: transform 0.2s ease, background-color 0.3s ease;
        cursor: pointer;
        user-select: none;
        position: relative;
    }
    .card:hover {
        transform: translateY(-5px);
        background: linear-gradient(to top right, #5ad1c1, #8a9ee3);
    }
    .card h3 {
        font-size: 18px;
        margin: 10px 0 5px;
    }
    .card p {
        font-size: 26px;
        font-weight: bold;
    }
    .card-icon {
        font-size: 32px;
        margin-bottom: 10px;
    }

    /* Booking details (hidden by default) */
    #bookingDetails {
        margin-top: 20px;
        display: none;
        overflow-x: auto;
        border-radius: 10px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        background-color: white;
    }
    #bookingDetails table {
        width: 100%;
        border-collapse: collapse;
        color: #2c3e50;
    }
    #bookingDetails th, #bookingDetails td {
        padding: 12px 15px;
        border: 1px solid rgba(44, 62, 80, 0.15);
        text-align: left;
        white-space: nowrap;
    }
    #bookingDetails th {
        background: #e0f7f7;
    }

    @media (max-width: 768px) {
        .dashboard-stats {
            flex-direction: column;
            align-items: center;
        }
        .card {
            min-width: auto;
            width: 100%;
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
    <h1>Admin Dashboard</h1>
    <ul>
        <li><a href="admin_dashboard.php">Home</a></li>
        <li><a href="manage_staff.php">Staff</a></li>
        <li><a href="manage_menu.php">Menu</a></li>
        <li><a href="view_orders.php">Orders</a></li>
        <li><a href="manage_users.php">Users</a></li>
        <li><a href="view_feedback1.php">See feedback</a></li>
        <li><a class="logout" href="logout.php">Logout</a></li>
    </ul>
</div>

<div class="container">
    <h2>Welcome, Admin <?= htmlspecialchars($_SESSION['username']) ?> 👋</h2>

    <div class="dashboard-stats">
         <a href="manage_staff.php" class="card" title="Total Staff">
        <div class="card-icon">👨‍🍳</div>
        <h3>Total Staff</h3>
        <p><?= $staffCount ?></p>
    </a>
    <a href="manage_menu.php" class="card" title="Total Menu Items">
        <div class="card-icon">🍽️</div>
        <h3>Total Menu Items</h3>
        <p><?= $menuCount ?></p>
    </a>
    <a href="view_orders.php" class="card" title="Total Orders">
        <div class="card-icon">🧾</div>
        <h3>Total Orders</h3>
        <p><?= $orderCount ?></p>
    </a>
    <a href="manage_users.php" class="card" title="Registered Users">
        <div class="card-icon">👥</div>
        <h3>Registered Users</h3>
        <p><?= $userCount ?></p>
    </a>

    <!-- Table Bookings Card remains clickable as before -->
    <div class="card" id="bookingCard" title="Click to view recent bookings" style="cursor: pointer;">
        <div class="card-icon">🪑</div>
        <h3>Table Bookings</h3>
        <p><?= $bookingCount ?></p>
        <small style="display:block; margin-top: 5px; font-weight: 400; color: #145d50;">Click to see details ↓</small>
    </div>
    </div>

    <div id="bookingDetails" class="card" style="background: white; color: #2c3e50; cursor: default;">
        <h3>Recent Table Bookings</h3>
        <table>
            <thead>
                <tr>
                    <th>Booking ID</th>
                    <th>Table Number</th>
                    <th>Booking Date</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($recentBookings)): ?>
                    <?php foreach ($recentBookings as $booking): ?>
                        <tr>
                            <td><?= htmlspecialchars($booking['id']) ?></td>
                            <td><?= htmlspecialchars($booking['table_number']) ?></td>
                            <td><?= htmlspecialchars($booking['booking_date']) ?></td>
                            <td><?= htmlspecialchars(ucfirst($booking['status'])) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="4" style="text-align:center;">No bookings found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<footer style="background-color: #2c3e50; color: white; padding: 20px 0; text-align: center; margin-top: 100px;">
    <div style="max-width: 1100px; margin: auto;">
        <p style="margin-bottom: 10px; font-size: 16px;">Quick Links</p>
        <div style="display: flex; justify-content: center; flex-wrap: wrap; gap: 20px;">
            <a href="manage_staff.php" style="color: #ecf0f1; text-decoration: none;">👨‍🍳 Staff</a>
            <a href="manage_menu.php" style="color: #1abc9c; text-decoration: none;">📋 Menu</a>
            <a href="view_orders.php" style="color: #ecf0f1; text-decoration: none;">🧾 Orders</a>
            <a href="manage_users.php" style="color: #ecf0f1; text-decoration: none;">👥 Users</a>
            <a href="logout.php" style="color: #e74c3c; text-decoration: none;">🚪 Logout</a>
        </div>
        <p style="margin-top: 15px; font-size: 14px; color: #bdc3c7;">&copy; <?= date("Y") ?> Restaurant Admin Panel</p>
    </div>
</footer>

<script>
    const bookingCard = document.getElementById('bookingCard');
    const bookingDetails = document.getElementById('bookingDetails');

    bookingCard.addEventListener('click', () => {
        if (bookingDetails.style.display === 'block') {
            bookingDetails.style.display = 'none';
            bookingCard.querySelector('small').textContent = 'Click to see details ↓';
        } else {
            bookingDetails.style.display = 'block';
            bookingCard.querySelector('small').textContent = 'Click to hide details ↑';
            bookingDetails.scrollIntoView({ behavior: 'smooth' });
        }
    });
</script>

</body>
</html>
