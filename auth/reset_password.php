<?php
session_start();
include "../connection.php";

$error = $success = "";
$token = $_GET['token'] ?? '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    
    // Check if user exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        // Generate token
        $token = bin2hex(random_bytes(32));
        $expires = date("Y-m-d H:i:s", strtotime("+1 hour"));

        // Save token to DB
        $update = $conn->prepare("UPDATE users SET reset_token = ?, reset_expires = ? WHERE id = ?");
        $update->bind_param("ssi", $token, $expires, $user['id']);
        $update->execute();

        // Demo mode: tampilkan link
        $reset_link = "http://localhost/other/sahabat-jalan/auth/new_password.php?token=$token";
        $message = "Password reset link (Demo Mode): <a href='$reset_link'>$reset_link</a>";

    } else {
        $message = "❌ Email not found.";
    }

    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Reset Password - Go Safe!</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      background-color: #f7fafe;
      font-family: "Segoe UI", Arial, sans-serif;
      color: #384a64;
    }
    .card {
      border-radius: 12px;
      border: none;
      animation: fadeInUp 0.5s ease;
    }
    h3 {
      font-weight: 600;
      color: #384a64;
    }
    .btn-primary {
      background-color: #5c99ee;
      border: none;
    }
    .btn-primary:hover {
      background-color: #2b6cb0;
    }
    a {
      color: #5c99ee;
      text-decoration: none;
    }
    a:hover {
      color: #2b6cb0;
    }
    .alert {
      border-radius: 10px;
      background: #e9f3ff;
      color: #384a64;
      border: none;
    }
    @keyframes fadeInUp {
      from { opacity: 0; transform: translateY(20px); }
      to { opacity: 1; transform: translateY(0); }
    }
  </style>
</head>
<body>

<div class="container d-flex justify-content-center align-items-center min-vh-100">
  <div class="col-md-6 col-lg-4">
    <div class="card shadow-sm p-4">
      <h3 class="mb-4 text-center">Reset Password</h3>

      <?php if($message): ?>
        <div class="alert"><?php echo $message; ?></div>
      <?php endif; ?>

      <form method="POST">
        <div class="mb-3">
          <label class="form-label">Email</label>
          <input type="email" name="email" class="form-control" placeholder="Enter your email" required>
        </div>
        <button type="submit" class="btn btn-primary w-100">Kirim Link Reset</button>
      </form>

      <p class="text-center mt-3">Kembali Ke <a href="login.php">Login</a></p>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
