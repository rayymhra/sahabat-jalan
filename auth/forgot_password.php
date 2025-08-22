<?php
session_start();
include "../connection.php";

$error = $success = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);

    // Check if email exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        // Generate token & expiry
        $token = bin2hex(random_bytes(32)); 
        $expires = date("Y-m-d H:i:s", strtotime("+1 hour"));

        // Store in DB
        $update = $conn->prepare("UPDATE users SET reset_token=?, reset_expires=? WHERE id=?");
        $update->bind_param("ssi", $token, $expires, $user['id']);
        $update->execute();
        $update->close();

        // Send email (for local dev, you can just echo the link)
        $resetLink = "http://localhost/yourproject/profile/reset_password.php?token=" . $token;

        // TODO: Replace with mail() or PHPMailer
        // mail($email, "Password Reset", "Click this link to reset: $resetLink");

        $success = "✅ Check your email for reset instructions. (Dev link: <a href='$resetLink'>$resetLink</a>)";
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
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Forgot Password</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container d-flex justify-content-center align-items-center min-vh-100">
  <div class="col-md-6 col-lg-4">
    <div class="card shadow-sm p-4">
      <h3 class="mb-4 text-center">Forgot Password</h3>

      <?php if($error): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
      <?php endif; ?>
      <?php if($success): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
      <?php endif; ?>

      <form method="POST">
        <div class="mb-3">
          <label class="form-label">Enter your Email</label>
          <input type="email" name="email" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary w-100">Send Reset Link</button>
      </form>

      <p class="text-center mt-3"><a href="login.php">Back to Login</a></p>
    </div>
  </div>
</div>
</body>
</html>
