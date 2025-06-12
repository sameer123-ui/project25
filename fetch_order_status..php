<?php
session_start();
include 'auth_check.php';
include 'db_connect.php';

$userId = $_SESSION['user_id'];

try {
    $stmt = $conn->prepare("SELECT id, status FROM orders WHERE user_id = :uid ORDER BY order_date DESC");
    $stmt->execute([':uid' => $userId]);
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

    header('Content-Type: application/json');
    echo json_encode($orders);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>
