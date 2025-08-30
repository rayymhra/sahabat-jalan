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
        $error = "⚠️ Email sudah terdaftar. Silakan masuk terlebih dahulu.";
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
                $mail->Subject = "Verifikasi email Anda";
                $mail->Body    = "Hai $name,<br><br>
                                  Terima kasih sudah mendaftar! Silakan verifikasi email Anda dengan mengklik link di bawah ini:<br>
                                  <a href='$verifyLink'>$verifyLink</a><br><br>
                                  Link ini berlaku selama 24 jam.";

                $mail->send();
                $success = "🎉 Pendaftaran berhasil! Silakan cek email Anda (<strong>$email</strong>) untuk memverifikasi akun.";
            } catch (Exception $e) {
                // fallback: show the link directly
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
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
       /* ===== Reset sederhana ===== */
* {
  box-sizing: border-box;
  margin: 0;
  padding: 0;
}

body {
  background: #f7fafe;
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  color: #384a64;
  display: flex;
  align-items: center;
  justify-content: center;
  min-height: 100vh;
}

/* Container */
.register-container {
  width: 100%;
  max-width: 420px;
  padding: 1rem;
  display: flex;
  justify-content: center;
}

/* Card */
.register-card {
  background: #fff;
  border-radius: 12px;
  padding: 2rem;
  width: 100%;
  box-shadow: 0 6px 20px rgba(0, 0, 0, 0.06);
}

/* Judul brand */
.brand-title {
  color: #5c99ee;
  font-size: 1.8rem;
  font-weight: 700;
  text-align: center;
  margin-bottom: 1.5rem;
}

/* Judul card */
.register-title {
  font-size: 1.3rem;
  font-weight: 600;
  text-align: center;
  margin-bottom: 1.2rem;
  color: #384a64;
}

/* Input */
.form-label {
  font-weight: 500;
  margin-bottom: 0.5rem;
  font-size: 0.9rem;
}

.form-control {
  border: 1.5px solid #e2e8f0;
  border-radius: 8px;
  padding: 0.65rem 0.9rem;
  font-size: 0.95rem;
  background: #fff;
  color: #384a64;
}

.form-control:focus {
  border-color: #5c99ee;
  box-shadow: 0 0 0 3px rgba(92, 153, 238, 0.15);
  outline: none;
}

/* Button utama */
.btn-success {
  background: #5c99ee;
  border: none;
  border-radius: 8px;
  padding: 0.65rem;
  font-weight: 600;
  font-size: 0.95rem;
  color: #fff;
  transition: background 0.3s, transform 0.2s;
}

.btn-success:hover {
  background: #2b6cb0;
  transform: translateY(-2px);
}

/* Button Google */
.btn-outline-danger {
  border: 1.5px solid #e2e8f0;
  border-radius: 8px;
  padding: 0.65rem;
  font-weight: 500;
  font-size: 0.95rem;
  background: #fff;
  color: #384a64;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  transition: all 0.3s;
}

.btn-outline-danger:hover {
  border-color: #5c99ee;
  color: #5c99ee;
  background: #f7fafe;
}

/* Icon Google */
.google-icon {
  width: 18px;
  height: 18px;
}

/* Link */
.link-primary {
  color: #5c99ee;
  text-decoration: none;
  font-weight: 500;
}

.link-primary:hover {
  color: #2b6cb0;
  text-decoration: underline;
}

/* Divider */
.divider {
  text-align: center;
  margin: 1.5rem 0;
  color: #a0aec0;
  font-size: 0.85rem;
  position: relative;
}

.divider::before,
.divider::after {
  content: "";
  position: absolute;
  top: 50%;
  width: 40%;
  height: 1px;
  background: #e2e8f0;
}

.divider::before {
  left: 0;
}
.divider::after {
  right: 0;
}

/* Alert */
.alert {
  border-radius: 8px;
  font-size: 0.9rem;
  padding: 0.75rem 1rem;
  margin-bottom: 1rem;
}

.alert-success {
  background: #e8fbe8;
  color: #2f855a;
  border-left: 4px solid #38a169;
}

.alert-danger {
  background: #ffe8e8;
  color: #c53030;
  border-left: 4px solid #dc3545;
}

/* Responsive */
@media (max-width: 576px) {
  .register-card {
    padding: 1.5rem 1rem;
  }
  .brand-title {
    font-size: 1.5rem;
    margin-bottom: 1rem;
  }
  .register-title {
    font-size: 1.1rem;
  }
}
    </style>
</head>
<body>
    <div class="register-container">
        <div class="w-100">
            <h1 class="brand-title">Go Safe!</h1>
            
            <div class="register-card mx-auto">
                <h3 class="register-title">Daftar</h3>
                
                <?php if($success): ?>
                    <div class="alert alert-success">
                        <?php echo $success; ?>
                    </div>
                <?php endif; ?>
                
                <?php if($error): ?>
                    <div class="alert alert-danger">
                        <?php echo $error; ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label">
                            <i class="fas fa-user me-2"></i>Nama Lengkap
                        </label>
                        <div class="input-group">
                            <input type="text" name="name" class="form-control input-with-icon" 
                                   placeholder="Masukan nama lengkap Anda" required>
                            <i class=""></i>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">
                            <i class="fas fa-envelope me-2"></i>Email
                        </label>
                        <div class="input-group">
                            <input type="email" name="email" class="form-control input-with-icon" 
                                   placeholder="contoh@email.com" required>
                            <i class=""></i>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">
                            <i class="fas fa-lock me-2"></i>Password
                        </label>
                        <div class="input-group">
                            <input type="password" name="password" class="form-control input-with-icon" 
                                   placeholder="Buat password yang kuat" required>
                            <i class=""></i>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-success w-100 mb-3">
                        <i class="fas fa-user-plus me-2"></i>Daftar
                    </button>
                </form>
                
                <div class="divider text-center">atau</div>
                
                <a href="google_login.php" class="btn btn-outline-danger w-100 mb-4">
                    <img src="https://www.svgrepo.com/show/475656/google-color.svg" class="google-icon">
                    Lanjutkan dengan Google
                </a>
                
                <p class="text-center mb-0">
                    Sudah punya akun? 
                    <a href="login.php" class="link-primary">Masuk</a>
                </p>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Add smooth scroll effect
        document.addEventListener('DOMContentLoaded', function() {
            // Add focus effects
            const inputs = document.querySelectorAll('.form-control');
            inputs.forEach(input => {
                input.addEventListener('focus', function() {
                    this.parentElement.style.transform = 'scale(1.02)';
                });
                
                input.addEventListener('blur', function() {
                    this.parentElement.style.transform = 'scale(1)';
                });
            });
            
            // Add click effect to buttons
            const buttons = document.querySelectorAll('.btn');
            buttons.forEach(button => {
                button.addEventListener('click', function(e) {
                    let ripple = document.createElement('span');
                    let rect = this.getBoundingClientRect();
                    let size = Math.max(rect.width, rect.height);
                    let x = e.clientX - rect.left - size / 2;
                    let y = e.clientY - rect.top - size / 2;
                    
                    ripple.style.cssText = `
                        position: absolute;
                        width: ${size}px;
                        height: ${size}px;
                        left: ${x}px;
                        top: ${y}px;
                        background: rgba(255, 255, 255, 0.4);
                        border-radius: 50%;
                        transform: scale(0);
                        animation: ripple 0.6s linear;
                        pointer-events: none;
                    `;
                    
                    this.style.position = 'relative';
                    this.style.overflow = 'hidden';
                    this.appendChild(ripple);
                    
                    setTimeout(() => {
                        ripple.remove();
                    }, 600);
                });
            });
        });
        
        // Add CSS for ripple animation
        const style = document.createElement('style');
        style.textContent = `
            @keyframes ripple {
                to {
                    transform: scale(2);
                    opacity: 0;
                }
            }
        `;
        document.head.appendChild(style);
    </script>
</body>
</html>