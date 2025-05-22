<?php
session_start();
include 'auth_check.php'; // ensure only authorized users (like admin) can delete
include 'db_connect.php';

if ($_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['id'])) {
    die("Staff ID not provided.");
}

$staff_id = intval($_GET['id']);

// First, verify the user exists and is a staff
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ? AND role = 'staff'");
$stmt->execute([$staff_id]);
$staff = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$staff) {
    // Redirect with error message
    header("Location: staff_list.php?msg=Staff+not+found");
    exit();
}

// Proceed to delete
$deleteStmt = $conn->prepare("DELETE FROM users WHERE id = ? AND role = 'staff'");
if ($deleteStmt->execute([$staff_id])) {
    // Redirect with success message
    header("Location: staff_list.php?msg=Staff+deleted+successfully");
    exit();
} else {
    // Redirect with error message
    header("Location: staff_list.php?msg=Error+deleting+staff");
    exit();
}
