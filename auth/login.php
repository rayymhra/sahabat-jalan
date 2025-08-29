<?php
session_start();
include "../connection.php";

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $usernameOrEmail = trim($_POST['username_email']);
    $password = $_POST['password'];
    
    // Check if user exists
    $stmt = $conn->prepare("SELECT id, username, email, password, role, name, avatar, is_verified, login_provider 
                            FROM users 
                            WHERE username = ? OR email = ?");
    $stmt->bind_param("ss", $usernameOrEmail, $usernameOrEmail);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        
        // Case 1: Google account (skip password)
        if ($user['login_provider'] === 'google') {
            $error = "⚠️ Akun ini dibuat dengan Google. Silakan masuk menggunakan Google.";
        }
        // Case 2: Manual account
        elseif (password_verify($password, $user['password'])) {
            if ($user['is_verified'] == 0) {
                // Instead of blocking, store session but mark as unverified
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['name'] = $user['name'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['avatar'] = $user['avatar'];
                $_SESSION['is_verified'] = 0;
                
                $error = "⚠️ Email Anda belum diverifikasi. Silakan periksa kotak masuk atau <a href='resend_verification.php' style='color: #5c99ee; text-decoration: none;'>kirim ulang verifikasi</a>.";
            } else {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['name'] = $user['name'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['avatar'] = $user['avatar'];
                
                header("Location: ../index.php");
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
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            transition: all 0.3s ease;
        }
        
        body {
            background: #ffffff;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #384e64;
            overflow-x: hidden;
        }
        
        .login-container {
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
            position: relative;
            overflow: hidden;
        }
        
        /* Background decorative elements */
        .login-container::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(45deg, rgba(92, 153, 238, 0.1) 25%, transparent 25%),
                        linear-gradient(-45deg, rgba(92, 153, 238, 0.1) 25%, transparent 25%),
                        linear-gradient(45deg, transparent 75%, rgba(92, 153, 238, 0.1) 75%),
                        linear-gradient(-45deg, transparent 75%, rgba(92, 153, 238, 0.1) 75%);
            background-size: 100px 100px;
            background-position: 0 0, 0 50px, 50px -50px, -50px 0px;
            animation: slidePattern 20s linear infinite;
            z-index: -1;
        }
        
        @keyframes slidePattern {
            0% { transform: translateX(0); }
            100% { transform: translateX(100px); }
        }
        
        .brand-title {
            color: #5c99ee;
            font-size: 2rem;
            font-weight: 700;
            text-align: center;
            margin-bottom: 1.5rem;
            text-shadow: 0 4px 8px rgba(92, 153, 238, 0.3);
            animation: fadeInDown 1s ease;
        }
        
        @keyframes fadeInDown {
            from {
                opacity: 0;
                transform: translateY(-30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .login-card {
            background: #f7fafe;
            border: none;
            border-radius: 16px;
            box-shadow: 0 10px 25px rgba(92, 153, 238, 0.12), 
                        0 4px 12px rgba(0, 0, 0, 0.06);
            padding: 1.8rem;
            max-width: 380px;
            width: 100%;
            backdrop-filter: blur(10px);
            animation: fadeInUp 1s ease 0.3s both;
            position: relative;
        }
        
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .login-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #5c99ee, #2b0cb0);
            border-radius: 20px 20px 0 0;
        }
        
        .login-title {
            color: #384e64;
            font-size: 1.5rem;
            font-weight: 600;
            text-align: center;
            margin-bottom: 1.5rem;
        }
        
        .form-label {
            color: #384e64;
            font-weight: 500;
            margin-bottom: 0.7rem;
        }
        
        .form-control {
            border: 2px solid #e8f1ff;
            border-radius: 10px;
            padding: 0.7rem 1rem;
            font-size: 0.95rem;
            color: #384e64;
            background: rgba(255, 255, 255, 0.8);
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            border-color: #5c99ee;
            box-shadow: 0 0 0 0.25rem rgba(92, 153, 238, 0.15);
            background: #ffffff;
            transform: translateY(-2px);
        }
        
        .form-control::placeholder {
            color: #a8b5c8;
        }
        
        .btn-primary {
            background: #5c99ee;
            border: none;
            border-radius: 10px;
            padding: 0.7rem 1.5rem;
            font-weight: 600;
            font-size: 0.95rem;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .btn-primary:hover {
            background: #2b0cb0;
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(43, 12, 176, 0.4);
        }
        
        .btn-primary:active {
            transform: translateY(0);
        }
        
        .btn-outline-danger {
            border: 2px solid #e8f1ff;
            color: #384e64;
            border-radius: 10px;
            padding: 0.7rem 1.5rem;
            font-weight: 500;
            font-size: 0.95rem;
            transition: all 0.3s ease;
            background: rgba(255, 255, 255, 0.8);
        }
        
        .btn-outline-danger:hover {
            background: #5c99ee;
            border-color: #5c99ee;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(92, 153, 238, 0.3);
        }
        
        .alert {
            border: none;
            border-radius: 12px;
            padding: 1rem 1.25rem;
            animation: shake 0.6s ease;
        }
        
        @keyframes shake {
            0%, 20%, 40%, 60%, 80%, 100% { transform: translateX(0); }
            10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
        }
        
        .alert-danger {
            background: linear-gradient(135deg, #ffe8e8, #ffebeb);
            color: #d63384;
            border-left: 4px solid #dc3545;
        }
        
        .divider {
            color: #a8b5c8;
            font-weight: 500;
            position: relative;
            margin: 1.5rem 0;
        }
        
        .divider::before,
        .divider::after {
            content: '';
            position: absolute;
            top: 50%;
            width: 45%;
            height: 1px;
            background: linear-gradient(to right, transparent, #e8f1ff, transparent);
        }
        
        .divider::before { left: 0; }
        .divider::after { right: 0; }
        
        .link-primary {
            color: #5c99ee;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
            position: relative;
        }
        
        .link-primary::after {
            content: '';
            position: absolute;
            width: 0;
            height: 2px;
            bottom: -2px;
            left: 0;
            background: #5c99ee;
            transition: width 0.3s ease;
        }
        
        .link-primary:hover {
            color: #2b0cb0;
            text-decoration: none;
        }
        
        .link-primary:hover::after {
            width: 100%;
            background: #2b0cb0;
        }
        
        .mb-3 {
            margin-bottom: 1.2rem !important;
        }
        
        .input-group {
            position: relative;
        }
        
        .input-icon {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #5c99ee;
            z-index: 5;
            transition: all 0.3s ease;
        }
        
        .input-with-icon {
            padding-left: 45px;
        }
        
        .form-control:focus + .input-icon {
            color: #2b0cb0;
            transform: translateY(-50%) scale(1.1);
        }
        
        .google-icon {
            width: 20px;
            height: 20px;
            margin-right: 10px;
            transition: transform 0.3s ease;
        }
        
        .btn:hover .google-icon {
            transform: rotate(360deg);
        }
        
        @media (max-width: 576px) {
            .brand-title {
                font-size: 1.8rem;
                margin-bottom: 1rem;
            }
            
            .login-card {
                padding: 1.5rem 1.25rem;
                margin: 0.5rem;
                max-width: 350px;
            }
            
            .login-title {
                font-size: 1.3rem;
                margin-bottom: 1.2rem;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="w-100">
            <h1 class="brand-title">Go Safe!</h1>
            
            <div class="login-card mx-auto">
                <h3 class="login-title">Masuk</h3>
                
                <?php if($error): ?>
                    <div class="alert alert-danger">
                        <?php echo $error; ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label">
                            <i class="fas fa-user me-2"></i>Username atau Email
                        </label>
                        <div class="input-group">
                            <input type="text" name="username_email" class="form-control input-with-icon" 
                                   placeholder="Masukan username atau email" required>
                            <i class="fas fa-user input-icon"></i>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">
                            <i class="fas fa-lock me-2"></i>Password
                        </label>
                        <div class="input-group">
                            <input type="password" name="password" class="form-control input-with-icon" 
                                   placeholder="Masukan password Anda" required>
                            <i class="fas fa-lock input-icon"></i>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <a href="forgot_password.php" class="link-primary">Lupa password?</a>
                    </div>
                    
                    <button type="submit" class="btn btn-primary w-100 mb-3">
                        <i class="fas fa-sign-in-alt me-2"></i>Masuk
                    </button>
                </form>
                
                <div class="divider text-center">atau</div>
                
                <a href="google_login.php" class="btn btn-outline-danger w-100 mb-4">
                    <img src="https://www.svgrepo.com/show/475656/google-color.svg" class="google-icon">
                    Lanjutkan dengan Google
                </a>
                
                <p class="text-center mb-0">
                    Belum punya akun? 
                    <a href="register.php" class="link-primary">Daftar</a>
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