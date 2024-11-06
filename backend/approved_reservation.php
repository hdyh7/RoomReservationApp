<?php
session_start();
$host = 'localhost'; // Change if necessary
$db = 'RoomBookingDB'; // Your database name
$user = 'root'; // Your database username
$pass = ''; // Your database password

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get the posted data
$reservation_id = $_POST['reservation_id'];
$status = $_POST['status'];

// Update reservation status
$sql = "UPDATE Reservations SET status = ? WHERE reservation_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("si", $status, $reservation_id);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Error updating reservation.']);
}

$stmt->close();
$conn->close();
?>
