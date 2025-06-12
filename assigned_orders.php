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

// Handle assignment form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['assign_order_id'])) {
    $assignOrderId = (int)$_POST['assign_order_id'];

    // Only assign if not already assigned
    $checkStmt = $conn->prepare("SELECT assigned_staff_id FROM orders WHERE id = :order_id");
    $checkStmt->execute(['order_id' => $assignOrderId]);
    $currentAssignment = $checkStmt->fetchColumn();

    if (empty($currentAssignment)) {
        $updateStmt = $conn->prepare("UPDATE orders SET assigned_staff_id = :staff_id WHERE id = :order_id");
        $updateStmt->execute(['staff_id' => $staffId, 'order_id' => $assignOrderId]);
        header("Location: assigned_orders.php?msg=" . urlencode("Order #$assignOrderId assigned to you."));
        exit();
    } else {
        header("Location: assigned_orders.php?msg=" . urlencode("Order #$assignOrderId is already assigned."));
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Assign Orders</title>
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f0f2f5;
            margin: 0; padding: 0;
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
        .navbar h1 { margin: 0; font-size: 26px; }
        .navbar ul {
            list-style: none;
            display: flex;
            margin: 0; padding: 0;
        }
        .navbar li { margin-left: 25px; }
        .navbar a {
            color: white; text-decoration: none; font-weight: 600;
            transition: 0.3s;
        }
        .navbar a:hover,
        .navbar a.logout:hover { color: #1abc9c; }
        .navbar .logout { color: #e74c3c; }
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
        select, button {
            padding: 6px 10px;
            border-radius: 4px;
            border: 1px solid #ccc;
            cursor: pointer;
            font-weight: 600;
            background-color: #34495e;
            color: white;
            transition: 0.3s;
        }
        button:hover {
            background-color: #1abc9c;
        }
        form {
            margin: 0;
        }
        .order-items {
            text-align: left;
            font-size: 14px;
            max-width: 250px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .message {
            text-align: center;
            font-weight: bold;
            margin-bottom: 20px;
            color: green;
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
        <li><a href="staff_dashboard.php">Home</a></li>
        <li><a href="staff_orders.php">My Orders</a></li>
        <li><a href="assigned_orders.php">Assigned Orders</a></li>
        <li><a href="table_bookings.php">Table Bookings</a></li>
         <li><a href="view_feedback2.php">See feedback</a></li>
        <li><a class="logout" href="logout.php">Logout</a></li>
    </ul>
</div>

<div class="container">
    <h2>Assign Orders to Myself</h2>

    <?php if (isset($_GET['msg'])): ?>
        <p class="message"><?= htmlspecialchars($_GET['msg']) ?></p>
    <?php endif; ?>

    <?php if (empty($orders)): ?>
        <p style="text-align:center;">No available orders to assign.</p>
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
                    <th>Assigned Staff</th>
                    <th>Assign to Me</th>
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
                            <?php
                            if ($order['assigned_staff_id']) {
                                echo "Staff #" . htmlspecialchars($order['assigned_staff_id']);
                            } else {
                                echo "<em>Unassigned</em>";
                            }
                            ?>
                        </td>
                        <td>
                            <?php if (empty($order['assigned_staff_id'])): ?>
                                <form method="POST" style="margin:0;">
                                    <input type="hidden" name="assign_order_id" value="<?= $order['id'] ?>">
                                    <button type="submit">Assign to Me</button>
                                </form>
                            <?php else: ?>
                                <span style="color: #888;">Already assigned</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
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
