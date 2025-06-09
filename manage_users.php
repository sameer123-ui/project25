<?php
session_start();
include 'auth_check.php';
include 'db_connect.php';

// Only admin allowed
if ($_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Handle Add User
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_user'])) {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $role = $_POST['role'];

    if ($username && $password && $role) {
        // Custom hashing: md5(sha1(password))
        $hashedPassword = md5(sha1($password));

        $stmt = $conn->prepare("INSERT INTO users (username, password, role) VALUES (:username, :password, :role)");
        $stmt->execute([
            ':username' => $username,
            ':password' => $hashedPassword,
            ':role' => $role,
        ]);
        header("Location: manage_users.php");
        exit();
    } else {
        $error = "Please fill all fields to add a user.";
    }
}

// Handle Delete User
if (isset($_GET['delete'])) {
    $deleteId = (int)$_GET['delete'];
    if ($deleteId !== (int)$_SESSION['user_id']) { // Prevent deleting self
        $stmt = $conn->prepare("DELETE FROM users WHERE id = :id");
        $stmt->execute([':id' => $deleteId]);
    }
    header("Location: manage_users.php");
    exit();
}

// Fetch all users
$stmt = $conn->query("SELECT id, username, role FROM users ORDER BY id DESC");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Manage Users</title>
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f0f2f5;
            margin: 0;
            padding: 0;
        }
        h1, h2 {
            text-align: center;
            margin-top: 30px;
        }
        table {
            border-collapse: collapse;
            width: 90%;
            margin: auto;
            background: white;
            box-shadow: 0 0 8px #ccc;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: left;
        }
        th {
            background: #2c3e50;
            color: white;
        }
        a.delete {
            color: red;
            text-decoration: none;
            font-weight: bold;
        }
        a.delete:hover {
            text-decoration: underline;
        }
        form.add-user {
            margin: 30px auto;
            background: white;
            padding: 20px;
            box-shadow: 0 0 8px #ccc;
            max-width: 400px;
        }
        form.add-user label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
        }
        form.add-user input, form.add-user select {
            width: 100%;
            padding: 8px;
            margin-bottom: 15px;
            box-sizing: border-box;
        }
        form.add-user button {
            padding: 10px 15px;
            background: #2980b9;
            color: white;
            border: none;
            cursor: pointer;
            font-weight: bold;
            border-radius: 4px;
        }
        form.add-user button:hover {
            background: #1abc9c;
        }
        .error {
            color: red;
            text-align: center;
            margin-bottom: 15px;
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
        <li><a href="manage_users.php">Users</a></li>
        <li><a class="logout" href="logout.php">Logout</a></li>
    </ul>
</div>

<h1>Manage Users</h1>

<?php if (!empty($error)): ?>
    <div class="error"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<table>
    <thead>
        <tr>
            <th>ID</th><th>Username</th><th>Role</th><th>Action</th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($users as $user): ?>
        <tr>
            <td><?= htmlspecialchars($user['id']) ?></td>
            <td><?= htmlspecialchars($user['username']) ?></td>
            <td><?= htmlspecialchars($user['role']) ?></td>
            <td>
                <?php if ($user['id'] !== $_SESSION['user_id']): ?>
                    <a href="manage_users.php?delete=<?= $user['id'] ?>" class="delete" onclick="return confirm('Are you sure you want to delete this user?');">Delete</a>
                <?php else: ?>
                    (You)
                <?php endif; ?>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>

<h2>Add New User</h2>
<form class="add-user" method="POST" action="">
    <label for="username">Username</label>
    <input required type="text" id="username" name="username" />

    <label for="password">Password</label>
    <input required type="password" id="password" name="password" />

    <label for="role">Role</label>
    <select id="role" name="role" required>
        <option value="">Select Role</option>
        <option value="admin">Admin</option>
        <option value="staff">Staff</option>
        <option value="user">User</option>
    </select>

    <button type="submit" name="add_user">Add User</button>
</form>

<footer>
    <div class="container">
        <p style="margin-bottom: 10px; font-size: 16px;">Quick Links</p>
        <div class="quick-links">
            <a href="manage_staff.php">👨‍🍳 Staff</a>
            <a href="manage_menu.php">📋 Menu</a>
            <a href="view_orders.php">🧾 Orders</a>
            <a href="manage_users.php">👥 Users</a>
            <a href="logout.php" class="logout">🚪 Logout</a>
        </div>
        <p>&copy; <?= date("Y") ?> Restaurant Admin Panel</p>
    </div>
</footer>

</body>
</html>
