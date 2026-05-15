<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

require_once "config/db.php";

$score = max(0, (int)($_GET['score'] ?? 0));
$total = max(1, (int)($_GET['total'] ?? 10));
$time  = max(0, (int)($_GET['time']  ?? 0));
$timer = max(0, (int)($_GET['timer'] ?? 30));   // original timer setting for replay link
$uid   = (int)$_SESSION['user_id'];

$score = min($score, $total);

// Save score to database
$stmt = $pdo->prepare(
    "INSERT INTO scores (user_id, score, total, time_taken) VALUES (?, ?, ?, ?)"
);
$stmt->execute([$uid, $score, $total, ($time > 0 ? $time : null)]);

$pct = $total > 0 ? ($score / $total) : 0;

if ($pct >= 1.0)       { $msg = "Perfect score. Flawless."; }
elseif ($pct >= 0.8)   { $msg = "Excellent work. Almost perfect."; }
elseif ($pct >= 0.6)   { $msg = "Good job. Room to improve."; }
elseif ($pct >= 0.4)   { $msg = "Not bad. Keep practising."; }
else                   { $msg = "Tough round. You will do better next time."; }

$pctVal = round($pct * 100);

$timeStr = "";
if ($time > 0) {
    $m = intdiv($time, 60);
    $s = $time % 60;
    $timeStr = $m > 0 ? "{$m}m {$s}s" : "{$s}s";
}

// Circle arc for the score ring (r = 66)
$circ = round(2 * M_PI * 66, 1);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Results - QuizVault</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>

<nav>
    <a href="index.php" class="nav-logo">QuizVault</a>
    <ul class="nav-links">
        <li><a href="leaderboard.php">Leaderboard</a></li>
        <li><a href="profile.php">Profile</a></li>
    </ul>
</nav>

<div class="page has-nav">
    <div class="card animate-in" style="text-align:center;">

        <p class="label" style="margin-bottom:1.5rem;">Quiz Complete</p>

        <div class="score-ring">
            <svg viewBox="0 0 148 148" xmlns="http://www.w3.org/2000/svg" width="148" height="148">
                <defs>
                    <linearGradient id="g1" x1="0%" y1="0%" x2="100%" y2="0%">
                        <stop offset="0%"   stop-color="#c8f135"/>
                        <stop offset="100%" stop-color="#4ff8d2"/>
                    </linearGradient>
                </defs>
                <circle cx="74" cy="74" r="66" stroke="rgba(255,255,255,0.06)" stroke-width="4"/>
                <circle cx="74" cy="74" r="66"
                        stroke="url(#g1)"
                        stroke-width="4"
                        stroke-linecap="round"
                        stroke-dasharray="<?= $circ ?>"
                        stroke-dashoffset="<?= $circ ?>"
                        id="arc"/>
            </svg>
            <span class="score-num"><?= $score ?></span>
            <span class="score-denom">/ <?= $total ?></span>
        </div>

        <h2 style="margin-bottom:.4rem;"><?= htmlspecialchars($msg) ?></h2>
        <p style="color:var(--muted); font-family:'Space Mono',monospace; font-size:.8rem; margin-bottom:1.75rem;">
            <?= $pctVal ?>% accuracy<?= $timeStr ? " &nbsp;|&nbsp; " . htmlspecialchars($timeStr) : "" ?>
        </p>

        <div class="stats-grid">
            <div class="stat-card">
                <span class="stat-val"><?= $score ?></span>
                <span class="stat-label">Correct</span>
            </div>
            <div class="stat-card">
                <span class="stat-val"><?= $total - $score ?></span>
                <span class="stat-label">Wrong</span>
            </div>
            <div class="stat-card">
                <span class="stat-val"><?= $pctVal ?>%</span>
                <span class="stat-label">Accuracy</span>
            </div>
        </div>

        <div class="btn-row" style="margin-top:1.5rem;">
            <a href="quiz.php?count=<?= (int)$total ?>&timer=<?= (int)$timer ?>" class="btn btn-primary">Play Again</a>
            <a href="index.php" class="btn btn-ghost">Change Settings</a>
            <a href="leaderboard.php" class="btn btn-ghost">View Leaderboard</a>
        </div>

    </div>
</div>

<script>
    var arc  = document.getElementById('arc');
    var pct  = <?= $pctVal ?> / 100;
    var circ = <?= $circ ?>;

    setTimeout(function() {
        arc.style.transition = 'stroke-dashoffset 1.1s cubic-bezier(0.4,0,0.2,1)';
        arc.style.strokeDashoffset = circ - (circ * pct);
    }, 120);
</script>

</body>
</html>