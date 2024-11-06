<?php
header('Content-Type: application/json'); // Set the content type to JSON

$host = 'localhost';
$db = 'RoomBookingDB';
$user = 'root';
$pass = '';

// Establish database connection
$conn = new mysqli($host, $user, $pass, $db);

// Check for connection errors
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed.']);
    exit();
}

// Retrieve POST data
$booking_id = isset($_POST['booking_id']) ? (int)$_POST['booking_id'] : null; // Cast to int for security
$status = isset($_POST['status']) ? $_POST['status'] : null;

// Validate inputs
if ($booking_id === null || $status === null) {
    echo json_encode(['success' => false, 'message' => 'Invalid input.']);
    exit();
}

// Prepare the update query
$query = "UPDATE Bookings SET status = ? WHERE booking_id = ?";
$stmt = $conn->prepare($query);

// Check for statement preparation errors
if ($stmt === false) {
    echo json_encode(['success' => false, 'message' => 'Failed to prepare statement.']);
    exit();
}

// Bind parameters and execute the statement
$stmt->bind_param('si', $status, $booking_id);
if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Booking status updated successfully.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to update booking status.']);
}

// Clean up
$stmt->close();
$conn->close();
?>



