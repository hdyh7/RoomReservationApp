<?php
// backend/process_booking.php
include 'connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $room_id = $_POST['room_id'];
    $user_id = $_POST['user_id'];
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];

    // Check if the room is available for the given time
    $stmt = $conn->prepare("SELECT * FROM Bookings WHERE room_id = :room_id AND start_time < :end_time AND end_time > :start_time");
    $stmt->bindParam(':room_id', $room_id);
    $stmt->bindParam(':start_time', $start_time);
    $stmt->bindParam(':end_time', $end_time);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        // Room is unavailable
        echo json_encode(['success' => false, 'message' => 'The room is not available for the selected time.']);
    } else {
        // Room is available, book it
        $stmt = $conn->prepare("INSERT INTO Bookings (user_id, room_id, start_time, end_time) VALUES (:user_id, :room_id, :start_time, :end_time)");
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':room_id', $room_id);
        $stmt->bindParam(':start_time', $start_time);
        $stmt->bindParam(':end_time', $end_time);

        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Room booked successfully!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to book the room.']);
        }
    }
}
?>
