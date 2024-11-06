<?php
// Database credentials
$host = 'localhost';
$db = 'RoomBookingDB';
$user = 'root';
$pass = '';

// Establish database connection
$conn = new mysqli($host, $user, $pass, $db);

// If connection fails, return an error message
if ($conn->connect_error) {
    http_response_code(500); // Internal Server Error
    echo json_encode(['error' => 'Database connection failed.']);
    exit();
}

// Query to fetch pending bookings along with user details
$query = "
    SELECT B.booking_id, B.room_id, B.start_time, B.end_time, B.reason, 
           U.name, U.department, R.room_name
    FROM Bookings B
    JOIN Users U ON B.user_id = U.user_id
    JOIN Rooms R ON B.room_id = R.room_id
    WHERE B.status = 'pending'
";

$result = $conn->query($query);
$bookings = [];

// If results are returned, store them in the $bookings array
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $bookings[] = $row;
    }
}

// Close the connection
$conn->close();

// Render the bookings table as HTML
if (count($bookings) > 0) {
    echo '<table class="table">
            <thead>
                <tr>
                    <th>Booking ID</th>
                    <th>Name</th>
                    <th>Department</th>
                    <th>Room</th>
                    <th>Start Time</th>
                    <th>End Time</th>
                    <th>Purpose</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>';
    foreach ($bookings as $booking) {
        echo '<tr>
                <td>' . htmlspecialchars($booking['booking_id']) . '</td>
                <td>' . htmlspecialchars($booking['name']) . '</td>
                <td>' . htmlspecialchars($booking['department']) . '</td>
                <td>' . htmlspecialchars($booking['room_name']) . '</td>
                <td>' . htmlspecialchars($booking['start_time']) . '</td>
                <td>' . htmlspecialchars($booking['end_time']) . '</td>
                <td>' . htmlspecialchars($booking['reason']) . '</td>
                <td>
                    <button class="btn btn-success approve-btn" data-booking-id="' . htmlspecialchars($booking['booking_id']) . '">Approve</button>
                    <button class="btn btn-danger reject-btn" data-booking-id="' . htmlspecialchars($booking['booking_id']) . '">Reject</button>
                </td>
            </tr>';
    }
    echo '</tbody></table>';
} else {
    echo '<p>No pending bookings found.</p>';
}
?>
