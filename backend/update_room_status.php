<?php
session_start();

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    echo json_encode(["success" => false, "message" => "You must be logged in as an admin."]);
    exit();
}

$host = 'localhost';
$db = 'RoomBookingDB';
$user = 'root';
$pass = '';

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $room_id = $_POST['room_id'];
    $room_status = $_POST['room_status'];

    // Validate status
    if (!in_array($room_status, ['available', 'unavailable', 'maintenance'])) {
        echo json_encode(["success" => false, "message" => "Invalid status value."]);
        exit();
    }

    // Update room status in the database
    $stmt = $conn->prepare("UPDATE Rooms SET room_status = ? WHERE room_id = ?");
    $stmt->bind_param("si", $room_status, $room_id);

    if ($stmt->execute()) {
        echo json_encode(["success" => true, "message" => "Room status updated successfully."]);
    } else {
        echo json_encode(["success" => false, "message" => "Error updating room status."]);
    }

    $stmt->close();
}

$conn->close();
?>
