<?php
session_start();
include "config.php";

$error = "";

// LOGIN
if (isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM users WHERE username=?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows == 1) {
        $row = $res->fetch_assoc();

        if (password_verify($password, $row['password'])) {
            $_SESSION['user'] = $row['username'];
            $_SESSION['user_id'] = $row['id'];
            header("Location: index.php");
            exit();
        } else {
            $error = "Wrong password";
        }
    } else {
        $error = "User not found";
    }
}

// REGISTER
if (isset($_POST['register'])) {
    $username = $_POST['reg_username'];
    $email    = $_POST['email'];
    $password = password_hash($_POST['reg_password'], PASSWORD_DEFAULT);

    $check = $conn->prepare("SELECT id FROM users WHERE username=? OR email=?");
    $check->bind_param("ss", $username, $email);
    $check->execute();
    $res = $check->get_result();

    if ($res->num_rows > 0) {
        $error = "User already exists";
    } else {
        $stmt = $conn->prepare("INSERT INTO users (username,email,password) VALUES (?,?,?)");
        $stmt->bind_param("sss", $username, $email, $password);

        if ($stmt->execute()) {
            $error = "Registration successful! Please login.";
        } else {
            $error = "Registration failed";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<title>MovieVault Auth</title>
<style>
body {
    margin:0;
    font-family:Arial;
    background:#0f0f0f;
    color:white;
    display:flex;
    justify-content:center;
    align-items:center;
    height:100vh;
}

.container {
    background:#181818;
    padding:30px;
    border-radius:12px;
    width:350px;
    text-align:center;
    box-shadow:0 0 20px rgba(229,9,20,0.3);
}

.back {
    text-align:left;
    margin-bottom:15px;
}

.back a {
    color:#aaa;
    text-decoration:none;
    font-size:14px;
}

.back a:hover {
    color:#fff;
}

h2 {
    margin-bottom:20px;
}

input {
    width:100%;
    padding:10px;
    margin:8px 0;
    border:none;
    border-radius:6px;
    background:#2c2c2c;
    color:white;
}

button {
    width:100%;
    padding:10px;
    background:#e50914;
    color:white;
    border:none;
    border-radius:6px;
    cursor:pointer;
    font-weight:bold;
}

button:hover {
    background:#ff2a2a;
}

.switch {
    margin-top:15px;
    cursor:pointer;
    color:#aaa;
}

.switch:hover {
    color:#fff;
}

.error {
    color:#ff4d4d;
    margin-bottom:10px;
}
</style>
</head>

<body>

<div class="container">

<!-- 🔙 BACK BUTTON -->
<div class="back">
    <a href="index.php">← Back to Home</a>
</div>

<?php if ($error): ?>
<div class="error"><?= $error ?></div>
<?php endif; ?>

<!-- LOGIN FORM -->
<div id="loginBox">
<h2>Login</h2>
<form method="POST">
<input type="text" name="username" placeholder="Username" required>
<input type="password" name="password" placeholder="Password" required>
<button name="login">Login</button>
</form>
<div class="switch" onclick="toggleForm()">New user? Register</div>
</div>

<!-- REGISTER FORM -->
<div id="registerBox" style="display:none;">
<h2>Register</h2>
<form method="POST">
<input type="text" name="reg_username" placeholder="Username" required>
<input type="email" name="email" placeholder="Email" required>
<input type="password" name="reg_password" placeholder="Password" required>
<button name="register">Register</button>
</form>
<div class="switch" onclick="toggleForm()">Already have account? Login</div>
</div>

</div>

<script>
function toggleForm() {
    let login = document.getElementById("loginBox");
    let register = document.getElementById("registerBox");

    if (login.style.display === "none") {
        login.style.display = "block";
        register.style.display = "none";
    } else {
        login.style.display = "none";
        register.style.display = "block";
    }
}
</script>

</body>
</html>