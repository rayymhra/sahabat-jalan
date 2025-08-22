<?php
session_start();
include "../connection.php";

$message = "";

// Check if token exists in URL
if (isset($_GET['token'])) {
    $token = $_GET['token'];

    // Search user with this token
    $stmt = $conn->prepare("SELECT id, is_verified FROM users WHERE verify_token = ?");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        if ($user['is_verified'] == 1) {
            $message = "✅ Your account is already verified. You can log in now.";
        } else {
            // Mark account as verified
            $stmt = $conn->prepare("UPDATE users SET is_verified = 1, verify_token = NULL WHERE id = ?");
            $stmt->bind_param("i", $user['id']);
            if ($stmt->execute()) {
                $message = "🎉 Your account has been verified! You can now log in.";
            } else {
                $message = "❌ Something went wrong. Please try again later.";
            }
        }
    } else {
        $message = "❌ Invalid or expired verification link.";
    }

    $stmt->close();
} else {
    $message = "❌ No verification token provided.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Email Verification</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex justify-content-center align-items-center min-vh-100">

  <div class="card shadow-sm p-4 text-center" style="max-width: 400px;">
    <h3>Email Verification</h3>
    <p class="mt-3"><?php echo $message; ?></p>
    <a href="login.php" class="btn btn-primary mt-3">Go to Login</a>
  </div>

</body>
</html>
