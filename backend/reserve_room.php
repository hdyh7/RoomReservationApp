<?php
session_start();

// Check if a valid session exists (either user or admin)
if (!isset($_SESSION['user_id']) && !isset($_SESSION['admin_logged_in'])) {
    echo json_encode(["success" => false, "message" => "You must be logged in to reserve a room."]);
    exit();
}

$host = "localhost";
$user = "root";
$pass = "";
$dbname = "RoomBookingDB";

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $room_id = $_POST['room_id'];
        $start_time = $_POST['start_time'];
        $end_time = $_POST['end_time'];
        $reason = $_POST['reason'];

        // Determine which user_id to use
        if (isset($_SESSION['admin_logged_in'])) {
            // Admin session: Get the user_id from the form
            $user_id = $_POST['user_id'];
        } else {
            // User session: Use the logged-in user's ID
            $user_id = $_SESSION['user_id'];
        }

        // Validate start and end times
        if (strtotime($start_time) >= strtotime($end_time)) {
            echo json_encode([
                "success" => false, 
                "message" => "Invalid dates: Start time must be earlier than end time."
            ]);
            exit();
        }

        // Check for overlapping bookings excluding cancelled ones
        $stmt = $conn->prepare("
            SELECT COUNT(*) FROM Bookings 
            WHERE room_id = :room_id 
              AND status != 'cancelled'
              AND (
                  (start_time < :end_time AND end_time > :start_time)
              )
        ");
        $stmt->bindParam(':room_id', $room_id);
        $stmt->bindParam(':start_time', $start_time);
        $stmt->bindParam(':end_time', $end_time);
        $stmt->execute();

        if ($stmt->fetchColumn() > 0) {
            echo json_encode([
                "success" => false, 
                "message" => "The room is not available during the selected time."
            ]);
            exit();
        }

        // Insert the booking into the database
        $stmt = $conn->prepare("
            INSERT INTO Bookings (room_id, user_id, start_time, end_time, reason, participants, status) 
            VALUES (:room_id, :user_id, :start_time, :end_time, :reason, :participants, 'pending')
        ");
        $stmt->bindParam(':room_id', $room_id);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':start_time', $start_time);
        $stmt->bindParam(':end_time', $end_time);
        $stmt->bindParam(':reason', $reason);
        $stmt->bindParam(':participants', $_POST['participants']);

        if ($stmt->execute()) {
            echo json_encode(["success" => true, "message" => "Room reserved successfully! (Pending approval)"]);
        } else {
            echo json_encode(["success" => false, "message" => "Failed to reserve the room."]);
        }
    }
} catch (PDOException $e) {
    echo json_encode(["success" => false, "message" => "Connection failed: " . $e->getMessage()]);
}
?>


