<?php
session_start();
include 'auth_check.php';
include 'db_connect.php';

if ($_SESSION['role'] !== 'staff') {
    header("Location: login.php");
    exit();
}

try {
    $stmt = $conn->query("SELECT tb.id, tb.booking_date, tb.booking_time, tb.num_guests, tb.status, u.username AS customer_name, tb.table_number
                          FROM table_bookings tb
                          JOIN users u ON tb.user_id = u.id
                          ORDER BY tb.booking_date DESC, tb.booking_time DESC");
    $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Table Bookings - Staff Panel</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f0f2f5;
            margin: 0;
            padding: 0;
        }
        nav {
            background: linear-gradient(to right, #2c3e50, #34495e);
            padding: 15px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            color: white;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }
        nav .logo {
            font-size: 24px;
            font-weight: 700;
        }
        nav ul {
            list-style: none;
            margin: 0;
            padding: 0;
            display: flex;
            gap: 20px;
        }
        nav ul li {
            display: inline;
        }
        nav ul li a {
            color: white;
            text-decoration: none;
            font-weight: 600;
            padding: 6px 14px;
            border-radius: 5px;
            transition: background-color 0.3s ease;
        }
        nav ul li a:hover {
            background-color: #1abc9c;
        }

        main {
            max-width: 1100px;
            margin: 40px auto;
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 6px 20px rgba(0,0,0,0.1);
        }
        h2 {
            color: #2c3e50;
            margin-bottom: 25px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 16px;
        }
        thead {
            background-color: #2980b9;
            color: white;
        }
        th, td {
            padding: 12px 15px;
            border: 1px solid #ddd;
            text-align: left;
        }
        tbody tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .status {
            padding: 6px 10px;
            border-radius: 6px;
            font-weight: 600;
            display: inline-block;
            color: white;
        }
        .status.pending {
            background-color: #f39c12;
        }
        .status.confirmed {
            background-color: #27ae60;
        }
        .status.cancelled {
            background-color: #e74c3c;
        }
        .status.completed {
            background-color: #3498db;
        }
        footer {
            background-color: #2c3e50;
            color: white;
            padding: 20px 0;
            text-align: center;
            margin-top: 80px;
        }
        footer .container {
            max-width: 1100px;
            margin: auto;
        }
    </style>
</head>
<body>

<nav>
    <div class="logo">Staff Panel</div>
    <ul>
        <li><a href="staff_dashboard.php">Home</a></li>
        <li><a href="staff_orders.php">My Orders</a></li>
        <li><a href="assigned_orders.php">Assigned Orders</a></li>
        <li><a href="table_bookings.php" style="background-color:#1abc9c;">Table Bookings</a></li>
        <li><a href="logout.php" class="logout">Logout</a></li>
    </ul>
</nav>

<main>
    <h2>Table Bookings</h2>

    <?php if (empty($bookings)): ?>
        <p>No table bookings found.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Booking ID</th>
                    <th>Customer</th>
                    <th>Date</th>
                    <th>Time</th>
                    <th>Table Number</th>
                    <th>Guests</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($bookings as $booking): ?>
                    <tr>
                        <td><?= htmlspecialchars($booking['id']) ?></td>
                        <td><?= htmlspecialchars($booking['customer_name']) ?></td>
                        <td><?= htmlspecialchars($booking['booking_date']) ?></td>
                        <td><?= htmlspecialchars(date("h:i A", strtotime($booking['booking_time']))) ?></td>
                        <td><?= htmlspecialchars($booking['table_number'] ?? 'N/A') ?></td>
                        <td><?= htmlspecialchars($booking['num_guests']) ?></td>
                        <td>
                            <?php
                                $status = strtolower($booking['status']);
                                $statusClass = match ($status) {
                                    'pending' => 'pending',
                                    'confirmed' => 'confirmed',
                                    'cancelled' => 'cancelled',
                                    'completed' => 'completed',
                                    default => 'pending',
                                };
                            ?>
                            <span class="status <?= $statusClass ?>"><?= ucfirst($status) ?></span>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</main>

<footer>
    <div class="container">
        &copy; <?= date('Y') ?> Restaurant Staff Panel
    </div>
</footer>

</body>
</html>
