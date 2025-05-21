<?php
session_start();
include 'auth_check.php';
include 'db_connect.php';

if ($_SESSION['role'] !== 'staff') {
    header("Location: login.php");
    exit();
}

$staffId = $_SESSION['user_id'];

try {
    $stmt = $conn->prepare("
        SELECT 
            orders.id, 
            orders.order_date, 
            orders.total, 
            orders.status, 
            orders.payment_method,
            orders.order_details,
            u.username AS customer_name,
            orders.assigned_staff_id
        FROM orders
        JOIN users u ON orders.user_id = u.id
        WHERE orders.assigned_staff_id = :staff_id OR orders.assigned_staff_id IS NULL
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
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet" />
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f0f2f5;
            margin: 0;
            padding: 0;
            color: #2c3e50;
        }
        nav {
            background: linear-gradient(to right, #2c3e50, #34495e);
            padding: 20px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            color: white;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }
        nav .logo {
            font-size: 24px;
            font-weight: 700;
        }
        nav ul {
            list-style: none;
            display: flex;
            margin: 0;
            padding: 0;
        }
        nav ul li {
            margin-left: 25px;
        }
        nav ul li a {
            color: white;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s ease;
            padding: 6px 12px;
            border-radius: 5px;
        }
        nav ul li a:hover {
            color: #1abc9c;
            background-color: rgba(26, 188, 156, 0.1);
        }
        nav ul li a.logout {
            color: #e74c3c;
        }
        nav ul li a.logout:hover {
            color: #c0392b;
            background-color: rgba(231, 76, 60, 0.1);
        }

        main.container {
            max-width: 1100px;
            margin: 40px auto;
            background-color: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 6px 20px rgba(0,0,0,0.1);
        }
        h2 {
            margin-bottom: 20px;
            font-weight: 600;
        }
        table {
            border-collapse: collapse;
            width: 100%;
            font-size: 15px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 12px 15px;
            vertical-align: top;
        }
        th {
            background: black;
            color: white;
            text-align: left;
        }
        button {
            padding: 8px 14px;
            cursor: pointer;
            background-color: #27ae60;
            border: none;
            color: white;
            border-radius: 6px;
            font-weight: 600;
            transition: background-color 0.3s ease;
        }
        button:hover {
            background-color: #1e8449;
        }
        select {
            padding: 6px 8px;
            border-radius: 6px;
            border: 1px solid #ccc;
            font-weight: 600;
            cursor: pointer;
            background-color: white;
            transition: border-color 0.3s ease;
        }
        select:hover {
            border-color: #2980b9;
        }
        small {
            display: block;
            margin-top: 5px;
            color: #27ae60;
            font-weight: 600;
        }
        form {
            margin: 0;
        }
        /* Responsive for small screens */
        @media (max-width: 768px) {
            nav ul {
                flex-wrap: wrap;
            }
            nav ul li {
                margin-left: 10px;
            }
            main.container {
                margin: 20px 15px;
                padding: 20px;
            }
            table {
                font-size: 13px;
            }
            th, td {
                padding: 8px 10px;
            }
        }
    </style>
</head>
<body>

<nav>
    <div class="logo">Staff Panel</div>
    <ul>
        <li><a href="staff_dashboard.php">Home</a></li>
        <li><a href="staff_orders.php">My Orders</a></li>
        <li><a href="assigned_orders.php">Assigned Orders</a></li>
        <li><a href="table_bookings.php">Table Bookings</a></li>
        <li><a class="logout" href="logout.php">Logout</a></li>
    </ul>
</nav>

<main class="container">
    <h2>My Assigned Orders</h2>

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
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($orders)): ?>
                <tr><td colspan="8" style="text-align:center;">No orders found.</td></tr>
            <?php else: ?>
                <?php foreach ($orders as $order): ?>
                    <tr>
                        <td><?= htmlspecialchars($order['id']) ?></td>
                        <td><?= htmlspecialchars($order['customer_name']) ?></td>
                        <td><?= htmlspecialchars($order['order_date']) ?></td>
                        <td>
                            <?php
                            $details = json_decode($order['order_details'], true);
                            if ($details) {
                                foreach ($details as $item) {
                                    echo htmlspecialchars("{$item['quantity']} x {$item['name']} (Rs " . number_format($item['subtotal'], 2) . ")") . "<br>";
                                }
                            } else {
                                echo "No details";
                            }
                            ?>
                        </td>
                        <td>Rs <?= number_format($order['total'], 2) ?></td>
                        <td><?= htmlspecialchars($order['payment_method'] ?? 'N/A') ?></td>
                        <td><?= ucfirst(htmlspecialchars($order['status'])) ?></td>
                        <td>
                            <?php if ($order['assigned_staff_id'] === null): ?>
                                <form method="POST" action="claim_order.php">
                                    <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                                    <button type="submit">Claim</button>
                                </form>
                            <?php else: ?>
                                <form method="POST" action="update_order_status.php">
                                    <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                                    <select name="status" onchange="this.form.submit()">
                                        <option value="pending" <?= $order['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                                        <option value="preparing" <?= $order['status'] === 'preparing' ? 'selected' : '' ?>>Preparing</option>
                                        <option value="completed" <?= $order['status'] === 'completed' ? 'selected' : '' ?>>Completed</option>
                                        <option value="cancelled" <?= $order['status'] === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                                    </select>
                                </form>
                                <small>✔ Yours</small>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</main>

</body>
</html>
