<?php
session_start();
include "../connection.php";

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $usernameOrEmail = trim($_POST['username_email']);
    $password = $_POST['password'];
    
    $stmt = $conn->prepare("SELECT id, username, email, password, role, name, avatar, is_verified, login_provider 
                            FROM users 
                            WHERE username = ? OR email = ?");
    $stmt->bind_param("ss", $usernameOrEmail, $usernameOrEmail);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        
        if ($user['login_provider'] === 'google') {
            $error = "⚠️ Akun ini dibuat dengan Google. Silakan masuk menggunakan Google.";
        }
        elseif (password_verify($password, $user['password'])) {
            if ($user['is_verified'] == 0) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['name'] = $user['name'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['avatar'] = $user['avatar'];
                $_SESSION['is_verified'] = 0;
                
                $error = "⚠️ Email Anda belum diverifikasi. Silakan periksa kotak masuk atau <a href='resend_verification.php' class='link-primary'>kirim ulang verifikasi</a>.";
            } else {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['name'] = $user['name'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['avatar'] = $user['avatar'];
                
                header("Location: ../main/index.php");
                exit;
            }
        } else {
            $error = "❌ Password salah.";
        }
    } else {
        $error = "❌ Pengguna tidak ditemukan.";
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Masuk - Go Safe!</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
* { box-sizing: border-box; margin: 0; padding: 0; }
body {
  background: #f7fafe;
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  color: #384a64;
  display: flex; align-items: center; justify-content: center;
  min-height: 100vh;
}
.login-container { max-width: 420px; width: 100%; padding: 1rem; }
.login-card {
  background: #fff; border-radius: 12px; padding: 2rem; width: 100%;
  box-shadow: 0 6px 20px rgba(0,0,0,0.06);
}
.brand-title {
  color: #5c99ee; font-size: 1.8rem; font-weight: 700;
  text-align: center; margin-bottom: 1.5rem;
}
.login-title {
  font-size: 1.3rem; font-weight: 600; text-align: center;
  margin-bottom: 1.2rem; color: #384a64;
}
.form-label { font-weight: 500; margin-bottom: .5rem; font-size: .9rem; }
.form-control {
  border: 1.5px solid #e2e8f0; border-radius: 8px;
  padding: .65rem .9rem; font-size: .95rem;
}
.form-control:focus {
  border-color: #5c99ee; box-shadow: 0 0 0 3px rgba(92,153,238,.15); outline: none;
}
.btn-primary {
  background: #5c99ee; border: none; border-radius: 8px;
  padding: .65rem; font-weight: 600; font-size: .95rem; color: #fff;
  transition: background .3s;
}
.btn-primary:hover { background: #2b6cb0; }
.btn-outline-danger {
  border: 1.5px solid #e2e8f0; border-radius: 8px;
  padding: .65rem; font-weight: 500; font-size: .95rem;
  background: #fff; color: #384a64; display: flex; justify-content: center; gap: 8px;
  transition: all .3s;
}
.btn-outline-danger:hover { border-color: #5c99ee; color: #5c99ee; background: #f7fafe; }
.google-icon { width: 18px; height: 18px; }
.link-primary { color: #5c99ee; text-decoration: none; font-weight: 500; }
.link-primary:hover { color: #2b6cb0; text-decoration: underline; }
.divider { text-align: center; margin: 1.5rem 0; color: #a0aec0; font-size: .85rem; position: relative; }
.divider::before, .divider::after {
  content: ""; position: absolute; top: 50%; width: 40%; height: 1px; background: #e2e8f0;
}
.divider::before { left: 0; } .divider::after { right: 0; }
.alert { border-radius: 8px; font-size: .9rem; padding: .75rem 1rem; margin-bottom: 1rem; }
.alert-danger { background: #ffe8e8; color: #c53030; border-left: 4px solid #dc3545; }
@media (max-width: 576px) {
  .login-card { padding: 1.5rem 1rem; }
  .brand-title { font-size: 1.5rem; margin-bottom: 1rem; }
  .login-title { font-size: 1.1rem; }
}
    </style>
</head>
<body>
<div class="login-container">
  <div class="login-card">
    <h3 class="login-title">Masuk</h3>
    <?php if($error): ?><div class="alert alert-danger"><?php echo $error; ?></div><?php endif; ?>
    <form method="POST">
      <div class="mb-3">
        <label class="form-label">Username atau Email</label>
        <input type="text" name="username_email" class="form-control" placeholder="Masukan username atau email" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Password</label>
        <input type="password" name="password" class="form-control" placeholder="Masukan password Anda" required>
      </div>
      <div class="mb-3"><a href="forgot_password.php" class="link-primary">Lupa password?</a></div>
      <button type="submit" class="btn btn-primary w-100 mb-3">Masuk</button>
    </form>
    <div class="divider">atau</div>
    <a href="google_login.php" class="btn btn-outline-danger w-100 mb-4">
      <img src="https://www.svgrepo.com/show/475656/google-color.svg" class="google-icon"> Lanjutkan dengan Google
    </a>
    <p class="text-center mb-0">Belum punya akun? <a href="register.php" class="link-primary">Daftar</a></p>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
