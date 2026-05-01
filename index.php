<?php
session_start();
?>

<?php
include 'config.php';

$TMDB_KEY = "YOUR_API_KEY";

$trendingUrl = "https://api.themoviedb.org/3/trending/movie/day?api_key={$TMDB_KEY}";
$trendingJson = @file_get_contents($trendingUrl);
$trending = @json_decode($trendingJson, true);
$movies = $trending['results'] ?? [];

$hero = null;
if (!empty($movies)) {
    $hero = $movies[0];

    $id = $hero['id'];
    $detailsUrl = "https://api.themoviedb.org/3/movie/{$id}?api_key={$TMDB_KEY}&append_to_response=videos,credits";
    $detailsJson = @file_get_contents($detailsUrl);
    $details = @json_decode($detailsJson, true);

    if ($details) {
        $trailerKey = '';
        if (!empty($details['videos']['results'])) {
            foreach ($details['videos']['results'] as $v) {
                if ($v['site'] === 'YouTube' && ($v['type'] === 'Trailer' || $v['type'] === 'Teaser')) {
                    $trailerKey = $v['key'];
                    break;
                }
            }
        }
        $hero['details'] = $details;
        $hero['trailer_key'] = $trailerKey;
    }
}
?>

<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>MovieVault</title>
  <meta name="viewport" content="width=1200">
  <link rel="stylesheet" href="style.css">
</head>
<body>

<?php include 'navbar.php'; ?>

<main class="site-wrap">

<!-- HERO -->
<?php if ($hero): 
    $heroPoster = $hero['poster_path'] ? "https://image.tmdb.org/t/p/original".$hero['poster_path'] : '';
    $heroTitle = htmlspecialchars($hero['title'] ?? $hero['name'] ?? '');
    $heroOverview = htmlspecialchars($hero['overview'] ?? '');
    $heroRating = htmlspecialchars($hero['vote_average'] ?? 'N/A');
    $trailerKey = $hero['trailer_key'] ?? '';
?>

<section class="hero" style="--hero-bg: url('<?= $heroPoster ?>')">
  <div class="hero-overlay"></div>
  <div class="hero-inner">
    <h1 class="site-title">MovieVault 🎬</h1>

    <div class="hero-content">
      <div class="hero-text">
        <h2 class="hero-movie"><?= $heroTitle ?></h2>
        <p class="hero-meta">⭐ <?= $heroRating ?></p>
        <p class="hero-desc">
          <?= strlen($heroOverview) > 350 ? substr($heroOverview,0,350).'…' : $heroOverview ?>
        </p>

        <div class="hero-cta">
          <?php if (!empty($trailerKey)): ?>
            <button class="btn-primary" id="openTrailer" data-video="<?= htmlspecialchars($trailerKey) ?>">
              ▶ Play Trailer
            </button>
          <?php endif; ?>

          <!-- ✅ FIXED HERE -->
          <a class="btn-outline" href="search.php?id=<?= $hero['id'] ?>">
            View Details
          </a>

        </div>
      </div>

      <div class="hero-poster">
        <?php if ($hero['poster_path']): ?>
          <img src="https://image.tmdb.org/t/p/w500<?= htmlspecialchars($hero['poster_path']) ?>" alt="<?= $heroTitle ?>">
        <?php endif; ?>
      </div>
    </div>
  </div>
</section>

<?php endif; ?>

<!-- SEARCH -->
<section class="search-section">
  <form action="search.php" method="get" class="search-form">
    <input type="text" name="q" placeholder="Search movies (e.g. Inception, Chhichhore)" required>
    <button type="submit" class="search-btn">Search</button>
  </form>
</section>

<!-- CATEGORIES -->
<section class="categories">
  <a href="discover.php?type=hollywood" class="cat">Hollywood</a>
  <a href="discover.php?type=bollywood" class="cat">Bollywood</a>
  <a href="discover.php?type=action" class="cat">Action</a>
  <a href="discover.php?type=romance" class="cat">Romance</a>
  <a href="discover.php?type=comedy" class="cat">Comedy</a>
</section>

<!-- TRENDING -->
<section class="section-heading">
  <h3>Trending Now</h3>
</section>

<section class="grid-container">
<?php if (!empty($movies)): ?>
  <?php foreach ($movies as $m): 
      $title = htmlspecialchars($m['title'] ?? $m['name'] ?? '');
      $poster = $m['poster_path'] ? "https://image.tmdb.org/t/p/w500".$m['poster_path'] : '';
      $rating = htmlspecialchars($m['vote_average'] ?? 'N/A');
  ?>

  <article class="card">
    <!-- ✅ FIXED HERE -->
    <a class="card-link" href="search.php?id=<?= $m['id'] ?>">

      <div class="card-thumb">
        <?php if ($poster): ?>
          <img src="<?= $poster ?>" alt="<?= $title ?>">
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
<?php else: ?>
  <p class="muted">No trending movies available right now.</p>
<?php endif; ?>
</section>

<footer class="site-footer">
  <small>© <?= date('Y') ?> MovieVault</small>
</footer>

</main>

<!-- TRAILER MODAL -->
<div id="trailerModal" class="modal" aria-hidden="true">
  <div class="modal-content">
    <button class="modal-close" id="closeTrailer">&times;</button>
    <div class="video-wrap" id="videoWrap"></div>
  </div>
</div>

<script>
(function(){
  const openBtn = document.getElementById('openTrailer');
  const modal = document.getElementById('trailerModal');
  const closeBtn = document.getElementById('closeTrailer');
  const videoWrap = document.getElementById('videoWrap');

  function openModal(key){
    if(!key) return;
    modal.style.display = 'flex';
    modal.setAttribute('aria-hidden','false');
    videoWrap.innerHTML =
      '<iframe width="100%" height="500" src="https://www.youtube.com/embed/'+key+'?rel=0&showinfo=0" frameborder="0" allowfullscreen></iframe>';
  }

  function closeModal(){
    modal.style.display = 'none';
    modal.setAttribute('aria-hidden','true');
    videoWrap.innerHTML = '';
  }

  if(openBtn){
    openBtn.addEventListener('click', function(){
      openModal(this.dataset.video);
    });
  }

  closeBtn.addEventListener('click', closeModal);
  modal.addEventListener('click', function(e){
    if(e.target === modal) closeModal();
  });

})();
</script>

</body>
</html>
