<?php
session_start();
include 'auth_check.php';
include 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$userId = $_SESSION['user_id'];

try {
    $stmt = $conn->prepare("SELECT id, booking_date, status, table_number 
                            FROM table_bookings 
                            WHERE user_id = :user_id 
                            ORDER BY booking_date DESC");
    $stmt->execute(['user_id' => $userId]);
    $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>My Table Bookings</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet" />
    <style>
        /* Navbar styles (same as user dashboard) */
        body {
            font-family: 'Inter', sans-serif;
            background: #f0f2f5;
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
        /* Main container to keep content aligned and padded */
        .container {
            max-width: 900px;
            margin: 100px auto 40px; /* leave space for fixed navbar */
            background: white;
            padding: 30px 40px;
            border-radius: 12px;
            box-shadow: 0 6px 20px rgba(0,0,0,0.1);
        }

        h2 {
            text-align: center;
            color: #2c3e50;
            margin-bottom: 30px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th, td {
            border: 1px solid #ccc;
            padding: 12px;
            text-align: center;
            font-weight: 500;
            font-size: 16px;
        }
        thead {
            background-color: #2980b9;
            color: white;
        }

        .status {
            padding: 5px 12px;
            border-radius: 6px;
            color: white;
            font-weight: 600;
            font-size: 14px;
            text-transform: capitalize;
        }
        .status.pending { background-color: #f39c12; }
        .status.confirmed { background-color: #27ae60; }
        .status.cancelled { background-color: #e74c3c; }
        .status.completed { background-color: #3498db; }

        /* Responsive adjustments */
        @media (max-width: 600px) {
            .container {
                margin: 120px 10px 20px;
                padding: 20px;
            }
            th, td {
                font-size: 14px;
                padding: 8px;
            }
        }
    </style>
</head>
<body>
    <div class="navbar">
        <h1>User Panel</h1>
        <ul>
            <li><a href="user_dashboard.php">Home</a></li>
            <li><a href="menu.php">View Menu</a></li>
            <li><a href="order_menu.php">Place an Order</a></li>
            <li><a href="my_orders.php">My Orders</a></li>
            <li><a href="order_history.php">Order History</a></li>
            <li><a href="book_table.php">Booking</a></li>
            <li><a href="my_bookings.php">My Bookings</a></li>
                 <li><a href="feedback.php">feedback</a></li>
            <li><a href="profile.php">Manage Profile</a></li>
            <li><a class="logout" href="logout.php">Logout</a></li>
        </ul>
    </div>

    <div class="container">
        <h2>My Table Bookings</h2>

        <?php if (empty($bookings)): ?>
            <p style="text-align:center; font-size:18px; color:#666;">You have no table bookings.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Booking ID</th>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Table Number</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($bookings as $booking): ?>
                        <tr>
                            <td><?= htmlspecialchars($booking['id']) ?></td>
                            <td><?= htmlspecialchars(date("Y-m-d", strtotime($booking['booking_date']))) ?></td>
                            <td><?= htmlspecialchars(date("h:i A", strtotime($booking['booking_date']))) ?></td>
                            <td><?= htmlspecialchars($booking['table_number'] ?? 'N/A') ?></td>
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
    </div>
        <footer style="background-color: #2c3e50; color: white; padding: 20px 0; text-align: center; margin-top: 400px;">
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
