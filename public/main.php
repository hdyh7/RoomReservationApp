<?php
session_start();

// Check if the user is logged in; if not, redirect to the login page
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

// Fetch the logged-in user's details
$user_id = $_SESSION['user_id'];
$query = "SELECT user_id, name, department FROM Users WHERE user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Room Reservation System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="reservation_style.css">
</head>
<body>
<nav class="navbar bg-light p-3">
        <h1 class="me-auto"><strong>Add New Booking</strong></h1>
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

    <div class="row">
        <div class="col-md-6">
            <form id="reservationForm">
            <div class="mb-3">
                    <label for="Form" class="form-label"><strong>Fill in your booking details.</strong></label>
                </div>
                <div class="mb-3">
                    <label for="room" class="form-label">Room</label>
                    <select id="room" class="form-select" name="room_id" required></select>
                </div>

                <div class="row g-3">
                    <div class="col">
                        <label for="user" class="form-label">User ID</label>
                        <input type="text" class="form-control" id="user" name="user_id" 
                            value="<?php echo htmlspecialchars($user['user_id']); ?>" readonly>
                    </div>
                    <div class="col">
                        <label for="name" class="form-label">Name</label>
                        <input type="text" class="form-control" id="name" 
                            value="<?php echo htmlspecialchars($user['name']); ?>" readonly>
                    </div>
                </div>    

                <div class="mb-3">
                    <label for="department" class="form-label">Department</label>
                    <input type="text" class="form-control" id="department" 
                        value="<?php echo htmlspecialchars($user['department']); ?>" readonly>
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

                <div class="mb-3">
                    <label for="terms" class="form-label">Terms and Conditions</label>
                    <div style="border: 1px solid #ced4da; border-radius: 5px; height: 150px; overflow-y: scroll; padding: 10px;">
                        <p><strong>Terma dan Syarat:</strong></p>
                        <p>Tempahan hendaklah dibuat 14 hari sebelum tarikh kursus/bengkel.</p>
                        <p>Penganjur (pemohon) kursus/bengkel bertanggungjawab memastikan semua peralatan dan kemudahan di Bilik Latihan 
                            dalam keadaan baik sepanjang tempoh kursus/bengkel. Sebarang kecacatan, kerosakan dan pengubahsuaian pada peralatan
                             atau kemudahan Bilik Latihan akan dipertanggungjawabkan dan dikenakan ganti rugi ke atas pihak pemohon.</p>
                        <p> Penganjur/peserta kursus/bengkel yang menggunakan kemudahan Bilik Latihan, adalah <strong>DILARANG</strong> : </p>
                        <p> 1. Membawa makanan dan minuman ke dalam Bilik Latihan.</p>
                        <p> 2. Mengubah dan menambah paparan skrin PC, perisian yang sedia ada dan mengubah kedudukan monitor, CPU dan sebagainya.</p>
                        <p> 3. Mengubah susun atur meja dan kerusi. </p>
                        <p> Kebersihan Bilik Latihan hendaklah sentiasa dijaga. </p>
                        <p> Pembatalan tempahan hendaklah dimaklumkan selewat-lewatnya 7 hari sebelum tarikh kursus/bengkel.</p>
                    </div>
                </div>

                <div class="col-12">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="" id="invalidCheck" required>
                        <label class="form-check-label" for="invalidCheck">
                            Agree to terms and conditions
                        </label>
                        <div class="invalid-feedback">
                            You must agree before submitting.
                        </div>
                    </div>
                </div>


                <button type="submit" class="btn btn-primary">Reserve Room</button>
            </form>
            <div id="reservationResult" class="mt-3"></div>
        </div>
    </div>

    <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 11">
        <div id="toast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="toast-header">
                <strong class="me-auto">Room Reservation</strong>
                <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
            <div class="toast-body" id="toastMessage"></div>
        </div>
    </div>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
    $(document).ready(function () {
        const toastElement = new bootstrap.Toast($('#toast'));

        // Initialize Bootstrap tooltip for the capacity tooltip element
        const capacityTooltip = new bootstrap.Tooltip('#capacityTooltip', {
            title: "Select a room to see its capacity.",
        });

        // Fetch rooms and populate the room dropdown
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

        // Handle the form submission for room reservation
        $('#reservationForm').submit(function (event) {
            event.preventDefault();
            $.ajax({
                url: '/RoomReservationApp/backend/reserve_room.php',
                type: 'POST',
                data: $(this).serialize(),
                success: function (response) {
                    const result = JSON.parse(response);
                    if (result.success) {
                        $('#reservationResult').html('<div class="alert alert-info">Booking pending approval.</div>');
                        showToast('Reservation successful!');
                    } else {
                        $('#reservationResult').html('<div class="alert alert-danger">' + result.message + '</div>');
                        showToast('Reservation failed.', 'danger');
                    }
                },
                error: function () {
                    $('#reservationResult').html('<div class="alert alert-danger">Error processing reservation.</div>');
                    showToast('An error occurred.', 'danger');
                }
            });
        });

        // Function to show toast notifications
        function showToast(message, type = 'info') {
            $('#toastMessage').text(message);
            $('#toast').removeClass('bg-danger bg-info').addClass(`bg-${type}`);
            toastElement.show();
        }

        // Handle the logout button click
        $('#logoutButton').click(function () {
            window.location.href = "/RoomReservationApp/backend/logout.php";
        });
    });

    </script>
</body>
</html>


