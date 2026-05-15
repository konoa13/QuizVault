QuizVault
A multiple-choice quiz web application built with PHP, MySQL, HTML, CSS, and JavaScript.
Live Site: https://quizvault.rf.gd/quiz-app

Features

User signup and login with password hashing
Multiple-choice quiz with A/B/C/D answer buttons
Configurable number of questions (5, 10, 15, or 20)
Optional per-question countdown timer (none, 15s, 30s, 60s)
Anti-repeat logic so unseen questions are shown first
Score saved to database after every game
Results page with animated score ring and accuracy stats
Profile page showing full play history
Leaderboard showing top 10 players with your rank highlighted
Replay or change settings from the results page


How to Run Locally
Requirements

PHP 8.0 or higher
MySQL 5.7 or higher
MAMP, XAMPP, or any local PHP server

Setup

Copy the project folder into your MAMP htdocs directory
Start MAMP and open phpMyAdmin at http://localhost:8888/phpMyAdmin
Create a database called quiz_app
Click the SQL tab, paste the contents of schema.sql, and click Go
Open config/db.php and set your local credentials:

MAMP default: host=localhost, port=8889, user=root, pass=root


Visit http://localhost:8888/quiz-app


Database Schema
users
ColumnTypeNotesidINT UNSIGNEDPrimary key, auto-incrementusernameVARCHAR(30)UniqueemailVARCHAR(255)UniquepasswordVARCHAR(255)bcrypt hashcreated_atDATETIMEDefault: current timestamp
scores
ColumnTypeNotesidINT UNSIGNEDPrimary key, auto-incrementuser_idINT UNSIGNEDForeign key to users.idscoreINTNumber of correct answerstotalINTTotal questions in that gametime_takenINTSeconds used; NULL if no timercreated_atDATETIMEDefault: current timestamp

Technologies Used

Frontend: HTML, CSS, JavaScript
Backend: PHP
Database: MySQL
Hosted on: InfinityFree
