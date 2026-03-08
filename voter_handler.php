<?php
require 'config.php';
session_start();

// Check login
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Only allow POST
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: dashboard.php");
    exit;
}

// Check candidate selected
if (!isset($_POST['candidate_id'])) {
    $_SESSION['msg'] = ["type" => "warning", "text" => "Please select a candidate."];
    header("Location: dashboard.php");
    exit;
}

$candidate_id = (int)$_POST['candidate_id'];

/*
-------------------------------------
1️⃣ Get position of selected candidate
-------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT p.name 
    FROM candidates c
    JOIN positions p ON c.position_id = p.id
    WHERE c.id = ?
");
$stmt->execute([$candidate_id]);
$position = $stmt->fetchColumn();

if (!$position) {
    $_SESSION['msg'] = ["type" => "danger", "text" => "Invalid candidate."];
    header("Location: dashboard.php");
    exit;
}

// Determine vote column
$field = ($position === 'President') ? 'voted_president' : 'voted_vice';

/*
-------------------------------------
2️⃣ Check if already voted
-------------------------------------
*/

$check = $pdo->prepare("SELECT $field FROM users WHERE id = ?");
$check->execute([$user_id]);

if ($check->fetchColumn()) {
    $_SESSION['msg'] = ["type" => "warning", "text" => "You have already voted for $position."];
    header("Location: dashboard.php");
    exit;
}

/*
-------------------------------------
3️⃣ Record vote
-------------------------------------
*/

try {
    $pdo->beginTransaction();

    $pdo->prepare("
        INSERT INTO votes (user_id, candidate_id)
        VALUES (?, ?)
    ")->execute([$user_id, $candidate_id]);

    $pdo->prepare("
        UPDATE users SET $field = 1
        WHERE id = ?
    ")->execute([$user_id]);

    $pdo->commit();

    $_SESSION['msg'] = ["type" => "success", "text" => "Vote recorded successfully!"];

} catch (Exception $e) {
    $pdo->rollBack();
    $_SESSION['msg'] = ["type" => "danger", "text" => "Vote failed. Try again."];
}

header("Location: dashboard.php");
exit;
