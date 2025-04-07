<?php
session_start();
require 'db_config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);
$item_id = filter_var($data['item_id'], FILTER_VALIDATE_INT);

if (!$item_id) {
    echo json_encode(['success' => false, 'message' => 'Invalid item ID']);
    exit();
}

try {
    $stmt = $pdo->prepare("DELETE FROM watchlist WHERE user_id = ? AND item_id = ?");
    $stmt->execute([$_SESSION['user_id'], $item_id]);
    
    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
?>