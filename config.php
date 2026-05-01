<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$servername = "localhost";
$username   = "root";
$password   = "";
$database   = "movievault";

$conn = new mysqli($servername, $username, $password, $database, 3307);

if ($conn->connect_error) {
    die("Database Connection Failed: " . $conn->connect_error);
}
?>