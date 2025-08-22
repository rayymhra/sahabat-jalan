<?php
include "../connection.php";
require "../vendor/autoload.php"; // PHPMailer

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$success = $error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = "user"; 
    $avatar = "default.png"; 
    $phone = null;
    $bio = null;
    $login_provider = "manual";
    $is_verified = 0;

    // auto-generate username
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

    // check if email already exists
    $checkEmail = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $checkEmail->bind_param("s", $email);
    $checkEmail->execute();
    $checkEmail->store_result();
    if ($checkEmail->num_rows > 0) {
        $error = "⚠️ Email already registered. Please login instead.";
        $checkEmail->close();
    } else {
        $checkEmail->close();

        // create email verification token
        $token = bin2hex(random_bytes(32));
        $expires_at = date("Y-m-d H:i:s", strtotime("+1 day"));

        // insert user
        $stmt = $conn->prepare("INSERT INTO users 
            (name, username, email, password, role, avatar, phone_number, bio, login_provider, is_verified, verify_token, token_expires) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssssssssss", 
            $name, $username, $email, $password, $role, $avatar, $phone, $bio, 
            $login_provider, $is_verified, $token, $expires_at
        );

        if ($stmt->execute()) {
            // verification link
            $verifyLink = "http://localhost/other/sahabat-jalan/auth/verify.php?token=" . $token;

            // try sending email
            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host       = 'smtp.mailtrap.io'; // ✅ safer for demo
                $mail->SMTPAuth   = true;
                $mail->Username   = 'your_mailtrap_username'; 
                $mail->Password   = 'your_mailtrap_password'; 
                $mail->SMTPSecure = 'tls';
                $mail->Port       = 587;

                $mail->setFrom('noreply@sahabatjalan.com', 'Sahabat Jalan');
                $mail->addAddress($email, $name);
                $mail->isHTML(true);
                $mail->Subject = "Verify your email";
                $mail->Body    = "Hi $name,<br><br>
                                  Thanks for registering! Please verify your email by clicking the link below:<br>
                                  <a href='$verifyLink'>$verifyLink</a><br><br>
                                  This link expires in 24 hours.";

                $mail->send();
                $success = "🎉 Registration successful! Please check your email (<strong>$email</strong>) to verify your account.";
            } catch (Exception $e) {
                // fallback: show the link directly
                $success = "🎉 Registration successful! (Demo Mode)<br>
                            Since email couldn't be sent, please verify directly:<br>
                            <a href='$verifyLink'>$verifyLink</a>";
            }
        } else {
            $error = "Error: " . $stmt->error;
        }
        $stmt->close();
    }
}
?>

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

      <div class="text-center my-3">— or —</div>
      <a href="google_login.php" class="btn btn-outline-danger w-100">
        <img src="https://www.svgrepo.com/show/475656/google-color.svg" width="20" class="me-2">
        Continue with Google
      </a>

      <p class="text-center mt-3">Already have an account? <a href="login.php">Login</a></p>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
