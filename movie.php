<?php
include 'config.php';

$id = $_GET['id'] ?? '';
$movie = null;
$TMDB_KEY = "YOUR_API_KEY";

function fetchTmdbDetails($tmdb_id, $TMDB_KEY) {
    $detailsUrl = "https://api.themoviedb.org/3/movie/{$tmdb_id}?api_key={$TMDB_KEY}&append_to_response=videos,credits";
    $d = @json_decode(file_get_contents($detailsUrl), true);
    return $d ?: null;
}

if ($id !== '') {
    if (strpos($id, 'tmdb_') === 0) {
        $tmdb_id = substr($id, 5);
        $details = fetchTmdbDetails($tmdb_id, $TMDB_KEY);
        if ($details) {
            $movie = [
                'title' => $details['title'] ?? '',
                'year' => isset($details['release_date']) ? substr($details['release_date'],0,4) : '',
                'genre' => implode(", ", array_column($details['genres'] ?? [], 'name')),
                'director' => $details['credits']['crew'][0]['name'] ?? '',
                'actors' => implode(", ", array_column($details['credits']['cast'] ?? [], 'name')),
                'poster' => $details['poster_path'] ? "https://image.tmdb.org/t/p/w500".$details['poster_path'] : '',
                'imdb_rating' => $details['vote_average'] ?? 'N/A',
                'plot' => $details['overview'] ?? '',
                'tmdb_videos' => $details['videos']['results'] ?? []
            ];
        }
    } else {
        // existing DB fetch by id or imdb_id (your existing code)
        if (ctype_digit($id)) {
            $stmt = $conn->prepare("SELECT * FROM saved_movies WHERE id = ?");
            $stmt->bind_param('i', $id);
        } else {
            $stmt = $conn->prepare("SELECT * FROM saved_movies WHERE imdb_id = ?");
            $stmt->bind_param('s', $id);
        }
        $stmt->execute();
        $res = $stmt->get_result();
        $movie = $res->fetch_assoc();
        $stmt->close();
        // no trailer info from DB, but you can try TMDb search by title for trailer (optional)
    }
}
?>
<!doctype html>
<html><head><meta charset="utf-8"><title><?= htmlspecialchars($movie['title'] ?? 'Movie') ?> - MovieVault</title>
<link rel="stylesheet" href="style.css"></head><body>
<?php include 'navbar.php'; ?>
<div class="wrap">
  <a class="back" href="index.php">⬅ Back</a>
  <?php if (!$movie): ?>
    <div class="msg error">Movie not found.</div>
  <?php else: ?>
    <div class="result">
      <div class="poster"><img src="<?= htmlspecialchars($movie['poster']) ?>" alt="Poster"></div>
      <div class="details">
        <h2><?= htmlspecialchars($movie['title']) ?> (<?= htmlspecialchars($movie['year']) ?>)</h2>
        <p><strong>Genre:</strong> <?= htmlspecialchars($movie['genre']) ?></p>
        <p><strong>Director:</strong> <?= htmlspecialchars($movie['director']) ?></p>
        <p><strong>Actors:</strong> <?= nl2br(htmlspecialchars($movie['actors'])) ?></p>
        <p><strong>IMDb Rating:</strong> <span style="color:#fff;"><?= htmlspecialchars($movie['imdb_rating']) ?></span></p>
        <p><strong>Plot:</strong> <?= nl2br(htmlspecialchars($movie['plot'])) ?></p>

        <!-- Watch Trailer -->
        <?php
          $trailerKey = null;
          if (!empty($movie['tmdb_videos'])) {
              foreach($movie['tmdb_videos'] as $v) {
                  if ($v['site']=='YouTube' && strtolower($v['type'])=='trailer') { $trailerKey = $v['key']; break; }
              }
          }
        ?>
        <?php if ($trailerKey): ?>
          <a class="save-btn" href="https://www.youtube.com/watch?v=<?= htmlspecialchars($trailerKey) ?>" target="_blank">▶ Watch Trailer</a>
        <?php endif; ?>

        <!-- Watchlist / Save buttons -->
        <form method="post" action="watchlist.php" style="display:inline-block;margin-left:10px;">
          <input type="hidden" name="imdb_id" value="<?= htmlspecialchars($movie['tmdb_id'] ?? $movie['imdb_id'] ?? '') ?>">
          <input type="hidden" name="title" value="<?= htmlspecialchars($movie['title']) ?>">
          <input type="hidden" name="year" value="<?= htmlspecialchars($movie['year']) ?>">
          <input type="hidden" name="poster" value="<?= htmlspecialchars($movie['poster']) ?>">
          <input type="hidden" name="action" value="add">
          <button class="save-btn" type="submit">+ Watchlist</button>
        </form>

        <form method="post" action="save.php" style="display:inline-block;margin-left:8px;">
          <input type="hidden" name="imdb_id" value="<?= htmlspecialchars($movie['tmdb_id'] ?? $movie['imdb_id'] ?? '') ?>">
          <input type="hidden" name="title" value="<?= htmlspecialchars($movie['title']) ?>">
          <input type="hidden" name="year" value="<?= htmlspecialchars($movie['year']) ?>">
          <input type="hidden" name="genre" value="<?= htmlspecialchars($movie['genre']) ?>">
          <input type="hidden" name="director" value="<?= htmlspecialchars($movie['director']) ?>">
          <input type="hidden" name="actors" value="<?= htmlspecialchars($movie['actors']) ?>">
          <input type="hidden" name="poster" value="<?= htmlspecialchars($movie['poster']) ?>">
          <input type="hidden" name="imdb_rating" value="<?= htmlspecialchars($movie['imdb_rating']) ?>">
          <input type="hidden" name="plot" value="<?= htmlspecialchars($movie['plot']) ?>">
          <button class="save-btn" type="submit">💾 Save</button>
        </form>

      </div>
    </div>
  <?php endif; ?>
</div>
</body>
</html>
