<?php
session_start();
require 'db_config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit();
}

$item_id = filter_var($_POST['item_id'], FILTER_VALIDATE_INT);
$rating = filter_var($_POST['rating'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 5]]);
$review = trim($_POST['review']);

if (!$item_id || !$rating) {
    echo json_encode(['success' => false, 'message' => 'Invalid data']);
    exit();
}

try {
    $stmt = $pdo->prepare("UPDATE won_items SET rating = ?, review = ? WHERE id = ? AND user_id = ?");
    $stmt->execute([$rating, $review, $item_id, $_SESSION['user_id']]);
    
    // Update user rating (average of all their ratings)
    $stmt = $pdo->prepare("UPDATE users SET rating = (
        SELECT AVG(rating) FROM won_items 
        WHERE user_id = ? AND rating IS NOT NULL
    ) WHERE id = ?");
    $stmt->execute([$_SESSION['user_id'], $_SESSION['user_id']]);
    
    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
?>