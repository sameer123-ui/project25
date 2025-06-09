<?php
session_start();

$role = $_SESSION['role'] ?? null;

// Destroy session and unset all variables
session_unset();
session_destroy();

if ($role === 'admin' || $role === 'staff') {
    header("Location: admin_staff_login.php");
} else {
    // Default redirect for users or guests
    header("Location: user_login.php");
}
exit();
