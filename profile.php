<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

require_once "config/db.php";
$uid = (int)$_SESSION['user_id'];

// Summary stats
$stmt = $pdo->prepare("
    SELECT
        COUNT(*)               AS games,
        MAX(score)             AS best,
        ROUND(AVG(score), 1)   AS avg,
        SUM(time_taken)        AS total_time
    FROM scores
    WHERE user_id = ?
");
$stmt->execute([$uid]);
$stats = $stmt->fetch();

// Full history, newest first (last 50 games)
$hist = $pdo->prepare("
    SELECT score, total, time_taken, created_at
    FROM scores
    WHERE user_id = ?
    ORDER BY created_at DESC
    LIMIT 50
");
$hist->execute([$uid]);
$rows = $hist->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Profile - QuizVault</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>

<nav>
    <a href="index.php" class="nav-logo">QuizVault</a>
    <ul class="nav-links">
        <li><a href="index.php">Home</a></li>
        <li><a href="leaderboard.php">Leaderboard</a></li>
        <li><a href="logout.php">Logout</a></li>
    </ul>
</nav>

<div class="page has-nav">
    <div class="card card-wide animate-in">

        <p class="label" style="margin-bottom:.4rem;">Player Profile</p>
        <h1 style="margin-bottom:1.5rem;"><?= htmlspecialchars($_SESSION['username']) ?></h1>

        <?php if ((int)$stats['games'] > 0): ?>

            <div class="stats-grid" style="margin-top:0;">
                <div class="stat-card">
                    <span class="stat-val"><?= (int)$stats['games'] ?></span>
                    <span class="stat-label">Games</span>
                </div>
                <div class="stat-card">
                    <span class="stat-val"><?= (int)$stats['best'] ?></span>
                    <span class="stat-label">Best Score</span>
                </div>
                <div class="stat-card">
                    <span class="stat-val"><?= $stats['avg'] ?? '--' ?></span>
                    <span class="stat-label">Average</span>
                </div>
            </div>

            <div class="divider">Play History</div>

            <?php if (empty($rows)): ?>
                <p style="color:var(--muted); text-align:center; padding:1rem 0;">No games recorded yet.</p>
            <?php else: ?>
                <?php foreach ($rows as $row):
                    $rowTotal = (int)($row['total'] ?? 10);
                    $rowScore = (int)$row['score'];
                    $p        = $rowTotal > 0 ? ($rowScore / $rowTotal) : 0;
                    $cls      = $p >= 0.8 ? 'pill-high' : ($p >= 0.5 ? 'pill-mid' : 'pill-low');
                    $dt       = date("M j, Y g:ia", strtotime($row['created_at']));
                    $t        = $row['time_taken']
                        ? (intdiv((int)$row['time_taken'], 60) . 'm ' . ((int)$row['time_taken'] % 60) . 's')
                        : '--';
                    ?>
                    <div class="history-row">
                        <span class="history-date"><?= htmlspecialchars($dt) ?></span>
                        <span class="history-date"><?= htmlspecialchars($t) ?></span>
                        <span class="score-pill <?= $cls ?>"><?= $rowScore ?>/<?= $rowTotal ?></span>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>

        <?php else: ?>
            <div class="empty">
                <p>You have not played yet.</p>
                <p style="margin-top:.5rem;"><a href="quiz.php">Start your first quiz</a></p>
            </div>
        <?php endif; ?>

        <div class="btn-row" style="margin-top:1.75rem;">
            <a href="quiz.php" class="btn btn-primary">Play Now</a>
            <a href="logout.php" class="btn btn-danger">Log Out</a>
        </div>

    </div>
</div>

</body>
</html>