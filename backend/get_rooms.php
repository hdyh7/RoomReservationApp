<?php
// backend/get_rooms.php

// Database connection settings
$host = "localhost";
$user = "root";  // Default WAMP username
$pass = "";      // Leave blank for default WAMP installation
$dbname = "RoomBookingDB";  // Ensure this matches your database

try {
    // Connect to the database
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Fetch the room data from the database
    $stmt = $conn->prepare("SELECT room_id, room_name, room_status FROM rooms");
    $stmt->execute();
    $rooms = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Return room data as JSON
    echo json_encode($rooms);
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
