<?php
session_start();
$userLoggedIn = isset($_SESSION['user_id']);
$userData = $userLoggedIn ? [
    'id' => $_SESSION['user_id'],
    'name' => $_SESSION['name'],
    'username' => $_SESSION['username'],
    'role' => $_SESSION['role'],
    'avatar' => $_SESSION['avatar']
] : null;
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
            --secondary-color: #2c3e50;
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
        }
    </style>
</head>
<body>
    <header>
        <div class="logo">
            <i class="fas fa-map-marked-alt"></i>
            <h1>Peta Jalan Aman</h1>
        </div>
        <div id="authButtons">
            <a href="auth/login.php" class="btn btn-outline-light me-2">Masuk</a>
            <a href="auth/register.php" class="btn btn-light" id="registerBtn">Daftar</a>
        </div>
        <div class="user-info" id="userInfo" style="display: none;">
            <div class="user-avatar" id="userAvatar"></div>
            <span id="userName"></span>
            <a href="auth/logout.php" class="btn btn-outline-light btn-sm" id="logoutBtn">Keluar</a>
        </div>
    </header>
    
    <div class="main-container">
        <div class="map-container">
            <div id="map"></div>
            
            <div class="mode-indicator" id="modeIndicator"></div>
            
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
                <div class="login-prompt" id="routeLoginPrompt" style="display: none;">
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
            <form id="reportForm">
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
            <form id="routeForm">
                <div class="form-group">
                    <label for="routeName">Nama Rute (Opsional)</label>
                    <input type="text" id="routeName" name="name" placeholder="Masukkan nama rute...">
                </div>
                <div class="form-group">
                    <label>Pilih titik awal dan akhir pada peta</label>
                    <div class="alert alert-info">
                        <small>
                            <i class="fas fa-info-circle"></i> 
                            Klik pada peta untuk memilih titik awal, kemudian klik lagi untuk memilih titik akhir.
                            Setelah membuat rute, Anda harus menambahkan laporan atau rute akan dihapus.
                        </small>
                    </div>
                </div>
                <div class="form-group">
                    <label>Titik Awal: <span id="startPointInfo">Belum dipilih</span></label>
                </div>
                <div class="form-group">
                    <label>Titik Akhir: <span id="endPointInfo">Belum dipilih</span></label>
                </div>
                <button type="button" id="confirmRouteBtn" class="submit-btn" disabled>Buat Rute</button>
            </form>
        </div>
    </div>

    <!-- Login Modal -->
    <div class="modal" id="loginModal">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="mb-0">Masuk ke Akun</h4>
                <button class="close-btn">&times;</button>
            </div>
            <form id="loginForm">
                <div class="form-group">
                    <label for="loginEmail">Email</label>
                    <input type="email" id="loginEmail" name="email" required>
                </div>
                <div class="form-group">
                    <label for="loginPassword">Password</label>
                    <input type="password" id="loginPassword" name="password" required>
                </div>
                <button type="submit" class="submit-btn">Masuk</button>
            </form>
        </div>
    </div>

    <!-- Register Modal -->
    <div class="modal" id="registerModal">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="mb-0">Daftar Akun Baru</h4>
                <button class="close-btn">&times;</button>
            </div>
            <form id="registerForm">
                <div class="form-group">
                    <label for="registerName">Nama Lengkap</label>
                    <input type="text" id="registerName" name="name" required>
                </div>
                <div class="form-group">
                    <label for="registerEmail">Email</label>
                    <input type="email" id="registerEmail" name="email" required>
                </div>
                <div class="form-group">
                    <label for="registerPassword">Password</label>
                    <input type="password" id="registerPassword" name="password" required>
                </div>
                <div class="form-group">
                    <label for="registerConfirmPassword">Konfirmasi Password</label>
                    <input type="password" id="registerConfirmPassword" name="confirmPassword" required>
                </div>
                <button type="submit" class="submit-btn">Daftar</button>
            </form>
        </div>
    </div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
