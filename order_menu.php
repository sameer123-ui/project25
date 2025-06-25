<?php
session_start();
include 'auth_check.php';
include 'db_connect.php';

$userId = $_SESSION['user_id'] ?? 0;

// Fetch all menu items ordered by category and name
try {
    $stmt = $conn->prepare("SELECT id, item_name, price, category FROM menu ORDER BY category, item_name");
    $stmt->execute();
    $menuItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}

// Function to get top ordered items (user-specific or global)
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

        arsort($itemTotals);
        $topItemIds = array_slice(array_keys($itemTotals), 0, $limit);

        if (empty($topItemIds)) return [];

        $placeholders = implode(',', array_fill(0, count($topItemIds), '?'));
        $stmt = $conn->prepare("SELECT id, item_name, price FROM menu WHERE id IN ($placeholders)");
        $stmt->execute($topItemIds);
        $topItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Sort according to order quantity descending
        usort($topItems, function($a, $b) use ($itemTotals) {
            return $itemTotals[$b['id']] <=> $itemTotals[$a['id']];
        });

        return $topItems;

    } catch (PDOException $e) {
        return [];
    }
}

$userTopItems = getTopItems($conn, $userId, 3);
$popularItems = getTopItems($conn, null, 3);

$inputQuantities = $_POST['quantity'] ?? [];
$paymentMethod = $_POST['payment_method'] ?? '';
$orderType = $_POST['order_type'] ?? '';
$deliveryAddress = trim($_POST['delivery_address'] ?? '');

