<?php
// Show all errors for debugging (remove on production)
// error_reporting(E_ALL);
// ini_set('display_errors', 1);

session_start();

include 'auth_check.php';    // Make sure user is logged in
include 'db_connect.php';    // Your PDO connection file - must define $conn

// Restrict access to users only
if ($_SESSION['role'] !== 'user') {
    header("Location: login.php");
    exit();
}

$userId = $_SESSION['user_id'] ?? 0;

// Function to get top ordered items by user or globally, with normalized score capped at 10
function getTopItems(PDO $conn, $userId = null, $limit = 3) {
    try {
        if ($userId) {
            $stmt = $conn->prepare("SELECT order_details FROM orders WHERE user_id = ?");
            $stmt->execute([$userId]);
        } else {
            $stmt = $conn->prepare("SELECT order_details FROM orders");
            $stmt->execute();
        }

        $allOrders = $stmt->fetchAll(PDO::FETCH_COLUMN);
        $itemTotals = [];

        foreach ($allOrders as $orderJson) {
            $items = json_decode($orderJson, true);
            if (is_array($items)) {
                foreach ($items as $item) {
                    $id = $item['id'] ?? 0;
                    $qty = $item['quantity'] ?? 0;
                    if ($id > 0) {
                        if (!isset($itemTotals[$id])) {
                            $itemTotals[$id] = 0;
                        }
                        $itemTotals[$id] += $qty;
                    }
                }
            }
        }

        if (empty($itemTotals)) return [];

        $topItemIds = array_keys($itemTotals);
        $placeholders = implode(',', array_fill(0, count($topItemIds), '?'));
        $stmt = $conn->prepare("SELECT id, item_name, price FROM menu WHERE id IN ($placeholders)");
        $stmt->execute($topItemIds);
        $topItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Calculate raw scores = quantity × price
        $rawScores = [];
        foreach ($topItems as $item) {
            $rawScores[$item['id']] = ($itemTotals[$item['id']] ?? 0) * $item['price'];
        }

        // Normalize scores 0-10 capped
        $minScore = min($rawScores);
        $maxScore = max($rawScores);
        $range = $maxScore - $minScore;

        if ($range == 0) {
            // all scores equal: assign 10 to all
            foreach ($topItems as &$item) {
                $item['score'] = 10;
            }
            unset($item);
        } else {
            foreach ($topItems as &$item) {
                $norm = (($rawScores[$item['id']] - $minScore) / $range) * 10;
                $item['score'] = round(min($norm, 10), 2);
            }
            unset($item);
        }

        // Sort by normalized score descending
        usort($topItems, function($a, $b) {
            return $b['score'] <=> $a['score'];
        });

        return array_slice($topItems, 0, $limit);

    } catch (PDOException $e) {
        return [];
    }
}

