<?php
session_start();
require 'db_config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit();
}

$username = trim($_POST['username']);
$email = trim($_POST['email']);
$avatar = trim($_POST['avatar']);
$current_password = $_POST['current_password'];
$new_password = $_POST['new_password'];

// Basic validation
if (empty($username) || empty($email)) {
    echo json_encode(['success' => false, 'message' => 'Username and email are required']);
    exit();
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Invalid email format']);
    exit();
}

try {
    // Check if email is already taken by another user
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
    $stmt->execute([$email, $_SESSION['user_id']]);
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Email already in use']);
        exit();
    }
    
    // Check if username is already taken by another user
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? AND id != ?");
    $stmt->execute([$username, $_SESSION['user_id']]);
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Username already in use']);
        exit();
    }
    
    // Handle password change if requested
    $password_update = '';
    $params = [$username, $email, $avatar, $_SESSION['user_id']];
    
    if (!empty($current_password)) {
        if (empty($new_password)) {
            echo json_encode(['success' => false, 'message' => 'New password is required']);
            exit();
        }
        
        // Verify current password
        $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch();
        
        if (!$user || !password_verify($current_password, $user['password'])) {
            echo json_encode(['success' => false, 'message' => 'Current password is incorrect']);
            exit();
        }
        
        $password_update = ', password = ?';
        $params[] = password_hash($new_password, PASSWORD_DEFAULT);
    }
    
    // Update profile
    $stmt = $pdo->prepare("UPDATE users SET username = ?, email = ?, avatar = ? $password_update WHERE id = ?");
    $stmt->execute($params);
    
    echo json_encode(['success' => true, 'avatar' => $avatar]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
?>