<?php
session_start();
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

require_once "config/db.php";
$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST["username"] ?? "");
    $email    = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";

    if (strlen($username) < 3 || strlen($username) > 30) {
        $error = "Username must be 3 to 30 characters.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters.";
    } else {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);
        if ($stmt->fetch()) {
            $error = "That username or email is already taken.";
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
            $stmt->execute([$username, $email, $hash]);
            session_regenerate_id(true);
            $_SESSION['user_id']  = $pdo->lastInsertId();
            $_SESSION['username'] = $username;
            header("Location: index.php");
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sign Up — QuizVault</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
<div class="page">
    <div class="card animate-in">
        <div class="hero">
            <a href="index.php" class="nav-logo" style="display:inline-block; margin-bottom:1rem;">QuizVault</a>
            <h2>Create an account</h2>
            <p>Start your quiz journey today.</p>
        </div>

        <?php if ($error): ?>
            <div class="msg msg-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" autocomplete="off">
            <div class="form-group">
                <label for="username">Username</label>
                <input id="username" name="username" type="text" placeholder="e.g. quizmaster99" maxlength="30" required
                       value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input id="email" name="email" type="email" placeholder="you@example.com" required
                       value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input id="password" name="password" type="password" placeholder="Minimum 6 characters" required>
            </div>
            <button type="submit" class="btn btn-primary" style="margin-top:.5rem;">Create Account</button>
        </form>

        <p class="auth-link">Already have an account? <a href="login.php">Sign in</a></p>
    </div>
</div>
</body>
</html>