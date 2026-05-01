<?php
include 'config.php';

$TMDB_KEY = "0036a36db63b8e42a8a376d8a4108a8a";

// Detect category from URL
$category = $_GET['type'] ?? 'hollywood';
$title = ucfirst($category) . " Movies";
$movies = [];

// Fetch data based on category
switch (strtolower($category)) {
    case 'hollywood':
        $url = "https://api.themoviedb.org/3/discover/movie?api_key={$TMDB_KEY}&with_original_language=en&sort_by=popularity.desc";
        break;

    case 'bollywood':
        $url = "https://api.themoviedb.org/3/discover/movie?api_key={$TMDB_KEY}&with_original_language=hi&sort_by=popularity.desc";
        break;

    case 'romance':
        $url = "https://api.themoviedb.org/3/discover/movie?api_key={$TMDB_KEY}&with_genres=10749&sort_by=popularity.desc";
        break;

    case 'action':
        $url = "https://api.themoviedb.org/3/discover/movie?api_key={$TMDB_KEY}&with_genres=28&sort_by=popularity.desc";
        break;

    case 'comedy':
        $url = "https://api.themoviedb.org/3/discover/movie?api_key={$TMDB_KEY}&with_genres=35&sort_by=popularity.desc";
        break;

    case 'horror':
        $url = "https://api.themoviedb.org/3/discover/movie?api_key={$TMDB_KEY}&with_genres=27&sort_by=popularity.desc";
        break;

    default:
        $url = "https://api.themoviedb.org/3/discover/movie?api_key={$TMDB_KEY}&sort_by=popularity.desc";
}

$response = @file_get_contents($url);
$data = @json_decode($response, true);
$movies = $data['results'] ?? [];
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Discover - <?= htmlspecialchars($title) ?> | MovieVault</title>
<link rel="stylesheet" href="style.css">
<style>
body {
  background-color: #0f0f0f;
  color: #fff;
  font-family: 'Arial', sans-serif;
}
h1 {
  text-align: center;
  color: #e50914;
  margin-top: 30px;
}
.subtitle {
  text-align: center;
  color: #aaa;
  margin-bottom: 30px;
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

<h1><?= htmlspecialchars($title) ?></h1>
<p class="subtitle">Discover trending <?= htmlspecialchars($category) ?> movies</p>

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
    <p style="text-align:center;">No movies found for this category.</p>
  <?php endif; ?>
</div>

</body>
</html>
