<?php
require 'config.php';
header("Content-Type: application/json");

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["status"=>"danger","message"=>"Unauthorized access"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
$candidate_id = (int)$data['candidate_id'];
$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("
    SELECT p.name FROM candidates c
    JOIN positions p ON c.position_id = p.id
    WHERE c.id = ?
");
$stmt->execute([$candidate_id]);
$position = $stmt->fetchColumn();

$field = ($position === 'President') ? 'voted_president' : 'voted_vice';

$check = $pdo->prepare("SELECT $field FROM users WHERE id = ?");
$check->execute([$user_id]);

if ($check->fetchColumn()) {
    echo json_encode(["status"=>"warning","message"=>"Already voted."]);
    exit;
}

$pdo->prepare("INSERT INTO votes (user_id, candidate_id) VALUES (?, ?)")
    ->execute([$user_id, $candidate_id]);

$pdo->prepare("UPDATE users SET $field = 1 WHERE id = ?")
    ->execute([$user_id]);

echo json_encode(["status"=>"success","message"=>"Vote recorded successfully."]);