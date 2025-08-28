<?php
session_start();
include "../../connection.php";

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
    $targetDir = "../uploads/";

    // Get original name safely
    $originalName = basename($_FILES["avatar"]["name"]);

    // Replace spaces and special characters
    $safeName = preg_replace("/[^A-Za-z0-9._-]/", "_", $originalName);

    // Add timestamp
    $fileName = time() . "_" . $safeName;
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
      
      // After successfully updating the user profile in the database
      $_SESSION['name'] = $name;
      $_SESSION['avatar'] = $avatar;
      // etc.
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
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
<style>
  :root {
    --notion-bg: #fafafa;
    --notion-white: #ffffff;
    --notion-gray-100: #f7f6f3;
    --notion-gray-200: #e9e7e4;
    --notion-gray-300: #d0cdc7;
    --notion-gray-400: #a8a29e;
    --notion-gray-600: #787774;
    --notion-gray-800: #373530;
    --notion-blue: #2383e2;
    --notion-red: #e03e3e;
    --notion-green: #0f7b0f;
    --notion-orange: #d9730d;
    --shadow-light: 0 1px 3px rgba(0, 0, 0, 0.1);
    --shadow-medium: 0 4px 12px rgba(0, 0, 0, 0.08);
    --shadow-focus: 0 0 0 3px rgba(35, 131, 226, 0.1);
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
    min-height: 100vh;
  }

  .container {
    max-width: 600px;
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
    display: flex;
    align-items: center;
    gap: 12px;
  }

  .page-title i {
    color: var(--notion-blue);
  }

  /* Main Card */
  .main-card {
    background: var(--notion-white);
    border-radius: var(--border-radius-lg);
    box-shadow: var(--shadow-light);
    padding: 32px;
    margin-bottom: 24px;
  }

  /* Alert Styles */
  .alert-custom {
    border: none;
    border-radius: var(--border-radius);
    padding: 16px 20px;
    margin-bottom: 24px;
    font-size: 14px;
    font-weight: 500;
    display: flex;
    align-items: center;
    gap: 12px;
  }

  .alert-success {
    background: rgba(15, 123, 15, 0.1);
    color: var(--notion-green);
    border-left: 4px solid var(--notion-green);
  }

  .alert-danger {
    background: rgba(224, 62, 62, 0.1);
    color: var(--notion-red);
    border-left: 4px solid var(--notion-red);
  }

  /* Avatar Section */
  .avatar-section {
    text-align: center;
    margin-bottom: 32px;
    padding: 24px;
    background: var(--notion-gray-100);
    border-radius: var(--border-radius-lg);
    border: 2px dashed var(--notion-gray-300);
    transition: all 0.2s ease;
  }

  .avatar-section:hover {
    border-color: var(--notion-blue);
    background: rgba(35, 131, 226, 0.02);
  }

  .avatar-container {
    position: relative;
    display: inline-block;
    margin-bottom: 16px;
  }

  .avatar-preview {
    width: 120px;
    height: 120px;
    border-radius: 50%;
    object-fit: cover;
    border: 4px solid var(--notion-white);
    box-shadow: var(--shadow-medium);
  }

  .avatar-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.6);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: opacity 0.2s ease;
    cursor: pointer;
  }

  .avatar-container:hover .avatar-overlay {
    opacity: 1;
  }

  .avatar-overlay i {
    color: white;
    font-size: 24px;
  }

  .avatar-upload-label {
    color: var(--notion-gray-600);
    font-size: 14px;
    font-weight: 500;
    margin-bottom: 8px;
  }

  .file-input-wrapper {
    position: relative;
    display: inline-block;
    cursor: pointer;
  }

  .file-input-custom {
    position: absolute;
    opacity: 0;
    width: 100%;
    height: 100%;
    cursor: pointer;
  }

  .file-input-btn {
    background: var(--notion-blue);
    color: white;
    padding: 8px 16px;
    border-radius: var(--border-radius);
    font-size: 13px;
    font-weight: 500;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: all 0.2s ease;
    border: none;
  }

  .file-input-btn:hover {
    background: #1a6bc7;
    transform: translateY(-1px);
  }

  /* Form Styles */
  .form-group {
    margin-bottom: 24px;
  }

  .form-label {
    display: block;
    font-size: 14px;
    font-weight: 600;
    color: var(--notion-gray-800);
    margin-bottom: 8px;
    display: flex;
    align-items: center;
    gap: 8px;
  }

  .form-label i {
    color: var(--notion-gray-600);
    width: 16px;
  }

  .form-input {
    width: 100%;
    padding: 12px 16px;
    border: 2px solid var(--notion-gray-200);
    border-radius: var(--border-radius);
    font-size: 14px;
    color: var(--notion-gray-800);
    background: var(--notion-white);
    transition: all 0.2s ease;
    outline: none;
  }

  .form-input:focus {
    border-color: var(--notion-blue);
    box-shadow: var(--shadow-focus);
  }

  .form-input:hover {
    border-color: var(--notion-gray-300);
  }

  .form-textarea {
    resize: vertical;
    min-height: 80px;
    font-family: inherit;
  }

  .form-input::placeholder {
    color: var(--notion-gray-400);
  }

  /* Required Field Indicator */
  .required-indicator {
    color: var(--notion-red);
    font-size: 12px;
    margin-left: 4px;
  }

  /* Action Buttons */
  .action-buttons {
    display: flex;
    gap: 12px;
    margin-top: 32px;
  }

  .btn-primary-custom {
    flex: 1;
    background: var(--notion-blue);
    color: white;
    border: none;
    padding: 14px 24px;
    border-radius: var(--border-radius);
    font-weight: 600;
    font-size: 14px;
    text-decoration: none;
    text-align: center;
    transition: all 0.2s ease;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    cursor: pointer;
  }

  .btn-primary-custom:hover {
    background: #1a6bc7;
    color: white;
    transform: translateY(-1px);
    box-shadow: var(--shadow-medium);
  }

  .btn-secondary-custom {
    background: transparent;
    color: var(--notion-gray-600);
    border: 2px solid var(--notion-gray-200);
    padding: 14px 24px;
    border-radius: var(--border-radius);
    font-weight: 500;
    font-size: 14px;
    text-decoration: none;
    text-align: center;
    transition: all 0.2s ease;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
  }

  .btn-secondary-custom:hover {
    background: var(--notion-gray-100);
    border-color: var(--notion-gray-300);
    color: var(--notion-gray-800);
  }

  /* Form Helper Text */
  .form-helper {
    font-size: 12px;
    color: var(--notion-gray-600);
    margin-top: 6px;
    display: flex;
    align-items: center;
    gap: 6px;
  }

  .form-helper i {
    font-size: 11px;
  }

  /* Responsive Design */
  @media (max-width: 768px) {
    .container {
      padding: 12px;
    }

    .header {
      padding: 12px 16px;
      flex-direction: column;
      gap: 12px;
      align-items: flex-start;
    }

    .page-title {
      font-size: 24px;
    }

    .main-card {
      padding: 24px;
    }

    .avatar-preview {
      width: 100px;
      height: 100px;
    }

    .action-buttons {
      flex-direction: column;
      gap: 8px;
    }

    .btn-primary-custom,
    .btn-secondary-custom {
      padding: 12px 20px;
    }
  }

  @media (max-width: 480px) {
    .container {
      padding: 8px;
    }

    .main-card {
      padding: 20px;
    }

    .page-title {
      font-size: 20px;
    }

    .avatar-section {
      padding: 20px;
    }

    .form-input {
      padding: 10px 14px;
    }
  }

  /* Loading State */
  .btn-loading {
    position: relative;
    color: transparent !important;
  }

  .btn-loading::after {
    content: '';
    position: absolute;
    width: 16px;
    height: 16px;
    top: 50%;
    left: 50%;
    margin-left: -8px;
    margin-top: -8px;
    border: 2px solid transparent;
    border-top-color: currentColor;
    border-radius: 50%;
    animation: spin 1s linear infinite;
  }

  @keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
  }
