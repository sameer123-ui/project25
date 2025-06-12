<?php
session_start();
include 'auth_check.php';  // Ensure user is logged in
include 'db_connect.php';  // PDO connection as $conn

$userId = $_SESSION['user_id'] ?? 0;

// 1. Fetch all menu items ordered by category and name
try {
    $stmt = $conn->prepare("SELECT id, item_name, description, price, category FROM menu ORDER BY category, item_name");
    $stmt->execute();
    $menuItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}

// Helper function to aggregate top items for a given user or all users
function getTopItems(PDO $conn, $userId = null, $limit = 3) {
    // Fetch order_details JSON from orders for either a user or all users
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

        // Sort by quantity descending
        arsort($itemTotals);

        $topItemIds = array_slice(array_keys($itemTotals), 0, $limit);

        if (empty($topItemIds)) {
            return [];
        }

        // Fetch menu details for top items
        $placeholders = implode(',', array_fill(0, count($topItemIds), '?'));
        $stmt = $conn->prepare("SELECT id, item_name, price FROM menu WHERE id IN ($placeholders)");
        $stmt->execute($topItemIds);
        $topItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Optional: sort the fetched items according to $itemTotals order
        usort($topItems, function($a, $b) use ($itemTotals) {
            return $itemTotals[$b['id']] <=> $itemTotals[$a['id']];
        });

        return $topItems;

    } catch (PDOException $e) {
        return [];
    }
}

// 2. Fetch top 3 items ordered by this user
$userTopItems = getTopItems($conn, $userId, 3);

// 3. Fetch top 3 popular items from all orders
$popularItems = getTopItems($conn, null, 3);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Menu - Restaurant</title>
    <style>
       body {
           margin: 0;
           padding: 0;
           font-family: 'Inter', sans-serif;
           background-color: #f0f2f5;
       }
       .navbar {
           background: linear-gradient(to right, #2c3e50, #34495e);
           padding: 20px 40px;
           display: flex;
           justify-content: space-between;
           align-items: center;
           color: white;
           box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
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
       .menu-container, .recommendations {
           max-width: 900px;
           margin: 30px auto;
           background: white;
           padding: 20px 30px;
           border-radius: 10px;
           box-shadow: 0 0 10px rgba(0,0,0,0.1);
       }
       h2, h3 {
           color: #2c3e50;
           margin-bottom: 20px;
       }
       .menu-category {
           margin-top: 30px;
       }
       .menu-category h3 {
           border-bottom: 2px solid #2980b9;
           padding-bottom: 5px;
           color: #2980b9;
       }
       .menu-item {
           display: flex;
           justify-content: space-between;
           padding: 10px 0;
           border-bottom: 1px solid #eee;
       }
       .menu-item:last-child {
           border-bottom: none;
       }
       .item-name {
           font-weight: bold;
       }
       .item-description {
           font-style: italic;
           color: #666;
           margin-left: 15px;
           flex-grow: 1;
       }
       .item-price {
           font-weight: bold;
           color: #27ae60;
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
                 <li><a href="feedback.php">feedback</a></li>
            <li><a href="profile.php">Manage Profile</a></li>
            
            <li><a class="logout" href="logout.php">Logout</a></li>
        </ul>
    </div>
</div>

<div class="recommendations">
    <h2>Recommended for You</h2>

    <?php if (!empty($userTopItems)): ?>
        <h3>Your Top Ordered Items</h3>
        <?php foreach ($userTopItems as $item): ?>
            <div class="menu-item">
                <div class="item-name"><?= htmlspecialchars($item['item_name']) ?></div>
                <div class="item-price">Rs <?= number_format($item['price'], 2) ?></div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p>You have no previous orders.</p>
    <?php endif; ?>

    <?php if (!empty($popularItems)): ?>
        <h3>Popular Items</h3>
        <?php foreach ($popularItems as $item): ?>
            <div class="menu-item">
                <div class="item-name"><?= htmlspecialchars($item['item_name']) ?></div>
                <div class="item-price">Rs <?= number_format($item['price'], 2) ?></div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p>No popular items found.</p>
    <?php endif; ?>
</div>

<div class="menu-container">
    <h2>Our Menu</h2>

    <?php
    $currentCategory = '';
    foreach ($menuItems as $item) {
        if ($item['category'] !== $currentCategory) {
            if ($currentCategory !== '') {
                echo "</div>";  // close previous category
            }
            $currentCategory = htmlspecialchars($item['category']);
            echo "<div class='menu-category'>";
            echo "<h3>$currentCategory</h3>";
        }
        echo "<div class='menu-item'>";
        echo "<div><span class='item-name'>" . htmlspecialchars($item['item_name']) . "</span>";
        if (!empty($item['description'])) {
            echo "<div class='item-description'>" . htmlspecialchars($item['description']) . "</div>";
        }
        echo "</div>";
        echo "<div class='item-price'>Rs " . number_format($item['price'], 2) . "</div>";
        echo "</div>";
    }
    if ($currentCategory !== '') {
        echo "</div>";
    }
    ?>
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
