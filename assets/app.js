let questions = [];
let current = 0;
let score = 0;

async function loadQuiz() {
    const res = await fetch("api/questions.php");
    questions = await res.json();

    renderQuestion();
}

function renderQuestion() {
    const q = questions[current];

    document.getElementById("quiz-box").innerHTML = `
        <h2>${q.question}</h2>
        ${q.options.map((opt, i) =>
        `<button onclick="select(${i})">${opt}</button>`
    ).join("")}
    `;
}

function select(i) {
    if (i === questions[current].correct) {
        score++;
    }

    current++;

    if (current < questions.length) {
        renderQuestion();
    } else {
        submitQuiz();
    }
}

function submitQuiz() {
    window.location.href = `results.php?score=${score}`;
}

loadQuiz();