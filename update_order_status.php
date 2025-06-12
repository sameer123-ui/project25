<?php
session_start();
include 'auth_check.php';
include 'db_connect.php';

if ($_SESSION['role'] !== 'staff') {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $staffId = $_SESSION['user_id'];
    $orderId = filter_input(INPUT_POST, 'order_id', FILTER_VALIDATE_INT);
    $newStatus = filter_input(INPUT_POST, 'status', FILTER_SANITIZE_STRING);

    $validStatuses = ['pending', 'preparing', 'completed', 'cancelled'];

    if (!$orderId || !in_array($newStatus, $validStatuses)) {
        die("Invalid order ID or status.");
    }

    try {
        // Verify the order belongs to this staff
        $stmt = $conn->prepare("SELECT assigned_staff_id FROM orders WHERE id = :order_id");
        $stmt->execute(['order_id' => $orderId]);
        $assignedStaffId = $stmt->fetchColumn();

        if ($assignedStaffId != $staffId) {
            die("You are not authorized to update this order.");
        }

        // Update the status
        $updateStmt = $conn->prepare("UPDATE orders SET status = :status WHERE id = :order_id");
        $updateStmt->execute([
            'status' => $newStatus,
            'order_id' => $orderId
        ]);

        header("Location: staff_orders.php?msg=" . urlencode("Order #$orderId status updated to " . ucfirst($newStatus) . "."));
        exit();
    } catch (PDOException $e) {
        die("Database error: " . $e->getMessage());
    }
} else {
    header("Location: staff_orders.php");
    exit();
}
