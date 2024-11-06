<?php
// backend/fetch_rooms.php
require 'connect.php'; // Make sure connect.php has the PDO connection

try {
    // Prepare and execute SQL query to fetch room data
    $stmt = $conn->prepare("SELECT id, room_name FROM rooms");
    $stmt->execute();

    // Fetch all rooms as an associative array
    $rooms = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Encode data in JSON format and send to frontend
    echo json_encode($rooms);

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
