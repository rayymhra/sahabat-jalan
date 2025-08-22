<?php
session_start();
include "../connection.php";

$message = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST['email']);

    // Check if user exists
    $stmt = $conn->prepare("SELECT id, is_verified FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        if ($user['is_verified'] == 0) {
            $error = "⚠️ Please verify your email before resetting password.";
        } else {
            // Generate reset token and expiration
            $token = bin2hex(random_bytes(32));
            $expires = date("Y-m-d H:i:s", strtotime("+1 hour"));

            // Update user with token
            $update = $conn->prepare("UPDATE users SET reset_token = ?, reset_expires = ? WHERE email = ?");
            $update->bind_param("sss", $token, $expires, $email);
            if ($update->execute()) {
                // Demo mode: show reset link directly
                $reset_link = "http://localhost/other/sahabat-jalan/auth/reset_password.php?token=$token";
                $message = "🎉 Password reset link (Demo Mode): <a href='$reset_link'>$reset_link</a>";
            } else {
                $error = "❌ Failed to generate reset link.";
            }
            $update->close();
        }
    } else {
        $error = "❌ Email not found.";
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Forgot Password</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container d-flex justify-content-center align-items-center min-vh-100">
<div class="col-md-6 col-lg-4">
<div class="card shadow-sm p-4">
<h3 class="mb-4 text-center">Forgot Password</h3>

<?php if($error): ?>
<div class="alert alert-danger"><?= $error ?></div>
<?php endif; ?>
<?php if($message): ?>
<div class="alert alert-success"><?= $message ?></div>
<?php endif; ?>

<form method="POST">
<div class="mb-3">
<label class="form-label">Enter your email</label>
<input type="email" name="email" class="form-control" placeholder="you@example.com" required>
</div>
<button type="submit" class="btn btn-primary w-100">Send Reset Link</button>
</form>

<p class="text-center mt-3">Remembered your password? <a href="login.php">Login</a></p>
</div>
</div>
</div>
</body>
</html>
