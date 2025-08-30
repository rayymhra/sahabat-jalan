<?php
session_start();
include "../connection.php";

$message = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST['email']);

    $stmt = $conn->prepare("SELECT id, is_verified FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        if ($user['is_verified'] == 0) {
            $error = "⚠️ Please verify your email before resetting password.";
        } else {
            $token = bin2hex(random_bytes(32));
            $expires = date("Y-m-d H:i:s", strtotime("+1 hour"));

            $update = $conn->prepare("UPDATE users SET reset_token = ?, reset_expires = ? WHERE email = ?");
            $update->bind_param("sss", $token, $expires, $email);
            if ($update->execute()) {
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
<title>Lupa Password - Go Safe!</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
    body {
        background-color: #f7fafe;
        font-family: system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
        color: #384a64;
    }
    .card {
        border: none;
        border-radius: 12px;
        background: #fff;
        box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        animation: fadeIn 0.5s ease;
    }
    h3 {
        color: #384a64;
        font-weight: 600;
    }
    label {
        color: #384a64;
        font-size: 0.95rem;
    }
    .form-control {
        border-radius: 8px;
        border: 1px solid #d1d9e6;
    }
    .btn-primary {
        background-color: #5c99ee;
        border: none;
        border-radius: 8px;
        font-weight: 600;
        transition: background 0.3s ease;
    }
    .btn-primary:hover {
        background-color: #2b6cb0;
    }
    a {
        color: #5c99ee;
        text-decoration: none;
        transition: color 0.3s ease;
    }
    a:hover {
        color: #2b6cb0;
    }
    .alert {
        font-size: 0.9rem;
        border-radius: 8px;
    }
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>
</head>
<body>
<div class="container d-flex justify-content-center align-items-center min-vh-100">
    <div class="col-md-6 col-lg-4">
        <div class="card p-4">
            <h3 class="mb-4 text-center">Reset Password</h3>

            <?php if($error): ?>
            <div class="alert alert-danger"><?= $error ?></div>
            <?php endif; ?>
            <?php if($message): ?>
            <div class="alert alert-success"><?= $message ?></div>
            <?php endif; ?>  

            <form method="POST">
                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" placeholder="you@example.com" required>
                </div>
                <button type="submit" class="btn btn-primary w-100">Kirim Link Reset</button>
            </form>

            <p class="text-center mt-3">Kembali Ke <a href="login.php">Login</a></p>
        </div>
    </div>
</div>
</body>
</html>
