<?php
session_start();

// Check if the user is logged in; if not, redirect to the login page
if (!isset($_SESSION['user_id'])) {
    header('Location: login.html');
    exit();
}

// Check if booking_id is provided in the URL
if (!isset($_GET['booking_id'])) {
    echo "Booking ID not specified.";
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

// Get booking details
$booking_id = intval($_GET['booking_id']);
$query = "SELECT booking_id, room_name, start_time, end_time FROM Bookings JOIN Rooms ON Bookings.room_id = Rooms.room_id WHERE booking_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $booking_id);
$stmt->execute();
$result = $stmt->get_result();
$booking = $result->fetch_assoc();
$stmt->close();

// Fetch available equipment
$equipment_query = "SELECT * FROM Equipment";
$equipment_result = $conn->query($equipment_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Equipment Borrowing</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar bg-light p-3">
        <h1 class="me-auto"><strong>Request Equipment</strong></h1>
        <button class="btn btn-outline-info mt-3" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasSidebar" aria-controls="offcanvasSidebar">
            ☰ Menu
        </button>
    </nav>

    <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasSidebar" aria-labelledby="offcanvasSidebarLabel">
        <div class="offcanvas-header">
            <h5 class="offcanvas-title" id="offcanvasSidebarLabel">User Menu</h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body">
            <ul class="list-unstyled">
                <li><a href="index.html" class="btn btn-outline-success w-100 mb-2">Home</a></li>
                <li><a href="user_bookings.php" class="btn btn-outline-primary w-100 mb-2">My Bookings</a></li>
                <li><button id="logoutButton" class="btn btn-outline-danger w-100">Logout</button></li>
            </ul>
        </div>
    </div>

<div class="container mt-5">
    <h3>Equipment Borrowing for Booking ID: <?= htmlspecialchars($booking['booking_id']); ?></h3>
    <p>Room: <?= htmlspecialchars($booking['room_name']); ?></p>
    <p>Start Time: <?= htmlspecialchars($booking['start_time']); ?></p>
    <p>End Time: <?= htmlspecialchars($booking['end_time']); ?></p>
    
    <form action="submit_equipment_request.php" method="POST">
        <input type="hidden" name="booking_id" value="<?= htmlspecialchars($booking['booking_id']); ?>">
        
        <div class="mb-3">
            <label for="equipment" class="form-label">Select Equipment:</label>
            <?php while ($equipment = $equipment_result->fetch_assoc()): ?>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="equipment_ids[]" 
                           value="<?= htmlspecialchars($equipment['equipment_id']); ?>" id="equipment<?= $equipment['equipment_id']; ?>">
                    <label class="form-check-label" for="equipment<?= $equipment['equipment_id']; ?>">
                        <?= htmlspecialchars($equipment['name']); ?>
                    </label>
                </div>
            <?php endwhile; ?>
        </div>
        
        <button type="submit" class="btn btn-primary">Submit Equipment Request</button>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php $conn->close(); ?>
