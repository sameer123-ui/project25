<?php
session_start();
include 'auth_check.php';
include 'db_connect.php';

if ($_SESSION['role'] !== 'staff') {
    header("Location: login.php");
    exit();
}

$staffId = $_SESSION['user_id'];

// Fetch orders assigned to this staff
try {
    $stmt = $conn->prepare("
        SELECT 
            orders.id, 
            orders.order_date, 
            orders.total, 
            orders.status, 
            orders.payment_method,
            orders.order_details,
            u.username AS customer_name
        FROM orders
        JOIN users u ON orders.user_id = u.id
        WHERE orders.assigned_staff_id = :staff_id
        ORDER BY orders.order_date DESC
    ");
    $stmt->execute(['staff_id' => $staffId]);
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>My Assigned Orders</title>
    <style>
        /* Keep your existing styles here */
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
            vertical-align: middle;
        }
        th {
            background: #2c3e50;
            color: white;
        }
        select {
            padding: 6px 10px;
            border-radius: 4px;
            border: 1px solid #ccc;
            cursor: pointer;
        }
        form {
            margin: 0;
        }
        .message {
            text-align: center;
            font-weight: bold;
            margin-bottom: 20px;
            color: green;
        }
        .order-items {
            text-align: left;
            font-size: 14px;
            max-width: 250px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
    </style>
</head>
<body>

<div class="navbar">
    <h1>Staff Panel</h1>
    <ul>
        <li><a href="staff_dashboard.php">Home</a></li>
        <li><a href="staff_orders.php">My Orders</a></li>
        <li><a href="assigned_orders.php">Assigned Orders</a></li>
        <li><a href="table_bookings.php">Table Bookings</a></li>
        <li><a class="logout" href="logout.php">Logout</a></li>
    </ul>
</div>

<div class="container">
    <h2>My Assigned Orders</h2>

    <?php if (isset($_GET['msg'])): ?>
        <p class="message"><?= htmlspecialchars($_GET['msg']) ?></p>
    <?php endif; ?>

    <?php if (empty($orders)): ?>
        <p style="text-align:center;">No orders assigned to you yet.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Customer</th>
                    <th>Date</th>
                    <th>Order Details</th>
                    <th>Total (Rs)</th>
                    <th>Payment</th>
                    <th>Status</th>
                    <th>Update Status</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($orders as $order): ?>
                <tr>
                    <td><?= htmlspecialchars($order['id']) ?></td>
                    <td><?= htmlspecialchars($order['customer_name']) ?></td>
                    <td><?= htmlspecialchars($order['order_date']) ?></td>
                    <td class="order-items">
                        <?php
                        $items = json_decode($order['order_details'], true);
                        if (is_array($items)) {
                            foreach ($items as $item) {
                                echo htmlspecialchars("{$item['quantity']} x {$item['name']} (Rs " . number_format($item['subtotal'], 2) . ")") . "<br>";
                            }
                        } else {
                            echo "N/A";
                        }
                        ?>
                    </td>
                    <td><?= number_format($order['total'], 2) ?></td>
                    <td><?= htmlspecialchars($order['payment_method'] ?? 'N/A') ?></td>
                    <td><?= ucfirst(htmlspecialchars($order['status'])) ?></td>
                    <td>
                        <form method="POST" action="update_order_status.php" style="margin:0;">
                            <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                            <select name="status" onchange="this.form.submit()">
                                <?php
                                $statuses = ['pending', 'preparing', 'completed', 'cancelled'];
                                foreach ($statuses as $statusOption) {
                                    $selected = ($order['status'] === $statusOption) ? 'selected' : '';
                                    echo "<option value='$statusOption' $selected>" . ucfirst($statusOption) . "</option>";
                                }
                                ?>
                            </select>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

</body>
</html>