$allowedMethods = ['Cash', 'Card', 'UPI'];
$allowedOrderTypes = ['pickup', 'delivery'];

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $orderItems = [];
    foreach ($inputQuantities as $menuId => $qty) {
        $qty = (int)$qty;
        if ($qty > 0) {
            $orderItems[$menuId] = $qty;
        }
    }

    if (count($orderItems) === 0) {
        $error = "Please select at least one item with quantity greater than zero.";
    } elseif (!in_array($paymentMethod, $allowedMethods, true)) {
        $error = "Please select a valid payment method.";
    } elseif (!in_array($orderType, $allowedOrderTypes, true)) {
        $error = "Please select a valid order type.";
    } elseif ($orderType === 'delivery' && empty($deliveryAddress)) {
        $error = "Please enter your delivery address for delivery orders.";
    } else {
        try {
            $menuIds = array_keys($orderItems);
            $placeholders = implode(',', array_fill(0, count($menuIds), '?'));
            $stmtMenu = $conn->prepare("SELECT id, item_name, price FROM menu WHERE id IN ($placeholders)");
            $stmtMenu->execute($menuIds);
            $menuDetails = $stmtMenu->fetchAll(PDO::FETCH_ASSOC);

            $orderDetailsArr = [];
            $totalPrice = 0;
            $menuMap = [];

            foreach ($menuDetails as $item) {
                $menuMap[$item['id']] = $item;
            }

            foreach ($orderItems as $menuId => $qty) {
                if (!isset($menuMap[$menuId])) continue;

                $item = $menuMap[$menuId];
                $subtotal = $item['price'] * $qty;
                $totalPrice += $subtotal;

                $orderDetailsArr[] = [
                    'id' => $menuId,
                    'name' => $item['item_name'],
                    'quantity' => $qty,
                    'price' => $item['price'],
                    'subtotal' => $subtotal,
                ];
            }

            $orderDetailsJson = json_encode($orderDetailsArr);

            $stmt = $conn->prepare("INSERT INTO orders (user_id, order_date, total, status, order_details, payment_method, order_type, delivery_address, assigned_staff_id) 
                                    VALUES (:user_id, NOW(), :total, 'pending', :order_details, :payment_method, :order_type, :delivery_address, NULL)");

            $stmt->execute([
                ':user_id' => $userId,
                ':total' => $totalPrice,
                ':order_details' => $orderDetailsJson,
                ':payment_method' => $paymentMethod,
                ':order_type' => $orderType,
                ':delivery_address' => $orderType === 'delivery' ? $deliveryAddress : null,
            ]);

            $success = "Order placed successfully! Total: Rs " . number_format($totalPrice, 2);

            // Reset inputs on success
            $inputQuantities = [];
            $paymentMethod = '';
            $orderType = '';
            $deliveryAddress = '';

        } catch (PDOException $e) {
            $error = "Failed to place order: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Place Order - Restaurant</title>
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
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
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
            margin: 30px auto;
            background: white;
            padding: 20px 30px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h2 {
            text-align: center;
            margin-bottom: 30px;
            color: #2c3e50;
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
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid #eee;
        }
        .menu-item:last-child {
            border-bottom: none;
        }
        .item-name {
            font-weight: bold;
        }
        .item-price {
            font-weight: bold;
            color: #27ae60;
            margin-right: 20px;
        }
        input[type=number], select, textarea {
            padding: 5px;
            font-size: 16px;
        }
        input[type=number] {
            width: 60px;
        }
        textarea {
            width: 100%;
            resize: vertical;
        }
        .btn-submit {
            display: block;
            margin: 30px auto 0;
            background: #2980b9;
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 6px;
            font-size: 18px;
            cursor: pointer;
        }
        .btn-submit:hover {
            background: #1abc9c;
        }
        .message {
            max-width: 900px;
            margin: 15px auto;
            text-align: center;
            font-weight: bold;
        }
        .error {
            color: #e74c3c;
        }
        .success {
            color: #27ae60;
        }
        .payment-method, .order-type, .delivery-address {
            margin-top: 25px;
        }

        /* Recommendation section styles */
        .recommendations {
            max-width: 900px;
            margin: 30px auto 10px auto;
            background: white;
            padding: 25px 30px;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0,0,0,0.12);
        }
        .recommendations h2 {
            color: #2980b9;
            font-weight: 700;
            font-size: 2rem;
            margin-bottom: 25px;
            border-bottom: 3px solid #1abc9c;
            padding-bottom: 8px;
        }
        .recommendations h3 {
            color: #34495e;
            margin-top: 30px;
            margin-bottom: 15px;
            font-weight: 600;
            font-size: 1.4rem;
            border-bottom: 1px solid #eee;
            padding-bottom: 6px;
        }
        .recommendation-list {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
        }
        .recommendation-card {
            background: #f9fafa;
            border-radius: 12px;
            box-shadow: 0 6px 12px rgba(0,0,0,0.05);
            padding: 18px 20px;
            flex: 1 1 260px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            transition: box-shadow 0.3s ease, transform 0.3s ease;
        }
        .recommendation-card:hover {
            box-shadow: 0 10px 25px rgba(0,0,0,0.12);
            transform: translateY(-6px);
        }
        .recommendation-card .item-name {
            font-weight: 700;
            font-size: 1.2rem;
            color: #2c3e50;
            margin-bottom: 8px;
        }
        .recommendation-card .item-price {
            font-weight: 700;
            color: #27ae60;
            font-size: 1.1rem;
            margin-bottom: 10px;
        }
        .recommendation-card input[type=number] {
            width: 70px;
            font-size: 1rem;
            padding: 6px;
            border-radius: 6px;
            border: 1px solid #ccc;
            text-align: center;
            align-self: flex-start;
        }
        @media (max-width: 700px) {
            .recommendation-list {
                flex-direction: column;
            }
            .recommendation-card {
                flex: 1 1 100%;
            }
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
            <li><a href="my_bookings.php">My Bookings</a></li>
            <li><a href="feedback.php">Feedback</a></li>
            <li><a href="profile.php">Manage Profile</a></li>
            <li><a class="logout" href="logout.php">Logout</a></li>
        </ul>
    </div>
</div>

<!-- Recommendations section -->
<div class="recommendations">
    <h2>Recommended for You</h2>

    <?php if (!empty($userTopItems)): ?>
        <h3>Your Top Ordered Items</h3>
        <div class="recommendation-list">
            <?php foreach ($userTopItems as $item): ?>
                <div class="recommendation-card">
                    <div class="item-name"><?= htmlspecialchars($item['item_name']) ?></div>
                    <div class="item-price">Rs <?= number_format($item['price'], 2) ?></div>
                    <input type="number" min="0" name="quantity[<?= $item['id'] ?>]" value="<?= isset($inputQuantities[$item['id']]) ? (int)$inputQuantities[$item['id']] : 0 ?>" />
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p>You have no previous orders.</p>
    <?php endif; ?>

    <?php if (!empty($popularItems)): ?>
        <h3>Popular Items</h3>
        <div class="recommendation-list">
            <?php foreach ($popularItems as $item): ?>
                <div class="recommendation-card">
                    <div class="item-name"><?= htmlspecialchars($item['item_name']) ?></div>
                    <div class="item-price">Rs <?= number_format($item['price'], 2) ?></div>
                    <input type="number" min="0" name="quantity[<?= $item['id'] ?>]" value="<?= isset($inputQuantities[$item['id']]) ? (int)$inputQuantities[$item['id']] : 0 ?>" />
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p>No popular items found.</p>
    <?php endif; ?>
</div>

<!-- Main Order Form -->
<div class="container">
    <h2>Place Your Order</h2>

    <?php if (!empty($error)): ?>
        <div class="message error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <?php if (!empty($success)): ?>
        <div class="message success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <form method="POST" action="order_menu.php">
        <?php
        $currentCategory = '';
        foreach ($menuItems as $item) {
            if ($item['category'] !== $currentCategory) {
                if ($currentCategory !== '') {
                    echo "</div>";
                }
                $currentCategory = htmlspecialchars($item['category']);
                echo "<div class='menu-category'>";
                echo "<h3>$currentCategory</h3>";
            }
            ?>
            <div class="menu-item">
                <div class="item-name"><?= htmlspecialchars($item['item_name']) ?></div>
                <div class="item-price">Rs <?= number_format($item['price'], 2) ?></div>
                <input type="number" name="quantity[<?= $item['id'] ?>]" value="<?= isset($inputQuantities[$item['id']]) ? (int)$inputQuantities[$item['id']] : 0 ?>" min="0" />
            </div>
            <?php
        }
        if ($currentCategory !== '') {
            echo "</div>";
        }
        ?>

        <div class="order-type">
            <label for="order_type"><strong>Order Type:</strong></label><br />
            <select name="order_type" id="order_type" required onchange="toggleDeliveryAddress()">
                <option value="">-- Select Order Type --</option>
                <option value="pickup" <?= $orderType === 'pickup' ? 'selected' : '' ?>>Pickup</option>
                <option value="delivery" <?= $orderType === 'delivery' ? 'selected' : '' ?>>Delivery</option>
            </select>
        </div>

        <div class="delivery-address" id="deliveryAddressDiv" style="display: <?= $orderType === 'delivery' ? 'block' : 'none' ?>;">
            <label for="delivery_address"><strong>Delivery Address:</strong></label><br />
            <textarea name="delivery_address" id="delivery_address" rows="3" placeholder="Enter your delivery address here"><?= htmlspecialchars($deliveryAddress) ?></textarea>
        </div>

        <div class="payment-method">
            <label for="payment_method"><strong>Payment Method:</strong></label>
            <select name="payment_method" id="payment_method" required>
                <option value="">-- Select Payment Method --</option>
                <option value="Cash" <?= $paymentMethod === 'Cash' ? 'selected' : '' ?>>Cash on Delivery</option>
                <option value="Card" <?= $paymentMethod === 'Card' ? 'selected' : '' ?>>Mobile Banking</option>
            </select>
        </div>

        <button type="submit" class="btn-submit">Place Order</button>
    </form>
</div>

<script>
function toggleDeliveryAddress() {
    var orderType = document.getElementById('order_type').value;
    var deliveryDiv = document.getElementById('deliveryAddressDiv');
    if (orderType === 'delivery') {
        deliveryDiv.style.display = 'block';
    } else {
        deliveryDiv.style.display = 'none';
    }
}
</script>

</body>
</html>
