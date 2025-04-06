<?php
session_start();
require 'backend/db_config.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validate password match
    if ($password !== $confirm_password) {
        $error = "Passwords do not match";
    } else {
        // Check if email exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->rowCount() > 0) {
            $error = "Email already exists";
        } else {
            // Hash password and create user
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
            $stmt->execute([$username, $email, $hashed_password]);
            
            // Get new user ID
            $user_id = $pdo->lastInsertId();
            
            // Log user in
            $_SESSION['user_id'] = $user_id;
            $_SESSION['username'] = $username;
            header("Location: login.php");
            exit();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up | AuctionPro</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="auth.css">
</head>
<body>
    <div class="auth-card">
        <div class="auth-header">
            <h1>Create Account</h1>
            <p>Join our auction community</p>
        </div>
        
        <div class="auth-body">
            <?php if ($error): ?>
                <div class="error-message" style="display: block;"><?= $error ?></div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="form-group">
                    <label for="username">Username</label>
                    <div class="input-field">
                        <input type="text" id="username" name="username" placeholder="Choose a username" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="email">Email</label>
                    <div class="input-field">
                        <input type="email" id="email" name="email" placeholder="Enter your email" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="input-field">
                        <input type="password" id="password" name="password" placeholder="Create a password" required>
                        <i class="fas fa-eye toggle-password"></i>
                    </div>
                    <div class="password-strength">
                        <div class="strength-bar" id="strength-bar"></div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Confirm Password</label>
                    <div class="input-field">
                        <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm your password" required>
                        <i class="fas fa-eye toggle-password"></i>
                    </div>
                </div>
                
                <div class="terms">
                    <input type="checkbox" id="terms" name="terms" required>
                    <label for="terms">I agree to the <a href="#">Terms of Service</a> and <a href="#">Privacy Policy</a></label>
                </div>
                
                <button type="submit" class="btn">Sign Up</button>
                
                <div class="auth-footer">
                    Already have an account? <a href="login.php">Login</a>
                </div>
            </form>
        </div>
    </div>
    
    <script src="auth.js"></script>
</body>
</html>