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
        SELECT orders.id, orders.order_date, orders.total, orders.status, u.username AS customer_name
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
    <meta charset="UTF-8">
    <title>Assigned Orders - Staff</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f4; padding: 20px; }
        .container { max-width: 900px; margin: auto; background: white; padding: 25px; border-radius: 10px; box-shadow: 0 0 15px rgba(0,0,0,0.1); }
        h2 { text-align: center; color: #2c3e50; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ccc; padding: 10px; text-align: left; }
        th { background-color: #2c3e50; color: white; }
        tr:nth-child(even) { background-color: #f9f9f9; }
        .no-orders { text-align: center; margin-top: 30px; color: #e74c3c; font-weight: bold; }
    </style>
</head>
<body>

<div class="container">
    <h2>Your Assigned Orders</h2>

    <?php if (empty($orders)): ?>
        <div class="no-orders">You don't have any assigned orders yet.</div>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Customer</th>
                    <th>Date</th>
                    <th>Total</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $order): ?>
                    <tr>
                        <td><?= htmlspecialchars($order['id']) ?></td>
                        <td><?= htmlspecialchars($order['customer_name']) ?></td>
                        <td><?= htmlspecialchars($order['order_date']) ?></td>
                        <td>₹<?= number_format($order['total'], 2) ?></td>
                        <td><?= htmlspecialchars($order['status']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

</body>
</html>
