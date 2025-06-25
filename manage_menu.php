<?php
session_start();
include 'auth_check.php';  // Your authentication and role verification
include 'db_connect.php';  // Your PDO connection as $conn

// Only admin allowed
if ($_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Handle Add/Edit form submission
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $item_name = trim($_POST['item_name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price = trim($_POST['price'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $id = $_POST['id'] ?? null; // For edit

    if ($item_name === '') {
        $errors[] = "Item name is required.";
    }
    if ($price === '' || !is_numeric($price)) {
        $errors[] = "Valid price is required.";
    }
    if ($category === '') {
        $errors[] = "Category is required.";
    }

    if (empty($errors)) {
        try {
            if ($id) {
                // Update existing
                $stmt = $conn->prepare("UPDATE menu SET item_name = :item_name, description = :description, price = :price, category = :category WHERE id = :id");
                $stmt->execute([
                    ':item_name' => $item_name,
                    ':description' => $description,
                    ':price' => $price,
                    ':category' => $category,
                    ':id' => $id,
                ]);
                $message = "Menu item updated successfully.";
            } else {
                // Insert new
                $stmt = $conn->prepare("INSERT INTO menu (item_name, description, price, category) VALUES (:item_name, :description, :price, :category)");
                $stmt->execute([
                    ':item_name' => $item_name,
                    ':description' => $description,
                    ':price' => $price,
                    ':category' => $category,
                ]);
                $message = "Menu item added successfully.";
            }
        } catch (PDOException $e) {
            $errors[] = "Database error: " . $e->getMessage();
        }
    }
}

// Handle delete
if (isset($_GET['delete'])) {
    $deleteId = (int)$_GET['delete'];
    try {
        $stmt = $conn->prepare("DELETE FROM menu WHERE id = :id");
        $stmt->execute([':id' => $deleteId]);
        header("Location: manage_menu.php?deleted=1");
        exit();
    } catch (PDOException $e) {
        $errors[] = "Delete failed: " . $e->getMessage();
    }
}

// Fetch all menu items
try {
    $stmt = $conn->query("SELECT * FROM menu ORDER BY category, item_name");
    $menuItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}

// If editing, fetch that item
$editItem = null;
if (isset($_GET['edit'])) {
    $editId = (int)$_GET['edit'];
    $stmt = $conn->prepare("SELECT * FROM menu WHERE id = :id");
    $stmt->execute([':id' => $editId]);
    $editItem = $stmt->fetch(PDO::FETCH_ASSOC);
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Manage Menu - Admin Panel</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet" />
    <style>
     /* Admin Panel Styles */
body {
    font-family: 'Inter', sans-serif;
    background-color: #f0f2f5;
    margin: 0;
    padding: 0;
}
.navbar {
    background: linear-gradient(to right, #2c3e50, #34495e);
    padding: 20px;
    display: flex;
    flex-wrap: wrap;
    justify-content: space-between;
    align-items: center;
    color: white;
    box-shadow: 0 4px 10px rgba(0,0,0,0.1);
}
.navbar h1 {
    margin: 0;
    font-size: 24px;
}
.navbar ul {
    list-style: none;
    display: flex;
    flex-wrap: wrap;
    margin: 10px 0 0;
    padding: 0;
}
.navbar li {
    margin-left: 20px;
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
form {
    margin-bottom: 30px;
    background: #eef7f9;
    padding: 20px;
    border-radius: 10px;
}
label {
    display: block;
    margin: 12px 0 6px;
    font-weight: 600;
    color: #34495e;
}
input[type="text"],
input[type="number"],
textarea,
select {
    width: 100%;
    padding: 8px 12px;
    border: 1px solid #ccc;
    border-radius: 6px;
    font-size: 16px;
    resize: vertical;
}
button {
    margin-top: 15px;
    padding: 12px 25px;
    background: #2980b9;
    color: white;
    border: none;
    border-radius: 8px;
    font-size: 16px;
    cursor: pointer;
    transition: background 0.3s ease;
}
button:hover {
    background: #1abc9c;
}
table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 10px;
}
th, td {
    padding: 12px 15px;
    border-bottom: 1px solid #ddd;
    text-align: left;
    word-break: break-word;
}
th {
    background: #2980b9;
    color: white;
}
a.action-link {
    margin-right: 10px;
    color: #2980b9;
    text-decoration: none;
    font-weight: 600;
}
a.action-link:hover {
    color: #1abc9c;
}
.error {
    background: #e74c3c;
    color: white;
    padding: 10px;
    border-radius: 6px;
    margin-bottom: 20px;
}
.message {
    background: #2ecc71;
    color: white;
    padding: 10px;
    border-radius: 6px;
    margin-bottom: 20px;
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

/* -------- RESPONSIVE -------- */
  @media (max-width: 768px) {
        .dashboard-stats {
            flex-direction: column;
            align-items: center;
        }
        .card {
            min-width: auto;
            width: 100%;
        }
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
      <h1><a href="admin_dashboard.php" style="color: white; text-decoration: none;">Admin Panel</a></h1>
    <ul>
          <li> <a href="admin_dashboard.php">Home</a></li>
        <li><a href="manage_staff.php">Staff</a></li>
        <li><a href="manage_menu.php" class="active">Menu</a></li>
        <li><a href="view_orders.php">Orders</a></li>
        <li><a href="admin_bookings.php">Bookings</a></li>
        <li><a href="manage_users.php">Users</a></li>
          <li><a href="view_feedback1.php">See feedback</a></li>
        <li><a class="logout" href="logout.php">Logout</a></li>
    </ul>
</div>

<div class="container">
    <h2><?= $editItem ? "Edit Menu Item" : "Add New Menu Item" ?></h2>

    <?php if (!empty($errors)): ?>
        <div class="error">
            <ul>
                <?php foreach ($errors as $err): ?>
                    <li><?= htmlspecialchars($err) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php if (!empty($message)): ?>
        <div class="message"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <form method="post" action="manage_menu.php">
        <input type="hidden" name="id" value="<?= $editItem['id'] ?? '' ?>">
        
        <label for="item_name">Item Name *</label>
        <input type="text" id="item_name" name="item_name" required value="<?= htmlspecialchars($editItem['item_name'] ?? '') ?>">

        <label for="description">Description</label>
        <textarea id="description" name="description" rows="3"><?= htmlspecialchars($editItem['description'] ?? '') ?></textarea>

        <label for="price">Price (e.g. 12.50) *</label>
        <input type="number" id="price" name="price" step="0.01" min="0" required value="<?= htmlspecialchars($editItem['price'] ?? '') ?>">

        <label for="category">Category *</label>
        <input type="text" id="category" name="category" required value="<?= htmlspecialchars($editItem['category'] ?? '') ?>">

        <button type="submit"><?= $editItem ? "Update Item" : "Add Item" ?></button>
        <?php if ($editItem): ?>
            <a href="manage_menu.php" style="margin-left: 15px; font-weight: 600; color: #e74c3c; text-decoration:none;">Cancel</a>
        <?php endif; ?>
    </form>

    <h2>All Menu Items</h2>
    <?php if (empty($menuItems)): ?>
        <p>No menu items found.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Item Name</th>
                    <th>Description</th>
                    <th>Price</th>
                    <th>Category</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($menuItems as $item): ?>
                    <tr>
                        <td><?= $item['id'] ?></td>
                        <td><?= htmlspecialchars($item['item_name']) ?></td>
                        <td><?= nl2br(htmlspecialchars($item['description'])) ?></td>
                        <td>Rs <?= number_format($item['price'], 2) ?></td>
                        <td><?= htmlspecialchars($item['category']) ?></td>
                        <td>
                            <a class="action-link" href="manage_menu.php?edit=<?= $item['id'] ?>">Edit</a>
                            <a class="action-link" href="manage_menu.php?delete=<?= $item['id'] ?>" onclick="return confirm('Are you sure you want to delete this item?');" style="color:#e74c3c;">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
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


</body>
</html>
