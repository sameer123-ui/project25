<?php
session_start();
include 'auth_check.php';
include 'db_connect.php';

// Use the same salt and hashing function as in register/login
define('CUSTOM_SALT', 'your-secure-salt-value'); // Keep consistent
function custom_hash($password) {
    return hash_hmac('sha256', $password, CUSTOM_SALT);
}

if ($_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_staff'])) {
    $username = trim($_POST['username']);
    $password = custom_hash($_POST['password']); // 👈 Use custom_hash here
    $role = 'staff';

    $stmt = $conn->prepare("SELECT COUNT(*) FROM users WHERE username = ?");
    $stmt->execute([$username]);
    if ($stmt->fetchColumn() > 0) {
        $error = "Username already exists!";
    } else {
        $stmt = $conn->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
        $stmt->execute([$username, $password, $role]);
        header("Location: manage_staff.php");
        exit();
    }
}

$stmt = $conn->prepare("SELECT id, username FROM users WHERE role = 'staff' ORDER BY username");
$stmt->execute();
$staffs = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<title>Manage Staff</title>
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
        max-width: 700px;
        margin: 40px auto;
        background-color: white;
        padding: 30px;
        border-radius: 12px;
        box-shadow: 0 6px 20px rgba(0,0,0,0.1);
    }
    h2 {
        color: #2c3e50;
        margin-bottom: 20px;
        text-align: center;
    }
    form label {
        display: block;
        margin: 10px 0 5px;
        font-weight: 600;
    }
    input[type="text"], input[type="password"] {
        width: 100%;
        padding: 10px;
        border-radius: 6px;
        border: 1px solid #ccc;
        box-sizing: border-box;
    }
    button {
        margin-top: 15px;
        padding: 12px 20px;
        border: none;
        background-color: #1abc9c;
        color: white;
        font-weight: 600;
        border-radius: 8px;
        cursor: pointer;
        width: 100%;
        transition: background-color 0.3s;
    }
    button:hover {
        background-color: #16a085;
    }
    .error {
        color: red;
        margin-bottom: 15px;
        text-align: center;
    }
    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 30px;
    }
    th, td {
        padding: 12px;
        border: 1px solid #ddd;
        text-align: left;
    }
    th {
        background-color: #2c3e50;
        color: white;
    }
    .actions a {
        margin-right: 15px;
        color: #e74c3c;
        text-decoration: none;
        font-weight: 600;
    }
    .actions a:hover {
        text-decoration: underline;
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
    <h1>Admin Panel</h1>
    <ul>
        <li><a href="admin_dashboard.php">Home</a></li>
        <li><a href="manage_staff.php">Staff</a></li>
        <li><a href="manage_menu.php">Menu</a></li>
        <li><a href="view_orders.php">Orders</a></li>
        <li><a href="manage_users.php">Users</a></li>
        <li><a class="logout" href="logout.php">Logout</a></li>
    </ul>
</div>

<div class="container">
    <h2>Manage Staff</h2>

    <?php if (!empty($error)): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="">
        <label for="username">Username</label>
        <input id="username" name="username" type="text" required />

        <label for="password">Password</label>
        <input id="password" name="password" type="password" required />

        <button type="submit" name="add_staff">Add Staff</button>
    </form>

    <table>
        <thead>
            <tr><th>Username</th><th>Actions</th></tr>
        </thead>
        <tbody>
            <?php foreach ($staffs as $staff): ?>
                <tr>
                    <td><?= htmlspecialchars($staff['username']) ?></td>
                    <td class="actions">
                        <a href="edit_staff.php?id=<?= $staff['id'] ?>">Edit</a>
                        <a href="delete_staff.php?id=<?= $staff['id'] ?>" onclick="return confirm('Are you sure you want to delete this staff?');">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
  <footer style="background-color: #2c3e50; color: white; padding: 20px 0; text-align: center; margin-top: 400px;">
    <div style="max-width: 1100px; margin: auto;">
        <p style="margin-bottom: 10px; font-size: 16px;">Quick Links</p>
        <div style="display: flex; justify-content: center; flex-wrap: wrap; gap: 20px;">
            <a href="manage_staff.php" style="color: #ecf0f1; text-decoration: none;">👨‍🍳 Staff</a>
            <a href="manage_menu.php" style="color: #ecf0f1; text-decoration: none;">📋 Menu</a>
            <a href="view_orders.php" style="color: #ecf0f1; text-decoration: none;">🧾 Orders</a>
            <a href="manage_users.php" style="color: #ecf0f1; text-decoration: none;">👥 Users</a>
            <a href="logout.php" style="color: #e74c3c; text-decoration: none;">🚪 Logout</a>
        </div>
        <p style="margin-top: 15px; font-size: 14px; color: #bdc3c7;">&copy; <?= date("Y") ?> Restaurant Admin Panel</p>
    </div>
</footer>
</body>
</html>
