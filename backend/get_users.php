<?php
$host = 'localhost';
$db = 'RoomBookingDB';
$user = 'root';
$pass = '';

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get the search query from the URL
$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';

$sql = "SELECT user_id, name, department FROM Users";
if (!empty($search)) {
    $sql .= " WHERE user_id LIKE '%$search%' OR name LIKE '%$search%'";
}

$result = $conn->query($sql);

if (!$result) {
    echo json_encode(["error" => "SQL Error: " . $conn->error]);  // Output SQL errors for debugging
    exit;
}

$users = [];
while ($row = $result->fetch_assoc()) {
    $users[] = $row;
}

// Send JSON response
echo json_encode($users);

$conn->close();
?>

