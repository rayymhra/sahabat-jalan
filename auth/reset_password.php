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
            
            $now = date("Y-m-d H:i:s");  // PHP current time
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
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Reset Password</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container d-flex justify-content-center align-items-center min-vh-100">
<div class="col-md-6 col-lg-4">
<div class="card shadow-sm p-4">
<h3 class="mb-4 text-center">Reset Password</h3>

<?php if($error): ?>
    <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>
    <?php if($success): ?>
        <div class="alert alert-success"><?= $success ?></div>
        <?php else: ?>
            <form method="POST">
            <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
            <div class="mb-3">
            <label class="form-label">New Password</label>
            <input type="password" name="new_password" class="form-control" required>
            </div>
            <div class="mb-3">
            <label class="form-label">Confirm Password</label>
            <input type="password" name="confirm_password" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-success w-100">Update Password</button>
            </form>
            <?php endif; ?>
            
            </div>
            </div>
            </div>
            </body>
            </html>
            