<?php
session_start();
include "config.php";

// If already logged in → go to homepage
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Get input safely
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    // Use prepared statement (SECURITY FIX)
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();

    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();

        // Verify password
        if (password_verify($password, $row['password'])) {

            // ✅ Store session data
            $_SESSION['user'] = $row['username'];
            $_SESSION['user_id'] = $row['id']; // 🔥 IMPORTANT

            // Redirect to homepage
            header("Location: index.php");
            exit();

        } else {
            $error = "Wrong password";
        }

    } else {
        $error = "User not found";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
</head>
<body>

<h2>Login</h2>

<?php if (isset($error)) echo "<p style='color:red;'>$error</p>"; ?>

<form method="POST">
    <input type="text" name="username" placeholder="Enter Username" required><br><br>
    <input type="password" name="password" placeholder="Enter Password" required><br><br>
    <button type="submit">Login</button>
</form>

<p>New user? <a href="register.php">Register here</a></p>

</body>
</html>