// Then fetch recommendations as usual:
$userTopItems = getTopItems($conn, $userId, 3);
$popularItems = getTopItems($conn, null, 3);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>User Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet" />
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

        .dashboard {
            display: flex;
            gap: 20px;
            justify-content: center;
            flex-wrap: wrap;
            margin-bottom: 30px;
        }

        .card {
            flex: 1 1 250px;
            background: linear-gradient(to top right, #74ebd5, #ACB6E5);
            border-radius: 10px;
            color: #2c3e50;
            padding: 20px;
            text-align: center;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            transition: transform 0.2s ease;
            cursor: pointer;
        }

        .card:hover {
            transform: translateY(-5px);
        }

        .card-icon {
            font-size: 32px;
            margin-bottom: 10px;
        }

        .card h3 {
            font-size: 18px;
            margin: 10px 0 5px;
        }

        .card p {
            font-size: 20px;
            font-weight: 600;
        }

        .recommendations {
            margin-top: 40px;
        }

        .recommendations h2 {
            color: #2980b9;
            margin-bottom: 25px;
            border-bottom: 3px solid #1abc9c;
            padding-bottom: 8px;
            font-weight: 700;
            font-size: 2rem;
        }

        .recommendations h3 {
            color: #2980b9;
            margin-bottom: 15px;
            border-bottom: 2px solid #2980b9;
            padding-bottom: 5px;
        }

        .rec-item {
            background: #e3f6f5;
            border-radius: 8px;
            padding: 12px 18px;
            margin-bottom: 12px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 1px 4px rgba(0,0,0,0.1);
            font-weight: 600;
            color: #2c3e50;
        }

        .rec-item .price {
            color: #27ae60;
            font-weight: 700;
            margin-left: 15px;
        }

        .rec-item .score {
            font-weight: normal;
            color: #2980b9;
            font-size: 0.9rem;
            margin-left: 15px;
            white-space: nowrap;
        }

        .links {
            list-style: none;
            padding: 0;
            margin: 0;
            text-align: center;
        }

        .links li {
            margin: 10px 0;
        }

        .links a {
            font-size: 16px;
            color: #2980b9;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.2s ease;
        }

        .links a:hover {
            color: #1abc9c;
        }

        @media (max-width: 600px) {
            .dashboard {
                flex-direction: column;
                align-items: center;
            }
        }
    </style>
</head>
<body>

<div class="navbar">
    <h1>User Panel</h1>
    <ul>
        <li><a href="user_dashboard.php">Home</a></li>
        <li><a href="menu.php">View Menu</a></li>
        <li><a href="order_menu.php">Place an Order</a></li>
        <li><a href="my_orders.php">My Orders</a></li>
        <li><a href="order_history.php">Order History</a></li>
        <li><a href="book_table.php">Booking</a></li>
        <li><a href="my_bookings.php">My Bookings</a></li>
        <li><a href="feedback.php">Feedback</a></li>
        <li><a href="profile.php">Manage Profile</a></li>
        <li><a class="logout" href="logout.php">Logout</a></li>
    </ul>
</div>

<div class="container">
    <h2>Welcome, <?= htmlspecialchars($_SESSION['username']) ?> 👋</h2>

    <div class="dashboard">
        <div class="card" onclick="location.href='menu.php'">
            <div class="card-icon">🍽️</div>
            <h3>View Menu</h3>
            <p>Browse & Order</p>
        </div>

        <div class="card" onclick="location.href='order_history.php'">
            <div class="card-icon">🧾</div>
            <h3>Order History</h3>
            <p>Track Past Orders</p>
        </div>

        <div class="card" onclick="location.href='profile.php'">
            <div class="card-icon">👤</div>
            <h3>Manage Profile</h3>
            <p>Edit Info</p>
        </div>
    </div>

    <div class="recommendations">
        <h2>Recommended for You</h2>

        <h3>Your Top Ordered Items</h3>
        <?php if (!empty($userTopItems)): ?>
            <?php foreach ($userTopItems as $item): ?>
                <div class="rec-item">
                    <div><?= htmlspecialchars($item['item_name']) ?></div>
                    <div class="price">Rs <?= number_format($item['price'], 2) ?></div>
                    <div class="score">Score: <?= $item['score'] ?>/10</div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>You have no previous orders.</p>
        <?php endif; ?>

        <h3>Popular Items</h3>
        <?php if (!empty($popularItems)): ?>
            <?php foreach ($popularItems as $item): ?>
                <div class="rec-item">
                    <div><?= htmlspecialchars($item['item_name']) ?></div>
                    <div class="price">Rs <?= number_format($item['price'], 2) ?></div>
                    <div class="score">Score: <?= $item['score'] ?>/10</div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No popular items found.</p>
        <?php endif; ?>
    </div>
</div>

<footer style="background-color: #2c3e50; color: white; padding: 20px 0; text-align: center; margin-top: 50px;">
    <div style="max-width: 1100px; margin: auto;">
        <p style="margin-bottom: 10px; font-size: 16px;">Quick Links</p>
        <div style="display: flex; justify-content: center; flex-wrap: wrap; gap: 20px;">
            <a href="logout.php" style="color: #e74c3c; text-decoration: none;">🚪 Logout</a>
        </div>
        <p style="margin-top: 15px; font-size: 14px; color: #bdc3c7;">&copy; <?= date("Y") ?> Restaurant Customer Panel</p>
    </div>
</footer>

</body>
</html>
