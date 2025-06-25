<?php
session_start();
include 'auth_check.php';
include 'db_connect.php';

// Only admin or staff allowed
if (!in_array($_SESSION['role'], ['admin', 'staff'])) {
    header("Location: login.php");
    exit();
}

// Handle status update (cancel or confirm)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $booking_id = $_POST['booking_id'] ?? 0;
    $new_status = $_POST['status'] ?? '';

    if ($booking_id > 0 && in_array($new_status, ['pending', 'confirmed', 'cancelled'])) {
        $stmt = $conn->prepare("UPDATE table_bookings SET status = ? WHERE id = ?");
        $stmt->execute([$new_status, $booking_id]);
    }
}

// Fetch bookings with user info
$stmt = $conn->prepare("
    SELECT tb.*, u.username 
    FROM table_bookings tb 
    JOIN users u ON tb.user_id = u.id 
    ORDER BY tb.booking_date DESC
");
$stmt->execute();
$bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<title>Manage Table Bookings</title>
<style>
    body {
        font-family: Arial, sans-serif;
        background: #f0f2f5;
        margin: 0;
        padding: 0px;
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
    table {
        border-collapse: collapse;
        width: 100%;
        background: white;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
    th, td {
        padding: 12px 15px;
        border: 1px solid #ddd;
        text-align: center;
    }
    th {
        background: #2980b9;
        color: white;
    }
    form {
        margin: 0;
    }
    select, button {
        padding: 5px 10px;
        font-size: 14px;
        margin: 0;
    }
    button {
        background-color: #1abc9c;
        border: none;
        color: white;
        cursor: pointer;
        border-radius: 4px;
    }
    button.cancel {
        background-color: #e74c3c;
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
          <li><a href="admin_bookings.php">Bookings</a></li>
        <li><a href="manage_users.php">Users</a></li>
        <li><a href="view_feedback1.php">See feedback</a></li>
        <li><a class="logout" href="logout.php">Logout</a></li>
    </ul>
</div>

<h1>Table Bookings Management</h1>
<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>User</th>
            <th>Table #</th>
            <th>Booking Date & Time</th>
            <th>People Count</th>
            <th>Status</th>
            <th>Change Status</th>
        </tr>
    </thead>
    <tbody>
        <?php if ($bookings): ?>
            <?php foreach ($bookings as $b): ?>
                <tr>
                    <td><?= htmlspecialchars($b['id']) ?></td>
                    <td><?= htmlspecialchars($b['username']) ?></td>
                    <td><?= htmlspecialchars($b['table_number']) ?></td>
                    <td><?= htmlspecialchars($b['booking_date']) ?></td>
                    <td><?= htmlspecialchars($b['people_count']) ?></td>
                    <td><?= htmlspecialchars(ucfirst($b['status'])) ?></td>
                    <td>
                        <form method="post" action="">
                            <input type="hidden" name="booking_id" value="<?= htmlspecialchars($b['id']) ?>" />
                            <select name="status">
                                <option value="pending" <?= $b['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                                <option value="confirmed" <?= $b['status'] === 'confirmed' ? 'selected' : '' ?>>Confirmed</option>
                                <option value="cancelled" <?= $b['status'] === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                            </select>
                            <button type="submit">Update</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr><td colspan="7">No bookings found.</td></tr>
        <?php endif; ?>
    </tbody>
</table>

<footer style="background-color: #2c3e50; color: white; padding: 20px 0; text-align: center; margin-top: 100px;">
    <div style="max-width: 1100px; margin: auto;">
        <p style="margin-bottom: 10px; font-size: 16px;">Quick Links</p>
        <div style="display: flex; justify-content: center; flex-wrap: wrap; gap: 20px;">
            <a href="manage_staff.php" style="color: #ecf0f1; text-decoration: none;">👨‍🍳 Staff</a>
            <a href="manage_menu.php" style="color: #1abc9c; text-decoration: none;">📋 Menu</a>
            <a href="view_orders.php" style="color: #ecf0f1; text-decoration: none;">🧾 Orders</a>
             <a href="admin_bookings.php" style="color: #ecf0f1; text-decoration: none;">🧾 Bookings</a>
            <a href="manage_users.php" style="color: #ecf0f1; text-decoration: none;">👥 Users</a>
            <a href="logout.php" style="color: #e74c3c; text-decoration: none;">🚪 Logout</a>
        </div>
        <p style="margin-top: 15px; font-size: 14px; color: #bdc3c7;">&copy; <?= date("Y") ?> Restaurant Admin Panel</p>
    </div>
</footer>


</body>
</html>
