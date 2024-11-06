<?php
session_start();


$host = 'localhost'; // Change if necessary
$db = 'RoomBookingDB'; // Your database name
$user = 'root'; // Your database username
$pass = ''; // Your database password

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get posted data
$username = $_POST['username'];
$password = $_POST['password'];

// Fetch admin from database
$sql = "SELECT * FROM Admins WHERE username = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $admin = $result->fetch_assoc();
    
    // Verify password
    if (password_verify($password, $admin['password'])) {
        $_SESSION['admin_logged_in'] = true; // Set session variable
        $_SESSION['admin_id'] = $admin['admin_id']; // Store admin ID in session
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid password.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Admin not found.']);
}

$stmt->close();
$conn->close();
?>
