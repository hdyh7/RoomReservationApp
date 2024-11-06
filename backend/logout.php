<?php
// backend/logout.php

session_start();
session_unset(); // Remove all session variables
session_destroy(); // Destroy the session

header('Location: /RoomReservationApp/public/login.html');

echo json_encode(["success" => true, "message" => "Logged out successfully."]);
?>
