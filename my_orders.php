<?php
session_start();
include 'auth_check.php';
include 'db_connect.php';

$userId = $_SESSION['user_id'];

try {
    $stmt = $conn->prepare("SELECT * FROM orders WHERE user_id = :uid ORDER BY order_date DESC");
    $stmt->execute([':uid' => $userId]);
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>My Orders</title>
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

        .order {
            border-bottom: 1px solid #ddd;
            padding: 15px 0;
        }

        .order:last-child {
            border-bottom: none;
        }

        .items {
            list-style-type: disc;
            padding-left: 20px;
        }
    </style>
</head>
<body>

<div class="navbar">
    <div>Welcome, <?= htmlspecialchars($_SESSION['username']) ?></div>
    <div>
        <ul>
            <li><a href="user_dashboard.php">Home</a></li>
            <li><a href="menu.php">View Menu</a></li>
            <li><a href="order_menu.php">Place an Order</a></li>
            <li><a href="my_orders.php">My Orders</a></li>
            <li><a href="order_history.php">Order History</a></li>
              <li><a href="book_table.php">Booking</a></li>
               <li><a href="my_bookings.php">My bookings</a></li>
            <li><a href="profile.php">Manage Profile</a></li>
            <li><a class="logout" href="logout.php">Logout</a></li>
        </ul>
    </div>
</div>

<div class="container">
    <h2>Your Orders</h2>
    <?php if (count($orders) === 0): ?>
        <p>No orders found.</p>
    <?php else: ?>
        <?php foreach ($orders as $order): ?>
            <div class="order">
                <div><strong>Date:</strong> <?= htmlspecialchars($order['order_date']) ?></div>
                <div><strong>Status:</strong> <span class="order-status"><?= htmlspecialchars($order['status']) ?></span></div>
                <div><strong>Total:</strong> Rs <?= number_format($order['total'], 2) ?></div>
                <div><strong>Payment:</strong> <?= htmlspecialchars($order['payment_method'] ?? 'N/A') ?></div>
                <div><strong>Items:</strong></div>
                <ul class="items">
                    <?php
                    $items = json_decode($order['order_details'], true);
                    if (is_array($items)) {
                        foreach ($items as $item) {
                            echo "<li>" . htmlspecialchars($item['name']) . " × " . intval($item['quantity']) . " — Rs " . number_format($item['subtotal'], 2) . "</li>";
                        }
                    } else {
                        echo "<li>No items found</li>";
                    }
                    ?>
                </ul>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

</body>
</html>
