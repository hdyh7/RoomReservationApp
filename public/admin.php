<?php
session_start();

// Check if admin is logged in; if not, redirect to login
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: admin_login.html');
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

// Fetch room names for dropdown
$roomsResult = $conn->query("SELECT room_name FROM Rooms");
$roomOptions = $roomsResult->fetch_all(MYSQLI_ASSOC);

// Fetch unique booking months for dropdown
$monthsResult = $conn->query("
    SELECT DISTINCT DATE_FORMAT(start_time, '%Y-%m') AS booking_month 
    FROM Bookings ORDER BY booking_month DESC
");
$monthOptions = $monthsResult->fetch_all(MYSQLI_ASSOC);

// Get selected room name and month from dropdowns
$selectedRoom = isset($_GET['room_name']) ? trim($_GET['room_name']) : '';
$selectedMonth = isset($_GET['booking_month']) ? trim($_GET['booking_month']) : '';

function getQuery($status, $selectedRoom, $selectedMonth) {
    $query = "
        SELECT B.*, U.name, U.department, R.room_name
        FROM Bookings B
        JOIN Users U ON B.user_id = U.user_id
        JOIN Rooms R ON B.room_id = R.room_id
        WHERE B.status = ?
    ";

    if (!empty($selectedRoom)) {
        $query .= " AND R.room_name = ?";
    }

    if (!empty($selectedMonth)) {
        $query .= " AND DATE_FORMAT(B.start_time, '%Y-%m') = ?";
    }

    $query .= " ORDER BY B.booking_id DESC"; // Order by latest booking

    return $query;
}

function fetchBookings($conn, $status, $selectedRoom, $selectedMonth) {
    $query = getQuery($status, $selectedRoom, $selectedMonth);
    $stmt = $conn->prepare($query);

    if (!empty($selectedRoom) && !empty($selectedMonth)) {
        $stmt->bind_param("sss", $status, $selectedRoom, $selectedMonth);
    } elseif (!empty($selectedRoom)) {
        $stmt->bind_param("ss", $status, $selectedRoom);
    } elseif (!empty($selectedMonth)) {
        $stmt->bind_param("ss", $status, $selectedMonth);
    } else {
        $stmt->bind_param("s", $status);
    }

    $stmt->execute();
    return $stmt->get_result();
}

$approvedResult = fetchBookings($conn, 'approved', $selectedRoom, $selectedMonth);
$rejectedResult = fetchBookings($conn, 'rejected', $selectedRoom, $selectedMonth);
$cancelledResult = fetchBookings($conn, 'cancelled', $selectedRoom, $selectedMonth);
$pendingResult = fetchBookings($conn, 'pending', $selectedRoom, $selectedMonth);

$conn->close();

function renderAccordion($result, $isPending) {
    if ($result->num_rows > 0) {
        echo '<div class="accordion" id="accordionExample">';
        while ($row = $result->fetch_assoc()) {
            $bookingId = htmlspecialchars($row['booking_id']);
            echo '
                <div class="accordion-item">
                    <h2 class="accordion-header" id="heading' . $bookingId . '">
                        <button class="accordion-button collapsed" type="button" 
                            data-bs-toggle="collapse" data-bs-target="#collapse' . $bookingId . '" 
                            aria-expanded="false" aria-controls="collapse' . $bookingId . '">
                            Booking ID: ' . $bookingId . ' | ' . htmlspecialchars($row['room_name']) . ' | ' . htmlspecialchars($row['name']) . ' (' . htmlspecialchars($row['department']) . ') 
                        </button>
                    </h2>
                    <div id="collapse' . $bookingId . '" class="accordion-collapse collapse" 
                        aria-labelledby="heading' . $bookingId . '" data-bs-parent="#accordionExample">
                        <div class="accordion-body">
                            <p><strong>Room:</strong> ' . htmlspecialchars($row['room_name']) . '</p>
                            <p><strong>Start Time:</strong> ' . htmlspecialchars($row['start_time']) . '</p>
                            <p><strong>End Time:</strong> ' . htmlspecialchars($row['end_time']) . '</p>
                            <p><strong>Purpose:</strong> ' . htmlspecialchars($row['reason']) . '</p>
                            <p><strong>Number of Participants:</strong> ' . htmlspecialchars($row['participants']) . '</p>';

            if ($isPending) {
                echo '
                    <button class="btn btn-success approve-btn" data-booking-id="' . $bookingId . '">Approve</button>
                    <button class="btn btn-danger reject-btn" data-booking-id="' . $bookingId . '">Reject</button>';
            }
            echo '</div></div></div>';
        }
        echo '</div>';
    } else {
        echo '<p>No bookings found.</p>';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Room Reservations</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar bg-light p-3">
        <h1 class="me-auto">Admin Dashboard</h1>
        <button class="btn btn-outline-info mt-3" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasSidebar" aria-controls="offcanvasSidebar">
            ☰ Menu
        </button>
    </nav>

    <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasSidebar" aria-labelledby="offcanvasSidebarLabel">
        <div class="offcanvas-header">
            <h5 class="offcanvas-title" id="offcanvasSidebarLabel">Admin Menu</h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body">
            <ul class="list-unstyled">
                <li><a href="index.html" class="btn btn-outline-success w-100 mb-2">Home</a></li>
                <li><a href="admin_booking.php" class="btn btn-outline-primary w-100 mb-2">Add New Booking</a></li>
                <li><a href="update_room_details.php" class="btn btn-outline-primary w-100 mb-2">Edit Room</a></li>
                <li><button id="logoutButton" class="btn btn-outline-danger w-100">Logout</button></li>
            </ul>
        </div>
    </div>

    <div class="container mt-4">
        <form class="mb-3" method="GET" action="admin.php">
            <div class="input-group">
                <select class="form-select" name="room_name">
                    <option value="">All Rooms</option>
                    <?php foreach ($roomOptions as $room): ?>
                        <option value="<?= htmlspecialchars($room['room_name']) ?>" <?= $selectedRoom === $room['room_name'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($room['room_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <select class="form-select" name="booking_month">
                    <option value="">All Months</option>
                    <?php foreach ($monthOptions as $month): ?>
                        <option value="<?= htmlspecialchars($month['booking_month']) ?>" <?= $selectedMonth === $month['booking_month'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($month['booking_month']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <button class="btn btn-primary" type="submit">Search</button>
            </div>
        </form>

        <ul class="nav nav-tabs">
            <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#pending">Pending</a></li>
            <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#approved">Approved</a></li>
            <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#rejected">Rejected</a></li>
            <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#cancelled">Cancelled</a></li>
        </ul>

        <div class="tab-content">
            <div class="tab-pane fade show active" id="pending">
                <h2>Pending Bookings</h2>
                <?= renderAccordion($pendingResult, true); ?>
            </div>
            <div class="tab-pane fade" id="approved">
                <h2>Approved Bookings</h2>
                <?= renderAccordion($approvedResult, false); ?>
            </div>
            <div class="tab-pane fade" id="rejected">
                <h2>Rejected Bookings</h2>
                <?= renderAccordion($rejectedResult, false); ?>
            </div>
            <div class="tab-pane fade" id="cancelled">
                <h2>Cancelled Bookings</h2>
                <?= renderAccordion($cancelledResult, false); ?>
            </div>
        </div>
    </div>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/admin.js"></script>

    <script>
        $(document).ready(function () {
            $('#logoutButton').click(function () {
                window.location.href = '/RoomReservationApp/backend/admin_logout.php';
            });

            function bindActionButtons() {
                $('.approve-btn').click(function () {
                    const bookingId = $(this).data('booking-id');
                    if (confirm('Are you sure you want to approve this booking?')) {
                        updateBookingStatus(bookingId, 'approved');
                    }
                });

                $('.reject-btn').click(function () {
                    const bookingId = $(this).data('booking-id');
                    if (confirm('Are you sure you want to reject this booking?')) {
                        updateBookingStatus(bookingId, 'rejected');
                    }
                });
            }

            function updateBookingStatus(bookingId, status) {
                $.post('/RoomReservationApp/backend/update_booking_status.php',
                    { booking_id: bookingId, status: status },
                    function (response) {
                        if (response.success) {
                            location.reload();
                        } else {
                            alert(response.message);
                        }
                    }, 'json');
            }

            bindActionButtons();
        });
    </script>
</body>
</html> 

