<?php
session_start();
include 'auth_check.php';
include 'db_connect.php';

if ($_SESSION['role'] !== 'staff') {
    header("Location: login.php");
    exit();
}

// Define table capacities (example: 10 tables, each seats 4)
$table_capacities = array_fill(1, 10, 4);

$success_message = '';
$error_message = '';

// Handle status update POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['booking_id'], $_POST['new_status'])) {
    $booking_id = intval($_POST['booking_id']);
    $new_status = $_POST['new_status'];

    $allowed_statuses = ['pending', 'confirmed', 'cancelled', 'completed'];
    if (in_array($new_status, $allowed_statuses)) {
        $stmt = $conn->prepare("UPDATE table_bookings SET status = ? WHERE id = ?");
        $stmt->execute([$new_status, $booking_id]);
        $success_message = "Booking #$booking_id status updated to " . ucfirst($new_status) . ".";
    } else {
        $error_message = "Invalid status selected.";
    }
}

// Initialize unassigned bookings array
$unassignedBookings = [];


// Handle seating optimization POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['optimize_seating'])) {
    // Fetch all pending or confirmed upcoming bookings (future bookings only)
    $stmt = $conn->prepare("SELECT id, people_count, status FROM table_bookings WHERE status IN ('pending', 'confirmed') AND booking_date >= NOW() ORDER BY booking_date ASC");
    $stmt->execute();
    $bookings_to_assign = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Reset table assignments for these bookings
    $booking_ids = array_column($bookings_to_assign, 'id');
    if (!empty($booking_ids)) {
        $placeholders = implode(',', array_fill(0, count($booking_ids), '?'));
        $resetStmt = $conn->prepare("UPDATE table_bookings SET table_number = NULL WHERE id IN ($placeholders)");
        $resetStmt->execute($booking_ids);
    }

    // Track table occupancy (table => assigned people count sum)
    $table_occupancy = array_fill_keys(array_keys($table_capacities), 0);

    // Assign tables using simple bin packing logic:
    foreach ($bookings_to_assign as $booking) {
        $people = $booking['people_count'];
        $assigned_table = null;

        // Try to find a table with enough free seats (capacity - occupancy >= people)
        foreach ($table_capacities as $table_num => $capacity) {
            $free_seats = $capacity - ($table_occupancy[$table_num] ?? 0);
            if ($free_seats >= $people) {
                $assigned_table = $table_num;
                break;
            }
        }

        if ($assigned_table !== null) {
            // If booking was pending, update status to confirmed, else keep existing status
            $new_status = ($booking['status'] === 'pending') ? 'confirmed' : $booking['status'];

            // Assign this booking to the table AND update status if needed
            $updateStmt = $conn->prepare("UPDATE table_bookings SET table_number = ?, status = ? WHERE id = ?");
            $updateStmt->execute([$assigned_table, $new_status, $booking['id']]);
            $table_occupancy[$assigned_table] += $people;
        }
        // If no table found, booking stays unassigned (table_number = NULL) and status unchanged
    }

    $success_message = "Seating optimization completed. Tables assigned and statuses updated where applicable.";

    // Fetch unassigned bookings (pending or confirmed with no table assigned)
    $stmtUnassigned = $conn->prepare("SELECT tb.id, tb.booking_date, tb.people_count, u.username AS customer_name 
                                      FROM table_bookings tb 
                                      JOIN users u ON tb.user_id = u.id
                                      WHERE tb.table_number IS NULL 
                                        AND tb.status IN ('pending', 'confirmed') 
                                        AND tb.booking_date >= NOW()
                                      ORDER BY tb.booking_date ASC");
    $stmtUnassigned->execute();
    $unassignedBookings = $stmtUnassigned->fetchAll(PDO::FETCH_ASSOC);
} else {
    // If not optimizing now, fetch unassigned bookings for display if you want (optional)
    $unassignedBookings = [];
}

