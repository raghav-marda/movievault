<?php
include 'config.php';

$TMDB_KEY = "0036a36db63b8e42a8a376d8a4108a8a";
$OMDB_KEY  = "c5556bd9";

function tmdb_search($query, $tmdb_key) {
    $url = "https://api.themoviedb.org/3/search/movie?api_key={$tmdb_key}&query=" . urlencode($query);
    $data = @json_decode(@file_get_contents($url), true);
    return $data['results'] ?? [];
}

function tmdb_details($id, $tmdb_key) {
    $url = "https://api.themoviedb.org/3/movie/{$id}?api_key={$tmdb_key}&append_to_response=credits,videos,external_ids";
    return @json_decode(@file_get_contents($url), true);
}

function get_imdb_rating($imdbID, $apikey) {
    if (!$imdbID) return null;

    $url = "https://www.omdbapi.com/?i={$imdbID}&apikey={$apikey}";
    $data = @json_decode(@file_get_contents($url), true);

    if ($data && isset($data['imdbRating']) && $data['imdbRating'] != 'N/A') {
        return $data['imdbRating'];
    }

    return null;
}

/* ================== ✅ CACHE FUNCTIONS ================== */

function get_cached_rating($tmdb_id, $conn) {
    $tmdb_id = intval($tmdb_id);
    $res = $conn->query("SELECT imdb_rating FROM movie_cache WHERE tmdb_id = $tmdb_id");

    if ($res && $row = $res->fetch_assoc()) {
        return $row['imdb_rating'];
    }
    return null;
}

