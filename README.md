# QuizVault

A multiple-choice quiz app built with PHP, MySQL, HTML, CSS, and JavaScript.

**Live Site:** https://quizvault.rf.gd/quiz-app

---

## Features

- Signup and login with password hashing
- Multiple-choice quiz with A/B/C/D buttons
- Choose number of questions: 5, 10, 15, or 20
- Optional countdown timer per question
- Anti-repeat logic so unseen questions show first
- Results page with score ring and accuracy stats
- Profile page with full play history
- Leaderboard showing top 10 players

---

## How to Run Locally

1. Copy the project into your MAMP `htdocs` folder
2. Open phpMyAdmin and create a database called `quiz_app`
3. Run `schema.sql` to create the tables
4. Update `config/db.php` with your local credentials
5. Visit `http://localhost:8888/quiz-app`

---

## Database Schema

**users**
- id — INT UNSIGNED, Primary key
- username — VARCHAR(30), Unique
- email — VARCHAR(255), Unique
- password — VARCHAR(255), bcrypt hash
- created_at — DATETIME, Auto timestamp

**scores**
- id — INT UNSIGNED, Primary key
- user_id — INT UNSIGNED, Foreign key to users
- score — INT, Number of correct answers
- total — INT, Total questions in that game
- time_taken — INT, Seconds used, NULL if no timer
- created_at — DATETIME, Auto timestamp

---

## Technologies

- PHP, MySQL, HTML, CSS, JavaScript
- Hosted on InfinityFree
