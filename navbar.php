<?php
// session already started in index.php
?>

<style>
nav {
  position: relative;
  z-index: 10000;
  background: #141414;
  padding: 14px 20px;
  display: flex;
  justify-content: center;
  align-items: center;
  border-bottom: 3px solid #e50914;
  font-family: 'Arial', sans-serif;
}

.nav-left {
  display: flex;
  gap: 25px;
  align-items: center;
  justify-content: center; 
}

nav a {
  color: #fff;
  text-decoration: none;
  font-weight: 600;
  padding: 6px 10px;
  transition: color 0.3s ease;
}

nav a:hover {
  color: #e50914;
}

.nav-right {
  position: absolute; 
  right: 20px;         
  display: flex;
  gap: 15px;
  align-items: center;
  color: white;
  font-weight: 500;
}

.dropdown {
  position: relative;
}

.dropdown-content {
  display: none;
  position: absolute;
  background-color: #1c1c1c;
  min-width: 180px;
  box-shadow: 0px 8px 16px rgba(0,0,0,0.3);
  border-radius: 6px;
  z-index: 9999;
}

.dropdown-content a {
  color: white;
  padding: 10px 14px;
  display: block;
}

.dropdown-content a:hover {
  background-color: rgba(255, 255, 255, 0.08);
}

.dropdown:hover .dropdown-content {
  display: block;
}

@media (max-width: 700px) {
  nav {
    flex-direction: column;
    gap: 12px;
  }
}
</style>

<nav>

  <!-- LEFT -->
  <div class="nav-left">
    <a href="index.php">🏠 Home</a>
    <a href="trending.php">🔥 Trending</a>

    <div class="dropdown">
      <a href="#">🔎 Discover ▾</a>
      <div class="dropdown-content">
        <a href="discover.php?type=hollywood">🎥 Hollywood</a>
        <a href="discover.php?type=bollywood">🇮🇳 Bollywood</a>
        <a href="discover.php?type=romance">💞 Romance</a>
        <a href="discover.php?type=action">💥 Action</a>
        <a href="discover.php?type=comedy">😂 Comedy</a>
        <a href="discover.php?type=horror">👻 Horror</a>
      </div>
    </div>

    <a href="view_saved.php">💾 Saved</a>
  </div>

  <!-- RIGHT -->
  <div class="nav-right">

    <?php if (isset($_SESSION['user'])): ?>
      👤 Welcome, <?= htmlspecialchars($_SESSION['user']); ?> |
      <a href="logout.php" style="color:#e50914;">Logout</a>

    <?php else: ?>
      <a href="auth.php">Login</a>
      <a href="auth.php" style="color:#e50914;">Register</a>
    <?php endif; ?>

  </div>

</nav>