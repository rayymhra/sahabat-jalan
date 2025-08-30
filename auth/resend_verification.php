<?php
session_start();
require_once "../connection.php";

$message = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = filter_var(trim($_POST['email']), FILTER_VALIDATE_EMAIL);

    if (!$email) {
        $error = "❌ Email tidak valid.";
    } else {
        $stmt = $conn->prepare("SELECT id, username, is_verified FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows === 1) {
            $user = $result->fetch_assoc();

            if ((int)$user['is_verified'] === 1) {
                $error = "✅ Email Anda sudah terverifikasi. Silakan login.";
            } else {
                $token = bin2hex(random_bytes(32));
                $expires = date("Y-m-d H:i:s", strtotime("+1 day"));

                $update = $conn->prepare("UPDATE users SET verify_token = ?, token_expires = ? WHERE id = ?");
                $update->bind_param("ssi", $token, $expires, $user['id']);

                if ($update->execute()) {
                    $verification_link = "http://localhost/other/sahabat-jalan/auth/verify.php?token=" . urlencode($token);
                    $message = "🎉 Link verifikasi berhasil dibuat (Demo Mode): <a href='$verification_link'>$verification_link</a>";
                } else {
                    $error = "❌ Gagal membuat link verifikasi.";
                }
                $update->close();
            }
        } else {
            $error = "❌ Email tidak ditemukan.";
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
  <title>Kirim Ulang Verifikasi - Go Safe!</title>
  <style>
* { box-sizing: border-box; margin: 0; padding: 0; }

body {
    background: #f7fafe;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
    color: #384a64;
    min-height: 100vh;
    display: flex;
    position: relative;
    overflow: hidden;
}

.bg-decoration {
    position: absolute;
    right: 0;
    top: 0;
    width: 50%;
    height: 100vh;
    background: linear-gradient(135deg, #5c99ee 0%, #2b6cb0 100%);
    clip-path: polygon(30% 0%, 100% 0%, 100% 100%, 0% 100%);
    opacity: 0.9;
    z-index: 1;
}
.bg-decoration::before {
    content: '';
    position: absolute;
    top: 0;
    left: -20%;
    width: 100%;
    height: 100%;
    background: linear-gradient(135deg, rgba(255,255,255,0.1) 0%, transparent 50%);
    clip-path: polygon(0% 0%, 70% 0%, 50% 100%, 0% 100%);
}

.login-container {
    width: 100%;
    max-width: 450px;
    padding: 2rem;
    display: flex;
    align-items: center;
    justify-content: flex-start;
    margin-left: 5%;
    z-index: 2;
    position: relative;
}
.login-form {
    width: 100%;
    max-width: 380px;
}
.login-title {
    font-size: 3rem;
    font-weight: 700;
    color: #5c99ee;
    margin-bottom: 2.5rem;
    letter-spacing: -0.02em;
}

.input-group {
    position: relative;
    margin-bottom: 1.5rem;
}
.input-icon {
    position: absolute;
    left: 18px;
    top: 50%;
    transform: translateY(-50%);
    width: 20px;
    height: 20px;
    color: #384a64;
    z-index: 3;
}
.form-input {
    width: 100%;
    padding: 18px 50px;
    border: 2px solid #384a64;
    border-radius: 25px;
    background: transparent;
    font-size: 1rem;
    color: #384a64;
    outline: none;
    transition: all 0.3s ease;
}
.form-input::placeholder { color: #9ca3af; font-weight: 400; }
.form-input:focus {
    border-color: #5c99ee;
    box-shadow: 0 0 0 3px rgba(92, 153, 238, 0.1);
}

.login-btn {
    width: 100%;
    padding: 18px;
    background: #5c99ee;
    color: white;
    border: none;
    border-radius: 25px;
    font-size: 1.1rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    margin-bottom: 2rem;
    box-shadow: 0 4px 12px rgba(92, 153, 238, 0.3);
}
.login-btn:hover {
    background: #2b6cb0;
    box-shadow: 0 6px 16px rgba(43, 108, 176, 0.4);
    transform: translateY(-2px);
}
.login-btn:active { transform: translateY(0); }

.alert-danger {
    background: #ffe8e8;
    color: #c53030;
    border-left: 4px solid #dc3545;
    border-radius: 8px;
    padding: 12px 16px;
    margin-bottom: 1.5rem;
    font-size: 0.9rem;
}
.alert-success {
    background: #e9f7ef;
    color: #2f855a;
    border-left: 4px solid #38a169;
    border-radius: 8px;
    padding: 12px 16px;
    margin-bottom: 1.5rem;
    font-size: 0.9rem;
}

@media (max-width: 768px) {
    body { overflow-y: auto; }
    .bg-decoration { display: none; }
    .login-container {
        margin-left: 0;
        justify-content: center;
        padding: 1.5rem;
    }
    .login-title {
        font-size: 2.5rem;
        text-align: center;
    }
}
@media (max-width: 480px) {
    .login-container { padding: 1rem; }
    .login-title {
        font-size: 2rem;
        margin-bottom: 2rem;
    }
    .form-input {
        padding: 16px 45px;
        font-size: 0.95rem;
    }
    .input-icon {
        width: 18px;
        height: 18px;
        left: 16px;
    }
}

.register-text {
    text-align: left;
    font-size: 0.95rem;
    color: #384a64;
    margin-bottom: 0;
}

.register-link {
    color: #5c99ee;
    text-decoration: none;
    font-weight: 600;
    transition: color 0.3s ease;
}

.register-link:hover {
    color: #2b6cb0;
}

  </style>
</head>
<body>
  <div class="bg-decoration"></div>
  
  <div class="login-container">
    <div class="login-form">
      <h1 class="login-title">Kirim Ulang Verifikasi</h1>

      <?php if ($error): ?>
          <div class="alert-danger"><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>
      <?php if ($message): ?>
          <div class="alert-success"><?= $message ?></div>
      <?php endif; ?>

      <form method="POST" novalidate>
        <div class="input-group">
          <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M4 4h16v16H4z" stroke="none"></path>
              <path d="M16 2H8a2 2 0 0 0-2 2v16l6-3 6 3V4a2 2 0 0 0-2-2z"></path>
          </svg>
          <input type="email" name="email" class="form-input" placeholder="Masukkan Email Anda" required>
        </div>

        <button type="submit" class="login-btn">Kirim Ulang Link</button>
      </form>

      <p class="register-text">Kembali ke <a href="login.php" class="register-link">Login</a></p>
    </div>
  </div>
</body>
</html>
