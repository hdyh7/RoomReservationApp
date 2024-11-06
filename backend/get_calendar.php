<?php
$host = 'localhost';
$db = 'RoomBookingDB';
$user = 'root';
$pass = '';

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$query = "SELECT room_name, start_time, end_time FROM Bookings 
          INNER JOIN Rooms ON Bookings.room_id = Rooms.room_id";
$result = $conn->query($query);

$events = [];
while ($row = $result->fetch_assoc()) {
    $events[] = [
        'title' => $row['room_name'],
        'start' => $row['start_time'],
        'end' => $row['end_time']
    ];
}

$conn->close();
echo json_encode($events);
?>
