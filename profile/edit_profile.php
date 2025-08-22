<?php
session_start();
include "../connection.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$success = $error = "";

// Fetch user info
$stmt = $conn->prepare("SELECT name, email, avatar, phone_number, bio FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $bio = trim($_POST['bio']);

    // Handle avatar upload
    $avatar = $user['avatar'];
    if (!empty($_FILES['avatar']['name'])) {
        $targetDir = "uploads/";
        $fileName = time() . "_" . basename($_FILES["avatar"]["name"]);
        $targetFile = $targetDir . $fileName;

        if (move_uploaded_file($_FILES["avatar"]["tmp_name"], $targetFile)) {
            $avatar = $fileName;
        } else {
            $error = "Failed to upload avatar.";
        }
    }

    if (!$error) {
        $stmt = $conn->prepare("UPDATE users SET name=?, email=?, phone_number=?, bio=?, avatar=? WHERE id=?");
        $stmt->bind_param("sssssi", $name, $email, $phone, $bio, $avatar, $user_id);

        if ($stmt->execute()) {
            $success = "Profile updated successfully!";
            // refresh user data
            $user['name'] = $name;
            $user['email'] = $email;
            $user['phone_number'] = $phone;
            $user['bio'] = $bio;
            $user['avatar'] = $avatar;
        } else {
            $error = "Error updating profile: " . $stmt->error;
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Edit Profile</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container py-5">
  <div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
      <div class="card shadow-sm p-4">
        <h3 class="mb-4 text-center">Edit Profile</h3>

        <?php if($success): ?>
          <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        <?php if($error): ?>
          <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
          <div class="mb-3 text-center">
            <img src="../uploads/<?php echo htmlspecialchars($user['avatar']); ?>" 
                 class="rounded-circle mb-2" width="100" height="100">
            <input type="file" name="avatar" class="form-control mt-2">
          </div>

          <div class="mb-3">
            <label class="form-label">Full Name</label>
            <input type="text" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" class="form-control" required>
          </div>

          <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" class="form-control" required>
          </div>

          <div class="mb-3">
            <label class="form-label">Phone</label>
            <input type="text" name="phone" value="<?php echo htmlspecialchars($user['phone_number'] ?? ''); ?>" class="form-control">
          </div>

          <div class="mb-3">
            <label class="form-label">Bio</label>
            <textarea name="bio" class="form-control" rows="3"><?php echo htmlspecialchars($user['bio'] ?? '' ); ?></textarea>
          </div>

          <button type="submit" class="btn btn-success w-100">Save Changes</button>
          <a href="index.php" class="btn btn-secondary w-100 mt-2">Cancel</a>
        </form>
      </div>
    </div>
  </div>
</div>

</body>
</html>
