<?php
session_start();
include_once 'connection.php'; // Your database connection file

$userLoggedIn = isset($_SESSION['user_id']);
$userData = $userLoggedIn ? [
'id' => $_SESSION['user_id'],
'name' => $_SESSION['name'],
'username' => $_SESSION['username'],
'role' => $_SESSION['role'],
'avatar' => $_SESSION['avatar']
] : null;

// Load routes from database
$routes = [];
$reports = [];

if ($conn) {
    // ================================
    // Fetch routes + their reports
    // ================================
    $routeQuery = "
    SELECT r.*, u.name AS creator_name 
    FROM routes r 
    LEFT JOIN users u ON r.created_by = u.id 
    ORDER BY r.created_at DESC
    ";
    $routeResult = $conn->query($routeQuery);
    
    if ($routeResult) {
        while ($route = $routeResult->fetch_assoc()) {
            $routeId = $route['id'];
            
            // Fetch reports for this route
            $reportQuery = "
            SELECT rep.*, u.name AS user_name 
            FROM reports rep 
            LEFT JOIN users u ON rep.user_id = u.id 
            WHERE rep.route_id = $routeId 
            ORDER BY rep.created_at DESC
            ";
            $reportResult = $conn->query($reportQuery);
            
            $routeReports = [];
            if ($reportResult) {
                while ($report = $reportResult->fetch_assoc()) {
                    $routeReports[] = $report;
                }
            } else {
                error_log("Report query error: " . $conn->error);
            }
            
            $route['reports'] = $routeReports;
            $routes[] = $route;
        }
    } else {
        error_log("Route query error: " . $conn->error);
    }
    
    // ================================
    // Fetch all reports (latest 20)
    // ================================
    $allReportsQuery = "
    SELECT rep.*, 
    CONCAT(r.start_latitude, ',', r.start_longitude, ' → ', r.end_latitude, ',', r.end_longitude) AS route_name,
    u.name AS user_name 
    FROM reports rep 
    LEFT JOIN routes r ON rep.route_id = r.id 
    LEFT JOIN users u ON rep.user_id = u.id 
    ORDER BY rep.created_at DESC 
    LIMIT 20
    ";
    $allReportsResult = $conn->query($allReportsQuery);
    
    if ($allReportsResult) {
        while ($report = $allReportsResult->fetch_assoc()) {
            $reports[] = $report;
        }
    } else {
        error_log("All reports query error: " . $conn->error);
    }
}


// In the route query, add avatar for creator
$routeQuery = "
SELECT r.*, u.name AS creator_name, u.avatar AS creator_avatar 
FROM routes r 
LEFT JOIN users u ON r.created_by = u.id 
ORDER BY r.created_at DESC
";

// In the report queries, add avatar for users
$reportQuery = "
SELECT rep.*, u.name AS user_name, u.avatar AS user_avatar 
FROM reports rep 
LEFT JOIN users u ON rep.user_id = u.id 
WHERE rep.route_id = $routeId 
ORDER BY rep.created_at DESC
";

$allReportsQuery = "
SELECT rep.*, 
CONCAT(r.start_latitude, ',', r.start_longitude, ' → ', r.end_latitude, ',', r.end_longitude) AS route_name,
u.name AS user_name, u.avatar AS user_avatar 
FROM reports rep 
LEFT JOIN routes r ON rep.route_id = r.id 
LEFT JOIN users u ON rep.user_id = u.id 
ORDER BY rep.created_at DESC 
LIMIT 20
";


