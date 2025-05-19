<?php
session_start();
include 'auth_check.php';
include 'db_connect.php';

if ($_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Handle staff assignment
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['assign_staff'])) {
    $orderId = $_POST['order_id'];
    $staffId = $_POST['staff_id'];
    $stmt = $conn->prepare("UPDATE orders SET assigned_staff_id = :staff WHERE id = :id");
    $stmt->execute(['staff' => $staffId, 'id' => $orderId]);
    header("Location: view_orders.php");
    exit();
}

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $orderId = $_POST['order_id'];
    $status = $_POST['status'];
    $stmt = $conn->prepare("UPDATE orders SET status = :status WHERE id = :id");
    $stmt->execute(['status' => $status, 'id' => $orderId]);
    header("Location: view_orders.php");
    exit();
}

// Fetch orders
$orders = $conn->query("
    SELECT o.*, u.username AS customer_name, s.name AS staff_name
    FROM orders o
    JOIN users u ON o.user_id = u.id
    LEFT JOIN staff s ON o.assigned_staff_id = s.id
    ORDER BY o.order_date DESC
")->fetchAll(PDO::FETCH_ASSOC);

// Fetch all staff
$staffList = $conn->query("SELECT id, name FROM staff")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Orders</title>
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
            text-align: center;
            color: #2c3e50;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            padding: 12px;
            border: 1px solid #ddd;
            text-align: center;
        }
        th {
            background: #2c3e50;
            color: white;
        }
        select, button {
            padding: 6px 10px;
            border-radius: 4px;
            border: 1px solid #ccc;
        }
        form {
            display: inline-block;
            margin: 0;
        }
    </style>
</head>
<body>

<div class="navbar">
    <h1>Admin Panel</h1>
    <ul>
        <li><a href="admin_dashboard.php">Home</a></li>
        <li><a href="manage_staff.php">Staff</a></li>
        <li><a href="manage_menu.php">Menu</a></li>
        <li><a href="view_orders.php">Orders</a></li>
        <li><a href="manage_users.php">Users</a></li>
        <li><a class="logout" href="logout.php">Logout</a></li>
    </ul>
</div>

<div class="container">
    <h2>Manage Orders</h2>
    <table>
        <thead>
        <tr>
            <th>ID</th>
            <th>Customer</th>
            <th>Total</th>
            <th>Payment</th>
            <th>Status</th>
            <th>Staff</th>
            <th>Assign Staff</th>
            <th>Update Status</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($orders as $order): ?>
            <tr>
                <td><?= $order['id'] ?></td>
                <td><?= htmlspecialchars($order['customer_name']) ?></td>
                <td>₹<?= number_format($order['total'], 2) ?></td>
                <td><?= $order['payment_method'] ?? 'Not set' ?></td>
                <td><?= htmlspecialchars($order['status']) ?></td>
                <td><?= $order['staff_name'] ?? 'Unassigned' ?></td>
                <td>
                    <?php if (!$order['assigned_staff_id']): ?>
                        <form method="post">
                            <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                            <select name="staff_id" required>
                                <option value="">Select</option>
                                <?php foreach ($staffList as $staff): ?>
                                    <option value="<?= $staff['id'] ?>"><?= htmlspecialchars($staff['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <button type="submit" name="assign_staff">Assign</button>
                        </form>
                    <?php else: ?>
                        ✔
                    <?php endif; ?>
                </td>
                <td>
                    <form method="post">
                        <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                        <select name="status">
                            <option <?= $order['status'] === 'Pending' ? 'selected' : '' ?>>Pending</option>
                            <option <?= $order['status'] === 'In Progress' ? 'selected' : '' ?>>In Progress</option>
                            <option <?= $order['status'] === 'Completed' ? 'selected' : '' ?>>Completed</option>
                        </select>
                        <button type="submit" name="update_status">Update</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

</body>
</html>
