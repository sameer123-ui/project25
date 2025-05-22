<?php
include 'db_connect.php';

// Fetch all staff users
$stmt = $conn->prepare("SELECT * FROM users WHERE role = 'staff'");
$stmt->execute();
$staffList = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Staff List</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
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
            max-width: 1000px;
            margin: 50px auto;
            background-color: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.1);
        }

        h2 {
            text-align: center;
            color: #2c3e50;
            margin-bottom: 30px;
        }

        .add-button {
            display: block;
            width: fit-content;
            margin: 0 auto 30px;
            padding: 10px 20px;
            background-color: #1abc9c;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: bold;
            transition: background 0.3s ease;
        }

        .add-button:hover {
            background-color: #16a085;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 16px;
        }

        th, td {
            padding: 12px 16px;
            border-bottom: 1px solid #ddd;
            text-align: center;
        }

        th {
            background-color: #ecf0f1;
            color: #34495e;
        }

        tr:hover {
            background-color: #f9f9f9;
        }

        .button {
            padding: 8px 14px;
            text-decoration: none;
            color: white;
            border-radius: 6px;
            font-weight: bold;
            transition: 0.3s ease;
        }

        .button.edit {
            background-color: #3498db;
        }

        .button.edit:hover {
            background-color: #2980b9;
        }

        .button.delete {
            background-color: #e74c3c;
        }

        .button.delete:hover {
            background-color: #c0392b;
        }

        @media (max-width: 768px) {
            table {
                font-size: 14px;
            }

            .button {
                padding: 6px 10px;
            }
        }
    </style>
</head>
<body>
     <div class="navbar">
    <h1>Admin Dashboard</h1>
    <ul>
        <li> <a href="admin_dashboard.php">Home</a></li>
        <li><a href="manage_staff.php">Staff</a></li>
        <li><a href="manage_menu.php">Menu</a></li>
        <li><a href="view_orders.php">Orders</a></li>
        <li><a href="manage_users.php">Users</a></li>
        <li><a class="logout" href="logout.php">Logout</a></li>
    </ul>
</div>


<div class="container">
    <h2>Staff List</h2>

    <a href="add_staff.php" class="add-button">➕ Add New Staff</a>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Username</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($staffList) > 0): ?>
                <?php foreach ($staffList as $staff): ?>
                    <tr>
                        <td><?= htmlspecialchars($staff['id']) ?></td>
                        <td><?= htmlspecialchars($staff['username']) ?></td>
                        <td>
                            <a href="edit_staff.php?id=<?= $staff['id'] ?>" class="button edit">Edit</a>
                            <a href="delete_staff.php?id=<?= $staff['id'] ?>" class="button delete" onclick="return confirm('Are you sure you want to delete this staff member?');">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="3">No staff members found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

</body>
</html>
