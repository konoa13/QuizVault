<?php

session_start();

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(["error" => "Unauthorized. Please log in."]);
    exit;
}

header("Content-Type: application/json");

$count = min(20, max(5, (int)($_GET['count'] ?? 10)));

$file = dirname(__DIR__) . "/data/questions.json";

if (!file_exists($file)) {
    http_response_code(500);
    echo json_encode([
        "error" => "questions.json not found. Expected at: " . $file
    ]);
    exit;
}

$raw = file_get_contents($file);
$all = json_decode($raw, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(500);
    echo json_encode(["error" => "questions.json is invalid JSON: " . json_last_error_msg()]);
    exit;
}

if (!is_array($all) || count($all) === 0) {
    http_response_code(500);
    echo json_encode(["error" => "questions.json is empty or not an array."]);
    exit;
}

// ---- Anti-repeat: show unseen questions first ----
$seen   = $_SESSION['seen_questions'] ?? [];
$unseen = [];
$seenQ  = [];

foreach ($all as $q) {
    $key = isset($q['id']) ? (string)$q['id'] : $q['question'];
    if (in_array($key, $seen, true)) {
        $seenQ[] = $q;
    } else {
        $unseen[] = $q;
    }
}

shuffle($unseen);
shuffle($seenQ);

$pool     = array_merge($unseen, $seenQ);
$selected = array_slice($pool, 0, $count);

// Track seen
$newSeen = array_map(function($q) {
    return isset($q['id']) ? (string)$q['id'] : $q['question'];
}, $selected);

$_SESSION['seen_questions'] = array_slice(
    array_unique(array_merge($seen, $newSeen)),
    -max(60, $count * 3)
);

// ---- Normalise each question into { question, options[], correct } ----
$letterKeys = ['A', 'B', 'C', 'D', 'E'];

$out = [];

foreach ($selected as $q) {

    $questionText = isset($q['question']) ? (string)$q['question'] : null;
    if (!$questionText) continue;

    // ---- Detect which format this question uses ----
    if (isset($q['A'])) {
        // Format: individual letter keys  "A": "stop", "B": "speed up", "answer": "A"
        $opts = [];
        foreach ($letterKeys as $letter) {
            if (isset($q[$letter])) {
                $opts[$letter] = (string)$q[$letter];
            }
        }

        if (empty($opts)) continue;

        $answerLetter = strtoupper(trim((string)($q['answer'] ?? 'A')));

        $optionTexts = [];
        $correctIdx  = 0;
        $i = 0;
        foreach ($opts as $letter => $text) {
            $optionTexts[] = $text;
            if ($letter === $answerLetter) {
                $correctIdx = $i;
            }
            $i++;
        }

    } else {
        // Format: options array with numeric correct index
        $optionTexts = $q['options'] ?? $q['answers'] ?? $q['choices'] ?? [];
        $correctIdx  = $q['correct'] ?? $q['answer'] ?? $q['correctAnswer'] ?? $q['correct_answer'] ?? 0;

        if (empty($optionTexts)) continue;

        if (is_string($correctIdx) && !is_numeric($correctIdx)) {
            $found      = array_search($correctIdx, $optionTexts);
            $correctIdx = $found !== false ? (int)$found : 0;
        }
        $correctIdx = (int)$correctIdx;
    }

    // ---- Shuffle options while keeping track of which is correct ----
    $pairs = [];
    foreach ($optionTexts as $i => $text) {
        $pairs[] = ['text' => $text, 'orig' => $i];
    }
    shuffle($pairs);

    $newCorrect = 0;
    foreach ($pairs as $j => $p) {
        if ($p['orig'] === $correctIdx) {
            $newCorrect = $j;
            break;
        }
    }

    $out[] = [
        'question' => $questionText,
        'options'  => array_column($pairs, 'text'),
        'correct'  => $newCorrect,
    ];
}

if (empty($out)) {
    http_response_code(500);
    echo json_encode([
        "error" => "No valid questions could be parsed. Each question needs a 'question' field, A/B/C/D option fields, and an 'answer' field with the correct letter."
    ]);
    exit;
}

echo json_encode($out);