<?php
session_start();
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

require_once "config/db.php";
$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email    = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";

    if ($email && $password) {
        $stmt = $pdo->prepare("SELECT id, username, password FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            session_regenerate_id(true);
            $_SESSION['user_id']  = $user['id'];
            $_SESSION['username'] = $user['username'];
            header("Location: index.php");
            exit;
        } else {
            $error = "Incorrect email or password.";
        }
    } else {
        $error = "Please fill in all fields.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sign In — QuizVault</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
<div class="page">
    <div class="card animate-in">
        <div class="hero">
            <a href="index.php" class="nav-logo" style="display:inline-block; margin-bottom:1rem;">QuizVault</a>
            <h2>Welcome back</h2>
            <p>Sign in to continue your streak.</p>
        </div>

        <?php if ($error): ?>
            <div class="msg msg-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label for="email">Email</label>
                <input id="email" name="email" type="email" placeholder="you@example.com" required
                       value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input id="password" name="password" type="password" placeholder="Your password" required>
            </div>
            <button type="submit" class="btn btn-primary" style="margin-top:.5rem;">Sign In</button>
        </form>

        <p class="auth-link">No account? <a href="signup.php">Sign up free</a></p>
    </div>
</div>
</body>
</html>