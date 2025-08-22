<?php
session_start();
include "../connection.php";

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $usernameOrEmail = trim($_POST['username_email']);
    $password = $_POST['password'];

    // Check if user exists
    $stmt = $conn->prepare("SELECT id, username, email, password, role, name, avatar FROM users WHERE username = ? OR email = ?");
    $stmt->bind_param("ss", $usernameOrEmail, $usernameOrEmail);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        if (password_verify($password, $user['password'])) {
            // Set session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['name'] = $user['name'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['avatar'] = $user['avatar'];

            // Redirect to dashboard or home
            header("Location: ../index.php");
            exit;
        } else {
            $error = "❌ Invalid password.";
        }
    } else {
        $error = "❌ User not found.";
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Login</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container d-flex justify-content-center align-items-center min-vh-100">
  <div class="col-md-6 col-lg-4">
    <div class="card shadow-sm p-4">
      <h3 class="mb-4 text-center">Login</h3>

      <?php if($error): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
      <?php endif; ?>

      <form method="POST">
        <div class="mb-3">
          <label class="form-label">Username or Email</label>
          <input type="text" name="username_email" class="form-control" placeholder="Enter username or email" required>
        </div>

        <div class="mb-3">
          <label class="form-label">Password</label>
          <input type="password" name="password" class="form-control" placeholder="********" required>
        </div>

        <button type="submit" class="btn btn-primary w-100">Login</button>
      </form>

      <p class="text-center mt-3">Don’t have an account? <a href="register.php">Register</a></p>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
