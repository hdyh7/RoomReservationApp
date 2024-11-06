<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $bookingId = $_POST['booking_id'];

    $conn = new mysqli('localhost', 'root', '', 'RoomBookingDB');
    if ($conn->connect_error) {
        echo json_encode(['success' => false, 'message' => 'Database connection failed.']);
        exit();
    }

    // Cancel the booking
    $query = "UPDATE Bookings SET status = 'cancelled' WHERE booking_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $bookingId);

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to cancel booking.']);
    }

    $stmt->close();
    $conn->close();
}
?>



