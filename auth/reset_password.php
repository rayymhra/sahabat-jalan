<?php
session_start();
include "../connection.php";

$error = $success = "";
$token = $_GET['token'] ?? '';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $token = $_POST['token'];
    $newPassword = $_POST['new_password'];
    $confirmPassword = $_POST['confirm_password'];
    
    if ($newPassword !== $confirmPassword) {
        $error = "❌ Passwords do not match.";
    } else {
        $stmt = $conn->prepare("SELECT id, reset_expires FROM users WHERE reset_token = ?");
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            $now = date("Y-m-d H:i:s");
            if ($user['reset_expires'] >= $now) {
                $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                
                $update = $conn->prepare("UPDATE users SET password = ?, reset_token = NULL, reset_expires = NULL WHERE id = ?");
                $update->bind_param("si", $hashedPassword, $user['id']);
                
                if ($update->execute()) {
                    $success = "✅ Password updated successfully! <a href='login.php'>Login</a>";
                } else {
                    $error = "❌ Failed to update password.";
                }
                $update->close();
            } else {
                $error = "❌ Invalid or expired token.";
            }
        } else {
            $error = "❌ Invalid or expired token.";
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Reset Password - Go Safe!</title>
<style>
* { box-sizing: border-box; margin: 0; padding: 0; }

body {
    background: #f7fafe;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
    color: #384a64;
    min-height: 100vh;
    display: flex;
    position: relative;
    overflow: hidden;
}

/* Background decorative elements */
.bg-decoration {
    position: absolute;
    right: 0;
    top: 0;
    width: 50%;
    height: 100vh;
    background: linear-gradient(135deg, #5c99ee 0%, #2b6cb0 100%);
    clip-path: polygon(30% 0%, 100% 0%, 100% 100%, 0% 100%);
    opacity: 0.9;
    z-index: 1;
}

.bg-decoration::before {
    content: '';
    position: absolute;
    top: 0;
    left: -20%;
    width: 100%;
    height: 100%;
    background: linear-gradient(135deg, rgba(255,255,255,0.1) 0%, transparent 50%);
    clip-path: polygon(0% 0%, 70% 0%, 50% 100%, 0% 100%);
}

/* Main container */
.login-container {
    width: 100%;
    max-width: 450px;
    padding: 2rem;
    display: flex;
    align-items: center;
    justify-content: flex-start;
    margin-left: 5%;
    z-index: 2;
    position: relative;
}

.login-form {
    width: 100%;
    max-width: 380px;
}

.login-title {
    font-size: 3rem;
    font-weight: 700;
    color: #5c99ee;
    margin-bottom: 2.5rem;
    letter-spacing: -0.02em;
}

/* Input styles */
.input-group {
    position: relative;
    margin-bottom: 1.5rem;
}

.input-icon {
    position: absolute;
    left: 18px;
    top: 50%;
    transform: translateY(-50%);
    width: 20px;
    height: 20px;
    color: #384a64;
    z-index: 3;
}

.form-input {
    width: 100%;
    padding: 18px 50px;
    border: 2px solid #384a64;
    border-radius: 25px;
    background: transparent;
    font-size: 1rem;
    color: #384a64;
    outline: none;
    transition: all 0.3s ease;
}

.form-input::placeholder {
    color: #9ca3af;
    font-weight: 400;
}

.form-input:focus {
    border-color: #5c99ee;
    box-shadow: 0 0 0 3px rgba(92, 153, 238, 0.1);
}

/* Button */
.login-btn {
    width: 100%;
    padding: 18px;
    background: #5c99ee;
    color: white;
    border: none;
    border-radius: 25px;
    font-size: 1.1rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    margin-bottom: 2rem;
    box-shadow: 0 4px 12px rgba(92, 153, 238, 0.3);
}

.login-btn:hover {
    background: #2b6cb0;
    box-shadow: 0 6px 16px rgba(43, 108, 176, 0.4);
    transform: translateY(-2px);
}

/* Alert */
.alert-danger {
    background: #ffe8e8;
    color: #c53030;
    border-left: 4px solid #dc3545;
    border-radius: 8px;
    padding: 12px 16px;
    margin-bottom: 1.5rem;
    font-size: 0.9rem;
}

.alert-success {
    background: #e9f7ef;
    color: #2f855a;
    border-left: 4px solid #38a169;
    border-radius: 8px;
    padding: 12px 16px;
    margin-bottom: 1.5rem;
    font-size: 0.9rem;
}

/* Responsive */
@media (max-width: 768px) {
    .bg-decoration { display: none; }
    .login-container { margin-left: 0; justify-content: center; padding: 1.5rem; }
    .login-title { font-size: 2.5rem; text-align: center; }
}

@media (max-width: 480px) {
    .login-container { padding: 1rem; }
    .login-title { font-size: 2rem; margin-bottom: 2rem; }
    .form-input { padding: 16px 45px; font-size: 0.95rem; }
    .input-icon { width: 18px; height: 18px; left: 16px; }
}
</style>
</head>
<body>
<div class="bg-decoration"></div>

<div class="login-container">
  <div class="login-form">
    <h1 class="login-title">Reset Password</h1>

    <?php if($error): ?>
      <div class="alert-danger"><?= $error ?></div>
    <?php endif; ?>

    <?php if($success): ?>
      <div class="alert-success"><?= $success ?></div>
    <?php else: ?>
      <form method="POST">
        <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">

        <div class="input-group">
          <svg class="input-icon lock-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
            <circle cx="12" cy="16" r="1"></circle>
            <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
          </svg>
          <input type="password" name="new_password" class="form-input" placeholder="Password Baru" required>
        </div>

        <div class="input-group">
          <svg class="input-icon lock-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
            <circle cx="12" cy="16" r="1"></circle>
            <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
          </svg>
          <input type="password" name="confirm_password" class="form-input" placeholder="Konfirmasi Password" required>
        </div>

        <button type="submit" class="login-btn">Perbarui Password</button>
      </form>
    <?php endif; ?>
  </div>
</div>
</body>
</html>
