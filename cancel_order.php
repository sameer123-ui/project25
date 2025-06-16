<?php
session_start();
include 'auth_check.php';
include 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id'])) {
    $orderId = $_POST['order_id'];
    $userId = $_SESSION['user_id'];

    try {
        // Check if order belongs to user and can be cancelled
        $stmt = $conn->prepare("SELECT status FROM orders WHERE id = :id AND user_id = :uid");
        $stmt->execute([':id' => $orderId, ':uid' => $userId]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$order) {
            die("Unauthorized or order not found.");
        }

        if (in_array(strtolower($order['status']), ['completed', 'delivered', 'cancelled'])) {
            die("This order cannot be cancelled.");
        }

        // Update order status to "Cancelled"
        $update = $conn->prepare("UPDATE orders SET status = 'Cancelled' WHERE id = :id");
        $update->execute([':id' => $orderId]);

        header("Location: my_orders.php?cancelled=1");
        exit;
    } catch (PDOException $e) {
        die("Error: " . $e->getMessage());
    }
} else {
    die("Invalid request.");
}
