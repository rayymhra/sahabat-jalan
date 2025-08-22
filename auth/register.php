<?php

include "../connection.php";

$success = $error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = "user"; 
    $avatar = "default.png"; 
    $phone = null;
    $bio = null;

    // auto-generate username (name + random number)
    $baseUsername = strtolower(preg_replace('/\s+/', '', $name)); 
    $username = $baseUsername . rand(100, 999);

    // ensure username unique
    $checkUser = $conn->prepare("SELECT id FROM users WHERE username = ?");
    do {
        $checkUser->bind_param("s", $username);
        $checkUser->execute();
        $checkUser->store_result();
        if ($checkUser->num_rows > 0) {
            $username = $baseUsername . rand(100, 999);
        } else {
            break;
        }
    } while (true);
    $checkUser->close();

    // insert user
    $stmt = $conn->prepare("INSERT INTO users (name, username, email, password, role, avatar, phone_number, bio) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssssss", $name, $username, $email, $password, $role, $avatar, $phone, $bio);

    if ($stmt->execute()) {
        $success = "🎉 Registration successful! Your username is <strong>$username</strong>. You can now <a href='login.php'>login</a>.";
    } else {
        $error = "Error: " . $stmt->error;
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Register</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container d-flex justify-content-center align-items-center min-vh-100">
  <div class="col-md-6 col-lg-5">
    <div class="card shadow-sm p-4">
      <h3 class="mb-4 text-center">Register</h3>

      <?php if($success): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
      <?php endif; ?>
      <?php if($error): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
      <?php endif; ?>

      <form method="POST">
        <div class="mb-3">
          <label class="form-label">Full Name</label>
          <input type="text" name="name" class="form-control" placeholder="John Doe" required>
        </div>

        <div class="mb-3">
          <label class="form-label">Email</label>
          <input type="email" name="email" class="form-control" placeholder="example@email.com" required>
        </div>

        <div class="mb-3">
          <label class="form-label">Password</label>
          <input type="password" name="password" class="form-control" placeholder="********" required>
        </div>

        <button type="submit" class="btn btn-success w-100">Register</button>
      </form>

      <p class="text-center mt-3">Already have an account? <a href="login.php">Login</a></p>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>