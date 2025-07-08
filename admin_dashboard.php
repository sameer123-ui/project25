<?php
session_start();
include 'auth_check.php';
include 'db_connect.php';

if ($_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Handle filters
$status_filter = $_GET['status'] ?? '';
$start_date = $_GET['start_date'] ?? '';
$end_date = $_GET['end_date'] ?? '';
$sort_by = $_GET['sort_by'] ?? 'order_date';

// Validate sorting fields
$allowed_sorts = ['order_date', 'total'];
if (!in_array($sort_by, $allowed_sorts)) {
    $sort_by = 'order_date';
}

// Build where clauses for filters
$where = [];
$params = [];

if ($status_filter && in_array($status_filter, ['pending', 'completed', 'cancelled'])) {
    $where[] = "status = ?";
    $params[] = $status_filter;
}

if ($start_date) {
    $where[] = "order_date >= ?";
    $params[] = $start_date . " 00:00:00";
}

if ($end_date) {
    $where[] = "order_date <= ?";
    $params[] = $end_date . " 23:59:59";
}

$where_sql = "";
if (count($where) > 0) {
    $where_sql = "WHERE " . implode(' AND ', $where);
}

// For dashboard counts
try {
    $staffCount = $conn->query("SELECT COUNT(*) FROM staff")->fetchColumn();
    $menuCount = $conn->query("SELECT COUNT(*) FROM menu")->fetchColumn();
    $orderCount = $conn->query("SELECT COUNT(*) FROM orders")->fetchColumn();
    $userCount = $conn->query("SELECT COUNT(*) FROM users")->fetchColumn();
    $bookingCount = $conn->query("SELECT COUNT(*) FROM table_bookings")->fetchColumn();

    // Fetch filtered & sorted orders (limit 100 for performance)
    $orderStmt = $conn->prepare("SELECT * FROM orders $where_sql ORDER BY $sort_by DESC LIMIT 100");
    $orderStmt->execute($params);
    $orders = $orderStmt->fetchAll(PDO::FETCH_ASSOC);

    // For charts data:

    // 1) Orders per day (last 30 days)
    $ordersPerDayStmt = $conn->prepare("
        SELECT DATE(order_date) as day, COUNT(*) as count
        FROM orders
        WHERE order_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
        GROUP BY day
        ORDER BY day ASC
    ");
    $ordersPerDayStmt->execute();
    $ordersPerDay = $ordersPerDayStmt->fetchAll(PDO::FETCH_ASSOC);

    // 2) Revenue per week (last 12 weeks)
    $revenuePerWeekStmt = $conn->prepare("
        SELECT YEAR(order_date) as year, WEEK(order_date, 1) as week, SUM(total) as revenue
        FROM orders
        WHERE order_date >= DATE_SUB(CURDATE(), INTERVAL 12 WEEK)
        GROUP BY year, week
        ORDER BY year, week ASC
    ");
    $revenuePerWeekStmt->execute();
    $revenuePerWeek = $revenuePerWeekStmt->fetchAll(PDO::FETCH_ASSOC);

    // 3) Top 5 items by total ordered quantity (assuming order_details stores JSON with items and quantity)
    $topItems = [];
    $menuItemCounts = [];

    $allOrdersStmt = $conn->prepare("SELECT order_details FROM orders");
    $allOrdersStmt->execute();
    $allOrders = $allOrdersStmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($allOrders as $order) {
        $items = json_decode($order['order_details'], true);
        if (is_array($items)) {
            foreach ($items as $item) {
                $itemName = $item['name'] ?? null;
                $qty = intval($item['quantity'] ?? 0);
                if ($itemName && $qty > 0) {
                    if (!isset($menuItemCounts[$itemName])) {
                        $menuItemCounts[$itemName] = 0;
                    }
                    $menuItemCounts[$itemName] += $qty;
                }
            }
        }
    }

    arsort($menuItemCounts);
    $topItems = array_slice($menuItemCounts, 0, 5, true);

} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}

// CSV Export logic
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="orders_export.csv"');

    $output = fopen('php://output', 'w');
    // CSV headers
    fputcsv($output, ['Order ID', 'User ID', 'Order Date', 'Total', 'Status', 'Payment Method', 'Order Type']);

    foreach ($orders as $order) {
        fputcsv($output, [
            $order['id'],
            $order['user_id'],
            $order['order_date'],
            $order['total'],
            $order['status'],
            $order['payment_method'],
            $order['order_type']
        ]);
    }
    fclose($output);
    exit;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<title>Admin Dashboard</title>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
        list-style: none; display: flex; margin: 0; padding: 0;
    }
    .navbar li { margin-left: 25px; }
    .navbar a {
        color: white; text-decoration: none; font-weight: 600; transition: 0.3s;
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
        color: #2c3e50;
        margin-bottom: 20px;
    }
    .dashboard-stats {
        display: flex;
        flex-wrap: wrap;
        gap: 20px;
        margin-bottom: 30px;
    }
    .card {
        flex: 1;
        min-width: 220px;
        padding: 20px;
        background: linear-gradient(to top right, #74ebd5, #ACB6E5);
        border-radius: 10px;
        color: #2c3e50;
        text-align: center;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        transition: transform 0.2s ease, background-color 0.3s ease;
        cursor: pointer;
        user-select: none;
        position: relative;
    }
    .card:hover {
        transform: translateY(-5px);
        background: linear-gradient(to top right, #5ad1c1, #8a9ee3);
    }
    .card h3 {
        font-size: 18px;
        margin: 10px 0 5px;
    }
    .card p {
        font-size: 26px;
        font-weight: bold;
    }
    .card-icon {
        font-size: 32px;
        margin-bottom: 10px;
    }

    /* Filters Form */
    form.filters {
        display: flex;
        gap: 15px;
        margin-bottom: 25px;
        flex-wrap: wrap;
    }
    form.filters label {
        font-weight: 600;
        margin-right: 5px;
    }
    form.filters input,
    form.filters select {
        padding: 7px 10px;
        border-radius: 6px;
        border: 1px solid #ccc;
        font-size: 14px;
    }
    form.filters button {
        background-color: #1abc9c;
        border: none;
        padding: 8px 18px;
        color: white;
        font-weight: 700;
        border-radius: 8px;
        cursor: pointer;
        transition: background-color 0.3s ease;
    }
    form.filters button:hover {
        background-color: #16a085;
    }

    /* Orders table */
    table.orders {
        width: 100%;
        border-collapse: collapse;
        font-size: 15px;
        margin-bottom: 40px;
    }
    table.orders th, table.orders td {
        border: 1px solid #ddd;
        padding: 12px 15px;
        text-align: center;
    }
    table.orders th {
        background-color: #1abc9c;
        color: white;
    }
    table.orders tr:nth-child(even) {
        background-color: #f9f9f9;
    }

    /* Chart containers */
    .charts {
        display: flex;
        flex-wrap: wrap;
        gap: 40px;
        justify-content: space-between;
    }
    .chart-container {
        background: white;
        border-radius: 12px;
        box-shadow: 0 6px 20px rgba(0,0,0,0.1);
        padding: 20px;
        flex: 1 1 320px;
    }
    .chart-container h3 {
        text-align: center;
        color: #34495e;
        margin-bottom: 15px;
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

<div class="container">
    <h2>Welcome, Admin <?= htmlspecialchars($_SESSION['username']) ?> 👋</h2>

    <div class="dashboard-stats">
        <a href="manage_staff.php" class="card" title="Total Staff">
            <div class="card-icon">👨‍🍳</div>
            <h3>Total Staff</h3>
            <p><?= $staffCount ?></p>
        </a>
        <a href="manage_menu.php" class="card" title="Total Menu Items">
            <div class="card-icon">🍽️</div>
            <h3>Total Menu Items</h3>
            <p><?= $menuCount ?></p>
        </a>
        <a href="view_orders.php" class="card" title="Total Orders">
            <div class="card-icon">🧾</div>
            <h3>Total Orders</h3>
            <p><?= $orderCount ?></p>
        </a>
        <a href="manage_users.php" class="card" title="Registered Users">
            <div class="card-icon">👥</div>
            <h3>Registered Users</h3>
            <p><?= $userCount ?></p>
        </a>
        <a href="admin_bookings.php" class="card" title="Table Bookings">
            <div class="card-icon">🪑</div>
            <h3>Table Bookings</h3>
            <p><?= $bookingCount ?></p>
        </a>
    </div>

    <form method="GET" class="filters">
        <label for="start_date">Start Date:</label>
        <input type="date" id="start_date" name="start_date" value="<?= htmlspecialchars($start_date) ?>" />

        <label for="end_date">End Date:</label>
        <input type="date" id="end_date" name="end_date" value="<?= htmlspecialchars($end_date) ?>" />

        <label for="status">Order Status:</label>
        <select id="status" name="status">
            <option value="">All</option>
            <option value="pending" <?= $status_filter === 'pending' ? 'selected' : '' ?>>Pending</option>
            <option value="completed" <?= $status_filter === 'completed' ? 'selected' : '' ?>>Completed</option>
            <option value="cancelled" <?= $status_filter === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
        </select>

        <label for="sort_by">Sort By:</label>
        <select id="sort_by" name="sort_by">
            <option value="order_date" <?= $sort_by === 'order_date' ? 'selected' : '' ?>>Date</option>
            <option value="total" <?= $sort_by === 'total' ? 'selected' : '' ?>>Price</option>
        </select>

        <button type="submit">Filter</button>

        <button type="submit" name="export" value="csv" style="background-color:#2980b9; margin-left: 10px;">Export CSV</button>
    </form>

    <table class="orders">
        <thead>
            <tr>
                <th>Order ID</th>
                <th>User ID</th>
                <th>Order Date</th>
                <th>Total (₹)</th>
                <th>Status</th>
                <th>Payment Method</th>
                <th>Order Type</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($orders)): ?>
                <tr><td colspan="7" style="text-align:center;">No orders found.</td></tr>
            <?php else: ?>
                <?php foreach ($orders as $order): ?>
                  <tr>
    <td><?= htmlspecialchars($order['id']) ?></td>
    <td><?= htmlspecialchars($order['user_id']) ?></td>
    <td><?= htmlspecialchars($order['order_date']) ?></td>
    <td><?= number_format($order['total'], 2) ?></td>
    <td><?= htmlspecialchars(ucfirst($order['status'])) ?></td>
    <td><?= htmlspecialchars($order['payment_method'] ?? '') ?></td>
    <td><?= htmlspecialchars($order['order_type'] ?? '') ?></td>
</tr>

                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <div class="charts">

        <div class="chart-container">
            <h3>Orders Per Day (Last 30 Days)</h3>
            <canvas id="ordersPerDayChart"></canvas>
        </div>

        <div class="chart-container">
            <h3>Revenue Per Week (Last 12 Weeks)</h3>
            <canvas id="revenuePerWeekChart"></canvas>
        </div>

        <div class="chart-container">
            <h3>Top 5 Items Ordered</h3>
            <canvas id="topItemsChart"></canvas>
        </div>

    </div>

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

<script>
    // Orders Per Day Chart
    const ordersPerDayCtx = document.getElementById('ordersPerDayChart').getContext('2d');
    const ordersPerDayLabels = <?= json_encode(array_column($ordersPerDay, 'day')) ?>;
    const ordersPerDayData = <?= json_encode(array_column($ordersPerDay, 'count')) ?>;

    new Chart(ordersPerDayCtx, {
        type: 'bar',
        data: {
            labels: ordersPerDayLabels,
            datasets: [{
                label: 'Orders',
                data: ordersPerDayData,
                backgroundColor: '#1abc9c',
            }]
        },
        options: {
            scales: {
                x: { ticks: { maxRotation: 90, minRotation: 45 }},
                y: { beginAtZero: true, stepSize: 1 }
            }
        }
    });

    // Revenue Per Week Chart
    const revenuePerWeekCtx = document.getElementById('revenuePerWeekChart').getContext('2d');
    const revenueLabels = <?= json_encode(array_map(fn($r) => "W{$r['week']} {$r['year']}", $revenuePerWeek)) ?>;
    const revenueData = <?= json_encode(array_map(fn($r) => floatval($r['revenue']), $revenuePerWeek)) ?>;

    new Chart(revenuePerWeekCtx, {
        type: 'line',
        data: {
            labels: revenueLabels,
            datasets: [{
                label: 'Revenue (₹)',
                data: revenueData,
                borderColor: '#2980b9',
                backgroundColor: 'rgba(41, 128, 185, 0.2)',
                fill: true,
                tension: 0.3,
                pointRadius: 3,
                pointHoverRadius: 6
            }]
        },
        options: {
            scales: {
                y: { beginAtZero: true }
            }
        }
    });

    // Top Items Chart
    const topItemsCtx = document.getElementById('topItemsChart').getContext('2d');
    const topItemsLabels = <?= json_encode(array_keys($topItems)) ?>;
    const topItemsData = <?= json_encode(array_values($topItems)) ?>;

    new Chart(topItemsCtx, {
        type: 'pie',
        data: {
            labels: topItemsLabels,
            datasets: [{
                label: 'Top Items',
                data: topItemsData,
                backgroundColor: [
                    '#1abc9c',
                    '#2980b9',
                    '#f39c12',
                    '#e74c3c',
                    '#8e44ad'
                ],
                hoverOffset: 20
            }]
        },
        options: {
            plugins: {
                legend: { position: 'bottom' }
            }
        }
    });
</script>

</body>
</html>
