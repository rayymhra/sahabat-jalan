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
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
  <style>
    :root {
      --notion-bg: #fafafa;
      --notion-white: #ffffff;
      --notion-gray-100: #f7f6f3;
      --notion-gray-200: #e9e7e4;
      --notion-gray-300: #d0cdc7;
      --notion-gray-600: #787774;
      --notion-gray-800: #373530;
      --notion-blue: #2383e2;
      --notion-red: #e03e3e;
      --notion-green: #0f7b0f;
      --notion-orange: #d9730d;
      --notion-purple: #8b46ff;
      --shadow-light: 0 1px 3px rgba(0, 0, 0, 0.1);
      --shadow-medium: 0 4px 12px rgba(0, 0, 0, 0.08);
      --border-radius: 8px;
      --border-radius-lg: 12px;
    }

    * {
      box-sizing: border-box;
    }

    body {
      background-color: var(--notion-bg);
      font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", sans-serif;
      color: var(--notion-gray-800);
      line-height: 1.6;
      margin: 0;
      padding: 0;
    }

    .container-fluid {
      max-width: 1200px;
      margin: 0 auto;
      padding: 20px;
    }

    /* Header */
    .header {
      background: var(--notion-white);
      border-radius: var(--border-radius-lg);
      box-shadow: var(--shadow-light);
      padding: 16px 24px;
      margin-bottom: 24px;
      display: flex;
      align-items: center;
      justify-content: space-between;
    }

    .back-btn {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      padding: 8px 16px;
      background: var(--notion-gray-100);
      color: var(--notion-gray-600);
      text-decoration: none;
      border-radius: var(--border-radius);
      font-size: 14px;
      font-weight: 500;
      transition: all 0.2s ease;
      border: none;
    }

    .back-btn:hover {
      background: var(--notion-gray-200);
      color: var(--notion-gray-800);
      transform: translateX(-2px);
    }

    .page-title {
      margin: 0;
      font-size: 28px;
      font-weight: 600;
      color: var(--notion-gray-800);
    }

    /* Main Layout */
    .main-layout {
      display: grid;
      grid-template-columns: 1fr 2fr;
      gap: 24px;
      align-items: start;
    }

    /* Profile Card */
    .profile-card {
      background: var(--notion-white);
      border-radius: var(--border-radius-lg);
      box-shadow: var(--shadow-light);
      padding: 32px;
      position: sticky;
      top: 20px;
    }

    .avatar-container {
      position: relative;
      display: inline-block;
      margin-bottom: 20px;
    }

    .avatar {
      width: 120px;
      height: 120px;
      border-radius: 50%;
      object-fit: cover;
      border: 4px solid var(--notion-white);
      box-shadow: var(--shadow-medium);
    }

    .user-name {
      font-size: 24px;
      font-weight: 600;
      color: var(--notion-gray-800);
      margin: 0 0 4px 0;
    }

    .username {
      font-size: 16px;
      color: var(--notion-gray-600);
      margin: 0 0 24px 0;
      font-weight: 400;
    }

    .user-info {
      margin-bottom: 24px;
    }

    .info-item {
      display: flex;
      align-items: center;
      gap: 12px;
      padding: 12px 0;
      border-bottom: 1px solid var(--notion-gray-200);
    }

    .info-item:last-child {
      border-bottom: none;
    }

    .info-icon {
      width: 20px;
      height: 20px;
      display: flex;
      align-items: center;
      justify-content: center;
      color: var(--notion-gray-600);
      font-size: 14px;
    }

    .info-label {
      font-size: 14px;
      color: var(--notion-gray-600);
      font-weight: 500;
      min-width: 80px;
    }

    .info-value {
      font-size: 14px;
      color: var(--notion-gray-800);
      font-weight: 400;
      flex: 1;
    }

    .reputation-badge {
      background: var(--notion-blue);
      color: white;
      padding: 4px 12px;
      border-radius: 16px;
      font-size: 12px;
      font-weight: 600;
      display: inline-block;
    }

    .action-buttons {
      display: flex;
      flex-direction: column;
      gap: 12px;
      margin-top: 24px;
    }

    .btn-primary-custom {
      background: var(--notion-blue);
      color: white;
      border: none;
      padding: 12px 20px;
      border-radius: var(--border-radius);
      font-weight: 500;
      font-size: 14px;
      text-decoration: none;
      text-align: center;
      transition: all 0.2s ease;
      display: inline-block;
    }

    .btn-primary-custom:hover {
      background: #1a6bc7;
      color: white;
      transform: translateY(-1px);
      box-shadow: var(--shadow-medium);
    }

    .btn-secondary-custom {
      background: transparent;
      color: var(--notion-red);
      border: 1px solid var(--notion-red);
      padding: 12px 20px;
      border-radius: var(--border-radius);
      font-weight: 500;
      font-size: 14px;
      text-decoration: none;
      text-align: center;
      transition: all 0.2s ease;
      display: inline-block;
    }

    .btn-secondary-custom:hover {
      background: var(--notion-red);
      color: white;
    }

    /* Reports Section */
    .reports-section {
      background: var(--notion-white);
      border-radius: var(--border-radius-lg);
      box-shadow: var(--shadow-light);
      overflow: hidden;
    }

    .reports-header {
      padding: 24px 24px 16px;
      border-bottom: 1px solid var(--notion-gray-200);
      display: flex;
      align-items: center;
      justify-content: space-between;
    }

    .reports-title {
      font-size: 20px;
      font-weight: 600;
      color: var(--notion-gray-800);
      margin: 0;
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .reports-count {
      background: var(--notion-gray-200);
      color: var(--notion-gray-600);
      padding: 4px 10px;
      border-radius: 12px;
      font-size: 12px;
      font-weight: 600;
    }

    .reports-list {
      max-height: 600px;
      overflow-y: auto;
    }

    .report-item {
      padding: 20px 24px;
      border-bottom: 1px solid var(--notion-gray-200);
      transition: background-color 0.2s ease;
    }

    .report-item:hover {
      background: var(--notion-gray-100);
    }

    .report-item:last-child {
      border-bottom: none;
    }

    .report-header {
      display: flex;
      align-items: center;
      gap: 12px;
      margin-bottom: 12px;
    }

    .report-type-badge {
      padding: 4px 12px;
      border-radius: 16px;
      font-size: 12px;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }

    .report-type-badge.accident { background: var(--notion-red); color: white; }
    .report-type-badge.hazard { background: var(--notion-orange); color: white; }
    .report-type-badge.construction { background: var(--notion-blue); color: white; }
    .report-type-badge.maintenance { background: var(--notion-purple); color: white; }
    .report-type-badge.other { background: var(--notion-gray-600); color: white; }

    .report-date {
      color: var(--notion-gray-600);
      font-size: 12px;
      font-weight: 500;
      margin-left: auto;
    }

    .report-content {
      display: grid;
      grid-template-columns: 1fr auto;
      gap: 16px;
      align-items: start;
    }

    .report-description {
      color: var(--notion-gray-800);
      font-size: 14px;
      line-height: 1.5;
      margin: 0;
    }

    .report-image {
      width: 80px;
      height: 80px;
      border-radius: var(--border-radius);
      object-fit: cover;
      box-shadow: var(--shadow-light);
    }

    .empty-state {
      text-align: center;
      padding: 48px 24px;
      color: var(--notion-gray-600);
    }

    .empty-state i {
      font-size: 48px;
      color: var(--notion-gray-300);
      margin-bottom: 16px;
    }

    .empty-state h4 {
      font-size: 18px;
      font-weight: 600;
      color: var(--notion-gray-600);
      margin-bottom: 8px;
    }

    .empty-state p {
      font-size: 14px;
      margin: 0;
    }

    /* Responsive Design */
    @media (max-width: 992px) {
      .main-layout {
        grid-template-columns: 1fr;
        gap: 20px;
      }

      .profile-card {
        position: relative;
        top: auto;
        padding: 24px;
      }

      .page-title {
        font-size: 24px;
      }
    }

    @media (max-width: 768px) {
      .container-fluid {
        padding: 12px;
      }

      .header {
        padding: 12px 16px;
        flex-direction: column;
        gap: 12px;
        align-items: flex-start;
      }

      .page-title {
        font-size: 20px;
      }

      .profile-card {
        padding: 20px;
        text-align: center;
      }

      .avatar {
        width: 100px;
        height: 100px;
      }

      .user-name {
        font-size: 20px;
      }

      .info-item {
        flex-direction: column;
        align-items: flex-start;
        gap: 4px;
      }

      .info-label {
        min-width: auto;
        font-size: 12px;
      }

      .report-content {
        grid-template-columns: 1fr;
        gap: 12px;
      }

      .report-image {
        width: 100%;
        height: 200px;
      }

      .reports-header {
        padding: 16px;
        flex-direction: column;
        align-items: flex-start;
        gap: 8px;
      }

      .report-item {
        padding: 16px;
      }
    }

    @media (max-width: 480px) {
      .action-buttons {
        gap: 8px;
      }

      .btn-primary-custom,
      .btn-secondary-custom {
        padding: 10px 16px;
        font-size: 13px;
      }

      .report-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 8px;
      }

      .report-date {
        margin-left: 0;
      }
    }
  </style>
