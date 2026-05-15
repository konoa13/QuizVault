<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

require_once "config/db.php";
$uid = (int)$_SESSION['user_id'];

$stmt = $pdo->prepare("
    SELECT COUNT(*) AS games,
           MAX(score) AS best,
           ROUND(AVG(score), 1) AS avg
    FROM scores WHERE user_id = ?
");
$stmt->execute([$uid]);
$stats = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>QuizVault</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>

<nav>
    <a href="index.php" class="nav-logo">QuizVault</a>
    <ul class="nav-links">
        <li><a href="leaderboard.php">Leaderboard</a></li>
        <li><a href="profile.php">Profile</a></li>
        <li><a href="logout.php">Logout</a></li>
    </ul>
</nav>

<div class="page has-nav">
    <div class="card card-wide animate-in">

        <div class="hero">
            <h1>Hey, <span class="accent-text"><?= htmlspecialchars($_SESSION['username']) ?></span></h1>
            <p>Test your knowledge. No two games are the same.</p>
        </div>

        <?php if ($stats['games'] > 0): ?>
            <div class="stats-grid">
                <div class="stat-card">
                    <span class="stat-val"><?= (int)$stats['games'] ?></span>
                    <span class="stat-label">Games</span>
                </div>
                <div class="stat-card">
                    <span class="stat-val"><?= (int)$stats['best'] ?></span>
                    <span class="stat-label">Best</span>
                </div>
                <div class="stat-card">
                    <span class="stat-val"><?= $stats['avg'] ?? '—' ?></span>
                    <span class="stat-label">Average</span>
                </div>
            </div>
        <?php endif; ?>

        <div class="divider">Quiz Settings</div>

        <form action="quiz.php" method="GET">
            <div class="settings-row">
                <label for="count">Number of questions</label>
                <select name="count" id="count">
                    <option value="5">5 questions</option>
                    <option value="10" selected>10 questions</option>
                    <option value="15">15 questions</option>
                    <option value="20">20 questions</option>
                </select>
            </div>
            <div class="settings-row">
                <label for="timer">Timer per question</label>
                <select name="timer" id="timer">
                    <option value="0">No timer</option>
                    <option value="15">15 seconds</option>
                    <option value="30" selected>30 seconds</option>
                    <option value="60">60 seconds</option>
                </select>
            </div>

            <div class="btn-row">
                <button type="submit" class="btn btn-primary">Start Quiz</button>
                <a href="leaderboard.php" class="btn btn-ghost">View Leaderboard</a>
                <a href="profile.php" class="btn btn-ghost">My History</a>
            </div>
        </form>

    </div>
</div>

</body>
</html>