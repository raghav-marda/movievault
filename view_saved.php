<?php
session_start();

if (!isset($_SESSION['user'])) {
    header("Location: auth.php");
    exit();
}
?>

<?php include 'config.php'; ?>

// 🔒 Only logged in users
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

// ✅ DIRECTLY use session (no DB query)
$user_id = $_SESSION['user_id'];

// Delete movie (only user's movie)
if (isset($_GET['delete'])) {
    $deleteId = intval($_GET['delete']);

    $stmt = $conn->prepare("DELETE FROM saved_movies WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $deleteId, $user_id);
    $stmt->execute();

    header("Location: view_saved.php");
    exit;
}

// Fetch ONLY this user's movies
$stmt = $conn->prepare("SELECT * FROM saved_movies WHERE user_id = ? ORDER BY id DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Saved Movies</title>
<link rel="stylesheet" href="style.css">

<style>
.saved-wrapper {
    max-width: 900px;
    margin: 40px auto;
    color: white;
}
.saved-list {
    display: flex;
    flex-direction: column;
    gap: 20px;
}
.saved-item {
    display: flex;
    background: #111317;
    padding: 12px;
    border-radius: 10px;
    gap: 15px;
}
.saved-item img {
    width: 130px;
    height: 190px;
    object-fit: cover;
    border-radius: 6px;
}
.saved-body {
    flex: 1;
}
.saved-title {
    font-size: 20px;
    font-weight: 600;
    margin-bottom: 6px;
}
.saved-meta {
    color: #f5c518;
    margin-bottom: 8px;
}
.remove-btn {
    display: inline-block;
    margin-top: 10px;
    background: #e50914;
    padding: 8px 12px;
    border-radius: 6px;
    color: white;
    text-decoration: none;
}
.remove-btn:hover {
    background: #ff3333;
}
</style>
</head>
<body>

<?php include 'navbar.php'; ?>

<div class="saved-wrapper">
    <h2>📁 Your Saved Movies</h2>

    <?php if ($result->num_rows > 0): ?>
        <div class="saved-list">

        <?php while ($row = $result->fetch_assoc()): ?>
            <div class="saved-item">
                <img src="<?= htmlspecialchars($row['poster']) ?>">

                <div class="saved-body">
                    <div class="saved-title">
                        <?= htmlspecialchars($row['title']) ?> (<?= htmlspecialchars($row['year']) ?>)
                    </div>

                    <div class="saved-meta">⭐ <?= htmlspecialchars($row['imdb_rating']) ?></div>

                    <p><?= nl2br(htmlspecialchars($row['plot'])) ?></p>

                    <a class="remove-btn"
                       href="?delete=<?= $row['id'] ?>"
                       onclick="return confirm('Remove this movie?')">🗑 Remove</a>
                </div>
            </div>
        <?php endwhile; ?>

        </div>

    <?php else: ?>
        <p style="margin-top:20px;">No movies saved yet.</p>
    <?php endif; ?>
</div>

</body>
</html>