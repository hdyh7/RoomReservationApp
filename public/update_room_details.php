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
    <title>Edit Room Details</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
            <nav class="navbar bg-light p-3">
                <h1 class="me-auto">Edit Room Status</h1>
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
                        <li><a href="admin.php" class="btn btn-outline-primary w-100 mb-2">Admin Dashboard</a></li>
                        <li><a href="admin_booking.php" class="btn btn-outline-primary w-100 mb-2">Add New Booking</a></li>
                        <li><button id="logoutButton" class="btn btn-outline-danger w-100">Logout</button></li>
                    </ul>
                </div>
            </div>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Room ID</th>
                    <th>Room Name</th>
                    <th>Current Status</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="roomTableBody">
                <!-- Room details will be populated here via AJAX -->
            </tbody>
        </table>
    </div>

    <!-- Offcanvas Sidebar -->
    <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasSidebar" aria-labelledby="offcanvasSidebarLabel">
        <div class="offcanvas-header">
            <h5 class="offcanvas-title" id="offcanvasSidebarLabel">Admin Menu</h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body">
            <ul class="list-unstyled">
                <li><a href="admin.php" class="btn btn-outline-primary w-100 mb-2">Admin Dashboard</a></li>
                <li><a href="admin_booking.php" class="btn btn-outline-primary w-100 mb-2">Add New Booking</a></li>
                <li><button id="LogoutButton" class="btn btn-outline-danger w-100">Logout</button></li>
            </ul>
        </div>
    </div>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>


    <script>
        $(document).ready(function () {
            // Fetch and display room details
            function fetchRooms() {
                $.ajax({
                    url: '/RoomReservationApp/backend/get_rooms.php', // Endpoint to get room details
                    type: 'GET',
                    success: function (response) {
                        const rooms = JSON.parse(response);
                        const roomTableBody = $('#roomTableBody');
                        roomTableBody.empty(); // Clear previous data

                        rooms.forEach(room => {
                            roomTableBody.append(`
                                <tr>
                                    <td>${room.room_id}</td>
                                    <td>${room.room_name}</td>
                                    <td>${room.room_status.charAt(0).toUpperCase() + room.room_status.slice(1)}</td> <!-- Show current status -->
                                    <td>
                                        <div>
                                            <input type="radio" name="status-${room.room_id}" class="room-status" data-room-id="${room.room_id}" value="available" ${room.room_status === 'available' ? 'checked' : ''}> Available
                                            <input type="radio" name="status-${room.room_id}" class="room-status" data-room-id="${room.room_id}" value="unavailable" ${room.room_status === 'unavailable' ? 'checked' : ''}> Unavailable
                                            <input type="radio" name="status-${room.room_id}" class="room-status" data-room-id="${room.room_id}" value="maintenance" ${room.room_status === 'maintenance' ? 'checked' : ''}> Maintenance
                                        </div>
                                    </td>
                                    <td>
                                        <button class="btn btn-warning update-room" data-room-id="${room.room_id}">Update</button>
                                    </td>
                                </tr>
                            `);
                        });
                    },
                    error: function () {
                        console.error('Error fetching room data.');
                    }
                });
            }

            // Fetch room details on page load
            fetchRooms();

            // Update room status
            $(document).on('click', '.update-room', function () {
                const roomId = $(this).data('room-id');
                const newStatus = $(`input[name="status-${roomId}"]:checked`).val(); // Get the selected status

                if (confirm(`Are you sure you want to change the status of this room to "${newStatus}"?`)) {
                    $.ajax({
                        url: '/RoomReservationApp/backend/update_room_status.php', // Endpoint to update room status
                        type: 'POST',
                        data: {
                            room_id: roomId,
                            room_status: newStatus
                        },
                        success: function (response) {
                            const result = JSON.parse(response);
                            alert(result.message);
                            fetchRooms(); // Refresh the room list
                        },
                        error: function () {
                            alert('Error updating room status.');
                        }
                    });
                }
            });
             // Logout functionality for the sidebar button
             $('#LogoutButton').click(function () {
                window.location.href = "/RoomReservationApp/backend/admin_logout.php";
            });
        });
    </script>
</body>
</html>


