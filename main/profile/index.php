<?php
session_start();
include "../../connection.php";

// Check if we're viewing another user's profile
if (isset($_GET['id'])) {
    $viewed_user_id = intval($_GET['id']);
    
    // Get the viewed user's data
    $stmt = $conn->prepare("SELECT name, username, email, avatar, phone_number, bio, reputation_score, created_at 
                            FROM users WHERE id = ?");
    $stmt->bind_param("i", $viewed_user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        // User not found, redirect to own profile
        header("Location: index.php");
        exit;
    }
    
    $user = $result->fetch_assoc();
    $stmt->close();
    
    // Get reports by this user with route names
    $stmt = $conn->prepare("SELECT r.id, r.type, r.description, r.photo_url, r.created_at, rt.name as route_name
                            FROM reports r 
                            LEFT JOIN routes rt ON r.route_id = rt.id 
                            WHERE r.user_id = ? 
                            ORDER BY r.created_at DESC");
    $stmt->bind_param("i", $viewed_user_id);
    $stmt->execute();
    $reports = $stmt->get_result();
    $stmt->close();
    
    // Get routes created by this user
    $stmt = $conn->prepare("SELECT id, name, start_latitude, start_longitude, end_latitude, end_longitude, created_at 
                            FROM routes WHERE created_by = ? ORDER BY created_at DESC");
    $stmt->bind_param("i", $viewed_user_id);
    $stmt->execute();
    $routes = $stmt->get_result();
    $stmt->close();
    
    // Get comments by this user
    $stmt = $conn->prepare("SELECT c.id, c.report_id, c.content, c.created_at, r.type as report_type
                            FROM comments c 
                            JOIN reports r ON c.report_id = r.id 
                            WHERE c.user_id = ? 
                            ORDER BY c.created_at DESC");
    $stmt->bind_param("i", $viewed_user_id);
    $stmt->execute();
    $comments = $stmt->get_result();
    $stmt->close();
    
    $is_own_profile = isset($_SESSION['user_id']) && $_SESSION['user_id'] == $viewed_user_id;
} else {
    // Viewing own profile
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit;
    }
    
    $viewed_user_id = $_SESSION['user_id'];
    $is_own_profile = true;
    
    // Get user data
    $stmt = $conn->prepare("SELECT name, username, email, avatar, phone_number, bio, reputation_score, created_at 
                            FROM users WHERE id = ?");
    $stmt->bind_param("i", $viewed_user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();

    // Get reports by this user with route names
    $stmt = $conn->prepare("SELECT r.id, r.type, r.description, r.photo_url, r.created_at, rt.name as route_name
                            FROM reports r 
                            LEFT JOIN routes rt ON r.route_id = rt.id 
                            WHERE r.user_id = ? 
                            ORDER BY r.created_at DESC");
    $stmt->bind_param("i", $viewed_user_id);
    $stmt->execute();
    $reports = $stmt->get_result();
    $stmt->close();

    // Get routes created by this user
    $stmt = $conn->prepare("SELECT id, name, start_latitude, start_longitude, end_latitude, end_longitude, created_at 
                            FROM routes WHERE created_by = ? ORDER BY created_at DESC");
    $stmt->bind_param("i", $viewed_user_id);
    $stmt->execute();
    $routes = $stmt->get_result();
    $stmt->close();
    
    // Get comments by this user
    $stmt = $conn->prepare("SELECT c.id, c.report_id, c.content, c.created_at, r.type as report_type
                            FROM comments c 
                            JOIN reports r ON c.report_id = r.id 
                            WHERE c.user_id = ? 
                            ORDER BY c.created_at DESC");
    $stmt->bind_param("i", $viewed_user_id);
    $stmt->execute();
    $comments = $stmt->get_result();
    $stmt->close();
}

// Handle delete actions
if ($is_own_profile && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete_report'])) {
        $report_id = intval($_POST['report_id']);
        $stmt = $conn->prepare("DELETE FROM reports WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $report_id, $viewed_user_id);
        $stmt->execute();
        $stmt->close();
        
        // Refresh page to show updated list
        header("Location: " . $_SERVER['PHP_SELF'] . (isset($_GET['id']) ? '?id=' . $viewed_user_id : ''));
        exit;
    }
    
    if (isset($_POST['delete_route'])) {
        $route_id = intval($_POST['route_id']);
        $stmt = $conn->prepare("DELETE FROM routes WHERE id = ? AND created_by = ?");
        $stmt->bind_param("ii", $route_id, $viewed_user_id);
        $stmt->execute();
        $stmt->close();
        
        // Refresh page to show updated list
        header("Location: " . $_SERVER['PHP_SELF'] . (isset($_GET['id']) ? '?id=' . $viewed_user_id : ''));
        exit;
    }
    
    if (isset($_POST['delete_comment'])) {
        $comment_id = intval($_POST['comment_id']);
        $stmt = $conn->prepare("DELETE FROM comments WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $comment_id, $viewed_user_id);
        $stmt->execute();
        $stmt->close();
        
        // Refresh page to show updated list
        header("Location: " . $_SERVER['PHP_SELF'] . (isset($_GET['id']) ? '?id=' . $viewed_user_id : ''));
        exit;
    }
}
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

    /* Content Tabs */
    .content-tabs {
      display: flex;
      border-bottom: 1px solid var(--notion-gray-200);
      margin-bottom: 16px;
    }

    .tab-btn {
      padding: 12px 24px;
      background: none;
      border: none;
      font-size: 14px;
      font-weight: 500;
      color: var(--notion-gray-600);
      cursor: pointer;
      transition: all 0.2s ease;
      position: relative;
    }

    .tab-btn.active {
      color: var(--notion-blue);
      font-weight: 600;
    }

    .tab-btn.active::after {
      content: '';
      position: absolute;
      bottom: -1px;
      left: 0;
      width: 100%;
      height: 2px;
      background: var(--notion-blue);
    }

    .tab-btn:hover {
      color: var(--notion-blue);
    }

    .tab-content {
      display: none;
    }

    .tab-content.active {
      display: block;
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

    .reports-list, .routes-list {
      max-height: 600px;
      overflow-y: auto;
    }

    .report-item, .route-item {
      padding: 20px 24px;
      border-bottom: 1px solid var(--notion-gray-200);
      transition: background-color 0.2s ease;
    }

    .report-item:hover, .route-item:hover {
      background: var(--notion-gray-100);
    }

    .report-item:last-child, .route-item:last-child {
      border-bottom: none;
    }

    .report-header, .route-header {
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

    .report-type-badge.crime { background: var(--notion-red); color: white; }
    .report-type-badge.accident { background: #ff4d4d; color: white; }
    .report-type-badge.hazard { background: var(--notion-orange); color: white; }
    .report-type-badge.safe_spot { background: var(--notion-green); color: white; }
    .report-type-badge.other { background: var(--notion-gray-600); color: white; }

    .route-badge {
      padding: 4px 12px;
      border-radius: 16px;
      font-size: 12px;
      font-weight: 600;
      background: var(--notion-purple);
      color: white;
    }

    .report-date, .route-date {
      color: var(--notion-gray-600);
      font-size: 12px;
      font-weight: 500;
      margin-left: auto;
    }

    .report-content, .route-content {
      display: grid;
      grid-template-columns: 1fr auto;
      gap: 16px;
      align-items: start;
    }

    .report-description, .route-description {
      color: var(--notion-gray-800);
      font-size: 14px;
      line-height: 1.5;
      margin: 0;
    }

    .route-locations {
      font-size: 14px;
      color: var(--notion-gray-600);
      margin-top: 8px;
    }

    .route-locations i {
      margin-right: 4px;
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

      .report-content, .route-content {
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

      .report-item, .route-item {
        padding: 16px;
      }

      .content-tabs {
        flex-direction: column;
      }

      .tab-btn {
        width: 100%;
        text-align: left;
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

      .report-header, .route-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 8px;
      }

      .report-date, .route-date {
        margin-left: 0;
      }
    }

    .route-info {
    display: flex;
    align-items: center;
    gap: 6px;
    margin-top: 8px;
    font-size: 14px;
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
    <h1 class="page-title">Profile <?php echo !$is_own_profile ? ' - ' . htmlspecialchars($user['name']) : ''; ?></h1>
  </div>

  <div class="main-layout">
    <!-- Profile Card -->
    <div class="profile-card">
      <div class="text-center">
        <div class="avatar-container">
          <img src="../../uploads/<?php echo rawurlencode($user['avatar']); ?>" 
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
          <div class="info-label">No Telp</div>
          <div class="info-value"><?php echo $user['phone_number'] ?: 'Not set'; ?></div>
        </div>
        
        <div class="info-item">
          <div class="info-icon"><i class="fas fa-user"></i></div>
          <div class="info-label">Bio</div>
          <div class="info-value"><?php echo $user['bio'] ?: 'No bio yet'; ?></div>
        </div>
        
        <div class="info-item">
          <div class="info-icon"><i class="fas fa-star"></i></div>
          <div class="info-label">Reputasi</div>
          <div class="info-value">
            <span class="reputation-badge"><?php echo $user['reputation_score'] ?: 0; ?></span>
          </div>
        </div>
        
        <div class="info-item">
          <div class="info-icon"><i class="fas fa-calendar"></i></div>
          <div class="info-label">Bergabung</div>
          <div class="info-value"><?php echo date("F j, Y", strtotime($user['created_at'])); ?></div>
        </div>
      </div>

      <div class="action-buttons">
    <?php if ($is_own_profile): ?>
        <a href="edit_profile.php" class="btn-primary-custom">
            <i class="fas fa-edit"></i> Edit Profil
        </a>
        <a href="../../auth/logout.php" class="btn-secondary-custom">
            <i class="fas fa-sign-out-alt"></i> Keluar
        </a>
    <?php else: ?>
        <!-- Optionally add a "Message" or "Follow" button for other users -->
        <!-- <button class="btn-primary-custom" disabled>
            <i class="fas fa-user"></i> Lihat Profil
        </button> -->
    <?php endif; ?>
</div>



    
  </div>
  <!-- Content Section with Tabs -->
    <div class="reports-section">
      <div class="reports-header">
        <h3 class="reports-title">
          <i class="fas fa-user-circle"></i>
          Kontribusi
        </h3>
      </div>

      <div class="content-tabs">
    <button class="tab-btn active" data-tab="reports">
        <i class="fas fa-flag"></i> Laporan
        <span class="reports-count"><?php echo $reports->num_rows; ?></span>
    </button>
    <button class="tab-btn" data-tab="routes">
        <i class="fas fa-route"></i> Rute
        <span class="reports-count"><?php echo $routes->num_rows; ?></span>
    </button>
    <button class="tab-btn" data-tab="comments">
        <i class="fas fa-comment"></i> Komentar
        <span class="reports-count"><?php echo $comments->num_rows; ?></span>
    </button>
</div>

      <!-- Reports Tab -->
      <div class="tab-content active" id="reports-tab">
    <?php if ($reports->num_rows > 0): ?>
        <div class="reports-list">
            <?php while ($report = $reports->fetch_assoc()): ?>
                <div class="report-item">
                    <div class="report-header">
                        <?php
                        $type_map = [
                            'crime' => 'Kejahatan',
                            'accident' => 'Kecelakaan',
                            'hazard' => 'Bahaya',
                            'safe_spot' => 'Aman',
                            'other' => 'Lainnya'
                        ];
                        ?>

                        <span class="report-type-badge <?php echo $report['type']; ?>">
                            <?php echo $type_map[$report['type']] ?? ucfirst($report['type']); ?>
                        </span>
                        <span class="report-date">
                            <?php echo date("M d, Y • H:i", strtotime($report['created_at'])); ?>
                        </span>
                    </div>
                    
                    <div class="report-content">
                        <div>
                            <p class="report-description">
                                <?php echo nl2br(htmlspecialchars($report['description'])); ?>
                            </p>

                            
                            
                            <!-- Show route name if available -->
                            <?php if (!empty($report['route_name'])): ?>
                                <div class="route-info" style="margin-top: 8px;">
                                    <i class="fas fa-route" style="color: #8b46ff;"></i>
                                    <span style="font-size: 14px; color: #8b46ff;">
                                        Rute: <?php echo htmlspecialchars($report['route_name']); ?>
                                    </span>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <?php if ($report['photo_url']): ?>
                            <img src="../uploads/<?php echo rawurlencode($report['photo_url']); ?>" 
                                 alt="Report photo" 
                                 class="report-image">
                        <?php endif; ?>
                    </div>

                    <div style="margin-top: 12px; text-align: right;">
                    <a href="../index.php?report_id=<?php echo $report['id']; ?>" 
                       class="btn-primary-custom" 
                       style="padding: 6px 12px; font-size: 12px; margin-left: 8px;">
                        <i class="fas fa-external-link-alt"></i> Lihat Laporan
                    </a>
                        </div>
                    
                    <!-- Delete button for own reports -->
                    <?php if ($is_own_profile): ?>
                        <div style="margin-top: 12px; text-align: right;">
                            <form method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus laporan ini?');">
                                <input type="hidden" name="report_id" value="<?php echo $report['id']; ?>">
                                <button type="submit" name="delete_report" class="btn-secondary-custom" style="padding: 6px 12px; font-size: 12px;">
                                    <i class="fas fa-trash"></i> Hapus
                                </button>
                            </form>
                        </div>
                    <?php endif; ?>
                    
                </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <div class="empty-state">
            <i class="fas fa-clipboard-list"></i>
            <h4>Belum ada laporan</h4>
            <p>Anda belum membuat laporan. Mulailah berkontribusi untuk membuat komunitas lebih aman!</p>
        </div>
    <?php endif; ?>
</div>

      <!-- Routes Tab -->
      <div class="tab-content" id="routes-tab">
    <?php if ($routes->num_rows > 0): ?>
        <div class="routes-list">
            <?php while ($route = $routes->fetch_assoc()): ?>
                <div class="route-item">
                    <div class="route-header">
                        <span class="route-badge">
                            Rute
                        </span>
                        <span class="route-date">
                            <?php echo date("M d, Y • H:i", strtotime($route['created_at'])); ?>
                        </span>
                    </div>
                    
                    <div class="route-content">
                        <div>
                            <h5 class="route-name"><?php echo htmlspecialchars($route['name']); ?></h5>
                            <div class="route-locations">
                                <div><i class="fas fa-play-circle"></i> 
                                    Start: <?php echo htmlspecialchars($route['start_latitude']); ?>, <?php echo htmlspecialchars($route['start_longitude']); ?>
                                </div>
                                <div><i class="fas fa-flag-checkered"></i> 
                                    End: <?php echo htmlspecialchars($route['end_latitude']); ?>, <?php echo htmlspecialchars($route['end_longitude']); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Delete button for own routes -->
                    <?php if ($is_own_profile): ?>
                        <div style="margin-top: 12px; text-align: right;">
                            <form method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus rute ini?');">
                                <input type="hidden" name="route_id" value="<?php echo $route['id']; ?>">
                                <button type="submit" name="delete_route" class="btn-secondary-custom" style="padding: 6px 12px; font-size: 12px;">
                                    <i class="fas fa-trash"></i> Hapus
                                </button>
                            </form>
                        </div>

                        <?php endif; ?>
                        <div style="margin-top: 12px; text-align: right;">
                    <a href="../index.php?highlight_route=<?php echo $route['id']; ?>" 
                       class="btn-primary-custom" 
                       style="padding: 6px 12px; font-size: 12px; margin-left: 8px;">
                        <i class="fas fa-external-link-alt"></i> Lihat Rute
                    </a>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <div class="empty-state">
            <i class="fas fa-route"></i>
            <h4>Belum ada rute</h4>
            <p>Anda belum membuat rute. Bagikan rute aman Anda untuk membantu komunitas!</p>
        </div>
    <?php endif; ?>
</div>

<div class="tab-content" id="comments-tab">
    <?php if ($comments->num_rows > 0): ?>
        <div class="reports-list">
            <?php while ($comment = $comments->fetch_assoc()): ?>
                <div class="report-item">
                    <div class="report-header">
                        <span class="report-type-badge <?php echo $comment['report_type']; ?>">
                            Komentar
                        </span>
                        <span class="report-date">
                            <?php echo date("M d, Y • H:i", strtotime($comment['created_at'])); ?>
                        </span>
                    </div>
                    
                    <div class="report-content">
                        <p class="report-description">
                            <?php echo nl2br(htmlspecialchars($comment['content'])); ?>
                        </p>
                        
                        <div style="margin-top: 8px;">
                            <a href="../index.php?report_id=<?php echo $comment['report_id']; ?>" 
                               class="btn-primary-custom" 
                               style="padding: 6px 12px; font-size: 12px;">
                                <i class="fas fa-external-link-alt"></i> Lihat Laporan
                            </a>
                        </div>
                    </div>
                    
                    <!-- Delete button for own comments -->
                    <?php if ($is_own_profile): ?>
                        <div style="margin-top: 12px; text-align: right;">
                            <form method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus komentar ini?');">
                                <input type="hidden" name="comment_id" value="<?php echo $comment['id']; ?>">
                                <button type="submit" name="delete_comment" class="btn-secondary-custom" style="padding: 6px 12px; font-size: 12px;">
                                    <i class="fas fa-trash"></i> Hapus
                                </button>
                            </form>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <div class="empty-state">
            <i class="fas fa-comments"></i>
            <h4>Belum ada komentar</h4>
            <p>Anda belum memberikan komentar pada laporan apapun.</p>
        </div>
    <?php endif; ?>
</div>

<script>
  // Tab functionality
document.addEventListener('DOMContentLoaded', function() {
    const tabButtons = document.querySelectorAll('.tab-btn');
    const tabContents = document.querySelectorAll('.tab-content');
    
    tabButtons.forEach(button => {
        button.addEventListener('click', () => {
            const tabId = button.getAttribute('data-tab');
            
            // Update active button
            tabButtons.forEach(btn => btn.classList.remove('active'));
            button.classList.add('active');
            
            // Show active content
            tabContents.forEach(content => content.classList.remove('active'));
            document.getElementById(`${tabId}-tab`).classList.add('active');
        });
    });
});
</script>

</body>
</html>