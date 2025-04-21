<?php
require_once 'backend/db_config.php';

// Initialize variables
$email = '';
$error = '';
$success = '';

// Handle password reset directly (no token)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Validate inputs
    if (empty($email) || empty($password) || empty($confirm_password)) {
        $error = "Please fill in all fields";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match";
    } elseif (strlen($password) < 8) {
        $error = "Password must be at least 8 characters";
    } else {
        try {
            // Check if user exists
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user) {
                // Update password
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                $stmt->execute([$hashed_password, $user['id']]);

                $success = "Your password has been updated successfully!";
            } else {
                $error = "No account found with that email";
            }
        } catch (PDOException $e) {
            $error = "Database error. Please try again later.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Reset Password | BidSphere</title>
    <link rel="stylesheet" href="../styles.css">
    <style>
    body {
        background-color: var(--background-color, #0f172a); /* fallback dark navy */
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    .password-recovery {
        max-width: 450px;
        margin: 4rem auto;
        padding: 0 1rem;
    }

    .recovery-card {
        background: var(--card-bg, #1e293b); /* dark surface */
        border-radius: 1rem;
        overflow: hidden;
        box-shadow: 0 0 20px rgba(0, 0, 0, 0.25);
        text-align: center;
    }

    .recovery-card h1 {
        background: var(--primary-color, #6366f1);
        color: white;
        margin: 0;
        padding: 1.5rem;
        font-size: 1.5rem;
        font-weight: 600;
    }

    .recovery-card form {
        padding: 2rem;
    }

    .form-group {
        margin-bottom: 1.5rem;
        text-align: left;
    }

    .form-group label {
        font-weight: 600;
        margin-bottom: 0.5rem;
        display: block;
    }

    .form-group input {
        width: 100%;
        padding: 0.75rem 1rem;
        font-size: 1rem;
        border: none;
        border-radius: 0.5rem;
        background: #334155;
        color: white;
        outline: none;
        transition: border 0.2s;
    }

    .form-group input::placeholder {
        color: #94a3b8;
    }

    .form-group input:focus {
        border: 1px solid var(--primary-color, #6366f1);
    }

    .submit-btn {
        width: 100%;
        background: var(--primary-color, #6366f1);
        color: white;
        border: none;
        padding: 0.75rem;
        font-size: 1rem;
        font-weight: 600;
        border-radius: 0.5rem;
        cursor: pointer;
        transition: background 0.2s;
        margin-top: 0.5rem;
    }

    .submit-btn:hover {
        background: var(--primary-dark, #4f46e5);
    }

    .success-message,
    .error-message {
        padding: 1rem;
        margin-bottom: 1.5rem;
        border-radius: 0.5rem;
        font-weight: 500;
    }

    .success-message {
        background: rgba(0, 255, 0, 0.1);
        color: var(--success-color, #22c55e);
    }

    .error-message {
        background: rgba(255, 0, 0, 0.1);
        color: var(--error-color, #ef4444);
    }

    .login-link {
        display: inline-block;
        margin-top: 1rem;
        font-size: 0.95rem;
        text-decoration: none;
        color: var(--primary-color, #6366f1);
        transition: text-decoration 0.2s;
    }

    .login-link:hover {
        text-decoration: underline;
    }
</style>


</head>
<body>

    <main class="password-recovery">
        <div class="recovery-card">
            <h1>Reset Your Password</h1>

            <?php if (!empty($error)): ?>
                <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <?php if (!empty($success)): ?>
                <div class="success-message"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
                </div>
                <div class="form-group">
                    <label for="new_password">New Password</label>
                    <input type="password" id="new_password" name="new_password" required minlength="8">
                </div>
                <div class="form-group">
                    <label for="confirm_password">Confirm New Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" required minlength="8">
                </div>
                <a href="login.php" class="login-link">Remember your password? Log in</a>
                <button type="submit" class="submit-btn">Update Password</button>
                
            </form>
        </div>
    </main>

    <script src="../script.js"></script>
</body>
</html>
