<?php
$host = 'localhost';
$db = 'RoomBookingDB';
$user = 'root';
$pass = '';

try {
    $conn = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if (isset($_GET['room_id'])) {
        $room_id = $_GET['room_id'];
        $stmt = $conn->prepare("SELECT capacity FROM Rooms WHERE room_id = :room_id");
        $stmt->bindParam(':room_id', $room_id);
        $stmt->execute();

        $capacity = $stmt->fetchColumn();
        echo json_encode(['capacity' => $capacity]);
    }
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
