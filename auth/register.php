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
    <style>
* { 
    box-sizing: border-box; 
    margin: 0; 
    padding: 0; 
}

body {
    background: #f7fafe;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
    color: #384a64;
    min-height: 100vh;
    display: flex;
    position: relative;
    overflow: hidden;
}

/* Background decorative elements */
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

/* Main container */
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

/* Input styles */
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

.form-input::placeholder {
    color: #9ca3af;
    font-weight: 400;
}

.form-input:focus {
    border-color: #5c99ee;
    box-shadow: 0 0 0 3px rgba(92, 153, 238, 0.1);
}

/* Login button */
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

.login-btn:active {
    transform: translateY(0);
}

/* Error message */
.alert-danger {
    background: #ffe8e8;
    color: #c53030;
    border-left: 4px solid #dc3545;
    border-radius: 8px;
    padding: 12px 16px;
    margin-bottom: 1.5rem;
    font-size: 0.9rem;
}

/* Success message */
.alert-success {
    background: #e9f7ef;
    color: #2f855a;
    border-left: 4px solid #38a169;
    border-radius: 8px;
    padding: 12px 16px;
    margin-bottom: 1.5rem;
    font-size: 0.9rem;
}

/* Responsive */
@media (max-width: 768px) {
    body {
        overflow-y: auto;
    }
    
    .bg-decoration {
        display: none;
    }
    
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
    .login-container {
        padding: 1rem;
    }
    
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
            <h1 class="login-title">Daftar</h1>

            <?php if($error): ?>
                <div class="alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            <?php if($success): ?>
                <div class="alert-success"><?php echo $success; ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="input-group">
                    <svg class="input-icon user-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                        <circle cx="12" cy="7" r="4"></circle>
                    </svg>
                    <input type="text" name="name" class="form-input" placeholder="Nama Lengkap" required>
                </div>

                <div class="input-group">
                    <svg class="input-icon user-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M4 4h16v16H4z" stroke="none"></path>
                        <path d="M16 2H8a2 2 0 0 0-2 2v16l6-3 6 3V4a2 2 0 0 0-2-2z"></path>
                    </svg>
                    <input type="email" name="email" class="form-input" placeholder="Email" required>
                </div>

                <div class="input-group">
                    <svg class="input-icon lock-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                        <circle cx="12" cy="16" r="1"></circle>
                        <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                    </svg>
                    <input type="password" name="password" class="form-input" placeholder="Password" required>
                </div>

                <button type="submit" class="login-btn">Daftar</button>
            </form>

            <p class="register-text">Sudah punya akun? <a href="login.php" class="register-link">Masuk</a></p>
        </div>
    </div>
</body>
</html>
