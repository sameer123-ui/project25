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

// Get filters and sorting from GET params
$startDate = $_GET['start_date'] ?? null;
$endDate = $_GET['end_date'] ?? null;
$sortBy = $_GET['sort_by'] ?? 'order_date';
$sortOrder = $_GET['sort_order'] ?? 'DESC';

// Validate sortBy and sortOrder values to avoid SQL injection
$allowedSortBy = ['order_date', 'total'];
$allowedSortOrder = ['ASC', 'DESC'];

if (!in_array($sortBy, $allowedSortBy)) $sortBy = 'order_date';
if (!in_array($sortOrder, $allowedSortOrder)) $sortOrder = 'DESC';

// Build WHERE conditions for date filtering
$whereClauses = [];
$params = [];

if ($startDate) {
    $whereClauses[] = "o.order_date >= :start_date";
    $params['start_date'] = $startDate;
}
if ($endDate) {
    $whereClauses[] = "o.order_date <= :end_date";
    $params['end_date'] = $endDate;
}

$whereSQL = '';
if (count($whereClauses) > 0) {
    $whereSQL = "WHERE " . implode(" AND ", $whereClauses);
}

// Prepare the SQL with dynamic WHERE and ORDER BY
$sql = "
    SELECT o.*, u.username AS customer_name, s.name AS staff_name
    FROM orders o
    JOIN users u ON o.user_id = u.id
    LEFT JOIN staff s ON o.assigned_staff_id = s.id
    $whereSQL
    ORDER BY o.$sortBy $sortOrder
";

$stmt = $conn->prepare($sql);
$stmt->execute($params);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
        select, button, input[type="date"] {
            padding: 6px 10px;
            border-radius: 4px;
            border: 1px solid #ccc;
        }
        form.inline {
            display: inline-block;
            margin: 0;
        }
        form.filters {
            margin-bottom: 20px;
            display: flex;
            gap: 15px;
            align-items: center;
            flex-wrap: wrap;
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
    <h1>Admin Panel</h1>
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

<div class="container">
    <h2>Manage Orders</h2>

    <!-- Filter & Sort Form -->
    <form method="get" class="filters">
        <label>
            Start Date:
            <input type="date" name="start_date" value="<?= htmlspecialchars($startDate) ?>">
        </label>
        <label>
            End Date:
            <input type="date" name="end_date" value="<?= htmlspecialchars($endDate) ?>">
        </label>
        <label>
            Sort By:
            <select name="sort_by">
                <option value="order_date" <?= $sortBy === 'order_date' ? 'selected' : '' ?>>Order Date</option>
                <option value="total" <?= $sortBy === 'total' ? 'selected' : '' ?>>Total Price</option>
            </select>
        </label>
        <label>
            Order:
            <select name="sort_order">
                <option value="DESC" <?= $sortOrder === 'DESC' ? 'selected' : '' ?>>Descending</option>
                <option value="ASC" <?= $sortOrder === 'ASC' ? 'selected' : '' ?>>Ascending</option>
            </select>
        </label>
        <button type="submit">Apply</button>
    </form>

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
                <td><?= htmlspecialchars($order['payment_method'] ?? 'Not set') ?></td>
                <td><?= htmlspecialchars($order['status']) ?></td>
                <td><?= htmlspecialchars($order['staff_name'] ?? 'Unassigned') ?></td>
                <td>
                    <?php if (!$order['assigned_staff_id']): ?>
                        <form method="post" class="inline">
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
                    <form method="post" class="inline">
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
