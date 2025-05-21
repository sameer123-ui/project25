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

if ($orderId) {
    try {
        // Assign order only if currently unassigned
        $stmt = $conn->prepare("UPDATE orders SET assigned_staff_id = :staff_id WHERE id = :order_id AND assigned_staff_id IS NULL");
        $stmt->execute(['staff_id' => $staffId, 'order_id' => $orderId]);
    } catch (PDOException $e) {
        // Handle error or log
    }
}

header("Location: assigned_orders.php");
exit();