// Fetch all bookings with user names
$stmt = $conn->query("SELECT tb.id, tb.booking_date, tb.status, tb.people_count, u.username AS customer_name, tb.table_number
                      FROM table_bookings tb
                      JOIN users u ON tb.user_id = u.id
                      ORDER BY tb.booking_date DESC");
$bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Staff Panel - Table Bookings</title>
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
        max-width: 1100px;
        margin: 100px auto 40px;
        background-color: white;
        padding: 30px;
        border-radius: 12px;
        box-shadow: 0 6px 20px rgba(0,0,0,0.1);
    }
    h2 {
        color: #34495e;
        margin-bottom: 25px;
        text-align: center;
    }
    table {
        width: 100%;
        border-collapse: collapse;
        font-size: 16px;
    }
    th, td {
        padding: 12px 15px;
        border: 1px solid #ddd;
        text-align: center;
        vertical-align: middle;
    }
    th {
        background-color: #1abc9c;
        color: white;
    }
    tr:nth-child(even) {
        background-color: #f9f9f9;
    }
    .status {
        padding: 6px 12px;
        border-radius: 20px;
        font-weight: 700;
        text-transform: capitalize;
        display: inline-block;
        min-width: 90px;
    }
    .status.pending {
        background-color: #f39c12;
        color: white;
    }
    .status.confirmed {
        background-color: #27ae60;
        color: white;
    }
    .status.cancelled {
        background-color: #e74c3c;
        color: white;
    }
    .status.completed {
        background-color: #2980b9;
        color: white;
    }
    select {
        padding: 6px 10px;
        font-size: 14px;
        border-radius: 6px;
        border: 1px solid #ccc;
        cursor: pointer;
    }
    .update-btn {
        background-color: #1abc9c;
        border: none;
        padding: 6px 12px;
        border-radius: 6px;
        color: white;
        font-weight: 600;
        cursor: pointer;
        margin-left: 6px;
        transition: background-color 0.3s ease;
    }
    .update-btn:hover {
        background-color: #16a085;
    }
    .optimize-btn {
        background-color: #2980b9;
        color: white;
        font-weight: 700;
        padding: 12px 25px;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        font-size: 16px;
        margin-bottom: 20px;
        transition: background-color 0.3s ease;
        display: block;
        margin-left: auto;
        margin-right: auto;
    }
    .optimize-btn:hover {
        background-color: #2471a3;
    }
    .message {
        text-align: center;
        font-weight: 700;
        margin-bottom: 20px;
        font-size: 18px;
    }
    .success {
        color: #27ae60;
    }
    .error {
        color: #842029;
        background-color: #f8d7da;
        padding: 15px;
        border-radius: 8px;
        max-width: 800px;
        margin: 20px auto;
        text-align: left;
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
    <h1>Staff Panel</h1>
    <ul>
        <li><a href="staff_dashboard.php">Home</a></li>
        <li><a href="staff_orders.php">My Orders</a></li>
        <li><a href="assigned_orders.php">Assigned Orders</a></li>
        <li><a href="table_bookings.php">Table Bookings</a></li>
        <li><a href="view_feedback2.php">See feedback</a></li>
        <li><a class="logout" href="logout.php">Logout</a></li>
    </ul>
</div>

<div class="container">
    <h2>Table Bookings Management</h2>

    <?php if (!empty($success_message)): ?>
        <div class="message success"><?= htmlspecialchars($success_message) ?></div>
    <?php endif; ?>

    <?php if (!empty($error_message)): ?>
        <div class="message error"><?= htmlspecialchars($error_message) ?></div>
    <?php endif; ?>

    <form method="post" onsubmit="return confirm('Are you sure you want to optimize seating assignments? This will reassign tables for pending and confirmed bookings.')">
        <button type="submit" name="optimize_seating" class="optimize-btn">Optimize Seating</button>
    </form>

    <?php if (!empty($unassignedBookings)): ?>
        <div class="message error">
            <strong>⚠️ The following bookings could NOT be assigned tables automatically and need manual attention:</strong>
            <ul>
                <?php foreach ($unassignedBookings as $ub): ?>
                    <li>
                        Booking #<?= htmlspecialchars($ub['id']) ?> for <?= htmlspecialchars($ub['customer_name']) ?> on <?= date("Y-m-d H:i", strtotime($ub['booking_date'])) ?> (<?= htmlspecialchars($ub['people_count']) ?> people)
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <table>
        <thead>
            <tr>
                <th>Booking ID</th>
                <th>Customer</th>
                <th>Date</th>
                <th>Time</th>
                <th>Table Number</th>
                <th>People</th>
                <th>Status</th>
                <th>Update Status</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($bookings)): ?>
                <tr><td colspan="8">No bookings found.</td></tr>
            <?php else: ?>
                <?php foreach ($bookings as $booking): ?>
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
                    <tr>
                        <td><?= htmlspecialchars($booking['id']) ?></td>
                        <td><?= htmlspecialchars($booking['customer_name']) ?></td>
                        <td><?= htmlspecialchars(date("Y-m-d", strtotime($booking['booking_date']))) ?></td>
                        <td><?= htmlspecialchars(date("h:i A", strtotime($booking['booking_date']))) ?></td>
                        <td><?= htmlspecialchars($booking['table_number'] ?? 'Unassigned') ?></td>
                        <td><?= htmlspecialchars($booking['people_count']) ?></td>
                        <td><span class="status <?= $statusClass ?>"><?= ucfirst($status) ?></span></td>
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
            <?php endif; ?>
        </tbody>
    </table>
</div>

  <footer style="background-color: #2c3e50; color: white; padding: 20px 0; text-align: center; margin-top: 400px;">
    <div style="max-width: 1100px; margin: auto;">
        <p style="margin-bottom: 10px; font-size: 16px;">Quick Links</p>
        <div style="display: flex; justify-content: center; flex-wrap: wrap; gap: 20px;">
           
            <a href="staff_orders.php" style="color: #ecf0f1; text-decoration: none;">🧾 Orders</a>

            <a href="logout.php" style="color: #e74c3c; text-decoration: none;">🚪 Logout</a>
        </div>
        <p style="margin-top: 15px; font-size: 14px; color: #bdc3c7;">&copy; <?= date("Y") ?> Restaurant Staff Panel</p>
    </div>
</footer>

</body>
</html>
