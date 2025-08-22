<?php
session_start();
include "../connection.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT name, username, email, avatar, phone_number, bio FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Profile</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container py-5">
  <div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
      <div class="card shadow-sm p-4 text-center">
        <img src="../uploads/<?php echo htmlspecialchars($user['avatar']); ?>" 
             class="rounded-circle mb-3" 
             alt="Avatar" 
             width="120" height="120">

        <h3><?php echo htmlspecialchars($user['name']); ?></h3>
        <p class="text-muted">@<?php echo htmlspecialchars($user['username']); ?></p>
        
        <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
        <p><strong>Phone:</strong> <?php echo $user['phone_number'] ?: 'Not set'; ?></p>
        <p><strong>Bio:</strong> <?php echo $user['bio'] ?: 'No bio yet'; ?></p>

        <a href="edit_profile.php" class="btn btn-primary w-100">Edit Profile</a>
        <a href="../auth/logout.php" class="btn btn-outline-danger w-100 mt-2">Logout</a>
      </div>
    </div>
  </div>
</div>

</body>
</html>
