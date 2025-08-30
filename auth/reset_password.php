<?php
session_start();
include "../connection.php";

$message = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST['email']);
    
    $stmt = $conn->prepare("SELECT id, is_verified FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        
        if ($user['is_verified'] == 0) {
            $error = "⚠️ Silakan verifikasi email Anda sebelum mereset password.";
        } else {
            $token = bin2hex(random_bytes(32));
            $expires = date("Y-m-d H:i:s", strtotime("+1 hour"));
            
            $update = $conn->prepare("UPDATE users SET reset_token = ?, reset_expires = ? WHERE email = ?");
            $update->bind_param("sss", $token, $expires, $email);
            if ($update->execute()) {
                $reset_link = "http://localhost/other/sahabat-jalan/auth/reset_password.php?token=$token";
                $message = "🎉 Link reset password (Demo Mode): <a href='$reset_link' style='color: #5c99ee; text-decoration: none;'>$reset_link</a>";
            } else {
                $error = "❌ Gagal membuat link reset.";
            }
            $update->close();
        }
    } else {
        $error = "❌ Email tidak ditemukan.";
    }
    
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Lupa Password - Go Safe!</title>
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

/* Container reset password */
.login-container {
  width: 100%;
  max-width: 420px;
  padding: 1rem;
  display: flex;
  justify-content: center;
}

/* Card reset password */
.login-card {
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
.login-title {
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
.btn-primary {
  background: #5c99ee;
  border: none;
  border-radius: 8px;
  padding: 0.65rem;
  font-weight: 600;
  font-size: 0.95rem;
  color: #fff;
  transition: background 0.3s, transform 0.2s;
}

.btn-primary:hover {
  background: #2b6cb0;
  transform: translateY(-2px);
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

/* Alert */
.alert {
  border-radius: 8px;
  font-size: 0.9rem;
  padding: 0.75rem 1rem;
  margin-bottom: 1rem;
}

.alert-danger {
  background: #ffe8e8;
  color: #c53030;
  border-left: 4px solid #dc3545;
}

.alert-success {
  background: #e8f5e8;
  color: #2d7d32;
  border-left: 4px solid #28a745;
}

/* Responsive */
@media (max-width: 576px) {
  .login-card {
    padding: 1.5rem 1rem;
  }
  .brand-title {
    font-size: 1.5rem;
    margin-bottom: 1rem;
  }
  .login-title {
    font-size: 1.1rem;
  }
}

    </style>
</head>
<body>
    <div class="login-container">
        <div class="w-100">
            <h1 class="brand-title">Go Safe!</h1>
            
            <div class="login-card mx-auto">
                <h3 class="login-title">Lupa Password</h3>
                
                <?php if($error): ?>
                    <div class="alert alert-danger">
                        <?php echo $error; ?>
                    </div>
                <?php endif; ?>
                
                <?php if($message): ?>
                    <div class="alert alert-success">
                        <?php echo $message; ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label">
                            <i class="fas fa-envelope me-2"></i>Masukkan email Anda
                        </label>
                        <div class="input-group">
                            <input type="email" name="email" class="form-control input-with-icon" 
                                   placeholder="you@example.com" required>
                            <i class=""></i>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary w-100 mb-3">
                        <i class="fas fa-paper-plane me-2"></i>Kirim Link Reset
                    </button>
                </form>
                
                <p class="text-center mb-0">
                    Sudah ingat password? 
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