</head>
<body>

<div class="container-fluid">
  <!-- Header -->
  <div class="header">
    <a href="../" class="back-btn">
      <i class="fas fa-arrow-left"></i>
      Back
    </a>
    <h1 class="page-title">Profile</h1>
  </div>

  <div class="main-layout">
    <!-- Profile Card -->
    <div class="profile-card">
      <div class="text-center">
        <div class="avatar-container">
          <img src="../uploads/<?php echo rawurlencode($user['avatar']); ?>" 
               class="avatar" 
               alt="Avatar">
        </div>
        
        <h2 class="user-name"><?php echo htmlspecialchars($user['name']); ?></h2>
        <p class="username">@<?php echo htmlspecialchars($user['username']); ?></p>
      </div>

      <div class="user-info">
        <div class="info-item">
          <div class="info-icon"><i class="fas fa-envelope"></i></div>
          <div class="info-label">Email</div>
          <div class="info-value"><?php echo htmlspecialchars($user['email']); ?></div>
        </div>
        
        <div class="info-item">
          <div class="info-icon"><i class="fas fa-phone"></i></div>
          <div class="info-label">Phone</div>
          <div class="info-value"><?php echo $user['phone_number'] ?: 'Not set'; ?></div>
        </div>
        
        <div class="info-item">
          <div class="info-icon"><i class="fas fa-user"></i></div>
          <div class="info-label">Bio</div>
          <div class="info-value"><?php echo $user['bio'] ?: 'No bio yet'; ?></div>
        </div>
        
        <div class="info-item">
          <div class="info-icon"><i class="fas fa-star"></i></div>
          <div class="info-label">Reputation</div>
          <div class="info-value">
            <span class="reputation-badge"><?php echo $user['reputation_score'] ?: 0; ?></span>
          </div>
        </div>
        
        <div class="info-item">
          <div class="info-icon"><i class="fas fa-calendar"></i></div>
          <div class="info-label">Joined</div>
          <div class="info-value"><?php echo date("F j, Y", strtotime($user['created_at'])); ?></div>
        </div>
      </div>

      <div class="action-buttons">
        <a href="edit_profile.php" class="btn-primary-custom">
          <i class="fas fa-edit"></i> Edit Profile
        </a>
        <a href="../auth/logout.php" class="btn-secondary-custom">
          <i class="fas fa-sign-out-alt"></i> Logout
        </a>
      </div>
    </div>

    <!-- Reports Section -->
    <div class="reports-section">
      <div class="reports-header">
        <h3 class="reports-title">
          <i class="fas fa-flag"></i>
          Your Reports
          <span class="reports-count"><?php echo $reports->num_rows; ?></span>
        </h3>
      </div>

      <?php if ($reports->num_rows > 0): ?>
        <div class="reports-list">
          <?php while ($report = $reports->fetch_assoc()): ?>
            <div class="report-item">
              <div class="report-header">
                <span class="report-type-badge <?php echo $report['type']; ?>">
                  <?php echo ucfirst($report['type']); ?>
                </span>
                <span class="report-date">
                  <?php echo date("M d, Y • H:i", strtotime($report['created_at'])); ?>
                </span>
              </div>
              
              <div class="report-content">
                <p class="report-description">
                  <?php echo nl2br(htmlspecialchars($report['description'])); ?>
                </p>
                
                <?php if ($report['photo_url']): ?>
                  <img src="../uploads/<?php echo rawurlencode($report['photo_url']); ?>" 
                       alt="Report photo" 
                       class="report-image">
                <?php endif; ?>
              </div>
            </div>
          <?php endwhile; ?>
        </div>
      <?php else: ?>
        <div class="empty-state">
          <i class="fas fa-clipboard-list"></i>
          <h4>No reports yet</h4>
          <p>You haven't made any reports. Start contributing to make your community safer!</p>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>

</body>
</html>