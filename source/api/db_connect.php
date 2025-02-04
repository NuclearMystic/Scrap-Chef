<?php
// Manually set environment variables (for local development)
$_ENV["DB_HOST"] = "localhost";
$_ENV["DB_USER"] = "root";
$_ENV["DB_PASS"] = "";
$_ENV["DB_NAME"] = "smart_prep_guide";

// Database credentials
$host = $_ENV["DB_HOST"];
$username = $_ENV["DB_USER"];
$password = $_ENV["DB_PASS"];
$database = $_ENV["DB_NAME"];

try {
    // Enable error reporting
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

    // Establish a secure connection
    $conn = new mysqli($host, $username, $password, $database);
    $conn->set_charset("utf8mb4");

} catch (Exception $e) {
    // Log error to a file for debugging
    file_put_contents("error_log.txt", date("Y-m-d H:i:s") . " - Database error: " . $e->getMessage() . "\n", FILE_APPEND);
    
    // Return generic error message to avoid exposing details
    die(json_encode(["error" => "Database connection failed."]));
}
?>
