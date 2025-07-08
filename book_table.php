<?php
session_start();
include 'auth_check.php';
include 'db_connect.php';

$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    header("Location: login.php");
    exit();
}

$error = "";
$success = "";
$booking_date = "";
$people_count = 1;

// Define your tables with capacities (could be fetched from DB in real app)
$tables = [
    ['table_number' => 1, 'capacity' => 2],
    ['table_number' => 2, 'capacity' => 2],
    ['table_number' => 3, 'capacity' => 4],
    ['table_number' => 4, 'capacity' => 4],
    ['table_number' => 5, 'capacity' => 6],
    ['table_number' => 6, 'capacity' => 6],
    ['table_number' => 7, 'capacity' => 8],
    ['table_number' => 8, 'capacity' => 8],
    ['table_number' => 9, 'capacity' => 10],
    ['table_number' => 10, 'capacity' => 12],
];

// Check if a table is free at the given booking date/time
function isTableFree($tableNumber, $bookingDate, $conn) {
    $stmt = $conn->prepare("SELECT COUNT(*) FROM table_bookings WHERE table_number = ? AND booking_date = ? AND status IN ('pending', 'confirmed')");
    $stmt->execute([$tableNumber, $bookingDate]);
    return $stmt->fetchColumn() == 0;
}

// Assign best-fit table (smallest capacity that fits people and is free)
function assignTable($tables, $peopleCount, $bookingDate, $conn) {
    // Sort tables ascending by capacity to pick smallest fitting
    usort($tables, fn($a, $b) => $a['capacity'] <=> $b['capacity']);

    foreach ($tables as $table) {
        if ($table['capacity'] >= $peopleCount && isTableFree($table['table_number'], $bookingDate, $conn)) {
            return $table['table_number'];
        }
    }
    return null; // No suitable table found
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['book_table'])) {
    $booking_date = trim($_POST['booking_date'] ?? '');
    $people_count = intval($_POST['people_count'] ?? 1);

    // Validate inputs
    if (!$booking_date) {
        $error = "Please select a booking date and time.";
    } elseif ($people_count <= 0) {
        $error = "Number of people must be at least 1.";
    } else {
        try {
            // Check booking date is not in the past
            $now = new DateTime();
            $bookingDT = new DateTime($booking_date);
            if ($bookingDT < $now) {
                $error = "Booking date and time cannot be in the past.";
            } else {
                $assigned_table = assignTable($tables, $people_count, $booking_date, $conn);
                if ($assigned_table !== null) {
                    $stmt = $conn->prepare("INSERT INTO table_bookings (user_id, table_number, booking_date, status, people_count) VALUES (?, ?, ?, 'pending', ?)");
                    if ($stmt->execute([$user_id, $assigned_table, $booking_date, $people_count])) {
                        $success = "Table #$assigned_table (Capacity: $people_count) booked successfully! Await confirmation.";
                        // Clear form values
                        $booking_date = "";
                        $people_count = 1;
                    } else {
                        $error = "Failed to book table. Please try again later.";
                    }
                } else {
                    $error = "Sorry, no available tables for the selected time and party size.";
                }
            }
        } catch (Exception $e) {
            $error = "Error: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Book a Table</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet" />
<style>
    /* Keep your existing styles plus some small cleanups */
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
        max-width: 450px;
        margin: 100px auto 40px;
        background-color: white;
        padding: 30px;
        border-radius: 12px;
        box-shadow: 0 6px 20px rgba(0,0,0,0.1);
    }
    h2 {
        color: #2c3e50;
        margin-bottom: 25px;
        text-align: center;
    }
    label {
        display: block;
        margin-bottom: 8px;
        font-weight: 600;
        color: #2c3e50;
    }
    input[type="datetime-local"],
    input[type="number"] {
        width: 100%;
        padding: 10px;
        margin-bottom: 20px;
        border-radius: 6px;
        border: 1px solid #ccc;
        font-size: 16px;
        box-sizing: border-box;
    }
    button {
        width: 100%;
        background-color: #1abc9c;
        color: white;
        font-size: 18px;
        padding: 12px;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        font-weight: 600;
        transition: background-color 0.3s ease;
    }
    button:hover {
        background-color: #16a085;
    }
    .message {
        margin-bottom: 15px;
        font-weight: 600;
        text-align: center;
    }
    .error {
        color: #e74c3c;
    }
    .success {
        color: #27ae60;
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
        <li><a href="feedback.php">Feedback</a></li>
        <li><a href="profile.php">Manage Profile</a></li>
        <li><a class="logout" href="logout.php">Logout</a></li>
    </ul>
</div>

<div class="container">
    <h2>Book a Table</h2>

    <?php if ($error): ?>
        <div class="message error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div class="message success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <form method="post" action="">
        <label for="booking_date">Booking Date & Time</label>
        <input
            type="datetime-local"
            id="booking_date"
            name="booking_date"
            value="<?= htmlspecialchars($booking_date) ?>"
            required
        />

        <label for="people_count">Number of People</label>
        <input
            type="number"
            id="people_count"
            name="people_count"
            value="<?= htmlspecialchars($people_count) ?>"
            min="1"
            max="20"
            required
        />

        <button type="submit" name="book_table">Book Now</button>
    </form>
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
