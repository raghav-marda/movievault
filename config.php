<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$servername = "HOST";
$username   = "USERNAME";
$password   = "PASSWORD";
$database   = "DATABASE";

$conn = new mysqli($servername, $username, $password, $database);

if ($conn->connect_error) {
    die("Database Connection Failed: " . $conn->connect_error);
}
?>