// Convert PHP data to JSON for JavaScript
$routesJson = json_encode($routes);
$reportsJson = json_encode($reports);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Peta Jalan Aman</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #3498db;
            --secondary-color: #008080;
            --danger-color: #e74c3c;
            --success-color: #2ecc71;
            --warning-color: #f39c12;
            --safe-color: #27ae60;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            display: flex;
            flex-direction: column;
            height: 100vh;
            background-color: #f8f9fa;
        }
        
        header {
            background-color: var(--secondary-color);
            color: white;
            padding: 1rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        .logo {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .logo i {
            font-size: 1.8rem;
            color: var(--primary-color);
        }
        
        .logo h1 {
            font-size: 1.5rem;
            margin: 0;
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: var(--primary-color);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
        }
        
        .main-container {
            display: flex;
            flex: 1;
            overflow: hidden;
        }
        
        .map-container {
            flex: 1;
            position: relative;
        }
        
        #map {
            height: 100%;
            width: 100%;
            z-index: 1;
        }
        
        .sidebar {
            width: 350px;
            background-color: white;
            padding: 0;
            overflow-y: auto;
            box-shadow: -2px 0 5px rgba(0,0,0,0.1);
            display: flex;
            flex-direction: column;
            z-index: 2;
        }
        
        .sidebar-header {
            padding: 1rem;
            background-color: var(--secondary-color);
            color: white;
        }
        
        .search-container {
            padding: 1rem;
            background-color: #f8f9fa;
            border-bottom: 1px solid #eee;
        }
        
        .filter-section {
            padding: 1rem;
            background-color: white;
            border-bottom: 1px solid #eee;
        }
        
        .filter-options {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            margin-bottom: 0.5rem;
        }
        
        .filter-options button {
            padding: 0.4rem 0.8rem;
            border: 1px solid #ddd;
            background-color: white;
            border-radius: 20px;
            cursor: pointer;
            transition: all 0.3s;
            font-size: 0.9rem;
        }
        
        .filter-options button.active {
            background-color: var(--primary-color);
            color: white;
            border-color: var(--primary-color);
        }
        
        .reports-container {
            flex: 1;
            overflow-y: auto;
            padding: 1rem;
        }
        
        .reports-list {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }
        
        .report-card {
            background-color: white;
            border-radius: 8px;
            padding: 1rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            border-left: 4px solid var(--primary-color);
            cursor: pointer;
            transition: transform 0.2s;
        }
        
        .report-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        }
        
        .report-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.5rem;
            align-items: flex-start;
        }
        
        .report-type {
            padding: 0.3rem 0.8rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: bold;
        }
        
        .crime { 
            background-color: #ff7675; 
            color: white; 
            border-left-color: #ff7675;
        }
        .accident { 
            background-color: #fdcb6e; 
            color: black; 
            border-left-color: #fdcb6e;
        }
        .hazard { 
            background-color: #e17055; 
            color: white; 
            border-left-color: #e17055;
        }
        .safe_spot { 
            background-color: #00b894; 
            color: white; 
            border-left-color: #00b894;
        }
        
        .report-date {
            color: #7f8c8d;
            font-size: 0.8rem;
        }
        
        .report-description {
            margin-bottom: 0.5rem;
            color: #2d3436;
            line-height: 1.5;
        }
        
        .report-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .report-user {
            font-size: 0.8rem;
            color: #7f8c8d;
            cursor: pointer;
        }
        
        .report-user:hover {
            color: var(--primary-color);
            text-decoration: underline;
        }
        
        .report-likes {
            display: flex;
            align-items: center;
            gap: 0.3rem;
            color: #7f8c8d;
            font-size: 0.9rem;
        }
        
        .add-route-btn {
            position: absolute;
            bottom: 20px;
            right: 20px;
            z-index: 1000;
            background-color: var(--success-color);
            color: white;
            border: none;
            border-radius: 50%;
            width: 60px;
            height: 60px;
            font-size: 1.5rem;
            cursor: pointer;
            box-shadow: 0 3px 10px rgba(0,0,0,0.2);
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .add-route-btn:hover {
            transform: scale(1.05);
            background-color: #25a589;
        }
        
        .route-info-panel {
            position: absolute;
            top: 80px;
            left: 20px;
            z-index: 1000;
            background: white;
            border-radius: 8px;
            padding: 1rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.2);
            max-width: 300px;
            display: none;
        }
        
        .panel-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 10px;
        }
        
        .panel-close {
            background: none;
            border: none;
            font-size: 1.2rem;
            cursor: pointer;
            color: #7f8c8d;
        }
        
        .route-reports-list {
            max-height: 300px;
            overflow-y: auto;
            margin: 10px 0;
        }
        
        .add-to-route-btn {
            background-color: var(--primary-color);
            color: white;
            border: none;
            border-radius: 4px;
            padding: 0.5rem 1rem;
            cursor: pointer;
            width: 100%;
            margin-top: 10px;
            transition: background-color 0.3s;
            display: <?php echo $userLoggedIn ? 'block' : 'none'; ?>;
        }
        
        .add-to-route-btn:hover {
            background-color: #2980b9;
        }
        
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            z-index: 2000;
            justify-content: center;
            align-items: center;
        }
        
        .modal-content {
            background-color: white;
            padding: 2rem;
            border-radius: 8px;
            width: 90%;
            max-width: 500px;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 4px 20px rgba(0,0,0,0.2);
        }
        
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 1px solid #eee;
        }
        
        .close-btn {
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: #7f8c8d;
        }
        
        .form-group {
            margin-bottom: 1rem;
            position: relative;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: var(--secondary-color);
            font-weight: 500;
        }
        
        .form-group select, 
        .form-group textarea,
        .form-group input {
            width: 100%;
            padding: 0.8rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1rem;
        }
        
        .form-group textarea {
            min-height: 120px;
            resize: vertical;
        }
        
        .submit-btn {
            background-color: var(--primary-color);
            color: white;
            border: none;
            padding: 0.8rem 1.5rem;
            border-radius: 4px;
            cursor: pointer;
            font-size: 1rem;
            transition: background-color 0.3s;
            width: 100%;
        }
        
        .submit-btn:hover {
            background-color: #2980b9;
        }
        
        .submit-btn:disabled {
            background-color: #ccc;
            cursor: not-allowed;
        }
        
        .route-line {
            stroke-width: 5;
            stroke-opacity: 0.7;
        }
        
        .route-line.crime {
            stroke: #ff7675;
        }
        
        .route-line.accident {
            stroke: #fdcb6e;
        }
        
        .route-line.hazard {
            stroke: #e17055;
        }
        
        .route-line.safe_spot {
            stroke: #00b894;
        }
        
        .user-marker {
            border: 2px solid white;
            border-radius: 50%;
            box-shadow: 0 0 10px rgba(0,0,0,0.5);
        }
        
        .route-creator {
            font-size: 0.8rem;
            color: #7f8c8d;
            margin-top: 5px;
            cursor: pointer;
        }
        
        .route-creator:hover {
            color: var(--primary-color);
            text-decoration: underline;
        }
        
        .mode-indicator {
            position: absolute;
            top: 80px;
            right: 20px;
            z-index: 1000;
            background-color: var(--warning-color);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 4px;
            font-weight: bold;
            display: none;
        }
        
        /* Route creation controls */
        .route-creation-controls {
            position: absolute;
            top: 20px;
            right: 20px;
            z-index: 1000;
            background-color: white;
            padding: 1rem;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.2);
            display: none;
            flex-direction: column;
            gap: 10px;
            min-width: 200px;
        }
        
        .route-step {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .route-step-icon {
            width: 24px;
            height: 24px;
            border-radius: 50%;
            background-color: #eee;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: bold;
        }
        
        .route-step.active .route-step-icon {
            background-color: var(--primary-color);
            color: white;
        }
        
        .route-step.completed .route-step-icon {
            background-color: var(--success-color);
            color: white;
        }
        
        .route-control-buttons {
            display: flex;
            flex-direction: column;
            gap: 8px;
            margin-top: 10px;
        }
        
        .route-control-btn {
            padding: 0.5rem;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 5px;
        }
        
        .select-point-btn {
            background-color: var(--primary-color);
            color: white;
        }
        
        .select-point-btn:hover {
            background-color: #2980b9;
        }
        
        .create-route-btn {
            background-color: var(--success-color);
            color: white;
        }
        
        .create-route-btn:hover {
            background-color: #25a589;
        }
        
        .create-route-btn:disabled {
            background-color: #ccc;
            cursor: not-allowed;
        }
        
        .cancel-route-btn {
            background-color: var(--danger-color);
            color: white;
        }
        
        .cancel-route-btn:hover {
            background-color: #c0392b;
        }
        
        /* Responsive styles */
        @media (max-width: 992px) {
            .main-container {
                flex-direction: column;
            }
            
            .sidebar {
                width: 100%;
                height: 40%;
                order: 2;
            }
            
            .map-container {
                height: 60%;
                order: 1;
            }
            
            .route-info-panel {
                top: 10px;
                left: 10px;
                right: 10px;
                max-width: none;
            }
            
            .route-creation-controls {
                top: 10px;
                right: 10px;
                left: 10px;
                max-width: none;
            }
        }
        
        @media (max-width: 576px) {
            .filter-options {
                gap: 0.3rem;
            }
            
            .filter-options button {
                padding: 0.3rem 0.6rem;
                font-size: 0.8rem;
            }
            
            .logo h1 {
                font-size: 1.2rem;
            }
            
            .add-route-btn {
                bottom: 15px;
                right: 15px;
                width: 50px;
                height: 50px;
                font-size: 1.2rem;
            }
            
            .user-info span {
                display: none;
            }
        }
        
        .no-reports {
            text-align: center;
            padding: 2rem;
            color: #7f8c8d;
        }
        
        .no-reports i {
            font-size: 3rem;
            margin-bottom: 1rem;
            color: #ddd;
            display: block;
        }
        
        .route-stats {
            display: flex;
            gap: 1rem;
            margin-bottom: 1rem;
            flex-wrap: wrap;
        }
        
        .stat-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 0.5rem;
            background-color: #f8f9fa;
            border-radius: 8px;
            min-width: 80px;
        }
        
        .stat-value {
            font-size: 1.2rem;
            font-weight: bold;
            color: var(--secondary-color);
        }
        
        .stat-label {
            font-size: 0.8rem;
            color: #7f8c8d;
        }
        
        .login-prompt {
            text-align: center;
            padding: 1rem;
            background-color: #fff3cd;
            border-radius: 4px;
            margin-top: 10px;
            color: #856404;
            display: <?php echo $userLoggedIn ? 'none' : 'block'; ?>;
        }
        
        .creating-route-mode .add-route-btn {
            display: none;
        }
        
        .creating-route-mode .mode-indicator {
            display: block;
        }
        
        .user-avatar-small {
            width: 24px;
            height: 24px;
            border-radius: 50%;
            background-color: var(--primary-color);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            margin-right: 5px;
            vertical-align: middle;
        }
        
        .user-avatar-small img {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            object-fit: cover;
        }
        
        .user-link {
            cursor: pointer;
            display: inline-flex;
            align-items: center;
        }
        
        .user-link:hover {
            color: var(--primary-color);
            text-decoration: underline;
        }
        
        /* Ensure route info panel is properly visible */
        .route-info-panel {
            z-index: 1001;
            max-height: 80vh;
            overflow-y: auto;
        }
        
        /* Make sure the add report button is visible */
        .add-to-route-btn {
            position: relative;
            z-index: 1002;
        }
        
        .user-avatar-large {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background-color: var(--primary-color);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 2rem;
            margin: 0 auto;
        }
        
        .user-avatar-large img {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            object-fit: cover;
        }
        
        /* Location button */
        .location-btn {
            position: absolute;
            top: 80px;
            right: 20px;
            z-index: 1000;
            background-color: white;
            color: var(--secondary-color);
            border: none;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            font-size: 1.2rem;
            cursor: pointer;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .location-btn:hover {
            background-color: var(--primary-color);
            color: white;
            transform: scale(1.05);
        }
        
        /* User location marker */
        .user-location-marker {
            border: 3px solid white;
            border-radius: 50%;
            box-shadow: 0 0 10px rgba(0,0,0,0.5);
            background-color: #4285F4;
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0% {
                transform: scale(1);
                opacity: 1;
            }
            50% {
                transform: scale(1.2);
                opacity: 0.7;
            }
            100% {
                transform: scale(1);
                opacity: 1;
            }
        }
        
    </style>
</head>
<body>
    <header>
        <div class="logo">
            <i class="fas fa-map-marked-alt"></i>
            <h1>Peta Jalan Aman</h1>
        </div>
        <div id="authButtons" style="<?php echo $userLoggedIn ? 'display:none;' : 'display:block;'; ?>">
            <a href="auth/login.php" class="btn btn-outline-light me-2">Masuk</a>
            <a href="auth/register.php" class="btn btn-light" id="registerBtn">Daftar</a>
        </div>
        <div class="user-info" id="userInfo" style="<?php echo $userLoggedIn ? 'display:flex;' : 'display:none;'; ?>">
            <div class="user-avatar" id="userAvatar"><?php echo $userLoggedIn ? strtoupper(substr($userData['name'], 0, 1)) : ''; ?></div>
            <span id="userName"><?php echo $userLoggedIn ? $userData['name'] : ''; ?></span>
            <a href="auth/logout.php" class="btn btn-outline-light btn-sm" id="logoutBtn">Keluar</a>
        </div>
    </header>
    
    <div class="main-container">
        <div class="map-container">
            <div id="map"></div>
            
            <button class="location-btn" id="locationBtn" title="Lokasi Saat Ini">
                <i class="fas fa-location-arrow"></i>
            </button>
            
            <div class="mode-indicator" id="modeIndicator"></div>
            
            <!-- Route Creation Controls -->
            <div class="route-creation-controls" id="routeCreationControls">
                <h5 class="mb-2">Buat Rute Baru</h5>
                
                <div class="route-step" id="step1">
                    <div class="route-step-icon">1</div>
                    <span>Pilih Titik Awal</span>
                </div>
                
                <div class="route-step" id="step2">
                    <div class="route-step-icon">2</div>
                    <span>Pilih Titik Akhir</span>
                </div>
                
                <div class="route-step" id="step3">
                    <div class="route-step-icon">3</div>
                    <span>Buat Rute</span>
                </div>
                
                <div class="route-control-buttons">
                    <button class="route-control-btn select-point-btn" id="selectStartBtn">
                        <i class="fas fa-map-marker-alt"></i> Pilih Titik Awal
                    </button>
                    <button class="route-control-btn select-point-btn" id="selectEndBtn" disabled>
                        <i class="fas fa-map-marker-alt"></i> Pilih Titik Akhir
                    </button>
                    <button class="route-control-btn create-route-btn" id="createRouteBtn" disabled>
                        <i class="fas fa-route"></i> Buat Rute
                    </button>
                    <button class="route-control-btn cancel-route-btn" id="cancelRouteBtn">
                        <i class="fas fa-times"></i> Batalkan
                    </button>
                </div>
            </div>
            
            <div class="route-info-panel" id="routeInfoPanel">
                <div class="panel-header">
                    <h5 id="routeNameTitle">Informasi Rute</h5>
                    <button class="panel-close" id="closeRoutePanel">&times;</button>
                </div>
                <div id="routeCreatorInfo" class="route-creator"></div>
                
                <div class="route-stats" id="routeStats">
                    <div class="stat-item">
                        <span class="stat-value" id="totalReports">0</span>
                        <span class="stat-label">Laporan</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-value" id="crimeCount">0</span>
                        <span class="stat-label">Kejahatan</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-value" id="accidentCount">0</span>
                        <span class="stat-label">Kecelakaan</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-value" id="safeCount">0</span>
                        <span class="stat-label">Aman</span>
                    </div>
                </div>
                
                <div class="route-reports-list" id="routeReportsList">
                    <!-- Route reports will be added here -->
                </div>
                <button class="add-to-route-btn" id="addToRouteBtn">
                    <i class="fas fa-plus me-1"></i> Tambahkan Laporan
                </button>
                <div class="login-prompt" id="routeLoginPrompt">
                    <i class="fas fa-info-circle"></i> Silakan masuk untuk menambahkan laporan
                </div>
            </div>
            
            <button class="add-route-btn" id="addRouteBtn" title="Tambah Rute Baru">
                <i class="fas fa-route"></i>
            </button>
        </div>
        
        <div class="sidebar">
            <div class="sidebar-header">
                <h5 class="mb-0">Laporan Jalan</h5>
            </div>
            
            <div class="search-container">
                <div class="input-group">
                    <input type="text" class="form-control" id="search" placeholder="Cari jalan, tempat, atau gedung...">
                    <button class="btn btn-primary" type="button" id="searchBtn">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </div>
            
            <div class="filter-section">
                <div class="filter-options">
                    <button class="active" data-type="all">Semua</button>
                    <button data-type="crime">Kejahatan</button>
                    <button data-type="accident">Kecelakaan</button>
                    <button data-type="hazard">Bahaya</button>
                    <button data-type="safe_spot">Aman</button>
                </div>
                <small class="text-muted">Klik pada rute di peta untuk melihat detail dan menambahkan laporan</small>
            </div>
            
            <div class="reports-container">
                <div class="reports-list" id="reportsList">
                    <!-- Reports will be loaded here -->
                </div>
            </div>
        </div>
    </div>
    
    <!-- Add Report Modal -->
    <div class="modal" id="reportModal">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="mb-0">Tambah Laporan Baru</h4>
                <button class="close-btn">&times;</button>
            </div>
            <form id="reportForm" action="save_report.php" method="POST">
                <input type="hidden" id="reportRouteId" name="route_id">
                <div class="form-group">
                    <label for="reportType">Jenis Laporan</label>
                    <select id="reportType" name="type" required>
                        <option value="">Pilih Jenis Laporan</option>
                        <option value="crime">Daerah Rawan Kejahatan</option>
                        <option value="accident">Titik Rawan Kecelakaan</option>
                        <option value="hazard">Bahaya di Jalan</option>
                        <option value="safe_spot">Titik Aman</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="reportDescription">Deskripsi</label>
                    <textarea id="reportDescription" name="description" placeholder="Jelaskan secara detail kondisi di lokasi tersebut..." required></textarea>
                </div>
                <button type="submit" class="submit-btn" id="reportSubmitBtn">Kirim Laporan</button>
            </form>
        </div>
    </div>
    
    <!-- Add Route Modal -->
    <div class="modal" id="routeModal">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="mb-0">Buat Rute Baru</h4>
                <button class="close-btn" data-dismiss="route">&times;</button>
            </div>
            <form id="routeForm" action="save_route.php" method="POST">
                <div class="form-group">
                    <label for="routeName">Nama Rute (Opsional)</label>
                    <input type="text" id="routeName" name="name" placeholder="Masukkan nama rute...">
                </div>
                <div class="form-group">
                    <label>Pilih titik awal dan akhir pada peta</label>
                    <div class="alert alert-warning">
                        <small>
                            <i class="fas fa-exclamation-triangle"></i> 
                            <strong>Penting:</strong> Setelah membuat rute, Anda harus menambahkan laporan atau rute akan dihapus.
                        </small>
                    </div>
                </div>
                <div class="form-group">
                    <input type="hidden" id="startLat" name="start_lat">
                    <input type="hidden" id="startLng" name="start_lng">
                    <label>Titik Awal: <span id="startPointInfo">Belum dipilih</span></label>
                </div>
                <div class="form-group">
                    <input type="hidden" id="endLat" name="end_lat">
                    <input type="hidden" id="endLng" name="end_lng">
                    <label>Titik Akhir: <span id="endPointInfo">Belum dipilih</span></label>
                </div>
                <button type="submit" class="submit-btn" id="confirmRouteBtn" disabled>Buat Rute</button>
            </form>
        </div>
    </div>
    
    <!-- User Profile Modal -->
    <div class="modal" id="userProfileModal">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="mb-0">Profil Pengguna</h4>
                <button class="close-btn" data-dismiss="profile">&times;</button>
            </div>
            <div class="modal-body" id="userProfileContent">
                <div class="text-center">
                    <div id="profileAvatar" class="user-avatar-large"></div>
                    <h4 id="profileName" class="mt-3"></h4>
                    <p id="profileUsername" class="text-muted"></p>
                </div>
                <div class="profile-stats mt-4">
                    <div class="row text-center">
                        <div class="col-4">
                            <div class="stat-value" id="profileRoutes">0</div>
                            <div class="stat-label">Rute</div>
                        </div>
                        <div class="col-4">
                            <div class="stat-value" id="profileReports">0</div>
                            <div class="stat-label">Laporan</div>
                        </div>
                        <div class="col-4">
                            <div class="stat-value" id="profileReputation">0</div>
                            <div class="stat-label">Reputasi</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Pass PHP data to JavaScript
        const userLoggedIn = <?php echo $userLoggedIn ? 'true' : 'false'; ?>;
        const userData = <?php echo $userData ? json_encode($userData) : 'null'; ?>;
        const phpRoutes = <?php echo $routesJson ?: '[]'; ?>;
        const phpReports = <?php echo $reportsJson ?: '[]'; ?>;
    </script>
    <script>
        // Initialize map
        const map = L.map('map').setView([-6.2088, 106.8456], 13); // Default to Jakarta
        
        // Add OpenStreetMap tiles
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);
        
        // Global variables
        let currentMode = 'view'; // 'view', 'addRouteStart', 'addRouteEnd'
        let routes = phpRoutes;
        let routeLines = [];
        let selectedRoute = null;
        let currentUser = userData;
        let routeStartPoint = null;
        let routeEndPoint = null;
        let routeStartMarker = null;
        let routeEndMarker = null;
        let routeTempLine = null;
        
        // DOM Elements
        const addRouteBtn = document.getElementById('addRouteBtn');
        const reportModal = document.getElementById('reportModal');
        const routeModal = document.getElementById('routeModal');
        const closeBtns = document.querySelectorAll('.close-btn');
        const reportForm = document.getElementById('reportForm');
        const routeForm = document.getElementById('routeForm');
        const filterButtons = document.querySelectorAll('.filter-options button');
        const reportsList = document.getElementById('reportsList');
        const searchInput = document.getElementById('search');
        const searchBtn = document.getElementById('searchBtn');
        const routeInfoPanel = document.getElementById('routeInfoPanel');
        const routeReportsList = document.getElementById('routeReportsList');
        const addToRouteBtn = document.getElementById('addToRouteBtn');
        const modeIndicator = document.getElementById('modeIndicator');
        const startPointInfo = document.getElementById('startPointInfo');
        const endPointInfo = document.getElementById('endPointInfo');
        const confirmRouteBtn = document.getElementById('confirmRouteBtn');
        const routeCreatorInfo = document.getElementById('routeCreatorInfo');
        const routeNameTitle = document.getElementById('routeNameTitle');
        const totalReports = document.getElementById('totalReports');
        const crimeCount = document.getElementById('crimeCount');
        const accidentCount = document.getElementById('accidentCount');
        const safeCount = document.getElementById('safeCount');
        const closeRoutePanel = document.getElementById('closeRoutePanel');
        const routeLoginPrompt = document.getElementById('routeLoginPrompt');
        
        // New elements for route creation
        const routeCreationControls = document.getElementById('routeCreationControls');
        const selectStartBtn = document.getElementById('selectStartBtn');
        const selectEndBtn = document.getElementById('selectEndBtn');
        const createRouteBtn = document.getElementById('createRouteBtn');
        const cancelRouteBtn = document.getElementById('cancelRouteBtn');
        const step1 = document.getElementById('step1');
        const step2 = document.getElementById('step2');
        const step3 = document.getElementById('step3');
        
        // Initialize the application
        function init() {
            setupEventListeners();
            loadRoutes();
            loadReports();
            tryGeolocation();
        }
        
        // Setup event listeners
        function setupEventListeners() {
            // Add route button
            if (addRouteBtn) {
                addRouteBtn.addEventListener('click', () => {
                    if (!userLoggedIn) {
                        alert('Silakan masuk terlebih dahulu untuk menambahkan rute.');
                        return;
                    }
                    startRouteCreation();
                });
            }
            
            // Route creation controls
            if (selectStartBtn) {
                selectStartBtn.addEventListener('click', () => {
                    setMode('addRouteStart');
                    updateStepIndicator(1);
                });
            }
            
            if (selectEndBtn) {
                selectEndBtn.addEventListener('click', () => {
                    setMode('addRouteEnd');
                    updateStepIndicator(2);
                });
            }
            
            if (createRouteBtn) {
                createRouteBtn.addEventListener('click', () => {
                    showRouteModal();
                });
            }
            
            if (cancelRouteBtn) {
                cancelRouteBtn.addEventListener('click', () => {
                    cancelRouteCreation();
                });
            }
            
            // Close modals
            if (closeBtns) {
                closeBtns.forEach(btn => {
                    btn.addEventListener('click', function() {
                        const modal = this.getAttribute('data-dismiss') === 'route' ? 
                        routeModal : reportModal;
                        if (modal) modal.style.display = 'none';
                        setMode('view');
                        
                        // Clean up route creation markers if needed
                        if (routeStartMarker) {
                            map.removeLayer(routeStartMarker);
                            routeStartMarker = null;
                        }
                        if (routeEndMarker) {
                            map.removeLayer(routeEndMarker);
                            routeEndMarker = null;
                        }
                        if (routeTempLine) {
                            map.removeLayer(routeTempLine);
                            routeTempLine = null;
                        }
                        routeStartPoint = null;
                        routeEndPoint = null;
                    });
                });
            }
            
            // Close route info panel
            if (closeRoutePanel) {
                closeRoutePanel.addEventListener('click', () => {
                    if (routeInfoPanel) routeInfoPanel.style.display = 'none';
                    selectedRoute = null;
                });
            }
            
            // Close modal when clicking outside
            window.addEventListener('click', (e) => {
                if (e.target === reportModal && reportModal) {
                    reportModal.style.display = 'none';
                    setMode('view');
                }
                if (e.target === routeModal && routeModal) {
                    routeModal.style.display = 'none';
                    setMode('view');
                    
                    // Clean up route creation markers
                    if (routeStartMarker) {
                        map.removeLayer(routeStartMarker);
                        routeStartMarker = null;
                    }
                    if (routeEndMarker) {
                        map.removeLayer(routeEndMarker);
                        routeEndMarker = null;
                    }
                    if (routeTempLine) {
                        map.removeLayer(routeTempLine);
                        routeTempLine = null;
                    }
                    routeStartPoint = null;
                    routeEndPoint = null;
                }
            });
            
            // Handle map clicks for route creation
            map.on('click', (e) => {
                if (currentMode === 'addRouteStart') {
                    setStartPoint(e.latlng);
                } else if (currentMode === 'addRouteEnd') {
                    setEndPoint(e.latlng);
                }
            });
            
            // Filter reports
            if (filterButtons) {
                filterButtons.forEach(button => {
                    button.addEventListener('click', () => {
                        // Remove active class from all buttons
                        filterButtons.forEach(btn => btn.classList.remove('active'));
                        
                        // Add active class to clicked button
                        button.classList.add('active');
                        
                        const filterType = button.getAttribute('data-type');
                        filterReports(filterType);
                    });
                });
            }
            
            // Handle form submission
            if (reportForm) {
                reportForm.addEventListener('submit', (e) => {
                    // Let the form submit normally (PHP will handle it)
                });
            }
            
            if (routeForm) {
                routeForm.addEventListener('submit', (e) => {
                    // Let the form submit normally (PHP will handle it)
                });
            }
            
            // Search functionality
            if (searchBtn) {
                searchBtn.addEventListener('click', searchLocation);
            }
            
            if (searchInput) {
                searchInput.addEventListener('keypress', (e) => {
                    if (e.key === 'Enter') {
                        searchLocation();
                    }
                });
            }
            
            // Add report to route
            if (addToRouteBtn) {
                addToRouteBtn.addEventListener('click', () => {
                    if (!userLoggedIn) {
                        alert('Silakan masuk terlebih dahulu untuk menambahkan laporan.');
                        return;
                    }
                    
                    if (selectedRoute) {
                        if (document.getElementById('reportRouteId')) {
                            document.getElementById('reportRouteId').value = selectedRoute.id;
                        }
                        if (reportModal) reportModal.style.display = 'flex';
                    } else {
                        alert('Silakan pilih rute terlebih dahulu dengan mengklik pada garis rute di peta.');
                    }
                });
            }
        }
        
        // Load routes from PHP data
        // Load routes from PHP data with actual road paths
        function loadRoutes() {
            try {
                // Clear existing route lines
                routeLines.forEach(line => map.removeLayer(line));
                routeLines = [];
                
                // Draw routes on map
                for (const route of routes) {
                    try {
                        let latLngs = [];
                        
                        // Check if we have a polyline stored in the database
                        if (route.polyline) {
                            try {
                                // Parse the polyline from database
                                const polylineData = JSON.parse(route.polyline);
                                if (polylineData && polylineData.coordinates) {
                                    // Convert GeoJSON format [lng, lat] to Leaflet format [lat, lng]
                                    latLngs = polylineData.coordinates.map(coord => [coord[1], coord[0]]);
                                }
                            } catch (e) {
                                console.error('Error parsing polyline:', e);
                                // Fallback to straight line
                                latLngs = [
                                [route.start_lat, route.start_lng],
                                [route.end_lat, route.end_lng]
                                ];
                            }
                        } else {
                            // Fallback to straight line if no polyline
                            latLngs = [
                            [route.start_lat, route.start_lng],
                            [route.end_lat, route.end_lng]
                            ];
                        }
                        
                        // Create the polyline
                        const line = L.polyline(latLngs, {
                            color: getColorForReportType(route.reports && route.reports[0] ? route.reports[0].type : 'hazard'),
                            weight: 6,
                            opacity: 0.7,
                            className: 'route-line'
                        }).addTo(map);
                        
                        // Store reference to the line and route
                        line.routeId = route.id;
                        routeLines.push(line);
                        
                        // Add click event to show route reports
                        line.on('click', function(e) {
                            showRouteReports(route.id);
                        });
                        
                        // Add markers for start and end points
                        L.marker([route.start_lat, route.start_lng]).addTo(map)
                        .bindPopup('Titik Awal: ' + (route.name || 'Rute #' + route.id));
                        
                        L.marker([route.end_lat, route.end_lng]).addTo(map)
                        .bindPopup('Titik Akhir: ' + (route.name || 'Rute #' + route.id));
                        
                    } catch (error) {
                        console.error('Error creating route:', error);
                    }
                }
                
            } catch (error) {
                console.error('Error loading routes:', error);
            }
        }
        
        // After the route is successfully saved, automatically open the report modal
        function saveRoute() {
            const formData = new FormData(routeForm);
            
            fetch('save_route.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Rute berhasil dibuat! Silakan tambahkan laporan untuk rute ini.');
                    routeModal.style.display = 'none';
                    
                    // Set the route ID for the report
                    document.getElementById('reportRouteId').value = data.route_id;
                    
                    // Automatically open the report modal
                    reportModal.style.display = 'flex';
                    
                    // Reset and reload routes
                    cancelRouteCreation();
                    // Reload routes from server after a short delay
                    setTimeout(() => {
                        fetchRoutesFromServer();
                    }, 1000);
                } else {
                    alert('Gagal membuat rute: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Terjadi kesalahan saat menyimpan rute');
            });
        }
        
        // Fetch routes from server
        function fetchRoutesFromServer() {
            fetch('get_routes.php')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    routes = data.routes;
                    loadRoutes();
                } else {
                    console.error('Failed to fetch routes:', data.message);
                }
            })
            .catch(error => {
                console.error('Error fetching routes:', error);
            });
        }
        
        // Save report via AJAX
        function saveReport() {
            const formData = new FormData(reportForm);
            
            fetch('save_report.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Laporan berhasil ditambahkan!');
                    reportModal.style.display = 'none';
                    
                    // Reset the form
                    reportForm.reset();
                    
                    // Reload routes to show the new report
                    fetchRoutesFromServer();
                    loadReports();
                } else {
                    alert('Gagal menambahkan laporan: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Terjadi kesalahan saat menyimpan laporan');
            });
        }
        
        // Get color for report type
        function getColorForReportType(type) {
            switch(type) {
                case 'crime': return '#ff7675';
                case 'accident': return '#fdcb6e';
                case 'hazard': return '#e17055';
                case 'safe_spot': return '#00b894';
                default: return '#3498db';
            }
        }
        
        // Show reports for a specific route
        function showRouteReports(routeId) {
            const route = routes.find(r => r.id == routeId);
            if (!route) return;
            
            selectedRoute = route;
            
            // Update route info panel
            routeReportsList.innerHTML = '';
            routeNameTitle.textContent = route.name || 'Rute #' + route.id;
            
            // Show creator with avatar
            const creatorAvatar = route.creator_avatar ? 
            `<img src="${route.creator_avatar}" alt="${route.creator_name}" class="user-avatar-small">` :
            `<div class="user-avatar-small">${route.creator_name ? route.creator_name.charAt(0).toUpperCase() : 'U'}</div>`;
            
            routeCreatorInfo.innerHTML = `Dibuat oleh: <span class="user-link" data-user-id="${route.created_by}">${creatorAvatar} ${route.creator_name || 'Unknown'}</span>`;
            
            // Update statistics
            updateRouteStats(route);
            
            if (!route.reports || route.reports.length === 0) {
                routeReportsList.innerHTML = '<div class="text-center py-3 text-muted">Belum ada laporan untuk rute ini</div>';
            } else {
                route.reports.forEach(report => {
                    const userAvatar = report.user_avatar ? 
                    `<img src="${report.user_avatar}" alt="${report.user_name}" class="user-avatar-small">` :
                    `<div class="user-avatar-small">${report.user_name ? report.user_name.charAt(0).toUpperCase() : 'U'}</div>`;
                    
                    const reportItem = document.createElement('div');
                    reportItem.className = 'report-card';
                    reportItem.innerHTML = `
                <div class="report-header">
                    <span class="report-type ${report.type}">${getTypeLabel(report.type)}</span>
                    <span class="report-date">${formatDate(report.created_at)}</span>
                </div>
                <p class="report-description">${report.description}</p>
                <div class="report-footer">
                    <div class="report-user" data-user-id="${report.user_id}">
                        <small>Oleh: ${userAvatar} ${report.user_name || 'Unknown'}</small>
                    </div>
                </div>
            `;
                    
                    routeReportsList.appendChild(reportItem);
                });
            }
            
            // Add event listeners to user links
            document.querySelectorAll('.user-link, .report-user').forEach(element => {
                element.addEventListener('click', function(e) {
                    e.stopPropagation();
                    const userId = this.getAttribute('data-user-id');
                    if (userId) {
                        showUserProfile(userId);
                    }
                });
            });
            
            // Show the panel with better positioning
            routeInfoPanel.style.display = 'block';
            
            // Ensure the "Tambah Laporan" button is visible
            routeInfoPanel.style.zIndex = '1001';
            routeInfoPanel.style.maxHeight = '80vh';
            routeInfoPanel.style.overflowY = 'auto';
            
            // Position the panel near the route
            const midLat = (parseFloat(route.start_lat) + parseFloat(route.end_lat)) / 2;
            const midLng = (parseFloat(route.start_lng) + parseFloat(route.end_lng)) / 2;
            map.setView([midLat, midLng], 13);
        }
        
        // Update route statistics
        function updateRouteStats(route) {
            const reports = route.reports || [];
            totalReports.textContent = reports.length;
            
            // Count reports by type
            const crimeReports = reports.filter(r => r.type === 'crime').length;
            const accidentReports = reports.filter(r => r.type === 'accident').length;
            const hazardReports = reports.filter(r => r.type === 'hazard').length;
            const safeReports = reports.filter(r => r.type === 'safe_spot').length;
            
            crimeCount.textContent = crimeReports;
            accidentCount.textContent = accidentReports;
            
            // Add hazard count to the UI
            if (!document.getElementById('hazardCount')) {
                // Add hazard stat item if it doesn't exist
                const hazardStat = document.createElement('div');
                hazardStat.className = 'stat-item';
                hazardStat.innerHTML = `
            <span class="stat-value" id="hazardCount">0</span>
            <span class="stat-label">Bahaya</span>
        `;
                routeStats.insertBefore(hazardStat, safeCount.parentElement.nextSibling);
            }
            
            document.getElementById('hazardCount').textContent = hazardReports;
            safeCount.textContent = safeReports;
        }
        
        // Start the route creation process
        function startRouteCreation() {
            routeCreationControls.style.display = 'flex';
            setMode('view');
            updateStepIndicator(0);
            
            // Reset button states
            selectStartBtn.disabled = false;
            selectEndBtn.disabled = true;
            createRouteBtn.disabled = true;
            
            // Hide the add route button
            addRouteBtn.style.display = 'none';
        }
        
        // Update step indicator
        function updateStepIndicator(step) {
            // Reset all steps
            step1.classList.remove('active', 'completed');
            step2.classList.remove('active', 'completed');
            step3.classList.remove('active', 'completed');
            
            // Update based on current step
            if (step >= 1) {
                step1.classList.add('completed');
            }
            
            if (step >= 2) {
                step2.classList.add('completed');
            }
            
            if (step === 1) {
                step1.classList.add('active');
            } else if (step === 2) {
                step2.classList.add('active');
            } else if (step === 3) {
                step3.classList.add('active');
            }
        }
        
        // Set the start point for route creation
        function setStartPoint(latlng) {
            routeStartPoint = latlng;
            
            // Remove existing start marker
            if (routeStartMarker) {
                map.removeLayer(routeStartMarker);
                routeStartMarker = null;
            }
            
            // Add new start marker
            routeStartMarker = L.marker(latlng, {
                icon: L.divIcon({
                    className: 'user-marker',
                    html: '<i class="fas fa-play-circle" style="color: #2ecc71; font-size: 24px;"></i>',
                    iconSize: [24, 24]
                })
            }).addTo(map).bindPopup('Titik Awal').openPopup();
            
            // Enable next step
            selectEndBtn.disabled = false;
            updateStepIndicator(1);
            setMode('view');
            
            // If we already have an end point, draw the line
            if (routeEndPoint) {
                drawTempRouteLine();
                createRouteBtn.disabled = false;
            }
        }
        
        // Set the end point for route creation
        function setEndPoint(latlng) {
            routeEndPoint = latlng;
            
            // Remove existing end marker
            if (routeEndMarker) {
                map.removeLayer(routeEndMarker);
                routeEndMarker = null;
            }
            
            // Add new end marker
            routeEndMarker = L.marker(latlng, {
                icon: L.divIcon({
                    className: 'user-marker',
                    html: '<i class="fas fa-stop-circle" style="color: #e74c3c; font-size: 24px;"></i>',
                    iconSize: [24, 24]
                })
            }).addTo(map).bindPopup('Titik Akhir').openPopup();
            
            // Draw the temporary route line
            drawTempRouteLine();
            
            // Enable create route button
            createRouteBtn.disabled = false;
            updateStepIndicator(2);
            setMode('view');
        }
        
        // Draw temporary route line
        function drawTempRouteLine() {
            if (routeStartPoint && routeEndPoint) {
                // Remove existing temp line
                if (routeTempLine) {
                    map.removeLayer(routeTempLine);
                    routeTempLine = null;
                }
                
                // Get route from OSRM (Open Source Routing Machine)
                const startLng = routeStartPoint.lng;
                const startLat = routeStartPoint.lat;
                const endLng = routeEndPoint.lng;
                const endLat = routeEndPoint.lat;
                
                const osrmUrl = `https://router.project-osrm.org/route/v1/driving/${startLng},${startLat};${endLng},${endLat}?overview=full&geometries=geojson`;
                
                fetch(osrmUrl)
                .then(response => response.json())
                .then(data => {
                    if (data.code === 'Ok' && data.routes.length > 0) {
                        const geometry = data.routes[0].geometry;
                        const coordinates = geometry.coordinates.map(coord => [coord[1], coord[0]]);
                        
                        routeTempLine = L.polyline(coordinates, {
                            color: '#3498db',
                            weight: 4,
                            opacity: 0.7,
                            dashArray: '10, 10'
                        }).addTo(map);
                    } else {
                        // Fallback to straight line if OSRM fails
                        drawStraightLine();
                    }
                })
                .catch(error => {
                    console.error('Error fetching OSRM route:', error);
                    // Fallback to straight line
                    drawStraightLine();
                });
            }
        }
        
        // Fallback: Draw straight line
        function drawStraightLine() {
            const latLngs = [
            [routeStartPoint.lat, routeStartPoint.lng],
            [routeEndPoint.lat, routeEndPoint.lng]
            ];
            
            routeTempLine = L.polyline(latLngs, {
                color: '#3498db',
                weight: 4,
                opacity: 0.7,
                dashArray: '10, 10'
            }).addTo(map);
        }
        
        // Show route modal for final confirmation
        function showRouteModal() {
            if (!routeStartPoint || !routeEndPoint) {
                alert('Silakan pilih titik awal dan titik akhir terlebih dahulu.');
                return;
            }
            
            // Update modal with coordinates
            startPointInfo.textContent = `${routeStartPoint.lat.toFixed(6)}, ${routeStartPoint.lng.toFixed(6)}`;
            endPointInfo.textContent = `${routeEndPoint.lat.toFixed(6)}, ${routeEndPoint.lng.toFixed(6)}`;
            
            // Set hidden form values
            document.getElementById('startLat').value = routeStartPoint.lat;
            document.getElementById('startLng').value = routeStartPoint.lng;
            document.getElementById('endLat').value = routeEndPoint.lat;
            document.getElementById('endLng').value = routeEndPoint.lng;
            
            // Enable confirm button
            confirmRouteBtn.disabled = false;
            
            // Show modal
            routeModal.style.display = 'flex';
            updateStepIndicator(3);
        }
        
        // Cancel route creation
        function cancelRouteCreation() {
            // Clean up markers
            if (routeStartMarker) {
                map.removeLayer(routeStartMarker);
                routeStartMarker = null;
            }
            if (routeEndMarker) {
                map.removeLayer(routeEndMarker);
                routeEndMarker = null;
            }
            if (routeTempLine) {
                map.removeLayer(routeTempLine);
                routeTempLine = null;
            }
            
            // Reset variables
            routeStartPoint = null;
            routeEndPoint = null;
            
            // Reset button states
            selectStartBtn.disabled = false;
            selectEndBtn.disabled = true;
            createRouteBtn.disabled = true;
            
            // Hide controls
            routeCreationControls.style.display = 'none';
            
            // Show add route button
            addRouteBtn.style.display = 'flex';
            
            // Reset mode
            setMode('view');
            
            // Reset step indicators
            step1.classList.remove('active', 'completed');
            step2.classList.remove('active', 'completed');
            step3.classList.remove('active', 'completed');
        }
        
        // Set the current mode
        function setMode(mode) {
            currentMode = mode;
            
            // Update mode indicator
            switch(mode) {
                case 'addRouteStart':
                modeIndicator.textContent = 'Mode: Pilih Titik Awal - Klik pada peta';
                modeIndicator.style.display = 'block';
                modeIndicator.style.backgroundColor = '#2ecc71';
                document.body.classList.add('creating-route-mode');
                break;
                case 'addRouteEnd':
                modeIndicator.textContent = 'Mode: Pilih Titik Akhir - Klik pada peta';
                modeIndicator.style.display = 'block';
                modeIndicator.style.backgroundColor = '#2ecc71';
                document.body.classList.add('creating-route-mode');
                break;
                default:
                modeIndicator.style.display = 'none';
                document.body.classList.remove('creating-route-mode');
            }
        }
        
        // Search location
        function searchLocation() {
            const query = searchInput.value.trim();
            if (!query) return;
            
            // In a real implementation, you would use a geocoding service here
            // For demo purposes, we'll just show an alert
            alert("Fitur pencarian lengkap akan diimplementasi dengan layanan geocoding. Pencarian untuk: " + query);
            
            // Clear search results
            searchInput.value = '';
        }
        
        // Filter reports
        function filterReports(type) {
            loadReports(type);
        }
        
        // Load reports (from all routes)
        function loadReports(filterType = 'all') {
            // Clear current list
            reportsList.innerHTML = '';
            
            let filteredReports = phpReports;
            
            // Apply filter
            if (filterType !== 'all') {
                filteredReports = filteredReports.filter(report => report.type === filterType);
            }
            
            // Display reports
            if (filteredReports.length === 0) {
                reportsList.innerHTML = `
                    <div class="no-reports">
                        <i class="fas fa-inbox"></i>
                        <p>Tidak ada laporan yang ditemukan</p>
                    </div>
                `;
            } else {
                filteredReports.forEach(report => {
                    addReportToDOM(report);
                });
            }
        }
        
        // Add report to DOM
        function addReportToDOM(report) {
            const reportCard = document.createElement('div');
            reportCard.className = 'report-card';
            reportCard.innerHTML = `
                <div class="report-header">
                    <span class="report-type ${report.type}">${getTypeLabel(report.type)}</span>
                    <span class="report-date">${formatDate(report.created_at)}</span>
                </div>
                <p class="report-description">${report.description}</p>
                <div class="report-footer">
                    <div class="report-user">
                        <small>Oleh: ${report.user_name || 'Unknown'} • ${report.route_name || 'Rute #' + report.route_id}</small>
                    </div>
                </div>
            `;
            
            // Add click event to focus on the route
            reportCard.addEventListener('click', () => {
                showRouteReports(report.route_id);
            });
            
            reportsList.appendChild(reportCard);
        }
        
        // Get Indonesian label for report type
        function getTypeLabel(type) {
            switch(type) {
                case 'crime': return 'Kejahatan';
                case 'accident': return 'Kecelakaan';
                case 'hazard': return 'Bahaya';
                case 'safe_spot': return 'Aman';
                default: return 'Lainnya';
            }
        }
        
        // Format date
        function formatDate(dateString) {
            if (!dateString) return 'Tanggal tidak tersedia';
            
            const date = new Date(dateString);
            return date.toLocaleDateString('id-ID', {
                day: 'numeric',
                month: 'short',
                year: 'numeric'
            });
        }
        
        
        let userLocationMarker = null;
        let userLocationCircle = null;
        let watchId = null;
        
        // Try to get user's location
        function tryGeolocation() {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(
                (position) => {
                    const userLat = position.coords.latitude;
                    const userLng = position.coords.longitude;
                    
                    // Set map view to user's location
                    map.setView([userLat, userLng], 14);
                    
                    // Add user marker
                    L.marker([userLat, userLng], {
                        className: 'user-marker'
                    })
                    .addTo(map)
                    .bindPopup('Lokasi Anda Saat Ini')
                    .openPopup();
                },
                (error) => {
                    console.log('Geolocation error:', error);
                },
                {
                    enableHighAccuracy: true,
                    timeout: 5000,
                    maximumAge: 0
                }
                );
            }
        }
        
        
        // Update the user avatar in the navbar
        function updateUserAvatar() {
            if (userLoggedIn && userData && userData.avatar) {
                const userAvatar = document.getElementById('userAvatar');
                userAvatar.innerHTML = `<img src="uploads/${userData.avatar}" alt="${userData.name}" style="width: 100%; height: 100%; border-radius: 50%;">`;
            }
        }
        
        
        function showUserProfile(userId) {
            // In a real implementation, you would fetch user data from the server
            // For now, we'll just show a simple alert
            // alert(`Fitur profil pengguna akan menampilkan informasi untuk user ID: ${userId}`);
            
            // Example of what you might do:
            fetch(`get_user_profile.php?id=${userId}`)
            .then(response => response.json())
            .then(user => {
                // Show user profile modal
                showProfileModal(user);
            });
        }
        
        
        // Call this function when the page loads
        updateUserAvatar();
        
        
        // Update the form submission handlers
        if (reportForm) {
            reportForm.addEventListener('submit', (e) => {
                e.preventDefault();
                saveReport();
            });
        }
        
        if (routeForm) {
            routeForm.addEventListener('submit', (e) => {
                e.preventDefault();
                saveRoute();
            });
        }
        
        // Function to show user profile modal
        function showProfileModal(user) {
            const modal = document.getElementById('userProfileModal');
            const content = document.getElementById('userProfileContent');
            
            // Set user data
            const avatarElement = document.getElementById('profileAvatar');
            if (user.avatar) {
                avatarElement.innerHTML = `<img src="${user.avatar}" alt="${user.name}">`;
            } else {
                avatarElement.innerHTML = user.name ? user.name.charAt(0).toUpperCase() : 'U';
            }
            
            document.getElementById('profileName').textContent = user.name || 'Unknown';
            document.getElementById('profileUsername').textContent = `@${user.username || 'user'}`;
            document.getElementById('profileRoutes').textContent = user.route_count || '0';
            document.getElementById('profileReports').textContent = user.report_count || '0';
            document.getElementById('profileReputation').textContent = user.reputation_score || '0';
            
            modal.style.display = 'flex';
        }
        
        // Close profile modal
        document.querySelector('[data-dismiss="profile"]').addEventListener('click', () => {
            document.getElementById('userProfileModal').style.display = 'none';
        });
        
        
        
        // Initialize the app
        init();
    </script>
</body>
</html>