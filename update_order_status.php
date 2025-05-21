<?php
session_start();
include 'auth_check.php';
include 'db_connect.php';

if ($_SESSION['role'] !== 'staff') {
    header("Location: login.php");
    exit();
}

$staffId = $_SESSION['user_id'];
$orderId = $_POST['order_id'] ?? null;
$status = $_POST['status'] ?? null;

$allowedStatuses = ['pending', 'preparing', 'completed', 'cancelled'];

if ($orderId && in_array($status, $allowedStatuses)) {
    try {
        // Update only orders assigned to this staff
        $stmt = $conn->prepare("UPDATE orders SET status = :status WHERE id = :order_id AND assigned_staff_id = :staff_id");
        $stmt->execute(['status' => $status, 'order_id' => $orderId, 'staff_id' => $staffId]);
    } catch (PDOException $e) {
        // Handle error or log
    }
}

header("Location: assigned_orders.php");
exit();
