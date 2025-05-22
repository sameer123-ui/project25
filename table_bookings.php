<?php
session_start();
include 'auth_check.php';
include 'db_connect.php';

if ($_SESSION['role'] !== 'staff') {
    header("Location: login.php");
    exit();
}

// Handle status update POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['booking_id'], $_POST['new_status'])) {
    $booking_id = intval($_POST['booking_id']);
    $new_status = $_POST['new_status'];

    $allowed_statuses = ['pending', 'confirmed', 'cancelled', 'completed'];
    if (in_array($new_status, $allowed_statuses)) {
        try {
            $stmtUpdate = $conn->prepare("UPDATE table_bookings SET status = ? WHERE id = ?");
            $stmtUpdate->execute([$new_status, $booking_id]);
            $success_message = "Booking status updated successfully.";
        } catch (PDOException $e) {
            $error_message = "Failed to update status: " . $e->getMessage();
        }
    } else {
        $error_message = "Invalid status selected.";
    }
}

try {
    $stmt = $conn->query("SELECT tb.id, tb.booking_date, tb.status, u.username AS customer_name, tb.table_number
                          FROM table_bookings tb
                          JOIN users u ON tb.user_id = u.id
                          ORDER BY tb.booking_date DESC");
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
        /* Your existing styles here */
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
        button.update-btn {
            background-color: #1abc9c;
            border: none;
            color: white;
            padding: 6px 12px;
            border-radius: 5px;
            cursor: pointer;
        }
        button.update-btn:hover {
            background-color: #16a085;
        }
        .message {
            margin-bottom: 20px;
            padding: 10px 15px;
            border-radius: 5px;
            font-weight: 600;
        }
        .success {
            background-color: #27ae60;
            color: white;
        }
        .error {
            background-color: #e74c3c;
            color: white;
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

    <?php if (!empty($success_message)): ?>
        <div class="message success"><?= htmlspecialchars($success_message) ?></div>
    <?php endif; ?>

    <?php if (!empty($error_message)): ?>
        <div class="message error"><?= htmlspecialchars($error_message) ?></div>
    <?php endif; ?>

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
                    <th>Status</th>
                    <th>Update Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($bookings as $booking): ?>
                    <tr>
                        <td><?= htmlspecialchars($booking['id']) ?></td>
                        <td><?= htmlspecialchars($booking['customer_name']) ?></td>
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
                        <td>
                            <form method="post" style="margin:0;">
                                <input type="hidden" name="booking_id" value="<?= $booking['id'] ?>" />
                                <select name="new_status" required>
                                    <?php
                                        $statuses = ['pending', 'confirmed', 'cancelled', 'completed'];
                                        foreach ($statuses as $s) {
                                            $selected = ($status === $s) ? 'selected' : '';
                                            echo "<option value=\"$s\" $selected>" . ucfirst($s) . "</option>";
                                        }
                                    ?>
                                </select>
                                <button type="submit" class="update-btn">Update</button>
                            </form>
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
