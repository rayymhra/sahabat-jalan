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

    // Buat username unik
    $baseUsername = strtolower(preg_replace('/\s+/', '', $name)); 
    $username = $baseUsername . rand(100, 999);

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

    // Cek email sudah dipakai?
    $checkEmail = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $checkEmail->bind_param("s", $email);
    $checkEmail->execute();
    $checkEmail->store_result();
    if ($checkEmail->num_rows > 0) {
        $error = "⚠️ Email sudah terdaftar. Silakan masuk terlebih dahulu.";
        $checkEmail->close();
    } else {
        $checkEmail->close();

        $token = bin2hex(random_bytes(32));
        $expires_at = date("Y-m-d H:i:s", strtotime("+1 day"));

        $stmt = $conn->prepare("INSERT INTO users 
            (name, username, email, password, role, avatar, phone_number, bio, login_provider, is_verified, verify_token, token_expires) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssssssssss", 
            $name, $username, $email, $password, $role, $avatar, $phone, $bio, 
            $login_provider, $is_verified, $token, $expires_at
        );

        if ($stmt->execute()) {
            $verifyLink = "http://localhost/other/sahabat-jalan/auth/verify.php?token=" . $token;

            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host       = 'smtp.mailtrap.io';
                $mail->SMTPAuth   = true;
                $mail->Username   = 'your_mailtrap_username'; 
                $mail->Password   = 'your_mailtrap_password'; 
                $mail->SMTPSecure = 'tls';
                $mail->Port       = 587;

                $mail->setFrom('noreply@sahabatjalan.com', 'Sahabat Jalan');
                $mail->addAddress($email, $name);
                $mail->isHTML(true);
                $mail->Subject = "Verifikasi email Anda";
                $mail->Body    = "Hai $name,<br><br>
                                  Terima kasih sudah mendaftar! Silakan verifikasi email Anda dengan mengklik link di bawah ini:<br>
                                  <a href='$verifyLink'>$verifyLink</a><br><br>
                                  Link ini berlaku selama 24 jam.";

                $mail->send();
                $success = "🎉 Pendaftaran berhasil! Silakan cek email Anda (<strong>$email</strong>) untuk memverifikasi akun.";
            } catch (Exception $e) {
                $success = "🎉 Pendaftaran berhasil! (Mode Demo)<br>
                            Karena email tidak dapat dikirim, silakan verifikasi langsung:<br>
                            <a href='$verifyLink' style='color: #5c99ee; text-decoration: none;'>$verifyLink</a>";
            }
        } else {
            $error = "Error: " . $stmt->error;
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
  <title>Daftar - Go Safe!</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      background-color: #f7fafe;
      font-family: "Segoe UI", Arial, sans-serif;
      color: #384a64;
    }
    .card {
      border-radius: 16px;
      border: none;
      box-shadow: 0 8px 24px rgba(0,0,0,0.06);
      animation: fadeInUp 0.6s ease;
    }
    .brand-title {
      color: #5c99ee;
      font-weight: 700;
      text-align: center;
      margin-bottom: 1.5rem;
      font-size: 1.8rem;
    }
    .btn-primary {
      background-color: #5c99ee;
      border: none;
      border-radius: 8px;
      font-weight: 600;
      transition: all 0.2s ease;
    }
    .btn-primary:hover {
      background-color: #2b6cb0;
      transform: translateY(-1px);
    }
    .btn-outline-secondary {
      border-radius: 8px;
      font-weight: 500;
    }
    a {
      color: #5c99ee;
      text-decoration: none;
    }
    a:hover {
      color: #2b6cb0;
      text-decoration: underline;
    }
    .alert {
      border-radius: 8px;
      background: #e9f3ff;
      border: none;
      color: #384a64;
      font-size: 0.9rem;
      animation: fadeInUp 0.4s ease;
    }
    @keyframes fadeInUp {
      from { opacity: 0; transform: translateY(20px); }
      to { opacity: 1; transform: translateY(0); }
    }

    .btn-outline-danger {
  border: 1.5px solid #e2e8f0; border-radius: 8px;
  padding: .65rem; font-weight: 500; font-size: .95rem;
  background: #fff; color: #384a64; display: flex; justify-content: center; gap: 8px;
  transition: all .3s;
}
.btn-outline-danger:hover { border-color: #5c99ee; color: #5c99ee; background: #f7fafe; }
.google-icon { width: 18px; height: 18px; }
.link-primary { color: #5c99ee; text-decoration: none; font-weight: 500; }
  </style>
</head>
<body>
<div class="container d-flex justify-content-center align-items-center min-vh-100">
  <div class="col-md-6 col-lg-4">
    <div class="card p-4">
      <h4 class="text-center mb-4">Daftar</h4>

      <?php if($success): ?>
        <div class="alert"><?php echo $success; ?></div>
      <?php endif; ?>
      <?php if($error): ?>
        <div class="alert"><?php echo $error; ?></div>
      <?php endif; ?>

      <form method="POST">
        <div class="mb-3">
          <label class="form-label">Nama Lengkap</label>
          <input type="text" name="name" class="form-control" placeholder="Masukan nama lengkap Anda" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Email</label>
          <input type="email" name="email" class="form-control" placeholder="contoh@email.com" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Password</label>
          <input type="password" name="password" class="form-control" placeholder="Buat password yang kuat" required>
        </div>
        <button type="submit" class="btn btn-primary w-100 mb-3">Daftar</button>
      </form>

      <div class="text-center my-3 text-muted">atau</div>
 <a href="google_login.php" class="btn btn-outline-danger w-100 mb-4">
      <img src="https://www.svgrepo.com/show/475656/google-color.svg" class="google-icon"> Lanjutkan dengan Google
    </a>

      <p class="text-center">Sudah punya akun? <a href="login.php">Masuk</a></p>
    </div>
  </div>
</div>
</body>
</html>
