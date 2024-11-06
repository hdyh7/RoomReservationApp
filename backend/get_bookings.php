<?php
require 'db_connection.php';

$room_id = $_POST['room_id'];

$query = "SELECT start_date, end_date, status FROM bookings WHERE room_id = ? AND status IN ('approved', 'pending', 'cancelled')";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $room_id);
$stmt->execute();
$result = $stmt->get_result();

$bookings = [];
while ($row = $result->fetch_assoc()) {
    $bookings[] = $row;
}
echo json_encode($bookings);
?>
