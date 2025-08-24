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
        
        .report-actions {
            display: flex;
            gap: 0.5rem;
        }
        
        .report-actions button {
            background: none;
            border: none;
            cursor: pointer;
            color: #7f8c8d;
            transition: color 0.3s;
            font-size: 0.9rem;
        }
        
        .report-actions button:hover {
            color: var(--primary-color);
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
            bottom: 90px;
            right: 20px;
            z-index: 1000;
            background-color: var(--success-color);
            color: white;
            border: none;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            font-size: 1.2rem;
            cursor: pointer;
            box-shadow: 0 3px 10px rgba(0,0,0,0.2);
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .add-report-btn {
            position: absolute;
            bottom: 20px;
            right: 20px;
            z-index: 1000;
            background-color: var(--primary-color);
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
        
        .add-route-btn:hover, .add-report-btn:hover {
            transform: scale(1.05);
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
            
            .add-route-btn {
                bottom: 80px;
                right: 15px;
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
            
            .add-report-btn {
                bottom: 15px;
                right: 15px;
                width: 50px;
                height: 50px;
                font-size: 1.2rem;
            }
            
            .add-route-btn {
                bottom: 75px;
                right: 15px;
                width: 45px;
                height: 45px;
                font-size: 1rem;
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
    </style>
</head>
<body>
    <header>
        <div class="logo">
            <i class="fas fa-map-marked-alt"></i>
            <h1>Peta Jalan Aman</h1>
        </div>
        <div>
            <button class="btn btn-outline-light me-2" id="loginBtn">Masuk</button>
            <button class="btn btn-light" id="registerBtn">Daftar</button>
        </div>
    </header>
    
    <div class="main-container">
        <div class="map-container">
            <div id="map"></div>
            
            <div class="mode-indicator" id="modeIndicator"></div>
            
            <div class="route-info-panel" id="routeInfoPanel">
                <h5>Informasi Rute</h5>
                <div id="routeCreatorInfo" class="route-creator"></div>
                <div class="route-reports-list" id="routeReportsList">
                    <!-- Route reports will be added here -->
                </div>
                <button class="add-to-route-btn" id="addToRouteBtn">
                    <i class="fas fa-plus me-1"></i> Tambahkan Laporan
                </button>
            </div>
            
            <button class="add-route-btn" id="addRouteBtn" title="Tambah Rute Baru">
                <i class="fas fa-route"></i>
            </button>
            
            <button class="add-report-btn" id="addReportBtn">
                <i class="fas fa-plus"></i>
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
                <button type="submit" class="submit-btn">Kirim Laporan</button>
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

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Initialize map
        const map = L.map('map').setView([-6.2088, 106.8456], 13); // Default to Jakarta
        
        // Add OpenStreetMap tiles
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);
        
        // Global variables
        let currentMode = 'view'; // 'view', 'addReport', 'addRouteStart', 'addRouteEnd'
        let routes = [];
        let routeLines = [];
        let selectedRoute = null;
        let currentUser = { id: 1, name: "Pengguna Demo" }; // In a real app, this would come from authentication
        let routeStartPoint = null;
        let routeEndPoint = null;
        let routeStartMarker = null;
        let routeEndMarker = null;
        
        // Sample routes data (in a real app, this would come from your backend)
        const sampleRoutes = [
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
        
        // DOM Elements
        const addReportBtn = document.getElementById('addReportBtn');
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
        
        // Initialize the application
        function init() {
            loadRoutes();
            setupEventListeners();
            tryGeolocation();
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
        
        // Load routes from backend (using sample data for demo)
        async function loadRoutes() {
            // In a real app, you would fetch this from your backend
            routes = sampleRoutes;
            
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
            routeCreatorInfo.textContent = `Dibuat oleh: ${route.creator.name}`;
            
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
                            <div class="report-user">
                                <small>Oleh: ${report.user.name}</small>
                            </div>
                            <div class="report-likes">
                                <i class="fas fa-heart"></i>
                                <span>${report.likes}</span>
                            </div>
                        </div>
                    `;
                    routeReportsList.appendChild(reportItem);
                });
            }
            
            // Show the panel
            routeInfoPanel.style.display = 'block';
            
            // Position the panel near the route
            const midLat = (route.start_lat + route.end_lat) / 2;
            const midLng = (route.start_lng + route.end_lng) / 2;
            map.setView([midLat, midLng], 13);
        }
        
        // Setup event listeners
        function setupEventListeners() {
            // Add report button
            addReportBtn.addEventListener('click', () => {
                setMode('addReport');
            });
            
            // Add route button
            addRouteBtn.addEventListener('click', () => {
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
            });
            
            // Close route info panel when clicking outside
            map.on('click', (e) => {
                if (currentMode === 'view') {
                    routeInfoPanel.style.display = 'none';
                    selectedRoute = null;
                } else if (currentMode === 'addRouteStart') {
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
            
            // Search functionality
            searchBtn.addEventListener('click', searchLocation);
            searchInput.addEventListener('keypress', (e) => {
                if (e.key === 'Enter') {
                    searchLocation();
                }
            });
            
            // Add report to route
            addToRouteBtn.addEventListener('click', () => {
                if (selectedRoute) {
                    document.getElementById('reportRouteId').value = selectedRoute.id;
                    reportModal.style.display = 'flex';
                }
            });
            
            // Confirm route creation
            confirmRouteBtn.addEventListener('click', createNewRoute);
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
                    id: Date.now(),
                    name: document.getElementById('routeName').value || '',
                    start_lat: routeStartPoint.lat,
                    start_lng: routeStartPoint.lng,
                    end_lat: routeEndPoint.lat,
                    end_lng: routeEndPoint.lng,
                    creator: currentUser,
                    created_at: new Date().toISOString(),
                    reports: [] // Start with no reports
                };
                
                // Add to routes array
                routes.push(newRoute);
                
                // Get the route geometry from OSRM
                const geometry = await getRouteGeometry(
                    newRoute.start_lat, 
                    newRoute.start_lng, 
                    newRoute.end_lat, 
                    newRoute.end_lng
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
                line.routeId = newRoute.id;
                routeLines.push(line);
                
                // Add click event to show route reports
                line.on('click', function(e) {
                    showRouteReports(newRoute.id);
                });
                
                // Add markers for start and end points
                L.marker([newRoute.start_lat, newRoute.start_lng]).addTo(map)
                    .bindPopup('Titik Awal: ' + (newRoute.name || 'Rute #' + newRoute.id));
                
                L.marker([newRoute.end_lat, newRoute.end_lng]).addTo(map)
                    .bindPopup('Titik Akhir: ' + (newRoute.name || 'Rute #' + newRoute.id));
                
                // Close the modal
                routeModal.style.display = 'none';
                
                // Show warning about required report
                alert('Rute berhasil dibuat! Ingat: Anda harus menambahkan laporan untuk rute ini atau akan dihapus dalam 24 jam.');
                
                // Prompt to add a report
                selectedRoute = newRoute;
                document.getElementById('reportRouteId').value = newRoute.id;
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
                case 'addReport':
                    modeIndicator.textContent = 'Mode: Tambah Laporan - Pilih rute pada peta';
                    modeIndicator.style.display = 'block';
                    modeIndicator.style.backgroundColor = '#3498db';
                    break;
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
            
            if (mode === 'addReport') {
                alert("Silakan pilih rute pada peta untuk menambahkan laporan.");
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
                    <div class="report-user">
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
        function saveReport() {
            const type = document.getElementById('reportType').value;
            const description = document.getElementById('reportDescription').value;
            const routeId = document.getElementById('reportRouteId').value;
            
            if (!type || !description) {
                alert("Harap isi semua field!");
                return;
            }
            
            // In a real app, you would send this to your backend
            // For demo, we'll just add it to the local data
            const newReport = {
                id: Date.now(),
                type: type,
                description: description,
                createdAt: new Date().toISOString(),
                likes: 0,
                dislikes: 0,
                user: currentUser
            };
            
            if (routeId) {
                // Add to existing route
                const route = routes.find(r => r.id == routeId);
                if (route) {
                    route.reports.push(newReport);
                    
                    // Update the route line color based on the new report
                    const line = routeLines.find(l => l.routeId == routeId);
                    if (line) {
                        line.setStyle({
                            color: getColorForReportType(type)
                        });
                    }
                }
            } else {
                // In a real app, you would create a new route
                alert("Silakan pilih rute terlebih dahulu");
                return;
            }
            
            // Close modal and reset form
            reportModal.style.display = 'none';
            reportForm.reset();
            
            // Reload reports
            loadReports();
            
            alert('Laporan berhasil ditambahkan!');
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