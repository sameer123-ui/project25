<?php
session_start();
include 'auth_check.php';
include 'db_connect.php';

if ($_SESSION['role'] !== 'staff') {
    header("Location: login.php");
    exit();
}

$staffId = $_SESSION['user_id'];

// Set default filter/sort values
$startDate = $_GET['start_date'] ?? '';
$endDate = $_GET['end_date'] ?? '';
$sort = $_GET['sort'] ?? 'order_date';  // default sort by order_date
$order = $_GET['order'] ?? 'DESC';      // default DESC

// Validate $sort and $order values to prevent SQL injection
$validSortColumns = ['order_date', 'total'];
$validOrderDirections = ['ASC', 'DESC'];

if (!in_array($sort, $validSortColumns)) {
    $sort = 'order_date';
}
if (!in_array($order, $validOrderDirections)) {
    $order = 'DESC';
}

// Build the query dynamically with filters and sorting
$query = "
    SELECT 
        orders.id, 
        orders.order_date, 
        orders.total, 
        orders.status, 
        orders.payment_method,
        orders.order_details,
        orders.order_type,
        orders.delivery_address,
        u.username AS customer_name
    FROM orders
    JOIN users u ON orders.user_id = u.id
    WHERE orders.assigned_staff_id = :staff_id
";

$params = ['staff_id' => $staffId];

if ($startDate) {
    $query .= " AND orders.order_date >= :start_date ";
    $params['start_date'] = $startDate . ' 00:00:00';
}
if ($endDate) {
    $query .= " AND orders.order_date <= :end_date ";
    $params['end_date'] = $endDate . ' 23:59:59';
}

$query .= " ORDER BY orders.$sort $order ";

try {
    $stmt = $conn->prepare($query);
    $stmt->execute($params);
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
        /* Your existing CSS */
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
        select, input[type="date"], button {
            padding: 6px 10px;
            border-radius: 4px;
            border: 1px solid #ccc;
            cursor: pointer;
            margin-right: 10px;
        }
        form.filter-form {
            margin-bottom: 20px;
            text-align: center;
        }
        .order-items {
            text-align: left;
            font-size: 14px;
            max-width: 250px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .delivery-address {
            font-size: 13px;
            color: #555;
            margin-top: 5px;
            text-align: left;
            max-width: 250px;
            white-space: normal;
            word-wrap: break-word;
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
        <li><a href="view_feedback2.php">See Feedback</a></li>
        <li><a class="logout" href="logout.php">Logout</a></li>
    </ul>
</div>

<div class="container">
    <h2>My Assigned Orders</h2>

    <form method="GET" class="filter-form">
        <label>
            Start Date: 
            <input type="date" name="start_date" value="<?= htmlspecialchars($startDate) ?>" />
        </label>
        <label>
            End Date: 
            <input type="date" name="end_date" value="<?= htmlspecialchars($endDate) ?>" />
        </label>
        <label>
            Sort By: 
            <select name="sort">
                <option value="order_date" <?= $sort === 'order_date' ? 'selected' : '' ?>>Order Date</option>
                <option value="total" <?= $sort === 'total' ? 'selected' : '' ?>>Total Price</option>
            </select>
        </label>
        <label>
            Order: 
            <select name="order">
                <option value="ASC" <?= $order === 'ASC' ? 'selected' : '' ?>>Ascending</option>
                <option value="DESC" <?= $order === 'DESC' ? 'selected' : '' ?>>Descending</option>
            </select>
        </label>
        <button type="submit">Filter</button>
        <a href="assigned_orders.php" style="padding:6px 10px; background:#e74c3c; color:white; border-radius:4px; text-decoration:none;">Clear</a>
    </form>

    <?php if (isset($_GET['msg'])): ?>
        <p class="message"><?= htmlspecialchars($_GET['msg']) ?></p>
    <?php endif; ?>

    <?php if (empty($orders)): ?>
        <p style="text-align:center;">No orders assigned to you matching the criteria.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Customer</th>
                    <th>Date</th>
                    <th>Order Details</th>
                    <th>Order Type</th>
                    <th>Delivery Address</th>
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
                        <td><?= ucfirst(htmlspecialchars($order['order_type'] ?? 'N/A')) ?></td>
                        <td class="delivery-address">
                            <?php 
                                if (($order['order_type'] ?? '') === 'delivery') {
                                    echo nl2br(htmlspecialchars($order['delivery_address'] ?? ''));
                                } else {
                                    echo "-";
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
