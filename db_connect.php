<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$servername = "localhost";  // or 127.0.0.1 for MySQL
$username = "root";         // default username for MySQL
$password = "";             // default password for MySQL (empty for XAMPP)
$dbname = "restaurant_db";  // replace with your database name

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    // set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // Do not output anything here
}
catch(PDOException $e) {
    echo "Connection failed: " . $e->getMessage(); // in case of failure, show the error message
}
?>
