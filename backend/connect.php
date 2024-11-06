<?php
// backend/connect.php
$host = "localhost";
$user = "root";  // Default WAMP MySQL username
$pass = "";      // Default WAMP MySQL password is blank
$dbname = "RoomBookingDB";  // Assuming the same database name

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
?>