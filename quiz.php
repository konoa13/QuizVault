<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$count = min(20, max(5, (int)($_GET['count'] ?? 10)));
$timer = max(0, (int)($_GET['timer'] ?? 30));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Quiz - QuizVault</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>

<nav>
    <a href="index.php" class="nav-logo">QuizVault</a>
    <ul class="nav-links">
        <li><a href="index.php" onclick="return confirm('Quit the quiz? Your progress will be lost.')">Quit</a></li>
    </ul>
</nav>

<div class="page has-nav">
    <div class="card card-wide animate-in" id="quiz-card">

        <div class="quiz-header">
            <span class="label" id="q-label">Loading...</span>
            <?php if ($timer > 0): ?>
                <div class="timer" id="timer-display">--</div>
            <?php endif; ?>
        </div>

        <div class="progress-bar">
            <div class="progress-fill" id="progress" style="width:0%"></div>
        </div>

        <div id="quiz-body">
            <div class="spinner"></div>
        </div>

    </div>
</div>

<script>
    var TOTAL   = <?= (int)$count ?>;
    var TIMER_S = <?= (int)$timer ?>;

    var questions     = [];
    var current       = 0;
    var score         = 0;
    var answered      = false;
    var timerInterval = null;
    var timeLeft      = TIMER_S;
    var totalTime     = 0;

    var qBody     = document.getElementById('quiz-body');
    var qProgress = document.getElementById('progress');
    var qLabel    = document.getElementById('q-label');
    var timerEl   = document.getElementById('timer-display');

    function loadQuestions() {
        var url = 'api/questions.php?count=' + TOTAL;
        fetch(url)
            .then(function(res) {
                return res.json().then(function(data) {
                    if (!res.ok) {
                        throw new Error(data.error || 'Server error ' + res.status);
                    }
                    return data;
                });
            })
            .then(function(data) {
                if (!Array.isArray(data) || data.length === 0) {
                    throw new Error('No questions returned from server.');
                }
                questions = data;
                showQuestion();
            })
            .catch(function(err) {
                qBody.innerHTML =
                    '<div class="msg msg-error">' +
                    'Failed to load questions: ' + err.message +
                    '<br><br>Make sure data/questions.json exists in your project root.' +
                    '</div>';
            });
    }

    function showQuestion() {
        answered = false;
        clearInterval(timerInterval);

        var q   = questions[current];
        var num = current + 1;

        qLabel.textContent = 'Question ' + num + ' of ' + TOTAL;
        qProgress.style.width = ((current / TOTAL) * 100) + '%';

        var letters  = ['A', 'B', 'C', 'D', 'E'];
        var optsHtml = '';

        for (var i = 0; i < q.options.length; i++) {
            optsHtml +=
                '<button class="option-btn" data-index="' + i + '">' +
                '<span class="opt-letter">' + (letters[i] || i) + '</span>' +
                '<span class="opt-text">' + escHtml(q.options[i]) + '</span>' +
                '</button>';
        }

        qBody.innerHTML =
            '<p class="question-text">' + escHtml(q.question) + '</p>' +
            '<div class="options-grid" id="options-grid">' + optsHtml + '</div>' +
            '<div id="feedback-box"></div>';

        var btns = document.querySelectorAll('.option-btn');
        for (var b = 0; b < btns.length; b++) {
            btns[b].addEventListener('click', handleAnswer);
        }

        if (TIMER_S > 0) {
            startTimer();
        }
    }

    function handleAnswer() {
        if (answered) return;
        var idx = parseInt(this.getAttribute('data-index'), 10);
        selectAnswer(idx);
    }

    function selectAnswer(idx) {
        if (answered) return;
        answered = true;

        clearInterval(timerInterval);
        if (TIMER_S > 0) {
            totalTime += (TIMER_S - timeLeft);
        }

        var correct = questions[current].correct;
        var isRight = (idx === correct);
        if (isRight) score++;

        disableAll();
        highlightAnswers(correct, idx);

        var feedback = document.getElementById('feedback-box');
        if (isRight) {
            feedback.innerHTML = '<div class="feedback-box feedback-correct">Correct!</div>';
        } else {
            feedback.innerHTML =
                '<div class="feedback-box feedback-wrong">' +
                'Wrong - the correct answer was: ' +
                escHtml(questions[current].options[correct]) +
                '</div>';
        }

        setTimeout(nextQuestion, 1400);
    }

    function nextQuestion() {
        current++;
        if (current < questions.length) {
            showQuestion();
        } else {
            finishQuiz();
        }
    }

    function highlightAnswers(correct, selected) {
        var btns = document.querySelectorAll('.option-btn');
        for (var i = 0; i < btns.length; i++) {
            var idx = parseInt(btns[i].getAttribute('data-index'), 10);
            if (idx === correct) {
                btns[i].classList.add('correct');
            } else if (idx === selected && selected !== correct) {
                btns[i].classList.add('wrong');
            }
        }
    }

    function disableAll() {
        var btns = document.querySelectorAll('.option-btn');
        for (var i = 0; i < btns.length; i++) {
            btns[i].disabled = true;
        }
    }

    function startTimer() {
        timeLeft = TIMER_S;
        updateTimerDisplay();
        timerInterval = setInterval(function() {
            timeLeft--;
            totalTime++;
            updateTimerDisplay();
            if (timeLeft <= 0) {
                clearInterval(timerInterval);
                if (!answered) timeOut();
            }
        }, 1000);
    }

    function updateTimerDisplay() {
        if (!timerEl) return;
        timerEl.textContent = timeLeft + 's';
        if (timeLeft <= 5) {
            timerEl.classList.add('urgent');
        } else {
            timerEl.classList.remove('urgent');
        }
    }

    function timeOut() {
        answered = true;
        disableAll();
        var correct = questions[current].correct;
        highlightAnswers(correct, -1);
        var feedback = document.getElementById('feedback-box');
        feedback.innerHTML =
            '<div class="feedback-box feedback-wrong">' +
            "Time's up! The correct answer was: " +
            escHtml(questions[current].options[correct]) +
            '</div>';
        setTimeout(nextQuestion, 1400);
    }

    function finishQuiz() {
        qProgress.style.width = '100%';
        var params = 'score=' + score + '&total=' + TOTAL + '&time=' + totalTime + '&timer=' + TIMER_S;
        window.location.href = 'results.php?' + params;
    }

    function escHtml(str) {
        var d = document.createElement('div');
        d.appendChild(document.createTextNode(str));
        return d.innerHTML;
    }

    loadQuestions();
</script>

</body>
</html>