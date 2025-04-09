<?php
session_start();
require '../login&signup/backend/db_config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login&signup/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $username = $_POST['username'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    // Basic validation
    $errors = [];
    
    if (empty($username)) {
        $errors[] = "Username is required";
    }
    
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Valid email is required";
    }
    
    // If no errors, update the database
    if (empty($errors)) {
        try {
            // Start transaction
            $pdo->beginTransaction();
            
            // Update username and email
            $stmt = $pdo->prepare("UPDATE users SET username = ?, email = ? WHERE id = ?");
            $stmt->execute([$username, $email, $user_id]);
            
            // Update password if provided
            if (!empty($password)) {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                $stmt->execute([$hashed_password, $user_id]);
            }
            
            // Commit transaction
            $pdo->commit();
            
            // Redirect back to profile with success message
            $_SESSION['success_message'] = "Profile updated successfully!";
            header("Location: profile.php?tab=settings");
            exit();
            
        } catch (PDOException $e) {
            $pdo->rollBack();
            $_SESSION['error_message'] = "Error updating profile: " . $e->getMessage();
            header("Location: profile.php?tab=settings");
            exit();
        }
    } else {
        $_SESSION['error_message'] = implode("<br>", $errors);
        header("Location: profile.php?tab=settings");
        exit();
    }
} else {
    header("Location: profile.php");
    exit();
}