<?php
session_start();
include 'auth_check.php';    // User authentication, make sure only logged-in users can access
include 'db_connect.php';    // PDO connection as $conn

// Fetch all menu items ordered by category and item_name
try {
    $stmt = $conn->prepare("SELECT id, item_name, description, price, category FROM menu ORDER BY category, item_name");
    $stmt->execute();
    $menuItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Menu - Restaurant</title>
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
        .menu-container {
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
        <a href="user_dashboard.php">Home</a>
        <a href="menu.php">Menu</a>
        <a href="logout.php" style="color:#e74c3c;">Logout</a>
    </div>
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

</body>
</html>
