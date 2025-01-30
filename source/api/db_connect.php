<?php
$host = "localhost";
$username = "root";
$password = "";
$database = "smart_prep_guide";

$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die(json_encode(["error" => "Database connection failed: " . $conn->connect_error]));
}

// No echo or output here!
?>
