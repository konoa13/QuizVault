<?php
/*
 * config/db.php
 * Edit the four constants below to match your hosting environment.
 * On most shared hosts: host=localhost, port=3306, user/pass from your control panel.
 * On MAMP (local): host=localhost, port=8889, user=root, pass=root
 */

$host    = "localhost";
$db      = "quiz_app";
$user    = "root";
$pass    = "root";
$port    = 3306;          // Change to 8889 if using MAMP locally
$charset = "utf8mb4";

$dsn = "mysql:host=$host;port=$port;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    http_response_code(500);
    die(json_encode(["error" => "Database connection failed: " . $e->getMessage()]));
}