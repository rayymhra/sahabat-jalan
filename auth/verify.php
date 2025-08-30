<?php
session_start();
require_once "../connection.php";

$message = "";
$error = "";

if (isset($_GET['token'])) {
    $token = $_GET['token'];

    $stmt = $conn->prepare("SELECT id, token_expires, is_verified FROM users WHERE verify_token = ?");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        if ($user['is_verified'] == 1) {
            $message = "✅ Email kamu sudah terverifikasi, silakan login.";
        } elseif (strtotime($user['token_expires']) < time()) {
            $error = "❌ Token sudah kadaluarsa. Silakan kirim ulang verifikasi.";
        } else {
            $update = $conn->prepare("UPDATE users SET is_verified = 1, verify_token = NULL, token_expires = NULL WHERE id = ?");
            $update->bind_param("i", $user['id']);
            if ($update->execute()) {
                $message = "🎉 Verifikasi berhasil! Email kamu sudah aktif, silakan login.";
            } else {
                $error = "❌ Terjadi kesalahan saat verifikasi.";
            }
            $update->close();
        }
    } else {
        $error = "❌ Token tidak valid.";
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Verifikasi - Go Safe!</title>
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

.verify-container {
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
.verify-box {
    width: 100%;
    max-width: 380px;
}
.verify-title {
    font-size: 3rem;
    font-weight: 700;
    color: #5c99ee;
    margin-bottom: 2.5rem;
    letter-spacing: -0.02em;
}

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

.verify-btn {
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
    margin-top: 1.5rem;
    box-shadow: 0 4px 12px rgba(92, 153, 238, 0.3);
    text-decoration: none;
}
.verify-btn:hover {
    background: #2b6cb0;
    box-shadow: 0 6px 16px rgba(43, 108, 176, 0.4);
    transform: translateY(-2px);
}
.verify-btn:active { transform: translateY(0); }

@media (max-width: 768px) {
    body { overflow-y: auto; }
    .bg-decoration { display: none; }
    .verify-container {
        margin-left: 0;
        justify-content: center;
        padding: 1.5rem;
    }
    .verify-title {
        font-size: 2.5rem;
        text-align: center;
    }
}
@media (max-width: 480px) {
    .verify-container { padding: 1rem; }
    .verify-title {
        font-size: 2rem;
        margin-bottom: 2rem;
    }
}
  </style>
</head>
<body>
  <div class="bg-decoration"></div>
  
  <div class="verify-container">
    <div class="verify-box">
      <h1 class="verify-title">Verifikasi Email</h1>

      <?php if ($error): ?>
          <div class="alert-danger"><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>
      <?php if ($message): ?>
          <div class="alert-success"><?= $message ?></div>
      <?php endif; ?>

      <a href="login.php" class="verify-btn">Kembali ke Login</a>
    </div>
  </div>
</body>
</html>
