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

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Room Booking</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mt-3">
            <h1>Admin Room Booking</h1>
            <!-- Sidebar Trigger Button positioned on the right -->
            <button class="btn btn-outline-info mt-3" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasSidebar" aria-controls="offcanvasSidebar">
                ☰ Menu
            </button>
        </div>

        <div class="row mt-3">
            <div class="col-md-6">
                <form id="adminReservationForm">
                    <div class="mb-3">
                        <label for="room" class="form-label">Room</label>
                        <select id="room" class="form-select" name="room_id" required></select>
                    </div>

                    <div class="mb-3">
                        <label for="userSearch" class="form-label">Search User</label>
                        <input type="text" class="form-control" id="userSearch" placeholder="Enter User ID or Name">
                        <select id="userSelect" class="form-select mt-2" name="user_id" required>
                            <option value="">Select a user...</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="participants" class="form-label">Number of Participants</label>
                        <div class="input-group">
                            <input type="number" class="form-control" id="participants" name="participants" 
                                min="1" required aria-describedby="capacityTooltip" />
                            <span class="input-group-text" id="capacityTooltip" 
                                data-bs-toggle="tooltip" data-bs-placement="right">
                                ?
                            </span>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="reason" class="form-label">Purpose of Booking</label>
                        <select id="reason" class="form-select" name="reason" required>
                            <option value="team meetings">Team Meetings</option>
                            <option value="client presentations">Client Presentations</option>
                            <option value="training session">Training Session</option>
                            <option value="interviews">Interviews</option>
                            <option value="conferences and seminars">Conferences and Seminars</option>
                            <option value="team building activities">Team Building Activities</option>
                            <option value="workshops">Workshops</option>
                            <option value="project planning">Project Planning</option>
                            <option value="executive meetings">Executive Meetings</option>
                        </select>
                    </div>
                    <div class="row g-3">
                        <div class="col">
                            <label for="start_time" class="form-label">Start Time</label>
                            <input type="datetime-local" class="form-control" id="start_time" name="start_time" required>
                        </div>

                        <div class="col">
                            <label for="end_time" class="form-label">End Time</label>
                            <input type="datetime-local" class="form-control" id="end_time" name="end_time" required>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary">Reserve Room</button>
                </form>
                <div id="reservationResult" class="mt-3"></div>
            </div>
        </div>
    </div>

    <!-- Offcanvas Sidebar -->
    <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasSidebar" aria-labelledby="offcanvasSidebarLabel">
        <div class="offcanvas-header">
            <h5 class="offcanvas-title" id="offcanvasSidebarLabel">Admin Menu</h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body">
            <ul class="list-unstyled">
                <li><a href="index.html" class="btn btn-outline-success w-100 mb-2">Home</a></li>
                <li><a href="admin.php" class="btn btn-outline-primary w-100 mb-2">Admin Dashboard</a></li>
                <li><a href="update_room_details.php" class="btn btn-outline-primary w-100 mb-2">Edit Room</a></li>
                <li><button id="sidebarLogoutButton" class="btn btn-outline-danger w-100">Logout</button></li>
            </ul>
        </div>
    </div>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        $(document).ready(function () {

            // Initialize Bootstrap tooltip for the capacity tooltip element
            const capacityTooltip = new bootstrap.Tooltip('#capacityTooltip', {
                title: "Select a room to see its capacity.",
            });

            // Fetch room data
            $.ajax({
                url: '/RoomReservationApp/backend/get_available_rooms.php',
                type: 'GET',
                success: function (response) {
                    const rooms = JSON.parse(response);
                    const roomSelect = $('#room');
                    rooms.forEach(room => {
                        roomSelect.append(new Option(room.room_name, room.room_id));
                    });
                },
                error: function () {
                    console.error('Error fetching room data.');
                }
            });

            // When a room is selected, fetch the capacity and update the tooltip
            $('#room').change(function () {
                const roomId = $(this).val();
                if (roomId) {
                    $.ajax({
                        url: '/RoomReservationApp/backend/get_room_capacity.php',
                        type: 'GET',
                        data: { room_id: roomId },
                        success: function (response) {
                            const capacity = JSON.parse(response).capacity;
                            capacityTooltip.setContent({
                                '.tooltip-inner': `The capacity of this room is ${capacity} participants.`
                            });
                        },
                        error: function () {
                            console.error('Error fetching room capacity.');
                        }
                    });
                }
            });

            // Search users dynamically based on input
            $('#userSearch').on('input', function () {
                const query = $(this).val();
                if (query.length > 1) {
                    $.ajax({
                        url: `/RoomReservationApp/backend/get_users.php?search=${query}`,
                        type: 'GET',
                        success: function (response) {
                            const users = JSON.parse(response);
                            const userSelect = $('#userSelect');
                            userSelect.empty();

                            if (users.length > 0) {
                                userSelect.append(new Option('Select a user...', ''));
                                users.forEach(user => {
                                    userSelect.append(new Option(
                                        `${user.name} (${user.department})`,
                                        user.user_id
                                    ));
                                });
                            } else {
                                userSelect.append(new Option('No users found', ''));
                            }
                        },
                        error: function (xhr, status, error) {
                            console.error('Error fetching user data:', status, error);
                        }
                    });
                }
            });


            // Handle form submission for reservation
            $('#adminReservationForm').submit(function (event) {
                event.preventDefault();
                $.ajax({
                    url: '/RoomReservationApp/backend/reserve_room.php',
                    type: 'POST',
                    data: $(this).serialize(),
                    success: function (response) {
                        const result = JSON.parse(response);
                        if (result.success) {
                            $('#reservationResult').html(`<div class="alert alert-info">${result.message}</div>`);
                        } else {
                            $('#reservationResult').html(`<div class="alert alert-danger">${result.message}</div>`);
                        }
                    },
                    error: function () {
                        $('#reservationResult').html('<div class="alert alert-danger">Error processing reservation.</div>');
                    }
                });
            });

            // Logout functionality for the sidebar button
            $('#sidebarLogoutButton').click(function () {
                window.location.href = "/RoomReservationApp/backend/admin_logout.php";
            });
        });
    </script>
</body>
</html>


