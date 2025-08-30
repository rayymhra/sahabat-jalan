<?php
session_start();
include "../connection.php";

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
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

  <style>
    body {
      background-color: #f7fafe;
      font-family: Arial, sans-serif;
      color: #384a64;
    }
    .card {
      border: none;
      border-radius: 12px;
      background: #fff;
      box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    }
    h3 {
      font-size: 1.6rem;
      font-weight: 600;
      color: #384a64;
    }
    p {
      font-size: 1rem;
      color: #384a64;
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
    .alert {
      font-size: 0.9rem;
      border-radius: 8px;
    }
    a {
      color: #5c99ee;
      text-decoration: none;
      transition: color 0.3s ease;
    }
    a:hover {
      color: #2b6cb0;
    }
  </style>
</head>
<body class="d-flex justify-content-center align-items-center min-vh-100">

  <div class="card p-4 text-center" style="max-width: 400px; width: 100%;">
    <h3 class="mb-3">Verifikasi Email</h3>

    <?php if($error): ?>
      <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>

    <?php if($message): ?>
      <div class="alert alert-success"><?= $message ?></div>
    <?php endif; ?>

    <a href="login.php" class="btn btn-primary w-100 mt-3">Kembali ke Login</a>
  </div>

</body>
</html>
