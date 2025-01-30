<?php
$host = "localhost";
$port = 5080; // Ensure this is correct
$username = "root";
$password = "";
$database = "smart_prep_guide";

$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die(json_encode(["error" => "Database connection failed: " . $conn->connect_error]));
} else {
    echo json_encode(["message" => "Database connected successfully!"]);
}
?>
