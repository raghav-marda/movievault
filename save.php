<?php
session_start();
include 'config.php';

// 🔒 Must be logged in
if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('Please login first'); window.location.href='login.html';</script>";
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Use session user_id
    $user_id = $_SESSION['user_id'];

    // Movie data
    $imdb_id     = $_POST['imdb_id'] ?? '';
    $title       = $_POST['title'] ?? '';
    $year        = $_POST['year'] ?? '';
    $genre       = $_POST['genre'] ?? '';
    $director    = $_POST['director'] ?? '';
    $actors      = $_POST['actors'] ?? '';
    $poster      = $_POST['poster'] ?? '';
    $imdb_rating = $_POST['imdb_rating'] ?? '';
    $plot        = $_POST['plot'] ?? '';

    // 🔍 Check if already saved by this user
    $check = $conn->prepare("SELECT id FROM saved_movies WHERE user_id = ? AND imdb_id = ?");
    $check->bind_param("is", $user_id, $imdb_id);
    $check->execute();
    $checkResult = $check->get_result();

    if ($checkResult->num_rows > 0) {
        // Already saved → redirect without inserting
        header("Location: view_saved.php");
        exit();
    }

    // Insert with user_id
    $sql = "INSERT INTO saved_movies 
        (user_id, imdb_id, title, year, genre, director, actors, poster, imdb_rating, plot)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param(
        "isssssssss",
        $user_id,
        $imdb_id,
        $title,
        $year,
        $genre,
        $director,
        $actors,
        $poster,
        $imdb_rating,
        $plot
    );

    if ($stmt->execute()) {
        header("Location: view_saved.php");
        exit();
    } else {
        die("Save failed: " . $stmt->error);
    }
}

header("Location: index.php");
exit;
?>