</style>
</head>

<body>

<div class="container">
  <!-- Header -->
  <div class="header">
    <a href="index.php" class="back-btn">
      <i class="fas fa-arrow-left"></i>
      Back to Profile
    </a>
    <h1 class="page-title">
      <i class="fas fa-user-edit"></i>
      Edit Profile
    </h1>
  </div>

  <!-- Main Card -->
  <div class="main-card">
    <?php if($success): ?>
      <div class="alert-custom alert-success">
        <i class="fas fa-check-circle"></i>
        <?php echo $success; ?>
      </div>
    <?php endif; ?>
    
    <?php if($error): ?>
      <div class="alert-custom alert-danger">
        <i class="fas fa-exclamation-circle"></i>
        <?php echo $error; ?>
      </div>
    <?php endif; ?>
    
    <form method="POST" enctype="multipart/form-data" id="profileForm">
      <!-- Avatar Section -->
      <div class="avatar-section">
        <div class="avatar-container">
          <img src="../../uploads/<?php echo htmlspecialchars($user['avatar']); ?>" 
               class="avatar-preview" 
               alt="Current avatar"
               id="avatarPreview">
          <div class="avatar-overlay">
            <i class="fas fa-camera"></i>
          </div>
        </div>
        
        <div class="avatar-upload-label">Profile Picture</div>
        
        <div class="file-input-wrapper">
          <input type="file" 
                 name="avatar" 
                 class="file-input-custom" 
                 accept="image/*"
                 id="avatarInput">
          <div class="file-input-btn">
            <i class="fas fa-upload"></i>
            Choose Image
          </div>
        </div>
        
        <div class="form-helper">
          <i class="fas fa-info-circle"></i>
          Maximum file size: 5MB. Supported formats: JPG, PNG, GIF
        </div>
      </div>

      <!-- Form Fields -->
      <div class="form-group">
        <label class="form-label">
          <i class="fas fa-user"></i>
          Full Name
          <span class="required-indicator">*</span>
        </label>
        <input type="text" 
               name="name" 
               value="<?php echo htmlspecialchars($user['name']); ?>" 
               class="form-input" 
               required
               placeholder="Enter your full name">
      </div>
      
      <div class="form-group">
        <label class="form-label">
          <i class="fas fa-envelope"></i>
          Email Address
          <span class="required-indicator">*</span>
        </label>
        <input type="email" 
               name="email" 
               value="<?php echo htmlspecialchars($user['email']); ?>" 
               class="form-input" 
               required
               placeholder="Enter your email address">
        <div class="form-helper">
          <i class="fas fa-info-circle"></i>
          This will be used for login and important notifications
        </div>
      </div>
      
      <div class="form-group">
        <label class="form-label">
          <i class="fas fa-phone"></i>
          Phone Number
        </label>
        <input type="tel" 
               name="phone" 
               value="<?php echo htmlspecialchars($user['phone_number'] ?? ''); ?>" 
               class="form-input"
               placeholder="Enter your phone number">
        <div class="form-helper">
          <i class="fas fa-info-circle"></i>
          Optional - for account recovery and updates
        </div>
      </div>
      
      <div class="form-group">
        <label class="form-label">
          <i class="fas fa-align-left"></i>
          Bio
        </label>
        <textarea name="bio" 
                  class="form-input form-textarea" 
                  rows="4"
                  placeholder="Tell us a bit about yourself..."><?php echo htmlspecialchars($user['bio'] ?? ''); ?></textarea>
        <div class="form-helper">
          <i class="fas fa-info-circle"></i>
          Share a brief description about yourself (optional)
        </div>
      </div>

      <!-- Action Buttons -->
      <div class="action-buttons">
        <button type="submit" class="btn-primary-custom" id="saveBtn">
          <i class="fas fa-save"></i>
          Save Changes
        </button>
        <a href="index.php" class="btn-secondary-custom">
          <i class="fas fa-times"></i>
          Cancel
        </a>
      </div>
    </form>
  </div>
</div>

<script>
// Avatar preview functionality
document.getElementById('avatarInput').addEventListener('change', function(e) {
  const file = e.target.files[0];
  if (file) {
    const reader = new FileReader();
    reader.onload = function(e) {
      document.getElementById('avatarPreview').src = e.target.result;
    }
    reader.readAsDataURL(file);
  }
});

// Form submission with loading state
document.getElementById('profileForm').addEventListener('submit', function(e) {
  const saveBtn = document.getElementById('saveBtn');
  saveBtn.classList.add('btn-loading');
  saveBtn.disabled = true;
});

// Auto-hide alerts after 5 seconds
const alerts = document.querySelectorAll('.alert-custom');
alerts.forEach(alert => {
  setTimeout(() => {
    alert.style.opacity = '0';
    alert.style.transform = 'translateY(-10px)';
    setTimeout(() => {
      alert.remove();
    }, 300);
  }, 5000);
});
</script>

</body>
</html>