// Pass PHP data to JavaScript
const userLoggedIn = <?php echo $userLoggedIn ? 'true' : 'false'; ?>;
const userData = <?php echo $userData ? json_encode($userData) : 'null'; ?>;
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
        let routes = [];
        let routeLines = [];
        let selectedRoute = null;
        let currentUser = null; // Will be set after login
        let routeStartPoint = null;
        let routeEndPoint = null;
        let routeStartMarker = null;
        let routeEndMarker = null;
        
        // API endpoints (replace with your actual backend endpoints)
        const API_BASE = 'https://your-api-domain.com/api';
        const ENDPOINTS = {
            LOGIN: `${API_BASE}/auth/login`,
            REGISTER: `${API_BASE}/auth/register`,
            LOGOUT: `${API_BASE}/auth/logout`,
            ROUTES: `${API_BASE}/routes`,
            REPORTS: `${API_BASE}/reports`,
            USERS: `${API_BASE}/users`
        };
        
        // DOM Elements
        const addRouteBtn = document.getElementById('addRouteBtn');
        const reportModal = document.getElementById('reportModal');
        const routeModal = document.getElementById('routeModal');
        const loginModal = document.getElementById('loginModal');
        const registerModal = document.getElementById('registerModal');
        const closeBtns = document.querySelectorAll('.close-btn');
        const reportForm = document.getElementById('reportForm');
        const routeForm = document.getElementById('routeForm');
        const loginForm = document.getElementById('loginForm');
        const registerForm = document.getElementById('registerForm');
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
        const authButtons = document.getElementById('authButtons');
        const userInfo = document.getElementById('userInfo');
        const userName = document.getElementById('userName');
        const userAvatar = document.getElementById('userAvatar');
        const logoutBtn = document.getElementById('logoutBtn');
        const loginBtn = document.getElementById('loginBtn');
        const registerBtn = document.getElementById('registerBtn');
        const routeLoginPrompt = document.getElementById('routeLoginPrompt');
        const reportSubmitBtn = document.getElementById('reportSubmitBtn');
        
        // Initialize the application
        function init() {
            checkAuthStatus();
            setupEventListeners();
            tryGeolocation();
        }
        
        // Check if user is authenticated
        function checkAuthStatus() {
    // Check if we have PHP session data
    if (userLoggedIn && userData) {
        currentUser = userData;
        updateUIForAuthenticatedUser();
        return;
    }
    
    // Fallback to localStorage check
    const token = localStorage.getItem('authToken');
    const storedUserData = localStorage.getItem('userData');
    
    if (token && storedUserData) {
        try {
            currentUser = JSON.parse(storedUserData);
            updateUIForAuthenticatedUser();
        } catch (e) {
            console.error('Error parsing user data:', e);
            localStorage.removeItem('authToken');
            localStorage.removeItem('userData');
        }
    }
    
    loadRoutes();
}
        
        // Update UI for authenticated user
        function updateUIForAuthenticatedUser() {
            authButtons.style.display = 'none';
            userInfo.style.display = 'flex';
            userName.textContent = currentUser.name;
            userAvatar.textContent = currentUser.name.charAt(0).toUpperCase();
        }
        
        // Update UI for logged out user
        function updateUIForLoggedOutUser() {
            authButtons.style.display = 'block';
            userInfo.style.display = 'none';
            currentUser = null;
        }
        
        // API call function
        async function apiCall(url, options = {}) {
            try {
                const token = localStorage.getItem('authToken');
                const headers = {
                    'Content-Type': 'application/json',
                    ...options.headers
                };
                
                if (token) {
                    headers['Authorization'] = `Bearer ${token}`;
                }
                
                const response = await fetch(url, {
                    ...options,
                    headers
                });
                
                if (response.status === 401) {
                    // Token expired or invalid
                    localStorage.removeItem('authToken');
                    localStorage.removeItem('userData');
                    updateUIForLoggedOutUser();
                    throw new Error('Authentication failed');
                }
                
                const data = await response.json();
                
                if (!response.ok) {
                    throw new Error(data.message || 'API request failed');
                }
                
                return data;
            } catch (error) {
                console.error('API call error:', error);
                throw error;
            }
        }
        
        // Get route geometry from OSRM (Open Source Routing Machine)
        async function getRouteGeometry(startLat, startLng, endLat, endLng) {
            try {
                const url = `https://router.project-osrm.org/route/v1/driving/${startLng},${startLat};${endLng},${endLat}?overview=full&geometries=geojson`;
                const response = await fetch(url);
                const data = await response.json();
                
                if (data.code === 'Ok' && data.routes.length > 0) {
                    return data.routes[0].geometry;
                }
                throw new Error('No route found');
            } catch (error) {
                console.error('Error fetching route:', error);
                // Fallback to straight line if routing service fails
                return {
                    type: 'LineString',
                    coordinates: [
                        [startLng, startLat],
                        [endLng, endLat]
                    ]
                };
            }
        }
        
        // Load routes from backend
        async function loadRoutes() {
            try {
                // In a real app, fetch from your backend API
                const data = await apiCall(ENDPOINTS.ROUTES);
                routes = data.routes || [];
                
                // For demo purposes, if API fails, use sample data
                if (routes.length === 0) {
                    routes = [
                        {
                            id: 1,
                            name: "Rute Utama",
                            start_lat: -6.2088,
                            start_lng: 106.8456,
                            end_lat: -6.2000,
                            end_lng: 106.8500,
                            creator: { id: 1, name: "John Doe" },
                            created_at: '2023-10-15T14:30:00',
                            reports: [
                                {
                                    id: 1,
                                    type: 'crime',
                                    description: 'Banyak pencopet berkeliaran di sekitar halte bus ini, terutama pada jam pulang kerja.',
                                    createdAt: '2023-10-15T14:30:00',
                                    likes: 12,
                                    dislikes: 2,
                                    user: { id: 2, name: "Jane Smith" }
                                },
                                {
                                    id: 2,
                                    type: 'hazard',
                                    description: 'Lubang besar di jalan yang belum diperbaiki selama berminggu-minggu.',
                                    createdAt: '2023-10-13T16:45:00',
                                    likes: 15,
                                    dislikes: 0,
                                    user: { id: 3, name: "Bob Johnson" }
                                }
                            ]
                        },
                        {
                            id: 2,
                            name: "Rute Alternatif",
                            start_lat: -6.1950,
                            start_lng: 106.8400,
                            end_lat: -6.2100,
                            end_lng: 106.8350,
                            creator: { id: 2, name: "Jane Smith" },
                            created_at: '2023-10-14T09:15:00',
                            reports: [
                                {
                                    id: 3,
                                    type: 'accident',
                                    description: 'Tikungan tajam ini sudah menyebabkan beberapa kecelakaan, terutama saat hujan.',
                                    createdAt: '2023-10-14T09:15:00',
                                    likes: 8,
                                    dislikes: 1,
                                    user: { id: 1, name: "John Doe" }
                                },
                                {
                                    id: 4,
                                    type: 'safe_spot',
                                    description: 'Pos polisi yang selalu aktif 24 jam, tempat aman untuk meminta bantuan.',
                                    createdAt: '2023-10-12T11:20:00',
                                    likes: 20,
                                    dislikes: 1,
                                    user: { id: 4, name: "Alice Brown" }
                                }
                            ]
                        }
                    ];
                }
                
                // Clear existing route lines
                routeLines.forEach(line => map.removeLayer(line));
                routeLines = [];
                
                // Draw routes on map
                for (const route of routes) {
                    try {
                        // Get the route geometry from OSRM
                        const geometry = await getRouteGeometry(
                            route.start_lat, 
                            route.start_lng, 
                            route.end_lat, 
                            route.end_lng
                        );
                        
                        // Convert GeoJSON coordinates to LatLng pairs
                        const latLngs = geometry.coordinates.map(coord => L.latLng(coord[1], coord[0]));
                        
                        // Create the polyline with the actual route path
                        const line = L.polyline(latLngs, {
                            color: getColorForReportType(route.reports[0]?.type || 'hazard'),
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
                
                // Load all reports
                loadReports();
            } catch (error) {
                console.error('Error loading routes:', error);
                alert('Gagal memuat data rute. Silakan coba lagi nanti.');
            }
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
            const route = routes.find(r => r.id === routeId);
            if (!route) return;
            
            selectedRoute = route;
            
            // Update route info panel
            routeReportsList.innerHTML = '';
            routeNameTitle.textContent = route.name || 'Rute #' + route.id;
            routeCreatorInfo.innerHTML = `Dibuat oleh: <span class="user-link" data-user-id="${route.creator.id}">${route.creator.name}</span>`;
            
            // Add event listener to creator name
            document.querySelector('.user-link').addEventListener('click', function() {
                const userId = this.getAttribute('data-user-id');
                showUserProfile(userId);
            });
            
            // Update statistics
            updateRouteStats(route);
            
            if (route.reports.length === 0) {
                routeReportsList.innerHTML = '<div class="text-center py-3 text-muted">Belum ada laporan untuk rute ini</div>';
            } else {
                route.reports.forEach(report => {
                    const reportItem = document.createElement('div');
                    reportItem.className = 'report-card';
                    reportItem.innerHTML = `
                        <div class="report-header">
                            <span class="report-type ${report.type}">${getTypeLabel(report.type)}</span>
                            <span class="report-date">${formatDate(report.createdAt)}</span>
                        </div>
                        <p class="report-description">${report.description}</p>
                        <div class="report-footer">
                            <div class="report-user" data-user-id="${report.user.id}">
                                <small>Oleh: ${report.user.name}</small>
                            </div>
                            <div class="report-likes">
                                <i class="fas fa-heart"></i>
                                <span>${report.likes}</span>
                            </div>
                        </div>
                    `;
                    
                    // Add event listener to user name
                    reportItem.querySelector('.report-user').addEventListener('click', function(e) {
                        e.stopPropagation();
                        const userId = this.getAttribute('data-user-id');
                        showUserProfile(userId);
                    });
                    
                    routeReportsList.appendChild(reportItem);
                });
            }
            
            // Show the panel
            routeInfoPanel.style.display = 'block';
            
            // Show/hide add report button based on auth status
            if (currentUser) {
                addToRouteBtn.style.display = 'block';
                routeLoginPrompt.style.display = 'none';
            } else {
                addToRouteBtn.style.display = 'none';
                routeLoginPrompt.style.display = 'block';
            }
            
            // Position the panel near the route
            const midLat = (route.start_lat + route.end_lat) / 2;
            const midLng = (route.start_lng + route.end_lng) / 2;
            map.setView([midLat, midLng], 13);
        }
        
        // Show user profile (placeholder function)
        function showUserProfile(userId) {
            alert(`Ini akan menampilkan profil pengguna dengan ID: ${userId}. Implementasi lengkap akan tergantung pada backend Anda.`);
            // In a real app, you would fetch user details and show a profile modal
        }
        
        // Update route statistics
        function updateRouteStats(route) {
            const reports = route.reports;
            totalReports.textContent = reports.length;
            
            // Count reports by type
            const crimeReports = reports.filter(r => r.type === 'crime').length;
            const accidentReports = reports.filter(r => r.type === 'accident').length;
            const hazardReports = reports.filter(r => r.type === 'hazard').length;
            const safeReports = reports.filter(r => r.type === 'safe_spot').length;
            
            crimeCount.textContent = crimeReports;
            accidentCount.textContent = accidentReports;
            safeCount.textContent = safeReports + hazardReports; // Combining safe and hazard for simplicity
        }
        
        // Setup event listeners
        function setupEventListeners() {
            // Add route button
            addRouteBtn.addEventListener('click', () => {
                if (!currentUser) {
                    alert('Silakan masuk terlebih dahulu untuk menambahkan rute.');
                    loginModal.style.display = 'flex';
                    return;
                }
                startRouteCreation();
            });
            
            // Close modals
            closeBtns.forEach(btn => {
                btn.addEventListener('click', function() {
                    const modal = this.getAttribute('data-dismiss') === 'route' ? 
                        routeModal : reportModal;
                    modal.style.display = 'none';
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
                    routeStartPoint = null;
                    routeEndPoint = null;
                });
            });
            
            // Close route info panel
            closeRoutePanel.addEventListener('click', () => {
                routeInfoPanel.style.display = 'none';
                selectedRoute = null;
            });
            
            // Close modal when clicking outside
            window.addEventListener('click', (e) => {
                if (e.target === reportModal) {
                    reportModal.style.display = 'none';
                    setMode('view');
                }
                if (e.target === routeModal) {
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
                    routeStartPoint = null;
                    routeEndPoint = null;
                }
                if (e.target === loginModal) {
                    loginModal.style.display = 'none';
                }
                if (e.target === registerModal) {
                    registerModal.style.display = 'none';
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
            
            // Handle form submission
            reportForm.addEventListener('submit', (e) => {
                e.preventDefault();
                saveReport();
            });
            
            // Login form submission
            loginForm.addEventListener('submit', async (e) => {
                e.preventDefault();
                await handleLogin();
            });
            
            // Register form submission
            registerForm.addEventListener('submit', async (e) => {
                e.preventDefault();
                await handleRegister();
            });
            
            // Search functionality
            searchBtn.addEventListener('click', searchLocation);
            searchInput.addEventListener('keypress', (e) => {
                if (e.key === 'Enter') {
                    searchLocation();
                }
            });
            
            // Add report to route
            addToRouteBtn.addEventListener('click', () => {
                if (!currentUser) {
                    alert('Silakan masuk terlebih dahulu untuk menambahkan laporan.');
                    loginModal.style.display = 'flex';
                    return;
                }
                
                if (selectedRoute) {
                    document.getElementById('reportRouteId').value = selectedRoute.id;
                    reportModal.style.display = 'flex';
                } else {
                    alert('Silakan pilih rute terlebih dahulu dengan mengklik pada garis rute di peta.');
                }
            });
            
            // Confirm route creation
            confirmRouteBtn.addEventListener('click', createNewRoute);
            
            // Auth buttons
            loginBtn.addEventListener('click', () => {
                loginModal.style.display = 'flex';
            });
            
            registerBtn.addEventListener('click', () => {
                registerModal.style.display = 'flex';
            });
            
            // Logout button
            logoutBtn.addEventListener('click', () => {
                handleLogout();
            });
        }
        
        // Handle login
        async function handleLogin() {
            const email = document.getElementById('loginEmail').value;
            const password = document.getElementById('loginPassword').value;
            
            try {
                const data = await apiCall(ENDPOINTS.LOGIN, {
                    method: 'POST',
                    body: JSON.stringify({ email, password })
                });
                
                // Save token and user data
                localStorage.setItem('authToken', data.token);
                localStorage.setItem('userData', JSON.stringify(data.user));
                currentUser = data.user;
                
                // Update UI
                updateUIForAuthenticatedUser();
                
                // Close modal
                loginModal.style.display = 'none';
                loginForm.reset();
                
                alert('Login berhasil!');
            } catch (error) {
                alert('Login gagal: ' + error.message);
            }
        }
        
        // Handle register
        async function handleRegister() {
            const name = document.getElementById('registerName').value;
            const email = document.getElementById('registerEmail').value;
            const password = document.getElementById('registerPassword').value;
            const confirmPassword = document.getElementById('registerConfirmPassword').value;
            
            if (password !== confirmPassword) {
                alert('Password dan konfirmasi password tidak cocok.');
                return;
            }
            
            try {
                const data = await apiCall(ENDPOINTS.REGISTER, {
                    method: 'POST',
                    body: JSON.stringify({ name, email, password })
                });
                
                // Save token and user data
                localStorage.setItem('authToken', data.token);
                localStorage.setItem('userData', JSON.stringify(data.user));
                currentUser = data.user;
                
                // Update UI
                updateUIForAuthenticatedUser();
                
                // Close modal
                registerModal.style.display = 'none';
                registerForm.reset();
                
                alert('Pendaftaran berhasil!');
            } catch (error) {
                alert('Pendaftaran gagal: ' + error.message);
            }
        }
        
        // Handle logout
        function handleLogout() {
            // In a real app, you might want to call your logout API endpoint
            localStorage.removeItem('authToken');
            localStorage.removeItem('userData');
            updateUIForLoggedOutUser();
            alert('Anda telah keluar.');
        }
        
        // Start the route creation process
        function startRouteCreation() {
            routeModal.style.display = 'flex';
            setMode('addRouteStart');
            startPointInfo.textContent = 'Belum dipilih';
            endPointInfo.textContent = 'Belum dipilih';
            confirmRouteBtn.disabled = true;
        }
        
        // Set the start point for route creation
        function setStartPoint(latlng) {
            routeStartPoint = latlng;
            
            // Remove existing start marker
            if (routeStartMarker) {
                map.removeLayer(routeStartMarker);
            }
            
            // Add new start marker
            routeStartMarker = L.marker(latlng, {
                icon: L.divIcon({
                    className: 'user-marker',
                    html: '<i class="fas fa-play-circle" style="color: #2ecc71; font-size: 24px;"></i>',
                    iconSize: [24, 24]
                })
            }).addTo(map).bindPopup('Titik Awal').openPopup();
            
            startPointInfo.textContent = `${latlng.lat.toFixed(4)}, ${latlng.lng.toFixed(4)}`;
            
            // Move to selecting end point
            setMode('addRouteEnd');
        }
        
        // Set the end point for route creation
        function setEndPoint(latlng) {
            routeEndPoint = latlng;
            
            // Remove existing end marker
            if (routeEndMarker) {
                map.removeLayer(routeEndMarker);
            }
            
            // Add new end marker
            routeEndMarker = L.marker(latlng, {
                icon: L.divIcon({
                    className: 'user-marker',
                    html: '<i class="fas fa-stop-circle" style="color: #e74c3c; font-size: 24px;"></i>',
                    iconSize: [24, 24]
                })
            }).addTo(map).bindPopup('Titik Akhir').openPopup();
            
            endPointInfo.textContent = `${latlng.lat.toFixed(4)}, ${latlng.lng.toFixed(4)}`;
            
            // Enable confirm button
            confirmRouteBtn.disabled = false;
        }
        
        // Create a new route
        async function createNewRoute() {
            if (!routeStartPoint || !routeEndPoint) {
                alert('Silakan pilih titik awal dan akhir terlebih dahulu.');
                return;
            }
            
            try {
                // Create the new route object
                const newRoute = {
                    name: document.getElementById('routeName').value || '',
                    start_lat: routeStartPoint.lat,
                    start_lng: routeStartPoint.lng,
                    end_lat: routeEndPoint.lat,
                    end_lng: routeEndPoint.lng
                };
                
                // Save to backend
                const data = await apiCall(ENDPOINTS.ROUTES, {
                    method: 'POST',
                    body: JSON.stringify(newRoute)
                });
                
                // Add to local routes array
                routes.push(data.route);
                
                // Get the route geometry from OSRM
                const geometry = await getRouteGeometry(
                    data.route.start_lat, 
                    data.route.start_lng, 
                    data.route.end_lat, 
                    data.route.end_lng
                );
                
                // Convert GeoJSON coordinates to LatLng pairs
                const latLngs = geometry.coordinates.map(coord => L.latLng(coord[1], coord[0]));
                
                // Create the polyline with the actual route path
                const line = L.polyline(latLngs, {
                    color: '#3498db', // Default color for routes without reports
                    weight: 6,
                    opacity: 0.7,
                    className: 'route-line'
                }).addTo(map);
                
                // Store reference to the line and route
                line.routeId = data.route.id;
                routeLines.push(line);
                
                // Add click event to show route reports
                line.on('click', function(e) {
                    showRouteReports(data.route.id);
                });
                
                // Add markers for start and end points
                L.marker([data.route.start_lat, data.route.start_lng]).addTo(map)
                    .bindPopup('Titik Awal: ' + (data.route.name || 'Rute #' + data.route.id));
                
                L.marker([data.route.end_lat, data.route.end_lng]).addTo(map)
                    .bindPopup('Titik Akhir: ' + (data.route.name || 'Rute #' + data.route.id));
                
                // Close the modal
                routeModal.style.display = 'none';
                
                // Show warning about required report
                alert('Rute berhasil dibuat! Ingat: Anda harus menambahkan laporan untuk rute ini atau akan dihapus dalam 24 jam.');
                
                // Prompt to add a report
                selectedRoute = data.route;
                document.getElementById('reportRouteId').value = data.route.id;
                reportModal.style.display = 'flex';
                
                // Reset mode
                setMode('view');
                
                // Clean up markers
                if (routeStartMarker) {
                    map.removeLayer(routeStartMarker);
                    routeStartMarker = null;
                }
                if (routeEndMarker) {
                    map.removeLayer(routeEndMarker);
                    routeEndMarker = null;
                }
                routeStartPoint = null;
                routeEndPoint = null;
                
            } catch (error) {
                console.error('Error creating route:', error);
                alert('Terjadi kesalahan saat membuat rute. Silakan coba lagi.');
            }
        }
        
        // Set the current mode
        function setMode(mode) {
            currentMode = mode;
            
            // Update mode indicator
            switch(mode) {
                case 'addRouteStart':
                    modeIndicator.textContent = 'Mode: Buat Rute - Pilih titik awal';
                    modeIndicator.style.display = 'block';
                    modeIndicator.style.backgroundColor = '#2ecc71';
                    break;
                case 'addRouteEnd':
                    modeIndicator.textContent = 'Mode: Buat Rute - Pilih titik akhir';
                    modeIndicator.style.display = 'block';
                    modeIndicator.style.backgroundColor = '#2ecc71';
                    break;
                default:
                    modeIndicator.style.display = 'none';
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
            // In a real app, you would fetch filtered reports from the backend
            // For demo, we'll just show all reports with a visual indication
            loadReports();
        }
        
        // Load reports (from all routes)
        function loadReports() {
            // Clear current list
            reportsList.innerHTML = '';
            
            // Get active filter
            const activeFilter = document.querySelector('.filter-options button.active');
            const filterType = activeFilter ? activeFilter.getAttribute('data-type') : 'all';
            
            // Collect all reports from all routes
            let allReports = [];
            routes.forEach(route => {
                route.reports.forEach(report => {
                    allReports.push({
                        ...report,
                        routeId: route.id,
                        routeName: route.name || 'Rute #' + route.id
                    });
                });
            });
            
            // Apply filter
            if (filterType !== 'all') {
                allReports = allReports.filter(report => report.type === filterType);
            }
            
            // Sort by date (newest first)
            allReports.sort((a, b) => new Date(b.createdAt) - new Date(a.createdAt));
            
            // Display reports
            if (allReports.length === 0) {
                reportsList.innerHTML = `
                    <div class="no-reports">
                        <i class="fas fa-inbox"></i>
                        <p>Tidak ada laporan yang ditemukan</p>
                    </div>
                `;
            } else {
                allReports.forEach(report => {
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
                    <span class="report-date">${formatDate(report.createdAt)}</span>
                </div>
                <p class="report-description">${report.description}</p>
                <div class="report-footer">
                    <div class="report-user" data-user-id="${report.user.id}">
                        <small>Oleh: ${report.user.name} • ${report.routeName}</small>
                    </div>
                    <div class="report-likes">
                        <i class="fas fa-heart"></i>
                        <span>${report.likes}</span>
                    </div>
                </div>
            `;
            
            // Add click event to focus on the route
            reportCard.addEventListener('click', () => {
                showRouteReports(report.routeId);
            });
            
            // Add event listener to user name
            reportCard.querySelector('.report-user').addEventListener('click', function(e) {
                e.stopPropagation();
                const userId = this.getAttribute('data-user-id');
                showUserProfile(userId);
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
            const date = new Date(dateString);
            return date.toLocaleDateString('id-ID', {
                day: 'numeric',
                month: 'short',
                year: 'numeric'
            });
        }
        
        // Save report
        async function saveReport() {
            const type = document.getElementById('reportType').value;
            const description = document.getElementById('reportDescription').value;
            const routeId = document.getElementById('reportRouteId').value;
            
            if (!type || !description) {
                alert("Harap isi semua field!");
                return;
            }
            
            try {
                // Save to backend
                const data = await apiCall(ENDPOINTS.REPORTS, {
                    method: 'POST',
                    body: JSON.stringify({
                        route_id: routeId,
                        type,
                        description
                    })
                });
                
                // Add to existing route locally
                const route = routes.find(r => r.id == routeId);
                if (route) {
                    route.reports.push(data.report);
                    
                    // Update the route line color based on the new report
                    const line = routeLines.find(l => l.routeId == routeId);
                    if (line) {
                        line.setStyle({
                            color: getColorForReportType(type)
                        });
                    }
                    
                    // Refresh the route info panel if it's currently showing this route
                    if (selectedRoute && selectedRoute.id == routeId) {
                        showRouteReports(routeId);
                    }
                }
                
                // Close modal and reset form
                reportModal.style.display = 'none';
                reportForm.reset();
                
                // Reload reports
                loadReports();
                
                alert('Laporan berhasil ditambahkan!');
            } catch (error) {
                alert('Gagal menambahkan laporan: ' + error.message);
            }
        }
        
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
        
        // Initialize the app
        init();
    </script>
</body>
</html>