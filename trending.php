<?php
include 'config.php';

// TMDB API key
$TMDB_KEY = "0036a36db63b8e42a8a376d8a4108a8a";

// Fetch Trending Movies (Daily)
$url = "https://api.themoviedb.org/3/trending/movie/day?api_key={$TMDB_KEY}";
$response = @file_get_contents($url);
$data = @json_decode($response, true);
$movies = $data['results'] ?? [];
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>🔥 Trending Movies - MovieVault</title>
<link rel="stylesheet" href="style.css">
<style>
body {
  background-color: #0f0f0f;
  color: #fff;
  font-family: 'Arial', sans-serif;
}
h1 {
  text-align: center;
  margin-top: 30px;
  color: #e50914;
}
.grid-container {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
  gap: 25px;
  padding: 20px;
  max-width: 1200px;
  margin: 0 auto;
}
.movie-box {
  background: #181818;
  border-radius: 12px;
  overflow: hidden;
  transition: transform 0.3s ease, box-shadow 0.3s ease;
}
.movie-box:hover {
  transform: scale(1.05);
  box-shadow: 0 0 20px rgba(229, 9, 20, 0.6);
}
.movie-box img {
  width: 100%;
  height: 320px;
  object-fit: cover;
}
.movie-info {
  padding: 12px;
  text-align: center;
}
.movie-info strong {
  color: #fff;
  font-size: 16px;
  display: block;
  margin-bottom: 8px;
}
.movie-info .rating {
  color: #f5c518;
  font-weight: bold;
}
.movie-info a {
  display: inline-block;
  background: #e50914;
  color: #fff;
  padding: 6px 12px;
  border-radius: 6px;
  margin-top: 10px;
  text-decoration: none;
  transition: background 0.3s ease;
}
.movie-info a:hover {
  background: #ff3333;
}
</style>
</head>
<body>

<?php include 'navbar.php'; ?>

<h1>🔥 Trending Movies</h1>

<div class="grid-container">
  <?php if (!empty($movies)): ?>
    <?php foreach ($movies as $m): ?>
      <div class="movie-box">
        <img src="https://image.tmdb.org/t/p/w500<?= htmlspecialchars($m['poster_path']) ?>" alt="<?= htmlspecialchars($m['title']) ?>">
        <div class="movie-info">
          <strong><?= htmlspecialchars($m['title']) ?></strong>
          <div class="rating">⭐ <?= htmlspecialchars($m['vote_average']) ?></div>
          <a href="search.php?id=<?= $m['id'] ?>">View Details</a>
        </div>
      </div>
    <?php endforeach; ?>
  <?php else: ?>
    <p style="text-align:center;">No trending movies available right now.</p>
  <?php endif; ?>
</div>

</body>
</html>
