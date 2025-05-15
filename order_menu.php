<?php
session_start();
include 'auth_check.php';
include 'db_connect.php';

$userId = $_SESSION['user_id'];

// Fetch menu items
try {
    $stmt = $conn->prepare("SELECT id, item_name, price, category FROM menu ORDER BY category, item_name");
    $stmt->execute();
    $menuItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $quantities = $_POST['quantity'] ?? [];

    // Filter only items with quantity > 0
    $orderItems = [];
    foreach ($quantities as $menuId => $qty) {
        $qty = (int)$qty;
        if ($qty > 0) {
            $orderItems[$menuId] = $qty;
        }
    }

    if (count($orderItems) === 0) {
        $error = "Please select at least one item with quantity greater than zero.";
    } else {
        // Calculate total price and build detailed order info
        try {
            // Prepare to map menu IDs to details
            $menuIds = array_keys($orderItems);
            $placeholders = implode(',', array_fill(0, count($menuIds), '?'));

            $stmtMenu = $conn->prepare("SELECT id, item_name, price FROM menu WHERE id IN ($placeholders)");
            $stmtMenu->execute($menuIds);
            $menuDetails = $stmtMenu->fetchAll(PDO::FETCH_ASSOC);

            $orderDetailsArr = [];
            $totalPrice = 0;

            // Map menu items by ID for easy lookup
            $menuMap = [];
            foreach ($menuDetails as $item) {
                $menuMap[$item['id']] = $item;
            }

            foreach ($orderItems as $menuId => $qty) {
                if (!isset($menuMap[$menuId])) {
                    continue; // Skip if menu item not found (should not happen)
                }
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

            // Insert order into orders table
            $stmt = $conn->prepare("INSERT INTO orders (user_id, order_date, total, status, order_details) VALUES (:user_id, NOW(), :total, 'Pending', :order_details)");
            $stmt->execute([
                ':user_id' => $userId,
                ':total' => $totalPrice,
                ':order_details' => $orderDetailsJson
            ]);

            $success = "Order placed successfully! Total: $" . number_format($totalPrice, 2);
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
            font-family: Arial, sans-serif;
            background: #f9f9f9;
            margin: 0; padding: 20px;
        }
        .navbar {
            background: #34495e;
            padding: 15px 30px;
            color: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .navbar a {
            color: white;
            text-decoration: none;
            margin-left: 20px;
            font-weight: 600;
        }
        .navbar a:hover {
            color: #1abc9c;
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
        input[type=number] {
            width: 60px;
            padding: 5px;
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
    </style>
</head>
<body>

<div class="navbar">
    <div>Welcome, <?= htmlspecialchars($_SESSION['username']) ?></div>
    <div>
        <a href="user_dashboard.php">Dashboard</a>
        <a href="order_menu.php">Order Menu</a>
        <a href="logout.php" style="color:#e74c3c;">Logout</a>
    </div>
</div>

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
                <div class="item-price">$<?= number_format($item['price'], 2) ?></div>
                <input type="number" name="quantity[<?= $item['id'] ?>]" value="0" min="0" />
            </div>
            <?php
        }
        if ($currentCategory !== '') {
            echo "</div>";
        }
        ?>
        <button type="submit" class="btn-submit">Place Order</button>
    </form>
</div>

</body>
</html>
