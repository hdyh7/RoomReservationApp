<?php
session_start();

// Check if the user is logged in
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

// Get room names and statuses for dropdowns
$roomQuery = "SELECT DISTINCT room_name FROM Rooms";
$roomResult = $conn->query($roomQuery);

$statusOptions = ['pending', 'approved', 'rejected', 'cancelled'];

// Fetch the user’s bookings with the latest on top
$user_id = $_SESSION['user_id'];
$searchMonth = $_GET['month'] ?? '';
$searchRoom = $_GET['room_name'] ?? '';
$searchStatus = $_GET['status'] ?? '';

$query = "
    SELECT B.booking_id, B.room_id, B.start_time, B.end_time, B.reason, B.status, 
           R.room_name
    FROM Bookings B
    JOIN Rooms R ON B.room_id = R.room_id
    WHERE B.user_id = ?
";

$params = [$user_id];
$types = "i";

// Add search filters to the query
if (!empty($searchMonth)) {
    $query .= " AND MONTH(B.start_time) = ?";
    $params[] = $searchMonth;
    $types .= "i";
}
if (!empty($searchRoom)) {
    $query .= " AND R.room_name = ?";
    $params[] = $searchRoom;
    $types .= "s";
}
if (!empty($searchStatus)) {
    $query .= " AND B.status = ?";
    $params[] = $searchStatus;
    $types .= "s";
}

$query .= " ORDER BY B.booking_id DESC";

$stmt = $conn->prepare($query);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Bookings</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<nav class="navbar bg-light p-3">
        <h1 class="me-auto">Booking History</h1>
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
                <li><a href="main.php" class="btn btn-outline-primary w-100 mb-2">Add New Booking</a></li>
                <li><button id="logoutButton" class="btn btn-outline-danger w-100">Logout</button></li>
            </ul>
        </div>
    </div>
    <div class="container mt-4">
        <h1>Your Bookings</h1>

        <form class="row g-3 mb-4" method="GET" action="user_bookings.php">
            <div class="col-md-4">
                <label for="month" class="form-label">Search by Month</label>
                <input type="number" class="form-control" id="month" name="month" 
                       min="1" max="12" placeholder="Enter month (1-12)">
            </div>
            <div class="col-md-4">
                <label for="room_name" class="form-label">Search by Room</label>
                <select class="form-select" id="room_name" name="room_name">
                    <option value="">All Rooms</option>
                    <?php while ($room = $roomResult->fetch_assoc()): ?>
                        <option value="<?= htmlspecialchars($room['room_name']); ?>">
                            <?= htmlspecialchars($room['room_name']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="col-md-4">
                <label for="status" class="form-label">Search by Status</label>
                <select class="form-select" id="status" name="status">
                    <option value="">All Statuses</option>
                    <?php foreach ($statusOptions as $status): ?>
                        <option value="<?= $status; ?>"><?= ucfirst($status); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-12 text-end">
                <button type="submit" class="btn btn-primary">Find</button>
            </div>
        </form>

        <table class="table">
            <thead>
                <tr>
                    <th>Booking ID</th>
                    <th>Room</th>
                    <th>Start Time</th>
                    <th>End Time</th>
                    <th>Purpose</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['booking_id']); ?></td>
                        <td><?= htmlspecialchars($row['room_name']); ?></td>
                        <td><?= htmlspecialchars($row['start_time']); ?></td>
                        <td><?= htmlspecialchars($row['end_time']); ?></td>
                        <td><?= htmlspecialchars($row['reason']); ?></td>
                        <td><?= htmlspecialchars($row['status']); ?></td>
                        <td>
                            <?php if ($row['status'] == 'pending'): ?>
                                <button class="btn btn-danger cancel-button" 
                                        data-id="<?= $row['booking_id']; ?>">
                                    Cancel
                                </button>
                            <?php elseif ($row['status'] == 'approved'): ?>
                                <a href="equipment_borrowing.php?booking_id=<?= htmlspecialchars($row['booking_id']); ?>" 
                                class="btn btn-primary">
                                    Request Equipment
                                </a>
                            <?php else: ?>
                                     No Actions
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <button class="btn btn-warning mt-3" onclick="window.location.href='main.php'">Back to Form</button>
    </div>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        $(document).ready(function() {
            $('.cancel-button').click(function() {
                const bookingId = $(this).data('id');

                if (confirm('Are you sure you want to cancel this booking?')) {
                    $.ajax({
                        url: '/RoomReservationApp/backend/cancel_booking.php',
                        method: 'POST',
                        data: { booking_id: bookingId },
                        success: function(response) {
                            const result = JSON.parse(response);
                            if (result.success) {
                                alert('Booking cancelled successfully.');
                                location.reload();
                            } else {
                                alert('Failed to cancel booking: ' + result.message);
                            }
                        },
                        error: function() {
                            alert('Error cancelling the booking.');
                        }
                    });
                }
                
            });
            // Handle the logout button click
            $('#logoutButton').click(function () {
                    window.location.href = "/RoomReservationApp/backend/logout.php";
            });
        });
    </script>
</body>
</html>










