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
$available_tables = [];
$booking_date = "";
$table_number = "";

$all_tables = range(1, 10);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['check_availability'])) {
        $booking_date = $_POST['booking_date'] ?? '';

        if (!$booking_date) {
            $error = "Please select a booking date and time.";
        } else {
            try {
                $stmt = $conn->prepare("SELECT table_number FROM table_bookings WHERE booking_date = :booking_date AND status IN ('pending', 'confirmed')");
                $stmt->execute(['booking_date' => $booking_date]);
                $booked_tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
                $available_tables = array_diff($all_tables, $booked_tables);

                if (empty($available_tables)) {
                    $error = "No tables available at the selected time. Please choose another time.";
                }
            } catch (PDOException $e) {
                $error = "Database error: " . $e->getMessage();
            }
        }
    } elseif (isset($_POST['book_table'])) {
        $booking_date = $_POST['booking_date'] ?? '';
        $table_number = intval($_POST['table_number'] ?? 0);

        if (!$booking_date || $table_number <= 0) {
            $error = "Please select both booking date/time and a table.";
        } else {
            try {
                $stmt = $conn->prepare("INSERT INTO table_bookings (user_id, table_number, booking_date, status) VALUES (?, ?, ?, ?)");
                if ($stmt->execute([$user_id, $table_number, $booking_date, 'pending'])) {
                    $success = "Table booked successfully! Await confirmation.";
                    $booking_date = "";
                    $table_number = "";
                    $available_tables = [];
                } else {
                    $error = "Failed to book table. Please try again.";
                }
            } catch (PDOException $e) {
                $error = "Database error: " . $e->getMessage();
            }
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
        position: fixed;
        width: 100%;
        top: 0;
        left: 0;
        z-index: 1000;
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
        margin: 100px auto 40px; /* add top margin to clear fixed navbar */
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
    select {
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

    <?php if (empty($available_tables)): ?>
    <form method="post" action="">
        <label for="booking_date">Booking Date & Time</label>
        <input
            type="datetime-local"
            id="booking_date"
            name="booking_date"
            value="<?= htmlspecialchars($booking_date) ?>"
            required
        />
        <button type="submit" name="check_availability">Check Available Tables</button>
    </form>
    <?php else: ?>
    <form method="post" action="">
        <input type="hidden" name="booking_date" value="<?= htmlspecialchars($booking_date) ?>" />
        <label for="table_number">Select Table</label>
        <select id="table_number" name="table_number" required>
            <option value="">-- Choose a Table --</option>
            <?php foreach ($available_tables as $table): ?>
                <option value="<?= $table ?>" <?= ($table == $table_number) ? 'selected' : '' ?>>Table <?= $table ?></option>
            <?php endforeach; ?>
        </select>
        <button type="submit" name="book_table">Book Now</button>
    </form>
    <?php endif; ?>
</div>

</body>
</html>
