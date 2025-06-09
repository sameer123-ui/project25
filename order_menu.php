<?php
session_start();
include 'auth_check.php';
include 'db_connect.php';

$userId = $_SESSION['user_id'];

try {
    $stmt = $conn->prepare("SELECT id, item_name, price, category FROM menu ORDER BY category, item_name");
    $stmt->execute();
    $menuItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}

$inputQuantities = $_POST['quantity'] ?? [];
$paymentMethod = $_POST['payment_method'] ?? '';

$allowedMethods = ['Cash', 'Card', 'UPI'];

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

            $stmt = $conn->prepare("INSERT INTO orders (user_id, order_date, total, status, order_details, payment_method, assigned_staff_id) 
                                    VALUES (:user_id, NOW(), :total, 'pending', :order_details, :payment_method, NULL)");

            $stmt->execute([
                ':user_id' => $userId,
                ':total' => $totalPrice,
                ':order_details' => $orderDetailsJson,
                ':payment_method' => $paymentMethod,
            ]);

            $success = "Order placed successfully! Total: Rs " . number_format($totalPrice, 2);

            // Reset inputs on success
            $inputQuantities = [];
            $paymentMethod = '';

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
        /* Your existing CSS here */
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
        .payment-method {
            margin-top: 25px;
            text-align: center;
        }
        .payment-method select {
            padding: 8px 12px;
            font-size: 16px;
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
            <li><a href="profile.php">Manage Profile</a></li>
            <li><a class="logout" href="logout.php">Logout</a></li>
        </ul>
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
                <div class="item-price">Rs <?= number_format($item['price'], 2) ?></div>
                <input type="number" name="quantity[<?= $item['id'] ?>]" value="<?= isset($inputQuantities[$item['id']]) ? (int)$inputQuantities[$item['id']] : 0 ?>" min="0" />
            </div>
            <?php
        }
        if ($currentCategory !== '') {
            echo "</div>";
        }
        ?>

        <div class="payment-method">
            <label for="payment_method"><strong>Payment Method:</strong></label>
            <select name="payment_method" id="payment_method" required>
                <option value="">-- Select Payment Method --</option>
                <option value="Cash" <?= $paymentMethod === 'Cash' ? 'selected' : '' ?>>Cash on Delivery</option>
                <option value="Card" <?= $paymentMethod === 'Card' ? 'selected' : '' ?>>Mobile Banking (Card)</option>
                <option value="UPI" <?= $paymentMethod === 'UPI' ? 'selected' : '' ?>>Mobile Banking (UPI)</option>
            </select>
        </div>

        <button type="submit" class="btn-submit">Place Order</button>
    </form>
</div>

</body>
</html>
