<?php
session_start();

// Redirect if user is not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.html');
    exit();
}

// Database connection
$host = 'localhost';
$db = 'RoomBookingDB';
$user = 'root';
$pass = '';

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get booking ID and selected equipment IDs from the POST request
$booking_id = isset($_POST['booking_id']) ? $_POST['booking_id'] : null;
$equipment_ids = isset($_POST['equipment_ids']) ? $_POST['equipment_ids'] : [];

// Check if booking ID is valid
if (!$booking_id || empty($equipment_ids)) {
    echo "Invalid request. Please select at least one equipment item.";
    exit();
}

// Insert each equipment request into the EquipmentRequests table
foreach ($equipment_ids as $equipment_id) {
    $stmt = $conn->prepare("INSERT INTO EquipmentRequests (booking_id, equipment_id, request_status) VALUES (?, ?, 'pending')");
    $stmt->bind_param("ii", $booking_id, $equipment_id);
    $stmt->execute();
    $stmt->close();
}

// Close the connection
$conn->close();

echo "Equipment request submitted successfully.";
header("Location: user_bookings.php?message=Request submitted successfully");
exit();
?>
