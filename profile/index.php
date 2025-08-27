<?php
session_start();
include "../connection.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Get user data
$stmt = $conn->prepare("SELECT name, username, email, avatar, phone_number, bio, reputation_score, created_at 
                        FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

// Get reports by this user
$stmt = $conn->prepare("SELECT id, type, description, photo_url, created_at 
                        FROM reports WHERE user_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$reports = $stmt->get_result();
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

<div class="container py-4">
  <!-- Back Button -->
  <div class="mb-3">
    <a href="../" class="btn btn-secondary">
      ← Back
    </a>
  </div>

  <div class="row justify-content-center">
    <div class="col-md-8 col-lg-6">
      <!-- Profile Card -->
      <div class="card shadow-sm p-4 text-center mb-4">
        <img src="../uploads/<?php echo rawurlencode($user['avatar']); ?>" 
             class="rounded-circle mb-3 img-fluid" 
             alt="Avatar" 
             style="width:120px; height:120px; object-fit:cover;">

        <h3 class="fw-bold"><?php echo htmlspecialchars($user['name']); ?></h3>
        <p class="text-muted">@<?php echo htmlspecialchars($user['username']); ?></p>
        
        <div class="text-start">
          <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
          <p><strong>Phone:</strong> <?php echo $user['phone_number'] ?: 'Not set'; ?></p>
          <p><strong>Bio:</strong> <?php echo $user['bio'] ?: 'No bio yet'; ?></p>
          <p><strong>Reputation:</strong> <?php echo $user['reputation_score'] ?: 0; ?></p>
          <p class="text-muted"><small>Joined on <?php echo date("F j, Y", strtotime($user['created_at'])); ?></small></p>
        </div>

        <a href="edit_profile.php" class="btn btn-primary w-100">Edit Profile</a>
        <a href="../auth/logout.php" class="btn btn-outline-danger w-100 mt-2">Logout</a>
      </div>

      <!-- Reports List -->
      <div class="card shadow-sm p-4">
        <h5 class="mb-3 fw-bold">Your Reports</h5>
        <?php if ($reports->num_rows > 0): ?>
          <ul class="list-group list-group-flush">
            <?php while ($report = $reports->fetch_assoc()): ?>
              <li class="list-group-item">
                <div class="d-flex justify-content-between align-items-start">
                  <div>
                    <span class="badge bg-info text-dark"><?php echo ucfirst($report['type']); ?></span>
                    <p class="mb-1"><?php echo nl2br(htmlspecialchars($report['description'])); ?></p>
                    <small class="text-muted">
                      <?php echo date("M d, Y H:i", strtotime($report['created_at'])); ?>
                    </small>
                  </div>
                  <?php if ($report['photo_url']): ?>
                    <img src="../uploads/<?php echo rawurlencode($report['photo_url']); ?>" 
                         alt="Report photo" class="img-thumbnail ms-3" 
                         style="width:80px; height:80px; object-fit:cover;">
                  <?php endif; ?>
                </div>
              </li>
            <?php endwhile; ?>
          </ul>
        <?php else: ?>
          <p class="text-muted">You haven't made any reports yet.</p>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

</body>
</html>
