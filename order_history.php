<?php
session_start();
include 'auth_check.php';   // Make sure this validates user is logged in
include 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$userId = $_SESSION['user_id'];

// Fetch all orders for this user
try {
    $stmt = $conn->prepare("
        SELECT 
            orders.id,
            orders.order_date,
            orders.total,
            orders.status,
            orders.payment_method,
            orders.order_details
        FROM orders
        WHERE orders.user_id = :user_id
        ORDER BY orders.order_date DESC
    ");
    $stmt->execute(['user_id' => $userId]);
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>My Order History</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter&display=swap" rel="stylesheet" />
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

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th, td {
            border: 1px solid #ddd;
            padding: 12px 15px;
            text-align: left;
            vertical-align: top;
            color: #2c3e50;
        }

        th {
            background: linear-gradient(to right, #74ebd5, #ACB6E5);
            color: #2c3e50;
            font-weight: 700;
        }

        .order-details {
            font-size: 14px;
            color: #555;
        }

        .status {
            font-weight: 600;
            padding: 6px 12px;
            border-radius: 12px;
            color: white;
            display: inline-block;
            font-size: 14px;
        }

        .status.pending { background-color: #f39c12; }
        .status.preparing { background-color: #3498db; }
        .status.completed { background-color: #27ae60; }
        .status.cancelled { background-color: #e74c3c; }

        .no-orders {
            text-align: center;
            padding: 40px 0;
            color: #777;
            font-size: 18px;
        }

        @media (max-width: 600px) {
            .navbar {
                flex-direction: column;
                align-items: flex-start;
            }

            .navbar ul {
                flex-direction: column;
                width: 100%;
            }

            .navbar li {
                margin-left: 0;
                margin-top: 10px;
            }
        }
    </style>
</head>
<body>

<nav class="navbar">
    <h1> User Panel</h1>
     <ul>
        <li> <a href="user_dashboard.php">Home</a></li>
        <li><a href="menu.php">View Menu</a></li>
    <li><a href="order_menu.php">Place an Order</a></li>
    <li><a href="my_orders.php">My Orders</a></li>
        <li><a href="order_history.php">Order History</a></li>
        <li><a href="profile.php">Manage Profile</a></li>
        <li><a class="logout" href="logout.php">Logout</a></li>
    </ul>
</nav>

<div class="container">
    <h2>My Order History</h2>

    <?php if (empty($orders)): ?>
        <div class="no-orders">You have not placed any orders yet.</div>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Date</th>
                    <th>Order Details</th>
                    <th>Total (Rs)</th>
                    <th>Payment Method</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $order): ?>
                    <tr>
                        <td><?= htmlspecialchars($order['id']) ?></td>
                        <td><?= htmlspecialchars(date("d M Y, H:i", strtotime($order['order_date']))) ?></td>
                        <td class="order-details">
                            <?php 
                                $details = json_decode($order['order_details'], true);
                                if ($details) {
                                    foreach ($details as $item) {
                                        echo htmlspecialchars("{$item['quantity']} x {$item['name']} (Rs " . number_format($item['subtotal'], 2) . ")") . "<br>";
                                    }
                                } else {
                                    echo "No details available";
                                }
                            ?>
                        </td>
                        <td>Rs <?= number_format($order['total'], 2) ?></td>
                        <td><?= htmlspecialchars($order['payment_method'] ?? 'N/A') ?></td>
                        <td>
                            <?php 
                                $status = strtolower($order['status']);
                                $status_class = "status " . $status;
                                echo "<span class=\"$status_class\">" . ucfirst($status) . "</span>";
                            ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

</body>
</html>
