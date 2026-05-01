<?php
include 'config.php';
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Search History - MovieVault</title>
<link rel="stylesheet" href="style.css">
<style>
body { background:#121212; color:white; font-family:Arial; margin:0; }
.wrap { max-width:700px; margin:40px auto; padding:0 10px; }
table { width:100%; border-collapse:collapse; margin-top:20px; }
th, td { border:1px solid #333; padding:8px; text-align:left; }
th { background:#ff9800; color:black; }
tr:nth-child(even) { background:#1e1e1e; }
</style>
</head>
<body>

<?php include 'navbar.php'; ?>

<div class="wrap">
<h2>🔎 Search History</h2>
<table>
<tr><th>ID</th><th>Search Query</th><th>Date/Time</th></tr>
<?php
$result = $conn->query("SELECT * FROM search_history ORDER BY searched_at DESC");

if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        echo "<tr>
                <td>{$row['id']}</td>
                <td>".htmlspecialchars($row['query'])."</td>
                <td>{$row['searched_at']}</td>
              </tr>";
    }
} else {
    echo "<tr><td colspan='3'>No searches yet.</td></tr>";
}
?>
</table>
</div>

</body>
</html>
