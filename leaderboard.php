<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

require_once "config/db.php";
$uid = (int)$_SESSION['user_id'];

// Top 10 by best score, average as tiebreaker
$top = $pdo->query("
    SELECT
        u.id,
        u.username,
        MAX(s.score)          AS best_score,
        ROUND(AVG(s.score),1) AS avg_score,
        COUNT(*)              AS games
    FROM users u
    JOIN scores s ON u.id = s.user_id
    GROUP BY u.id, u.username
    ORDER BY best_score DESC, avg_score DESC
    LIMIT 10
")->fetchAll();

// Current user rank (count how many players beat them)
$myBestStmt = $pdo->prepare("SELECT MAX(score) FROM scores WHERE user_id = ?");
$myBestStmt->execute([$uid]);
$myBest = $myBestStmt->fetchColumn();

$myRank = null;
if ($myBest !== false && $myBest !== null) {
    $rankStmt = $pdo->prepare("
        SELECT COUNT(*) + 1
        FROM (
            SELECT MAX(score) AS best
            FROM scores
            GROUP BY user_id
        ) ranked
        WHERE best > ?
    ");
    $rankStmt->execute([$myBest]);
    $myRank = (int)$rankStmt->fetchColumn();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Leaderboard - QuizVault</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>

<nav>
    <a href="index.php" class="nav-logo">QuizVault</a>
    <ul class="nav-links">
        <li><a href="index.php">Home</a></li>
        <li><a href="profile.php">Profile</a></li>
        <li><a href="logout.php">Logout</a></li>
    </ul>
</nav>

<div class="page has-nav">
    <div class="card card-wide animate-in">

        <div style="display:flex; align-items:flex-start; justify-content:space-between; margin-bottom:1.75rem; flex-wrap:wrap; gap:1rem;">
            <div>
                <p class="label" style="margin-bottom:.4rem;">Global Rankings</p>
                <h1>Leaderboard</h1>
            </div>
            <?php if ($myRank): ?>
                <div style="text-align:right;">
                    <p class="label" style="margin-bottom:.25rem;">Your Rank</p>
                    <p style="font-size:2rem; font-weight:800; color:var(--accent); font-family:'Space Mono',monospace; line-height:1;">
                        #<?= (int)$myRank ?>
                    </p>
                    <?php if ($myBest !== null): ?>
                        <p style="font-size:.75rem; color:var(--muted); font-family:'Space Mono',monospace; margin-top:.25rem;">
                            Best: <?= (int)$myBest ?>
                        </p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>

        <?php if (empty($top)): ?>
            <div class="empty">
                <p>No scores yet.</p>
                <p style="margin-top:.5rem;"><a href="quiz.php">Be the first to play</a></p>
            </div>
        <?php else: ?>

            <?php foreach ($top as $i => $row):
                $rank = $i + 1;
                $cls  = $rank <= 3 ? "top-$rank" : "";
                $isMe = ((int)$row['id'] === $uid);
                if ($isMe) $cls .= ($cls ? " " : "") . "is-me";
                ?>
                <div class="lb-row <?= trim($cls) ?>">
                    <div class="lb-rank"><?= $rank ?></div>
                    <div>
                        <div class="lb-name">
                            <?= htmlspecialchars($row['username']) ?>
                            <?php if ($isMe): ?>
                                <span class="you-tag">YOU</span>
                            <?php endif; ?>
                        </div>
                        <div style="font-size:.72rem; color:var(--muted); font-family:'Space Mono',monospace; margin-top:.15rem;">
                            <?= (int)$row['games'] ?> game<?= $row['games'] != 1 ? 's' : '' ?>
                            &nbsp;|&nbsp; avg <?= $row['avg_score'] ?>
                        </div>
                    </div>
                    <div class="lb-score"><?= (int)$row['best_score'] ?></div>
                </div>
            <?php endforeach; ?>

        <?php endif; ?>

        <div class="btn-row" style="margin-top:1.5rem;">
            <a href="quiz.php" class="btn btn-primary">Play to Climb the Ranks</a>
        </div>

    </div>
</div>

</body>
</html>