function save_rating_cache($tmdb_id, $imdb_id, $rating, $conn) {
    $tmdb_id = intval($tmdb_id);
    $imdb_id = $conn->real_escape_string($imdb_id);
    $rating = floatval($rating);

    $conn->query("
        INSERT INTO movie_cache (tmdb_id, imdb_id, imdb_rating)
        VALUES ($tmdb_id, '$imdb_id', $rating)
        ON DUPLICATE KEY UPDATE imdb_rating = $rating, updated_at = NOW()
    ");
}

/* ======================================================= */

$movieList = [];
$movieData = null;
$error = null;

/* ================== DETAIL PAGE ================== */

if (isset($_GET['id'])) {

    $id = intval($_GET['id']);
    $details = tmdb_details($id, $TMDB_KEY);

    if ($details) {

        $director = "N/A";
        foreach ($details['credits']['crew'] as $c) {
            if ($c['job'] === 'Director') {
                $director = $c['name'];
                break;
            }
        }

        $trailerKey = '';
        if (!empty($details['videos']['results'])) {
            foreach ($details['videos']['results'] as $v) {
                if (
                    isset($v['site'], $v['type'], $v['key']) &&
                    $v['site'] === 'YouTube' &&
                    ($v['type'] === 'Trailer' || $v['type'] === 'Teaser')
                ) {
                    $trailerKey = $v['key'];
                    break;
                }
            }
        }

        $imdbID = $details['external_ids']['imdb_id'] ?? null;

        // CACHE FIRST
        $realRating = get_cached_rating($id, $conn);

        if (!$realRating) {
            $realRating = get_imdb_rating($imdbID, $OMDB_KEY);

            if ($realRating) {
                save_rating_cache($id, $imdbID, $realRating, $conn);
            }
        }

        $movieData = [
            'Title' => $details['title'],
            'Year' => substr($details['release_date'], 0, 4),
            'Genre' => implode(', ', array_column($details['genres'], 'name')),
            'Director' => $director,
            'Actors' => implode(', ', array_column(array_slice($details['credits']['cast'], 0, 8), 'name')),
            'Poster' => "https://image.tmdb.org/t/p/w500" . $details['poster_path'],
            'Backdrop' => "https://image.tmdb.org/t/p/original" . $details['backdrop_path'],
            'imdbRating' => $realRating ? $realRating : $details['vote_average'],
            'Plot' => $details['overview'],
            'imdbID' => $details['id'],
            'TrailerKey' => $trailerKey
        ];
    }
}

/* ================== SEARCH MODE ================== */

elseif (isset($_GET['q'])) {

    $q = trim($_GET['q']);

    if ($q === '') {
        $error = "Please enter a movie name.";
    } else {

        // ✅ First normal search
        $results = tmdb_search($q, $TMDB_KEY);

        // ✅ If no results → try spaced version (for KGF type)
        if (empty($results) && strlen($q) <= 4) {

           $spacedQuery = implode(' ', str_split($q));
           $results = tmdb_search($spacedQuery, $TMDB_KEY);
        }

        if (empty($results)) {
           $extendedQuery = $q . " movie";
           $results = tmdb_search($extendedQuery, $TMDB_KEY);
        }
        $filtered = [];

        foreach ($results as $movie) {

             // Skip movies without poster
             if (empty($movie['poster_path'])) continue;

             // ✅ FIXED language filter
             $allowed_languages = ['en', 'hi', 'te', 'ta', 'kn', 'ml'];

             if (!in_array($movie['original_language'], $allowed_languages)) {
                 continue;
             }

            $title = strtolower($movie['title'] ?? '');
            $query = strtolower($q);

            similar_text($query, $title, $similarity);

            $popularity = $movie['popularity'] ?? 0;

            $year = isset($movie['release_date']) ? intval(substr($movie['release_date'], 0, 4)) : 0;
            $yearScore = ($year >= 2000) ? 10 : 0;

            $score = ($similarity * 5) + ($popularity * 2) + $yearScore;

            // BOOST LOGIC
            $cleanTitle = trim($title);
            $cleanQuery = trim($query);

            if (strlen($cleanQuery) <= 4) {

               // 🔥 remove ALL special characters (fix for KGF, RRR, PK etc)
               $normalizedTitle = preg_replace('/[^a-z0-9]/', '', $cleanTitle);
               $normalizedQuery = preg_replace('/[^a-z0-9]/', '', $cleanQuery);

               if (strpos($normalizedTitle, $normalizedQuery) === false) {
                  continue;
               }
            }           

            if ($cleanTitle === $cleanQuery && $cleanTitle !== '') {
                $score += 1000;
            }

            if ($cleanQuery !== '' && strpos($cleanTitle, $cleanQuery) !== false) {
                $score += 500;
            }

            $words = explode(' ', $cleanQuery);

            foreach ($words as $word) {
                $word = trim($word);
                if ($word !== '' && strpos($cleanTitle, $word) !== false) {
                    $score += 100;
                }
            }

            $movie['score'] = $score;
            $filtered[] = $movie;
        }

        usort($filtered, function ($a, $b) {
            return $b['score'] <=> $a['score'];
        });

        $movieList = $filtered;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Search Results - MovieVault</title>
<link rel="stylesheet" href="style.css">
</head>

<body>

<?php include 'navbar.php'; ?>

<main class="site-wrap">

<a class="back" href="index.php">⬅ Back</a>

<?php if ($error): ?>

<div class="msg error"><?= $error ?></div>

<?php elseif ($movieData): ?>

<?php
$poster = htmlspecialchars($movieData['Poster']);
$backdrop = htmlspecialchars($movieData['Backdrop']);
$title = htmlspecialchars($movieData['Title']);
$year = htmlspecialchars($movieData['Year']);
$genre = htmlspecialchars($movieData['Genre']);
$director = htmlspecialchars($movieData['Director']);
$actors = htmlspecialchars($movieData['Actors']);
$rating = htmlspecialchars($movieData['imdbRating']);
$plot = nl2br(htmlspecialchars($movieData['Plot']));
$trailerKey = htmlspecialchars($movieData['TrailerKey']);
?>

<section class="search-result" style="--bg: url('<?= $backdrop ?>')">
<div class="result-inner">

<div class="left-col">
<img class="result-poster" src="<?= $poster ?>">
</div>

<div class="right-col">

<h1><?= $title ?> (<?= $year ?>)</h1>

<p><strong>Genre:</strong> <?= $genre ?></p>
<p><strong>Director:</strong> <?= $director ?></p>
<p><strong>Actors:</strong> <?= $actors ?></p>
<p><strong>IMDb Rating:</strong> <?= $rating ?></p>

<p><?= $plot ?></p>

<div class="actions">

<?php if (!empty($trailerKey)): ?>
<button class="btn-primary" id="openTrailer" data-video="<?= $trailerKey ?>">▶ Watch Trailer</button>
<?php else: ?>
<a class="btn-outline" target="_blank"
href="https://www.youtube.com/results?search_query=<?= urlencode($title . ' official trailer') ?>">
🔍 Search Trailer
</a>
<?php endif; ?>

<form method="post" action="save.php" style="display:inline-block;">
<input type="hidden" name="imdb_id" value="<?= $movieData['imdbID'] ?>">
<input type="hidden" name="title" value="<?= $title ?>">
<input type="hidden" name="year" value="<?= $year ?>">
<input type="hidden" name="genre" value="<?= $genre ?>">
<input type="hidden" name="director" value="<?= $director ?>">
<input type="hidden" name="actors" value="<?= $actors ?>">
<input type="hidden" name="poster" value="<?= $poster ?>">
<input type="hidden" name="imdb_rating" value="<?= $rating ?>">
<input type="hidden" name="plot" value="<?= htmlspecialchars($movieData['Plot']) ?>">

<button type="submit" class="btn-outline">💾 Save to MovieVault</button>
</form>

</div>
</div>
</div>
</section>

<?php elseif (!empty($movieList)): ?>

<div class="grid-container">

<?php foreach ($movieList as $m):

$title = htmlspecialchars($m['title']);
$poster = $m['poster_path'] ? "https://image.tmdb.org/t/p/w500" . $m['poster_path'] : '';
$id = $m['id'];

$cached = get_cached_rating($id, $conn);

if ($cached) {
    $rating = htmlspecialchars($cached);
} else {
    $rating = htmlspecialchars($m['vote_average']);
}
?>

<article class="card">
<a class="card-link" href="search.php?id=<?= $id ?>">

<div class="card-thumb">
<?php if ($poster): ?>
<img src="<?= $poster ?>">
<?php else: ?>
<div class="no-poster">No Image</div>
<?php endif; ?>
</div>

<div class="card-body">
<strong class="card-title"><?= $title ?></strong>
<div class="card-meta">⭐ <?= $rating ?></div>
</div>

</a>
</article>

<?php endforeach; ?>

</div>

<?php else: ?>

<div class="msg muted">Enter a movie name to search.</div>

<?php endif; ?>

</main>

<div id="trailerModal" class="modal">
<div class="modal-content">
<button id="closeTrailer">&times;</button>
<div id="videoWrap"></div>
</div>
</div>

<script>
(function(){
    const openBtn = document.getElementById('openTrailer');
    const modal = document.getElementById('trailerModal');
    const closeBtn = document.getElementById('closeTrailer');
    const videoWrap = document.getElementById('videoWrap');

    function openModal(key){
        modal.style.display = 'flex';
        videoWrap.innerHTML =
            '<iframe width="100%" height="480" src="https://www.youtube.com/embed/'+key+'?autoplay=1" frameborder="0" allowfullscreen></iframe>';
    }

    function closeModal(){
        modal.style.display = 'none';
        videoWrap.innerHTML = '';
    }

    if(openBtn){
        openBtn.onclick = function(){
            openModal(this.dataset.video);
        }
    }

    if(closeBtn){
        closeBtn.onclick = closeModal;
    }

})();
</script>

</body>
</html>