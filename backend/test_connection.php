<?php
// backend/test_connection.php

// Database connection settings
$host = "localhost";
$user = "root";  // Default username for WAMP
$pass = "";      // Leave this blank if you're using the default WAMP installation
$dbname = "RoomBookingDB";  // Change this to your actual database name

// Try to establish a connection
try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);
    // Set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Connection successful!";  // This message will appear if the connection is successful
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();  // Error message if the connection fails
}
?>
