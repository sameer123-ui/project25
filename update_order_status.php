<?php
session_start();
include 'auth_check.php';  // Or whatever file you use for session validation

// Check if user is logged in and role is staff
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'staff') {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    include 'db_connect.php';

    $orderId = $_POST['order_id'] ?? null;
    $newStatus = $_POST['status'] ?? null;

    if ($orderId && $newStatus) {
        try {
            $stmt = $conn->prepare("UPDATE orders SET status = :status WHERE id = :id AND assigned_staff_id = :staff_id");
            $stmt->execute([
                ':status' => $newStatus,
                ':id' => $orderId,
                ':staff_id' => $_SESSION['user_id']
            ]);
        } catch (PDOException $e) {
            die("Database error: " . $e->getMessage());
        }
    }
    // After updating, redirect back to the orders page
    header("Location: view_orders.php");
    exit();
}

// If accessed without POST, redirect away
header("Location: view_orders.php");
exit();
