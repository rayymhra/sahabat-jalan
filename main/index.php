<?php
session_start();
include_once '../connection.php'; // Your database connection file

$userLoggedIn = isset($_SESSION['user_id']);
$userData = $userLoggedIn ? [
'id' => $_SESSION['user_id'],
'name' => $_SESSION['name'],
'username' => $_SESSION['username'],
'role' => $_SESSION['role'],
'avatar' => $_SESSION['avatar']
    ] : null;
    
    // Function to get like counts for reports
    // Function to get like counts for reports
    function getReportLikes($conn, $report_id, $user_id = null) {
        // Use prepared statements to prevent SQL injection
        $query = "
    SELECT 
    SUM(CASE WHEN value = 1 THEN 1 ELSE 0 END) as likes,
    SUM(CASE WHEN value = -1 THEN 1 ELSE 0 END) as dislikes";
        
        if ($user_id) {
            $query .= ", (SELECT value FROM likes WHERE user_id = ? AND report_id = ?) as user_vote";
        }
        
        $query .= " FROM likes WHERE report_id = ?";
        
        $stmt = $conn->prepare($query);
        
        if ($user_id) {
            $stmt->bind_param("iii", $user_id, $report_id, $report_id);
        } else {
            $stmt->bind_param("i", $report_id);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        
        $data = $result->fetch_assoc();
        $stmt->close();
        
        return $data ?: ['likes' => 0, 'dislikes' => 0, 'user_vote' => 0];
    }
    
    // Load routes from database
    $routes = [];
    $reports = [];
    
    if ($conn) {
        // ================================
        // Fetch routes + their reports
        // ================================
        $routeQuery = "
    SELECT r.*, 
    r.start_latitude as start_lat, 
    r.start_longitude as start_lng,
    r.end_latitude as end_lat, 
    r.end_longitude as end_lng,
    u.name AS creator_name, 
    u.id AS creator_id,
    u.avatar AS creator_avatar 
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
            SELECT rep.*, u.name AS user_name, u.avatar AS user_avatar 
            FROM reports rep 
            LEFT JOIN users u ON rep.user_id = u.id 
            WHERE rep.route_id = $routeId 
            ORDER BY rep.created_at DESC
            ";
                $reportResult = $conn->query($reportQuery);
                
                $routeReports = [];
                if ($reportResult) {
                    while ($report = $reportResult->fetch_assoc()) {
                        // Add like information to each report
                        $likes_data = getReportLikes($conn, $report['id'], $userLoggedIn ? $userData['id'] : null);
                        $report['likes'] = $likes_data['likes'] ?? 0;
                        $report['dislikes'] = $likes_data['dislikes'] ?? 0;
                        $report['user_vote'] = $likes_data['user_vote'] ?? 0;
                        
                        $routeReports[] = $report;
                    }
                } else {
                    error_log("Report query error: " . $conn->error);
                }
                
                $route['reports'] = $routeReports;
                $routes[] = $route;
            }
        }
        
        // ================================
        // Fetch all reports (latest 20)
        // ================================
        // Modify your allReportsQuery to include like counts
        $allReportsQuery = "
    SELECT rep.*, 
    CONCAT(r.start_latitude, ',', r.start_longitude, ' → ', r.end_latitude, ',', r.end_longitude) AS route_name,
    u.name AS user_name, 
    u.avatar AS user_avatar,
    COALESCE(SUM(CASE WHEN l.value = 1 THEN 1 ELSE 0 END), 0) as likes,
    COALESCE(SUM(CASE WHEN l.value = -1 THEN 1 ELSE 0 END), 0) as dislikes,
    (SELECT COUNT(*) FROM comments WHERE report_id = rep.id) as comment_count,
    " . ($userLoggedIn ? 
        "(SELECT value FROM likes WHERE user_id = " . $userData['id'] . " AND report_id = rep.id) as user_vote" 
        : "0 as user_vote") . "
    FROM reports rep 
    LEFT JOIN routes r ON rep.route_id = r.id 
    LEFT JOIN users u ON rep.user_id = u.id 
    LEFT JOIN likes l ON rep.id = l.report_id
    GROUP BY rep.id
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
SELECT r.*, 
r.start_latitude as start_lat, 
r.start_longitude as start_lng,
r.end_latitude as end_lat, 
r.end_longitude as end_lng,
u.name AS creator_name, 
u.avatar AS creator_avatar 
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
    
    // Modify your allReportsQuery to include like counts
    $allReportsQuery = "
SELECT rep.*, 
CONCAT(r.start_latitude, ',', r.start_longitude, ' → ', r.end_latitude, ',', r.end_longitude) AS route_name,
u.name AS user_name, 
u.avatar AS user_avatar,
COALESCE(SUM(CASE WHEN l.value = 1 THEN 1 ELSE 0 END), 0) as likes,
COALESCE(SUM(CASE WHEN l.value = -1 THEN 1 ELSE 0 END), 0) as dislikes,
(SELECT COUNT(*) FROM comments WHERE report_id = rep.id) as comment_count,
" . ($userLoggedIn ? 
"(SELECT value FROM likes WHERE user_id = " . $userData['id'] . " AND report_id = rep.id) as user_vote" 
    : "0 as user_vote") . "
FROM reports rep 
LEFT JOIN routes r ON rep.route_id = r.id 
LEFT JOIN users u ON rep.user_id = u.id 
LEFT JOIN likes l ON rep.id = l.report_id
GROUP BY rep.id
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
        --primary-color: #2563eb;
        --secondary-color: #1f2937;
        --danger-color: #dc2626;
        --success-color: #059669;
        --warning-color: #d97706;
        --safe-color: #10b981;
        --text-primary: #111827;
        --text-secondary: #6b7280;
        --border-color: #e5e7eb;
        --bg-primary: #ffffff;
        --bg-secondary: #f9fafb;
        --bg-tertiary: #f3f4f6;
        --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
        --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
        --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
        --radius-sm: 6px;
        --radius-md: 8px;
        --radius-lg: 12px;
    }
    
    * {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Inter', 'SF Pro Display', system-ui, sans-serif;
    display: flex;
    flex-direction: column;
    height: 100vh;
    background-color: var(--bg-secondary);
    color: var(--text-primary);
    line-height: 1.6;
}

/* Header with Notion-inspired clean design */
header {
    background-color: var(--bg-primary);
    color: var(--text-primary);
    padding: 0.75rem 1.5rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 1px solid var(--border-color);
    box-shadow: var(--shadow-sm);
    position: relative;
    z-index: 1000;
}

.logo {
    display: flex;
    align-items: center;
    gap: 12px;
}

.logo i {
    font-size: 1.5rem;
    color: var(--primary-color);
}

.logo h1 {
    font-size: 1.375rem;
    font-weight: 600;
    margin: 0;
    color: var(--text-primary);
}

.user-info {
    display: flex;
    align-items: center;
    gap: 12px;
}

.user-avatar {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--primary-color), #3b82f6);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: 500;
    font-size: 0.875rem;
    transition: all 0.2s ease;
    cursor: pointer;
}

.user-avatar:hover {
    transform: scale(1.05);
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.2);
}

#userName {
    font-weight: 500;
    color: var(--text-primary);
    font-size: 0.875rem;
}

#logoutBtn {
    background: none;
    border: 1px solid var(--border-color);
    color: var(--text-secondary);
    padding: 0.375rem 0.75rem;
    border-radius: var(--radius-sm);
    font-size: 0.8rem;
    transition: all 0.2s ease;
}

#logoutBtn:hover {
    background-color: var(--bg-tertiary);
    color: var(--text-primary);
    border-color: var(--text-secondary);
}

.main-container {
    display: flex;
    flex: 1;
    overflow: hidden;
}

.map-container {
    flex: 1;
    position: relative;
    background-color: var(--bg-tertiary);
}

#map {
    height: 100%;
    width: 100%;
    z-index: 1;
    border-radius: 0;
}

/* Google Maps inspired sidebar */
.sidebar {
    width: 380px;
    background-color: var(--bg-primary);
    padding: 0;
    overflow-y: auto;
    border-left: 1px solid var(--border-color);
    display: flex;
    flex-direction: column;
    z-index: 2;
}

.sidebar-header {
    padding: 1.25rem 1.5rem;
    background-color: #2563eb;
    border-bottom: 1px solid var(--border-color);
}

.sidebar-header h5 {
    font-size: 1.125rem;
    font-weight: 600;
    color: #ffffff;
    
    margin: 0;
}

.search-container {
    padding: 1rem 1.5rem;
    background-color: var(--bg-primary);
    border-bottom: 1px solid var(--border-color);
}

.search-container .input-group input {
    border: 1px solid var(--border-color);
    border-radius: var(--radius-md);
    padding: 0.75rem 1rem;
    font-size: 0.875rem;
    background-color: var(--bg-secondary);
    transition: all 0.2s ease;
}

.search-container .input-group input:focus {
    outline: none;
    border-color: var(--primary-color);
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
    background-color: var(--bg-primary);
}

.search-container .btn-primary {
    background-color: var(--primary-color);
    border-color: var(--primary-color);
    border-radius: var(--radius-md);
    padding: 0.75rem 1rem;
    transition: all 0.2s ease;
}

.search-container .btn-primary:hover {
    background-color: #1d4ed8;
    transform: translateY(-1px);
    box-shadow: var(--shadow-md);
}

.filter-section {
    padding: 1rem 1.5rem;
    background-color: var(--bg-primary);
    border-bottom: 1px solid var(--border-color);
}

.filter-options {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    margin-bottom: 0.75rem;
}

.filter-options button {
    padding: 0.5rem 1rem;
    border: 1px solid var(--border-color);
    background-color: var(--bg-primary);
    border-radius: 20px;
    cursor: pointer;
    transition: all 0.2s ease;
    font-size: 0.8rem;
    font-weight: 500;
    color: var(--text-secondary);
}

.filter-options button:hover {
    background-color: var(--bg-secondary);
    border-color: var(--primary-color);
    color: var(--text-primary);
}

.filter-options button.active {
    background-color: var(--primary-color);
    color: white;
    border-color: var(--primary-color);
    box-shadow: var(--shadow-sm);
}

.filter-section small {
    color: var(--text-secondary);
    font-size: 0.75rem;
}

.reports-container {
    flex: 1;
    overflow-y: auto;
    padding: 1rem 1.5rem;
    background-color: var(--bg-secondary);
}

.reports-list {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

/* Notion-inspired report cards */
.report-card {
    background-color: var(--bg-primary);
    border-radius: var(--radius-md);
    padding: 1.25rem;
    box-shadow: var(--shadow-sm);
    border: 1px solid var(--border-color);
    cursor: pointer;
    transition: all 0.2s ease;
    position: relative;
    margin-top: 7px;
}

.report-card:hover {
    transform: translateY(-1px);
    box-shadow: var(--shadow-md);
    border-color: var(--primary-color);
}

.report-header {
    display: flex;
    justify-content: space-between;
    margin-bottom: 0.75rem;
    align-items: flex-start;
}

.report-type {
    padding: 0.375rem 0.75rem;
    border-radius: 16px;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.025em;
}

.crime { 
    background-color: rgba(220, 38, 38, 0.1);
    color: var(--danger-color);
    border: 1px solid rgba(220, 38, 38, 0.2);
}
.accident { 
    background-color: rgba(217, 119, 6, 0.1);
    color: var(--warning-color);
    border: 1px solid rgba(217, 119, 6, 0.2);
}
.hazard { 
    background-color: rgba(234, 88, 12, 0.1);
    color: #ea580c;
    border: 1px solid rgba(234, 88, 12, 0.2);
}
.safe_spot { 
    background-color: rgba(5, 150, 105, 0.1);
    color: var(--success-color);
    border: 1px solid rgba(5, 150, 105, 0.2);
}

.report-date {
    color: var(--text-secondary);
    font-size: 0.75rem;
    font-weight: 500;
}

.report-description {
    margin-bottom: 1rem;
    color: var(--text-primary);
    line-height: 1.6;
    font-size: 0.875rem;
}

.report-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-top: 0.75rem;
    border-top: 1px solid var(--border-color);
}

.report-user {
    font-size: 0.75rem;
    color: var(--text-secondary);
    cursor: pointer;
    transition: color 0.2s ease;
}

.report-user:hover {
    color: var(--primary-color);
}

/* Google Maps inspired floating controls */
.map-controls {
    position: absolute;
    bottom: 24px;
    right: 24px;
    z-index: 1000;
    display: flex;
    flex-direction: column-reverse;
    gap: 12px;
    align-items: flex-end;
}

.location-btn, .add-route-btn {
    background-color: var(--bg-primary);
    color: var(--text-primary);
    border: none;
    border-radius: 50%;
    width: 48px;
    height: 48px;
    font-size: 1.125rem;
    cursor: pointer;
    box-shadow: var(--shadow-lg);
    transition: all 0.2s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 1px solid var(--border-color);
}

.location-btn:hover {
    background-color: var(--primary-color);
    color: white;
    transform: translateY(-2px);
    box-shadow: 0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1);
}

.add-route-btn {
    background-color: var(--success-color);
    color: white;
    width: 56px;
    height: 56px;
    font-size: 1.25rem;
}

.add-route-btn:hover {
    background-color: #047857;
    transform: translateY(-2px);
    box-shadow: 0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1);
}

/* Notion-inspired panels */
.route-info-panel {
    position: absolute;
    top: 20px;
    left: 20px;
    z-index: 1000;
    background: var(--bg-primary);
    border-radius: var(--radius-lg);
    padding: 0;
    box-shadow: var(--shadow-lg);
    max-width: 320px;
    display: none;
    border: 1px solid var(--border-color);
    overflow: hidden;
}

.panel-header {
    padding: 1.25rem 1.5rem 1rem;
    border-bottom: 1px solid var(--border-color);
    background-color: var(--bg-primary);
}

.panel-header h5 {
    font-size: 1.125rem;
    font-weight: 600;
    color: var(--text-primary);
    margin: 0;
}

.panel-close {
    background: none;
    border: none;
    font-size: 1.25rem;
    cursor: pointer;
    color: var(--text-secondary);
    padding: 0.25rem;
    border-radius: var(--radius-sm);
    transition: all 0.2s ease;
    position: absolute;
    top: 1rem;
    right: 1rem;
}

.panel-close:hover {
    background-color: var(--bg-tertiary);
    color: var(--text-primary);
}

.route-creator {
    font-size: 0.75rem;
    color: var(--text-secondary);
    margin-bottom: 1rem;
    padding: 0 1.5rem;
}

.route-stats {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 0.75rem;
    margin: 1rem 1.5rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid var(--border-color);
}

.stat-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 1rem 0.75rem;
    background-color: var(--bg-secondary);
    border-radius: var(--radius-md);
    border: 1px solid var(--border-color);
}

.stat-value {
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--text-primary);
    line-height: 1;
}

.stat-label {
    font-size: 0.75rem;
    color: var(--text-secondary);
    text-transform: uppercase;
    letter-spacing: 0.05em;
    font-weight: 500;
    margin-top: 0.25rem;
}

.route-reports-list {
    max-height: 300px;
    overflow-y: auto;
    padding: 0 1.5rem 1rem;
}

.add-to-route-btn {
    background-color: var(--primary-color);
    color: white;
    border: none;
    border-radius: var(--radius-md);
    padding: 0.75rem 1.25rem;
    cursor: pointer;
    width: calc(100% - 3rem);
    margin: 0 1.5rem 1.5rem;
    transition: all 0.2s ease;
    font-weight: 500;
    font-size: 0.875rem;
}

.add-to-route-btn:hover {
    background-color: #1d4ed8;
    transform: translateY(-1px);
    box-shadow: var(--shadow-md);
}

.route-creation-controls {
    position: absolute;
    top: 20px;
    right: 20px;
    z-index: 1000;
    background-color: var(--bg-primary);
    padding: 1.5rem;
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-lg);
    display: none;
    flex-direction: column;
    gap: 1rem;
    min-width: 280px;
    border: 1px solid var(--border-color);
}

.route-creation-controls h5 {
    font-size: 1rem;
    font-weight: 600;
    color: var(--text-primary);
    margin-bottom: 0.5rem;
}

.route-step {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 0.5rem 0;
}

.route-step-icon {
    width: 28px;
    height: 28px;
    border-radius: 50%;
    background-color: var(--bg-tertiary);
    border: 2px solid var(--border-color);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.75rem;
    font-weight: 600;
    color: var(--text-secondary);
    transition: all 0.2s ease;
}

.route-step.active .route-step-icon {
    background-color: var(--primary-color);
    color: white;
    border-color: var(--primary-color);
}

.route-step.completed .route-step-icon {
    background-color: var(--success-color);
    color: white;
    border-color: var(--success-color);
}

.route-step span {
    font-size: 0.875rem;
    color: var(--text-primary);
    font-weight: 500;
}

.route-control-buttons {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
    margin-top: 0.5rem;
}

.route-control-btn {
    padding: 0.75rem 1rem;
    border: none;
    border-radius: var(--radius-md);
    cursor: pointer;
    font-size: 0.875rem;
    font-weight: 500;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    transition: all 0.2s ease;
}

.select-point-btn {
    background-color: var(--primary-color);
    color: white;
}

.select-point-btn:hover:not(:disabled) {
    background-color: #1d4ed8;
    transform: translateY(-1px);
}

.create-route-btn {
    background-color: var(--success-color);
    color: white;
}

.create-route-btn:hover:not(:disabled) {
    background-color: #047857;
    transform: translateY(-1px);
}

.cancel-route-btn {
    background-color: transparent;
    color: var(--text-secondary);
    border: 1px solid var(--border-color);
}

.cancel-route-btn:hover {
    background-color: var(--bg-tertiary);
    color: var(--text-primary);
}

.route-control-btn:disabled {
    background-color: var(--bg-tertiary);
    color: var(--text-secondary);
    cursor: not-allowed;
    transform: none;
}

/* Modal styling */
.modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.4);
    backdrop-filter: blur(8px);
    z-index: 2000;
    justify-content: center;
    align-items: center;
    padding: 1rem;
}

.modal-content {
    background-color: var(--bg-primary);
    padding: 0;
    border-radius: var(--radius-lg);
    width: 100%;
    max-width: 500px;
    max-height: 90vh;
    overflow: hidden;
    box-shadow: 0 25px 50px -12px rgb(0 0 0 / 0.25);
    border: 1px solid var(--border-color);
}

.modal-header {
    padding: 1.5rem 2rem 1rem;
    border-bottom: 1px solid var(--border-color);
    background-color: var(--bg-primary);
}

.modal-header h4 {
    font-size: 1.25rem;
    font-weight: 600;
    color: var(--text-primary);
    margin: 0;
}

.close-btn {
    background: none;
    border: none;
    font-size: 1.25rem;
    cursor: pointer;
    color: var(--text-secondary);
    padding: 0.25rem;
    border-radius: var(--radius-sm);
    transition: all 0.2s ease;
    position: absolute;
    top: 1.25rem;
    right: 1.5rem;
}

.close-btn:hover {
    background-color: var(--bg-tertiary);
    color: var(--text-primary);
}

.modal-body {
    padding: 1.5rem 2rem 2rem;
    overflow-y: auto;
    max-height: calc(90vh - 140px);
}

.form-group {
    margin-bottom: 1.5rem;
}

.form-group label {
    display: block;
    margin-bottom: 0.5rem;
    color: var(--text-primary);
    font-weight: 500;
    font-size: 0.875rem;
}

.form-group select, 
.form-group textarea,
.form-group input {
    width: 100%;
    padding: 0.75rem 1rem;
    border: 1px solid var(--border-color);
    border-radius: var(--radius-md);
    font-size: 0.875rem;
    background-color: var(--bg-primary);
    color: var(--text-primary);
    transition: all 0.2s ease;
}

.form-group select:focus, 
.form-group textarea:focus,
.form-group input:focus {
    outline: none;
    border-color: var(--primary-color);
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
}

.form-group textarea {
    min-height: 120px;
    resize: vertical;
    line-height: 1.6;
}

.submit-btn {
    background-color: var(--primary-color);
    color: white;
    border: none;
    padding: 0.875rem 1.5rem;
    border-radius: var(--radius-md);
    cursor: pointer;
    font-size: 0.875rem;
    font-weight: 500;
    transition: all 0.2s ease;
    width: 100%;
    margin-top: 1rem;
}

.submit-btn:hover:not(:disabled) {
    background-color: #1d4ed8;
    transform: translateY(-1px);
    box-shadow: var(--shadow-md);
}

.submit-btn:disabled {
    background-color: var(--bg-tertiary);
    color: var(--text-secondary);
    cursor: not-allowed;
    transform: none;
}

/* Enhanced route line styles */
.route-line {
    stroke-width: 5;
    stroke-opacity: 0.8;
    filter: drop-shadow(0px 1px 3px rgba(0, 0, 0, 0.3));
}

.route-line.crime {
    stroke: #dc2626;
}

.route-line.accident {
    stroke: #d97706;
    stroke-dasharray: 8, 4;
}

.route-line.hazard {
    stroke: #ea580c;
    stroke-dasharray: 6, 3;
}

.route-line.safe_spot {
    stroke: #059669;
}

.user-marker {
    border: 2px solid white;
    border-radius: 50%;
    box-shadow: var(--shadow-md);
}

.mode-indicator {
    position: absolute;
    top: 20px;
    left: 50%;
    transform: translateX(-50%);
    z-index: 1000;
    background-color: var(--primary-color);
    color: white;
    padding: 0.75rem 1.5rem;
    border-radius: 24px;
    font-weight: 500;
    font-size: 0.875rem;
    display: none;
    box-shadow: var(--shadow-lg);
}

.user-avatar-small {
    width: 20px;
    height: 20px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--primary-color), #3b82f6);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: 500;
    font-size: 0.6rem;
    margin-right: 0.375rem;
    vertical-align: middle;
}

.user-avatar-small img {
    width: 100%;
    height: 100%;
    border-radius: 50%;
    object-fit: cover;
}

.user-avatar-large {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--primary-color), #3b82f6);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: 600;
    font-size: 2rem;
    margin: 0 auto;
    box-shadow: var(--shadow-md);
}

.user-avatar-large img {
    width: 100%;
    height: 100%;
    border-radius: 50%;
    object-fit: cover;
}

.user-link {
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    transition: color 0.2s ease;
}

.user-link:hover {
    color: var(--primary-color);
}

/* Like/dislike system */
.like-dislike-container {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.like-btn, .dislike-btn {
    background: none;
    border: none;
    cursor: pointer;
    font-size: 0.875rem;
    display: flex;
    align-items: center;
    gap: 0.375rem;
    padding: 0.375rem 0.5rem;
    border-radius: var(--radius-sm);
    transition: all 0.2s ease;
    color: var(--text-secondary);
}

.like-btn:hover {
    background-color: rgba(16, 185, 129, 0.1);
    color: var(--success-color);
}

.dislike-btn:hover {
    background-color: rgba(220, 38, 38, 0.1);
    color: var(--danger-color);
}

.like-btn.active {
    color: var(--success-color);
    background-color: rgba(16, 185, 129, 0.15);
}

.dislike-btn.active {
    color: var(--danger-color);
    background-color: rgba(220, 38, 38, 0.15);
}

.like-count, .dislike-count {
    font-size: 0.75rem;
    font-weight: 500;
    min-width: 16px;
    text-align: center;
}

/* Report actions menu */
.report-actions {
    position: relative;
    display: inline-block;
}

.report-menu-btn {
    background: none;
    border: none;
    cursor: pointer;
    padding: 0.375rem;
    border-radius: var(--radius-sm);
    color: var(--text-secondary);
    transition: all 0.2s ease;
}

.report-menu-btn:hover {
    background-color: var(--bg-tertiary);
    color: var(--text-primary);
}

.report-menu {
    position: absolute;
    right: 0;
    top: 100%;
    background: var(--bg-primary);
    border-radius: var(--radius-md);
    box-shadow: var(--shadow-lg);
    z-index: 100;
    display: none;
    min-width: 140px;
    overflow: hidden;
    border: 1px solid var(--border-color);
    margin-top: 0.25rem;
}

.report-menu.show {
    display: block;
}

.report-menu-item {
    padding: 0.75rem 1rem;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    border: none;
    background: none;
    width: 100%;
    text-align: left;
    font-size: 0.875rem;
    transition: background-color 0.2s ease;
    color: var(--text-primary);
}

.report-menu-item:hover {
    background-color: var(--bg-secondary);
}

.report-menu-item.edit {
    color: var(--primary-color);
}

.report-menu-item.delete {
    color: var(--danger-color);
}

.report-edited {
    font-size: 0.7rem;
    color: var(--text-secondary);
    font-style: italic;
    margin-top: 0.5rem;
}

.login-prompt {
    text-align: center;
    padding: 1rem 1.5rem;
    background-color: rgba(217, 119, 6, 0.1);
    border-radius: var(--radius-md);
    margin: 1rem 1.5rem 1.5rem;
    color: #92400e;
    font-size: 0.875rem;
    border: 1px solid rgba(217, 119, 6, 0.2);
}

.no-reports {
    text-align: center;
    padding: 3rem 1rem;
    color: var(--text-secondary);
}

.no-reports i {
    font-size: 3rem;
    margin-bottom: 1rem;
    color: var(--border-color);
    display: block;
}

.user-location-marker {
    border: 3px solid white;
    border-radius: 50%;
    box-shadow: var(--shadow-md);
    background-color: var(--primary-color);
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0% {
        transform: scale(1);
        opacity: 1;
    }
    50% {
        transform: scale(1.1);
        opacity: 0.8;
    }
    100% {
        transform: scale(1);
        opacity: 1;
    }
}

.user-avatar-link {
    display: flex;
    align-items: center;
    text-decoration: none;
    color: inherit;
    transition: all 0.2s ease;
}

.user-avatar-link:hover .user-avatar {
    transform: scale(1.05);
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.2);
}

.creating-route-mode .add-route-btn {
    display: none;
}

.creating-route-mode .mode-indicator {
    display: block;
}

/* Profile modal styles */
.profile-stats {
    background-color: var(--bg-secondary);
    border-radius: var(--radius-md);
    padding: 1.5rem;
    margin-top: 1.5rem;
}

.profile-stats .row {
    margin: 0;
}

.profile-stats .col-4 {
    padding: 0.5rem;
    text-align: center;
}

.profile-stats .stat-value {
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--text-primary);
    display: block;
}

.profile-stats .stat-label {
    font-size: 0.75rem;
    color: var(--text-secondary);
    text-transform: uppercase;
    letter-spacing: 0.05em;
    font-weight: 500;
    margin-top: 0.25rem;
}

/* Enhanced alert styling */
.alert {
    border-radius: var(--radius-md);
    border: 1px solid;
    font-size: 0.875rem;
}

.alert-warning {
    background-color: rgba(217, 119, 6, 0.1);
    border-color: rgba(217, 119, 6, 0.2);
    color: #92400e;
}

/* Auth buttons */
#authButtons .btn {
    font-size: 0.875rem;
    padding: 0.5rem 1rem;
    border-radius: var(--radius-md);
    font-weight: 500;
    transition: all 0.2s ease;
}

#authButtons .btn-outline-light {
    border-color: var(--border-color);
    color: var(--text-secondary);
}

#authButtons .btn-outline-light:hover {
    background-color: var(--bg-tertiary);
    color: var(--text-primary);
    border-color: var(--text-secondary);
}

#authButtons .btn-light {
    background-color: var(--primary-color);
    color: white;
    border-color: var(--primary-color);
}

#authButtons .btn-light:hover {
    background-color: #1d4ed8;
    border-color: #1d4ed8;
    transform: translateY(-1px);
}

/* Responsive design */
@media (max-width: 992px) {
    .main-container {
        flex-direction: column;
    }
    
    .sidebar {
        width: 100%;
        height: 40%;
        order: 2;
        border-left: none;
        border-top: 1px solid var(--border-color);
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
    
    .map-controls {
        bottom: 12px;
        right: 12px;
    }
}

@media (max-width: 768px) {
    header {
        padding: 0.75rem 1rem;
    }
    
    .logo h1 {
        font-size: 1.25rem;
    }
    
    .filter-options {
        gap: 0.375rem;
    }
    
    .filter-options button {
        padding: 0.375rem 0.75rem;
        font-size: 0.75rem;
    }
    
    .sidebar {
        width: 100%;
    }
    
    .reports-container {
        padding: 1rem;
    }
    
    .route-stats {
        grid-template-columns: repeat(2, 1fr);
        gap: 0.5rem;
        margin: 0.75rem 1rem;
    }
    
    .stat-item {
        padding: 0.75rem 0.5rem;
    }
    
    .modal-content {
        margin: 1rem;
        width: calc(100% - 2rem);
    }
    
    .modal-header {
        padding: 1.25rem 1.5rem 0.75rem;
    }
    
    .modal-body {
        padding: 1rem 1.5rem 1.5rem;
    }
}

@media (max-width: 576px) {
    .user-info span {
        display: none;
    }
    
    .map-controls {
        bottom: 10px;
        right: 10px;
    }
    
    .location-btn, .add-route-btn {
        width: 44px;
        height: 44px;
        font-size: 1rem;
    }
    
    .add-route-btn {
        width: 52px;
        height: 52px;
        font-size: 1.125rem;
    }
    
    .route-stats {
        grid-template-columns: 1fr 1fr;
        margin: 0.5rem 1rem;
    }
    
    .search-container {
        padding: 0.75rem 1rem;
    }
    
    .filter-section {
        padding: 0.75rem 1rem;
    }
    
    .reports-container {
        padding: 0.75rem 1rem;
    }
    
    .report-card {
        padding: 1rem;
    }
    
    .sidebar-header {
        padding: 1rem 1rem;
    }
}

/* Smooth scrollbars */
.sidebar::-webkit-scrollbar,
.reports-container::-webkit-scrollbar,
.route-reports-list::-webkit-scrollbar,
.modal-body::-webkit-scrollbar {
    width: 6px;
}

.sidebar::-webkit-scrollbar-track,
.reports-container::-webkit-scrollbar-track,
.route-reports-list::-webkit-scrollbar-track,
.modal-body::-webkit-scrollbar-track {
    background: var(--bg-secondary);
}

.sidebar::-webkit-scrollbar-thumb,
.reports-container::-webkit-scrollbar-thumb,
.route-reports-list::-webkit-scrollbar-thumb,
.modal-body::-webkit-scrollbar-thumb {
    background: var(--border-color);
    border-radius: 3px;
}

.sidebar::-webkit-scrollbar-thumb:hover,
.reports-container::-webkit-scrollbar-thumb:hover,
.route-reports-list::-webkit-scrollbar-thumb:hover,
.modal-body::-webkit-scrollbar-thumb:hover {
    background: var(--text-secondary);
}

/* Focus states */
*:focus {
    outline: 2px solid var(--primary-color);
    outline-offset: 2px;
}

button:focus,
input:focus,
select:focus,
textarea:focus {
    outline: none;
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.2);
}

/* Loading states */
.loading {
    opacity: 0.6;
    pointer-events: none;
}

/* Typography improvements */
h1, h2, h3, h4, h5, h6 {
    line-height: 1.25;
    font-weight: 600;
}

p {
    margin-bottom: 0;
}

small {
    font-size: 0.75rem;
}

/* Button hover effects */
button {
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
}

/* Card hover effects */
.report-card,
.stat-item {
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
}

/* Enhanced shadows for depth */
.route-info-panel,
.route-creation-controls,
.modal-content {
    box-shadow: 0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1);
}

/* Improved form styling */
.form-group {
    position: relative;
}

.form-group input:focus + label,
.form-group select:focus + label,
.form-group textarea:focus + label {
    color: var(--primary-color);
}

/* Better spacing for mobile */
@media (max-width: 480px) {
    .modal {
        padding: 0.5rem;
    }
    
    .route-info-panel,
    .route-creation-controls {
        left: 0.5rem;
        right: 0.5rem;
        top: 0.5rem;
    }
    
    header {
        padding: 0.5rem 1rem;
    }
    
    .logo h1 {
        font-size: 1.125rem;
    }
    
    .user-avatar {
        width: 32px;
        height: 32px;
        font-size: 0.8rem;
    }
}

/* Comment styles */
.comments-container {
    max-height: 300px;
    overflow-y: auto;
    margin-bottom: 1rem;
}

.comment-item {
    padding: 0.75rem 0;
    border-bottom: 1px solid var(--border-color);
}

.comment-item:last-child {
    border-bottom: none;
}

.comment-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 0.5rem;
}

.comment-user {
    cursor: pointer;
    transition: all 0.2s ease;
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 4px 8px;
    border-radius: 4px;
}

.comment-user:hover {
    background-color: var(--bg-secondary);
}

.comment-username {
    color: var(--primary-color);
    font-weight: 500;
    transition: color 0.2s ease;
}

.comment-user:hover .comment-username {
    color: var(--primary-color-dark);
    text-decoration: underline;
}

.comment-avatar {
    width: 24px;
    height: 24px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--primary-color), #3b82f6);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 0.7rem;
    font-weight: 500;
}

.comment-avatar img {
    width: 100%;
    height: 100%;
    border-radius: 50%;
    object-fit: cover;
}

.comment-actions {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.comment-delete-btn {
    background: none;
    border: none;
    color: var(--text-secondary);
    cursor: pointer;
    padding: 0.25rem;
    border-radius: var(--radius-sm);
    transition: all 0.2s ease;
}

.comment-delete-btn:hover {
    color: var(--danger-color);
    background-color: rgba(220, 38, 38, 0.1);
}

.comment-date {
    font-size: 0.75rem;
    color: var(--text-secondary);
}

.comment-content {
    font-size: 0.875rem;
    line-height: 1.5;
    color: var(--text-primary);
}

.comment-count {
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
    font-size: 0.8rem;
    color: var(--text-secondary);
    margin-left: 0.5rem;
}

.comment-btn {
    background: none;
    border: none;
    cursor: pointer;
    font-size: 0.875rem;
    display: flex;
    align-items: center;
    gap: 0.375rem;
    padding: 0.375rem 0.5rem;
    border-radius: var(--radius-sm);
    transition: all 0.2s ease;
    color: var(--text-secondary);
    outline: none !important;
}

.comment-btn:hover {
    background-color: rgba(59, 130, 246, 0.1);
    color: var(--primary-color);
}

.comment-btn:focus {
    outline: none;
    box-shadow: none;
}

.reporter-name {
    color: var(--primary-color);
    cursor: pointer;
    transition: all 0.2s ease;
}

.reporter-name:hover {
    text-decoration: underline;
    color: #1d4ed8;
}


</style>
</head>
<body>
<header>
<div class="logo">
<i class="fas fa-map-marked-alt"></i>
<h1>GoSafe</h1>
</div>
<div id="authButtons" style="<?php echo $userLoggedIn ? 'display:none;' : 'display:block;'; ?>">
<a href="../auth/login.php" class="btn btn-outline-light me-2">Masuk</a>
<a href="../auth/register.php" class="btn btn-light" id="registerBtn">Daftar</a>
</div>
<div class="user-info" id="userInfo" style="<?php echo $userLoggedIn ? 'display:flex;' : 'display:none;'; ?>">
<a href="profile/index.php" class="user-avatar-link">
<div class="user-avatar" id="userAvatar">
<?php echo $userLoggedIn ? strtoupper(substr($userData['name'], 0, 1)) : ''; ?>
</div>
</a>
<span id="userName"><?php echo $userLoggedIn ? $userData['name'] : ''; ?></span>
<a href="../auth/logout.php" class="btn btn-outline-light btn-sm" id="logoutBtn">Keluar</a>
</div>
</header>

<div class="main-container">
<div class="map-container">
<div id="map"></div>

<div class="map-controls">
<button class="location-btn" id="locationBtn" title="Lokasi Saat Ini">
<i class="fas fa-location-arrow"></i>
</button>

<button class="add-route-btn" id="addRouteBtn" title="Tambah Rute Baru">
<i class="fas fa-route"></i>
</button>
</div>

<!-- <button class="location-btn" id="locationBtn" title="Lokasi Saat Ini">
<i class="fas fa-location-arrow"></i>
</button> -->

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
<div class="modal-content p-3">
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
<div class="modal-content p-3">
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
<div class="modal-content p-3">
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

<!-- Add Report Edit Modal -->
<div class="modal" id="editReportModal">
<div class="modal-content p-3">
<div class="modal-header">
<h4 class="mb-0">Edit Laporan</h4>
<button class="close-btn" data-dismiss="edit">&times;</button>
</div>
<form id="editReportForm" action="update_report.php" method="POST">
<input type="hidden" id="editReportId" name="report_id">
<div class="form-group">
<label for="editReportType">Jenis Laporan</label>
<select id="editReportType" name="type" required>
<option value="">Pilih Jenis Laporan</option>
<option value="crime">Daerah Rawan Kejahatan</option>
<option value="accident">Titik Rawan Kecelakaan</option>
<option value="hazard">Bahaya di Jalan</option>
<option value="safe_spot">Titik Aman</option>
</select>
</div>
<div class="form-group">
<label for="editReportDescription">Deskripsi</label>
<textarea id="editReportDescription" name="description" placeholder="Jelaskan secara detail kondisi di lokasi tersebut..." required></textarea>
</div>
<button type="submit" class="submit-btn" id="editReportSubmitBtn">Perbarui Laporan</button>
</form>
</div>
</div>

<!-- Comment Modal -->
<div class="modal" id="commentModal">
<div class="modal-content p-3">
<div class="modal-header">
<h4 class="mb-0">Komentar</h4>
<button class="close-btn" data-dismiss="comment">&times;</button>
</div>
<div class="modal-body">
<div class="comments-container" id="commentsContainer">
<!-- Comments will be loaded here -->
</div>
<?php if ($userLoggedIn): ?>
    <form id="commentForm">
    <input type="hidden" id="commentReportId" name="report_id">
    <div class="form-group">
    <textarea id="commentContent" name="content" placeholder="Tulis komentar Anda..." rows="3" required></textarea>
    </div>
    <button type="submit" class="submit-btn" id="commentSubmitBtn">Kirim Komentar</button>
    </form>
    <?php else: ?>
        <div class="login-prompt">
        <i class="fas fa-info-circle"></i> Silakan masuk untuk menambahkan komentar
        </div>
        <?php endif; ?>
        </div>
        </div>
        </div>
        
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
        <script>
        // Pass PHP data to JavaScript
        const userLoggedIn = <?php echo $userLoggedIn ? 'true' : 'false'; ?>;
        const userData = <?php echo $userData ? json_encode($userData) : 'null'; ?>;
        const phpRoutes = <?php echo isset($routesJson) ? $routesJson : '[]'; ?>;
        const phpReports = <?php echo isset($reportsJson) ? $reportsJson : '[]'; ?>;
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
        let reportSubmitted = false;
        let editReportModal = document.getElementById('editReportModal');
        let commentModal = document.getElementById('commentModal');
        let currentReportId = null;
        
        
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
            setupLocationButton();
            setupAvatarClick();
            setupCommentUserClicks(); 
            loadRoutes();
            loadReports();
            tryGeolocation();
            setupReporterClicks();
            setupReportActions();
        }
        
        document.addEventListener('DOMContentLoaded', function() {
            init();
            setupReportActions(); // Add this line
            
            // Reinitialize routes to ensure they're clickable
            setTimeout(() => {
                loadRoutes();
                addCommentButtonToReports();
            }, 500);
        });
        
        function addCommentButtonToReports() {
            document.querySelectorAll('.report-card').forEach(card => {
                const reportId = card.dataset.reportId || card.querySelector('.like-btn')?.getAttribute('data-report-id');
                if (reportId) {
                    const footer = card.querySelector('.report-footer');
                    if (footer && !footer.querySelector('.comment-btn')) {
                        const commentBtn = document.createElement('button');
                        commentBtn.className = 'comment-btn';
                        commentBtn.innerHTML = '<i class="far fa-comment"></i> <span class="comment-count">0</span>';
                        commentBtn.setAttribute('data-report-id', reportId);
                        commentBtn.addEventListener('click', (e) => {
                            e.stopPropagation();
                            openCommentModal(reportId);
                        });
                        
                        // Add to like-dislike container
                        const likeContainer = card.querySelector('.like-dislike-container');
                        if (likeContainer) {
                            likeContainer.appendChild(commentBtn);
                        }
                        
                        // Load initial comment count
                        loadCommentCount(reportId, commentBtn);
                    }
                }
            });
        }
        
        // Handle comment button clicks using event delegation
        document.addEventListener('click', function(e) {
            if (e.target.closest('.comment-btn')) {
                const commentBtn = e.target.closest('.comment-btn');
                const reportId = commentBtn.getAttribute('data-report-id');
                if (reportId) {
                    e.stopPropagation();
                    openCommentModal(reportId);
                }
            }
        });
        
        // Safe function to show/hide modals
        function showModal(modal) {
            if (modal && typeof modal.style !== 'undefined') {
                modal.style.display = 'flex';
            }
        }
        
        function hideModal(modal) {
            if (modal && typeof modal.style !== 'undefined') {
                modal.style.display = 'none';
            }
        }
        
        // Load comment count for a report
        function loadCommentCount(reportId, buttonElement) {
            fetch(`get_comments.php?report_id=${reportId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const count = data.comments.length;
                    if (buttonElement) {
                        buttonElement.querySelector('.comment-count').textContent = count;
                    }
                    
                    // Also update any other instances of this report's comment count
                    document.querySelectorAll(`.comment-btn[data-report-id="${reportId}"] .comment-count`).forEach(el => {
                        el.textContent = count;
                    });
                }
            })
            .catch(error => {
                console.error('Error loading comment count:', error);
            });
        }
        
        // Open comment modal
        function openCommentModal(reportId) {
            try {
                currentReportId = reportId;
                const commentReportId = document.getElementById('commentReportId');
                if (commentReportId) {
                    commentReportId.value = reportId;
                }
                commentModal.style.display = 'flex';
                loadComments(reportId);
            } catch (error) {
                console.error('Error opening comment modal:', error);
                alert('Could not open comments. Please try again.');
            }
        }
        
        // Load comments for a report
        function loadComments(reportId) {
            fetch(`get_comments.php?report_id=${reportId}`)
            .then(response => response.json())
            .then(data => {
                const container = document.getElementById('commentsContainer');
                if (data.success) {
                    if (data.comments.length === 0) {
                        container.innerHTML = '<div class="text-center py-3 text-muted">Belum ada komentar</div>';
                    } else {
                        container.innerHTML = '';
                        data.comments.forEach(comment => {
                            const commentElement = createCommentElement(comment, reportId);
                            container.appendChild(commentElement);
                        });
                    }
                    
                    // Update comment count on the button
                    updateCommentCount(reportId, data.comments.length);
                } else {
                    container.innerHTML = `<div class="text-center py-3 text-danger">${data.message}</div>`;
                }
            })
            .catch(error => {
                console.error('Error loading comments:', error);
                document.getElementById('commentsContainer').innerHTML = 
                '<div class="text-center py-3 text-danger">Gagal memuat komentar</div>';
            });
        }
        
        // Create comment element
function createCommentElement(comment, reportId) {
    const commentDiv = document.createElement('div');
    commentDiv.className = 'comment-item';
    commentDiv.id = `comment-${comment.id}`;
    
    const userAvatar = comment.user_avatar ? 
        `<img src="../uploads/${comment.user_avatar}" alt="${comment.user_name}" class="comment-avatar">` :
        `<div class="comment-avatar">${comment.user_name ? comment.user_name.charAt(0).toUpperCase() : 'U'}</div>`;
    
    const commentDate = new Date(comment.created_at).toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
    
    // Check if current user is the comment owner
    const isOwner = userLoggedIn && currentUser && currentUser.id == comment.user_id;
    const deleteButton = isOwner ? 
        `<button class="comment-delete-btn" onclick="deleteComment(${comment.id}, ${reportId})">
            <i class="fas fa-trash"></i>
        </button>` : '';
    
    // Make the user name clickable with data-user-id attribute
    commentDiv.innerHTML = `
        <div class="comment-header">
            <div class="comment-user" data-user-id="${comment.user_id}" style="cursor: pointer;">
                ${userAvatar}
                <span class="comment-username" data-user-id="${comment.user_id}">
                    ${comment.user_name || 'Unknown'}
                </span>
            </div>
            <div class="comment-actions">
                <div class="comment-date">${commentDate}</div>
                ${deleteButton}
            </div>
        </div>
        <div class="comment-content">${comment.content}</div>
    `;
    
    return commentDiv;
}
        
        // Update comment count
        function updateCommentCount(reportId, count) {
            document.querySelectorAll(`.comment-btn[data-report-id="${reportId}"] .comment-count`).forEach(el => {
                el.textContent = count;
            });
        }
        
        // Setup event listeners SETUP EVENT LISTENER
        function setupEventListeners() {
            
            function bringRouteLinesToFront() {
                routeLines.forEach(line => {
                    line.bringToFront();
                });
            }
            
            document.querySelector('[data-dismiss="edit"]').addEventListener('click', () => {
                editReportModal.style.display = 'none';
            });
            
            
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
            
            // Location button
            if (document.getElementById('locationBtn')) {
                document.getElementById('locationBtn').addEventListener('click', () => {
                    tryGeolocation();
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
                        reportSubmitted = false;
                        
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
            
            
            
            
            
            
            
            document.addEventListener('DOMContentLoaded', function() {
                init();
                setupReportActions();
                
                // Reinitialize routes to ensure they're clickable
                setTimeout(() => {
                    loadRoutes();
                    addCommentButtonToReports();
                }, 500);
            }); 
            
            
            
            
            
            
            
            // Load comment count for a report
            function loadCommentCount(reportId, buttonElement) {
                fetch(`get_comments.php?report_id=${reportId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const count = data.comments.length;
                        if (buttonElement) {
                            buttonElement.querySelector('.comment-count').textContent = count;
                        }
                        
                        // Also update any other instances of this report's comment count
                        document.querySelectorAll(`.comment-btn[data-report-id="${reportId}"] .comment-count`).forEach(el => {
                            el.textContent = count;
                        });
                    }
                })
                .catch(error => {
                    console.error('Error loading comment count:', error);
                });
            }
            
            
            
            // Handle comment form submission - REPLACE THE EXISTING CODE
            const commentForm = document.getElementById('commentForm');
            if (commentForm) {
                // Remove any existing event listeners first
                const newCommentForm = commentForm.cloneNode(true);
                commentForm.parentNode.replaceChild(newCommentForm, commentForm);
                
                // Add the event listener once
                document.getElementById('commentForm').addEventListener('submit', function(e) {
                    e.preventDefault();
                    submitComment();
                });
            }
            
            // Submit comment
            function submitComment() {
                const formData = new FormData(document.getElementById('commentForm'));
                const contentTextarea = document.getElementById('commentContent');
                
                if (!formData.get('content').trim()) {
                    alert('Komentar tidak boleh kosong');
                    return false; // Add return false to prevent further execution
                }
                
                const submitBtn = document.getElementById('commentSubmitBtn');
                submitBtn.disabled = true;
                submitBtn.textContent = 'Mengirim...';
                
                fetch('save_comment.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Add new comment to the list
                        const container = document.getElementById('commentsContainer');
                        if (container.innerHTML.includes('Belum ada komentar')) {
                            container.innerHTML = '';
                        }
                        
                        const commentElement = createCommentElement(data.comment);
                        container.insertBefore(commentElement, container.firstChild);
                        
                        // Clear the form
                        contentTextarea.value = '';
                        
                        // Update comment count
                        const reportId = formData.get('report_id');
                        loadCommentCount(reportId);
                    } else {
                        alert('Gagal mengirim komentar: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Terjadi kesalahan saat mengirim komentar');
                })
                .finally(() => {
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Kirim Komentar';
                });
                
                
            }
            
            // Close comment modal
            document.querySelector('[data-dismiss="comment"]').addEventListener('click', () => {
                commentModal.style.display = 'none';
            });
            
            // Close modal when clicking outside
            window.addEventListener('click', (e) => {
                if (e.target === commentModal) {
                    commentModal.style.display = 'none';
                }
            });
            
            // Close comment modal with escape key
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape' && commentModal.style.display === 'flex') {
                    commentModal.style.display = 'none';
                }
            });
            
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
        } //END OF SETUP EVENT LISTENER


        // Handle clicks on comment users
function setupCommentUserClicks() {
    document.addEventListener('click', function(e) {
        // Check if clicked on comment user or username
        const commentUser = e.target.closest('.comment-user');
        const commentUsername = e.target.closest('.comment-username');
        
        const targetElement = commentUser || commentUsername;
        
        if (targetElement) {
            e.preventDefault();
            e.stopPropagation();
            
            const userId = targetElement.getAttribute('data-user-id');
            if (userId) {
                // Close comment modal first
                if (commentModal) {
                    commentModal.style.display = 'none';
                }
                
                // Redirect to user profile
                window.location.href = `profile/index.php?id=${userId}`;
            }
        }
    });
}
        
        
        // Add this function to handle reporter name clicks
        function setupReporterClicks() {
    document.addEventListener('click', function(e) {
        const reporterElement = e.target.closest('.reporter-name');
        if (reporterElement) {
            e.preventDefault();
            e.stopPropagation();
            
            const userId = reporterElement.getAttribute('data-user-id');
            if (userId) {
                // Redirect to the profile page
                window.location.href = `profile/index.php?id=${userId}`;
            }
        }
        
        // Also handle user avatar clicks in route reports
        const userLink = e.target.closest('.user-link');
        if (userLink) {
            e.preventDefault();
            e.stopPropagation();
            
            const userId = userLink.getAttribute('data-user-id');
            if (userId) {
                window.location.href = `profile/index.php?id=${userId}`;
            }
        }
    });
}
        
        
        // Function to delete a comment
        function deleteComment(commentId, reportId) {
            if (!confirm('Apakah Anda yakin ingin menghapus komentar ini?')) {
                return;
            }
            
            const formData = new FormData();
            formData.append('comment_id', commentId);
            
            fetch('delete_comment.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Komentar berhasil dihapus!');
                    // Reload comments
                    loadComments(reportId);
                } else {
                    alert('Gagal menghapus komentar: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Terjadi kesalahan saat menghapus komentar');
            });
        }

        function setupReportActions() {
    // Remove any existing event listeners first
    document.removeEventListener('click', handleReportActions);
    
    // Add the event listener once
    document.addEventListener('click', handleReportActions);
}

function handleReportActions(e) {
    // Close all menus when clicking elsewhere
    if (!e.target.closest('.report-actions')) {
        document.querySelectorAll('.report-menu').forEach(menu => {
            menu.classList.remove('show');
        });
    }
    
    // Toggle menu when clicking the three dots
    if (e.target.closest('.report-menu-btn')) {
        const menuBtn = e.target.closest('.report-menu-btn');
        const menu = menuBtn.nextElementSibling;
        const isVisible = menu.classList.contains('show');
        
        // Close all other menus
        document.querySelectorAll('.report-menu').forEach(m => {
            m.classList.remove('show');
        });
        
        // Toggle this menu
        if (!isVisible) {
            menu.classList.add('show');
        }
        
        e.stopPropagation();
    }
    
    // Handle edit button click
    if (e.target.closest('.report-menu-item.edit')) {
        const menuItem = e.target.closest('.report-menu-item.edit');
        const reportId = menuItem.getAttribute('data-report-id');
        openEditReportModal(reportId);
        e.stopPropagation();
    }
    
    // Handle delete button click
    if (e.target.closest('.report-menu-item.delete')) {
        const menuItem = e.target.closest('.report-menu-item.delete');
        const reportId = menuItem.getAttribute('data-report-id');
        deleteReport(reportId);
        e.stopPropagation();
    }
}
        
        
        // Function to open edit modal
        function openEditReportModal(reportId) {
            // Find the report data
            let reportData = null;
            
            // Search in route reports
            if (selectedRoute && selectedRoute.reports) {
                reportData = selectedRoute.reports.find(r => r.id == reportId);
            }
            
            // If not found, search in all reports
            if (!reportData) {
                reportData = phpReports.find(r => r.id == reportId);
            }
            
            if (reportData) {
                document.getElementById('editReportId').value = reportData.id;
                document.getElementById('editReportType').value = reportData.type;
                document.getElementById('editReportDescription').value = reportData.description;
                editReportModal.style.display = 'flex';
            }
        }
        
        // Function to delete report
        let isDeleting = false;
        function deleteReport(reportId) {
    if (isDeleting) return;
    
    if (!confirm('Apakah Anda yakin ingin menghapus laporan ini?')) {
        return;
    }
    
    isDeleting = true;
    const formData = new FormData();
    formData.append('report_id', reportId);
    
    fetch('delete_report.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Laporan berhasil dihapus!');
            // Refresh the reports
            fetchRoutesFromServer();
            fetchReportsFromServer();
        } else {
            alert('Gagal menghapus laporan: ' + data.message);
        }
        isDeleting = false;
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Terjadi kesalahan saat menghapus laporan');
        isDeleting = false;
    });
}
        
        // Load routes from PHP data
        // Load routes from PHP data with actual road paths
        // Load routes from PHP data with enhanced error handling and property name compatibility
        function loadRoutes() {
            try {
                // Clear existing route lines
                routeLines.forEach(line => map.removeLayer(line));
                routeLines = [];
                
                // Validate routes data
                if (!phpRoutes || !Array.isArray(phpRoutes)) {
                    console.error('Invalid routes data:', phpRoutes);
                    return;
                }
                
                // Draw routes on map
                for (const route of phpRoutes) {
                    try {
                        // Validate route data
                        if (!route || typeof route !== 'object') {
                            console.warn('Skipping invalid route:', route);
                            continue;
                        }
                        
                        // Handle both property naming conventions
                        const startLat = route.start_lat || route.start_latitude;
                        const startLng = route.start_lng || route.start_longitude;
                        const endLat = route.end_lat || route.end_latitude;
                        const endLng = route.end_lng || route.end_longitude;
                        
                        // Check if we have valid coordinates
                        const hasValidStart = !isNaN(parseFloat(startLat)) && !isNaN(parseFloat(startLng));
                        const hasValidEnd = !isNaN(parseFloat(endLat)) && !isNaN(parseFloat(endLng));
                        
                        if (!hasValidStart || !hasValidEnd) {
                            console.warn('Skipping route with invalid coordinates:', route);
                            continue;
                        }
                        
                        let latLngs = [];
                        
                        // Check if we have a polyline stored in the database
                        if (route.polyline) {
                            try {
                                // Parse the polyline from database
                                const polylineData = JSON.parse(route.polyline);
                                if (polylineData && polylineData.coordinates && Array.isArray(polylineData.coordinates)) {
                                    // Convert GeoJSON format [lng, lat] to Leaflet format [lat, lng]
                                    latLngs = polylineData.coordinates.map(coord => {
                                        if (Array.isArray(coord) && coord.length >= 2) {
                                            return [coord[1], coord[0]]; // [lat, lng]
                                        }
                                        console.warn('Invalid coordinate in polyline:', coord);
                                        return null;
                                    }).filter(coord => coord !== null);
                                }
                            } catch (e) {
                                console.error('Error parsing polyline:', e, 'for route:', route);
                                // Fallback to straight line
                                latLngs = [
                                    [parseFloat(startLat), parseFloat(startLng)],
                                    [parseFloat(endLat), parseFloat(endLng)]
                                ];
                            }
                        } else {
                            // Fallback to straight line if no polyline
                            latLngs = [
                                [parseFloat(startLat), parseFloat(startLng)],
                                [parseFloat(endLat), parseFloat(endLng)]
                            ];
                        }
                        
                        // Ensure we have valid coordinates for the polyline
                        if (latLngs.length < 2) {
                            console.warn('Not enough valid coordinates for route:', route);
                            continue;
                        }
                        
                        // Create the polyline with proper event handling
                        const line = L.polyline(latLngs, {
                            color: getColorForReportType(route.reports && route.reports[0] ? route.reports[0].type : 'hazard'),
                            weight: 6,
                            opacity: 0.7,
                            className: 'route-line'
                        }).addTo(map);
                        
                        // Store reference to the line and route
                        line.routeId = route.id;
                        routeLines.push(line);
                        
                        // Add proper click event to show route reports
                        line.on('click', function(e) {
                            // Prevent event propagation to map
                            L.DomEvent.stopPropagation(e);
                            showRouteReports(route.id);
                            
                            // FIX: Bring the clicked route to front for better visibility
                            routeLines.forEach(l => map.removeLayer(l));
                            routeLines.forEach(l => map.addLayer(l));
                            this.bringToFront();
                        });
                        
                        // Also make the line bringable to front on hover for better UX
                        line.on('mouseover', function() {
                            this.bringToFront();
                        });
                        
                        // Add markers for start and end points with validation
                        try {
                            // L.marker([parseFloat(startLat), parseFloat(startLng)]).addTo(map)
                            // .bindPopup('Titik Awal: ' + (route.name || 'Rute #' + route.id));
                            
                            // L.marker([parseFloat(endLat), parseFloat(endLng)]).addTo(map)
                            // .bindPopup('Titik Akhir: ' + (route.name || 'Rute #' + route.id));
                        } catch (markerError) {
                            console.error('Error creating markers for route:', route, markerError);
                        }
                        
                    } catch (error) {
                        console.error('Error creating route:', error, 'Route data:', route);
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
                    fetchRoutesFromServer();
                    alert('Rute berhasil dibuat! Silakan tambahkan laporan untuk rute ini.');
                    routeModal.style.display = 'none';
                    
                    // Set the route ID for the report
                    document.getElementById('reportRouteId').value = data.route_id;
                    
                    // Refresh routes and reports
                    fetchRoutesFromServer();
                    fetchReportsFromServer();
                    
                    // Automatically open the report modal
                    reportModal.style.display = 'flex';
                    
                    // Reset and reload routes
                    cancelRouteCreation();
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
                    reportSubmitted = true;
                    alert('Laporan berhasil ditambahkan!');
                    reportModal.style.display = 'none';
                    
                    // Reset the form
                    reportForm.reset();
                    
                    // Reload routes and reports to show the new data
                    fetchRoutesFromServer();
                    fetchReportsFromServer();
                } else {
                    alert('Gagal menambahkan laporan: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Terjadi kesalahan saat menyimpan laporan');
            });
        }
        
        // Add function to fetch updated reports
        function fetchReportsFromServer() {
            fetch('get_reports.php')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update the global variable
                    phpReports = data.reports;
                    // Reload the reports list
                    loadReports();
                } else {
                    console.error('Failed to fetch reports:', data.message);
                }
            })
            .catch(error => {
                console.error('Error fetching reports:', error);
            });
        }
        
        // Get color for report type
        function getColorForReportType(type) {
            switch(type) {
                case 'crime': return '#ff5252';
                case 'accident': return '#ffeb3b';
                case 'hazard': return '#ff9800';
                case 'safe_spot': return '#4caf50';
                default: return '#3498db';
            }
        }
        
        // Update the route creation function to include the enhanced styling
        function createRoutePolyline(latLngs, type) {
            const isAccident = type === 'accident';
            const isHazard = type === 'hazard';
            
            return L.polyline(latLngs, {
                color: getColorForReportType(type),
                weight: 6,
                opacity: 0.9,
                className: 'route-line ' + type,
                dashArray: isAccident ? '5, 3' : isHazard ? '7, 2' : null
            });
        }
        
        // Show reports for a specific route
        // Show reports for a specific route
        function showRouteReports(routeId) {
            // Make sure we're searching in the right data source
            const route = phpRoutes.find(r => r.id == routeId);
            if (!route) {
                console.error('Route not found with ID:', routeId);
                return;
            }
            
            selectedRoute = route;
            
            // Update route info panel
            routeReportsList.innerHTML = '';
            routeNameTitle.textContent = route.name || 'Rute #' + route.id;
            
            // Show creator with avatar
            // In showRouteReports function, update avatar URLs:
            const creatorAvatar = route.creator_avatar ? 
            `<img src="../uploads/${route.creator_avatar.startsWith('http') ? route.creator_avatar : '../uploads/' + route.creator_avatar}?t=${new Date().getTime()}" alt="${route.creator_name}" class="user-avatar-small">` :
            `<div class="user-avatar-small">${route.creator_name ? route.creator_name.charAt(0).toUpperCase() : 'U'}</div>`;
            
            routeCreatorInfo.innerHTML = `Dibuat oleh: <span class="user-link" data-user-id="${route.created_by}">${creatorAvatar} ${route.creator_name || 'Unknown'}</span>`;
            
            // Update statistics
            updateRouteStats(route);
            
            if (!route.reports || route.reports.length === 0) {
                routeReportsList.innerHTML = '<div class="text-center py-3 text-muted">Belum ada laporan untuk rute ini</div>';
            } else {
                
                route.reports.forEach(report => {
                    
                    const userAvatar = report.user_avatar ? 
                    `<img src="../uploads/${report.user_avatar}?t=${new Date().getTime()}" alt="${report.user_name}" class="user-avatar-small">` :
                    `<div class="user-avatar-small">${report.user_name ? report.user_name.charAt(0).toUpperCase() : 'U'}</div>`;
                    
                    const likeActiveClass = report.user_vote === 1 ? 'active' : '';
                    const dislikeActiveClass = report.user_vote === -1 ? 'active' : '';
                    
                    const reportItem = document.createElement('div');
                    reportItem.className = 'report-card';
                    const isOwner = userLoggedIn && currentUser && currentUser.id == report.user_id;
                    
                    reportItem.innerHTML = `
    <div class="report-header">
        <span class="report-type ${report.type}">${getTypeLabel(report.type)}</span>
        <div style="display: flex; align-items: center; gap: 8px;">
            <span class="report-date">${formatDate(report.created_at)}</span>
            ${isOwner ? `
            <div class="report-actions">
                <button class="report-menu-btn">
                    <i class="fas fa-ellipsis-h"></i>
                </button>
                <div class="report-menu">
                    <button class="report-menu-item edit" data-report-id="${report.id}">
                        <i class="fas fa-edit"></i> Edit
                    </button>
                    <button class="report-menu-item delete" data-report-id="${report.id}">
                        <i class="fas fa-trash"></i> Hapus
                    </button>
                </div>
            </div>
            ` : ''}
        </div>
    </div>
    <p class="report-description">${report.description}</p>
    <div class="report-footer">
        <div class="report-user" data-user-id="${report.user_id}">
            <small>Oleh: ${userAvatar} <span class="reporter-name" data-user-id="${report.user_id}" style="cursor: pointer; color: var(--primary-color);">${report.user_name || 'Unknown'}</span></small>
        </div>
        <div class="like-dislike-container">
            <button class="like-btn ${likeActiveClass}" data-report-id="${report.id}">
                <i class="fas fa-thumbs-up"></i>
                <span class="like-count">${report.likes || 0}</span>
            </button>
            <button class="dislike-btn ${dislikeActiveClass}" data-report-id="${report.id}">
                <i class="fas fa-thumbs-down"></i>
                <span class="dislike-count">${report.dislikes || 0}</span>
            </button>
        </div>
    </div>
`;
                    
                    // Add like/dislike event listeners
                    const likeBtn = reportItem.querySelector('.like-btn');
                    const dislikeBtn = reportItem.querySelector('.dislike-btn');
                    
                    likeBtn.addEventListener('click', (e) => {
                        e.stopPropagation();
                        handleLike(report.id, 1, likeBtn, dislikeBtn);
                    });
                    
                    dislikeBtn.addEventListener('click', (e) => {
                        e.stopPropagation();
                        handleLike(report.id, -1, likeBtn, dislikeBtn);
                    });
                    
                    routeReportsList.appendChild(reportItem);
                });
                // Add comment buttons to route reports
                setTimeout(addCommentButtonToReports, 100);
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
            
            // Show/hide login prompt and add report button based on login status
            if (userLoggedIn) {
                addToRouteBtn.style.display = 'block';
                routeLoginPrompt.style.display = 'none';
            } else {
                addToRouteBtn.style.display = 'none';
                routeLoginPrompt.style.display = 'block';
            }
            
            // Position the panel near the route
            const midLat = (parseFloat(route.start_lat || route.start_latitude) + parseFloat(route.end_lat || route.end_latitude)) / 2;
            const midLng = (parseFloat(route.start_lng || route.start_longitude) + parseFloat(route.end_lng || route.end_longitude)) / 2;
            map.setView([midLat, midLng], 13);
        }
        
        // Update the user avatar in the navbar
        // Update the updateUserAvatar function
        function updateUserAvatar() {
            if (userLoggedIn && userData && userData.avatar) {
                const userAvatar = document.getElementById('userAvatar');
                // Add timestamp to prevent caching
                userAvatar.innerHTML = `<img src="../uploads/${userData.avatar}?t=${new Date().getTime()}" alt="${userData.name}" style="width: 100%; height: 100%; border-radius: 50%;">`;
            }
        }
        
        
        function handleLike(reportId, value, likeBtn, dislikeBtn) {
            if (!userLoggedIn) {
                alert('Silakan masuk terlebih dahulu untuk menyukai laporan.');
                return;
            }
            
            const formData = new FormData();
            formData.append('report_id', reportId);
            formData.append('value', value);
            
            fetch('handle_like.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                // First check if the response is JSON
                const contentType = response.headers.get("content-type");
                if (contentType && contentType.includes("application/json")) {
                    return response.json();
                } else {
                    // Handle non-JSON responses (like HTML error pages)
                    return response.text().then(text => {
                        throw new Error('Server returned non-JSON response: ' + text);
                    });
                }
            })
            .then(data => {
                if (data.success) {
                    // Update UI with new like counts
                    const likeCountEl = likeBtn.querySelector('.like-count');
                    const dislikeCountEl = dislikeBtn.querySelector('.dislike-count');
                    
                    likeCountEl.textContent = data.likes || 0;
                    dislikeCountEl.textContent = data.dislikes || 0;
                    
                    // Update active states
                    if (data.user_vote === 1) {
                        likeBtn.classList.add('active');
                        dislikeBtn.classList.remove('active');
                    } else if (data.user_vote === -1) {
                        likeBtn.classList.remove('active');
                        dislikeBtn.classList.add('active');
                    } else {
                        likeBtn.classList.remove('active');
                        dislikeBtn.classList.remove('active');
                    }
                    
                    // Also update the report in the sidebar if it exists
                    updateReportInSidebar(reportId, data.likes, data.dislikes, data.user_vote);
                } else {
                    alert('Terjadi kesalahan: ' + (data.message || 'Unknown error'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Terjadi kesalahan saat memproses permintaan.');
            });
        }
        
        // Add this function to update reports in the sidebar
        function updateReportInSidebar(reportId, likes, dislikes, userVote) {
            const reportCards = document.querySelectorAll('.report-card');
            reportCards.forEach(card => {
                const reportIdElem = card.querySelector('[data-report-id]');
                if (reportIdElem && reportIdElem.getAttribute('data-report-id') == reportId) {
                    const likeBtn = card.querySelector('.like-btn');
                    const dislikeBtn = card.querySelector('.dislike-btn');
                    const likeCount = card.querySelector('.like-count');
                    const dislikeCount = card.querySelector('.dislike-count');
                    
                    likeCount.textContent = likes;
                    dislikeCount.textContent = dislikes;
                    
                    // Update active states
                    likeBtn.classList.remove('active');
                    dislikeBtn.classList.remove('active');
                    
                    if (userVote === 1) {
                        likeBtn.classList.add('active');
                    } else if (userVote === -1) {
                        dislikeBtn.classList.add('active');
                    }
                }
            });
        }
        
        if (document.getElementById('editReportForm')) {
            document.getElementById('editReportForm').addEventListener('submit', function(e) {
                e.preventDefault();
                updateReport();
            });
        }
        
        // Function to update report
        function updateReport() {
            const formData = new FormData(document.getElementById('editReportForm'));
            
            fetch('update_report.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Laporan berhasil diperbarui!');
                    editReportModal.style.display = 'none';
                    
                    // Refresh routes and reports
                    fetchRoutesFromServer();
                    fetchReportsFromServer();
                } else {
                    alert('Gagal memperbarui laporan: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Terjadi kesalahan saat memperbarui laporan');
            });
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
            
            // Hide the map controls container
            document.querySelector('.map-controls').style.display = 'none';
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
            
            // Show the map controls container
            document.querySelector('.map-controls').style.display = 'flex';
            
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
                        setTimeout(() => {
                            setupReportActions();
                            addCommentButtonToReports(); // Add this line
                        }, 100);
                    }
                }
                
                // Add report to DOM
                function addReportToDOM(report) {
                    const likeActiveClass = report.user_vote == 1 ? 'active' : '';
                    const dislikeActiveClass = report.user_vote == -1 ? 'active' : '';
                    
                    // Check if current user is the report owner
                    const isOwner = userLoggedIn && currentUser && currentUser.id == report.user_id;
                    
                    // Format edited text if report was edited
                    const editedText = report.edited_at ? 
                    `<div class="report-edited">Diedit pada: ${formatDate(report.edited_at)}</div>` : '';
                    
                    const reportCard = document.createElement('div');
reportCard.className = 'report-card';
reportCard.innerHTML = `
    <div class="report-header">
        <span class="report-type ${report.type}">${getTypeLabel(report.type)}</span>
        <div style="display: flex; align-items: center; gap: 8px;">
            <span class="report-date">${formatDate(report.created_at)}</span>
            ${isOwner ? `
            <div class="report-actions">
                <button class="report-menu-btn">
                    <i class="fas fa-ellipsis-h"></i>
                </button>
                <div class="report-menu">
                    <button class="report-menu-item edit" data-report-id="${report.id}">
                        <i class="fas fa-edit"></i> Edit
                    </button>
                    <button class="report-menu-item delete" data-report-id="${report.id}">
                        <i class="fas fa-trash"></i> Hapus
                    </button>
                </div>
            </div>
            ` : ''}
        </div>
    </div>
    <p class="report-description">${report.description}</p>
    ${editedText}
    <div class="report-footer">
        <div class="report-user">
            <small>Oleh: <span class="reporter-name" data-user-id="${report.user_id}" style="cursor: pointer; color: var(--primary-color);">${report.user_name || 'Unknown'}</span> • ${report.route_name || 'Rute #' + report.route_id}</small>
        </div>
        <div class="like-dislike-container">
            <button class="like-btn ${likeActiveClass}" data-report-id="${report.id}">
                <i class="fas fa-thumbs-up"></i>
                <span class="like-count">${report.likes || 0}</span>
            </button>
            <button class="dislike-btn ${dislikeActiveClass}" data-report-id="${report.id}">
                <i class="fas fa-thumbs-down"></i>
                <span class="dislike-count">${report.dislikes || 0}</span>
            </button>
        </div>
    </div>
`;
                    
                    // Add click event to focus on the route
                    reportCard.addEventListener('click', (e) => {
                        // Don't trigger route focus if clicking like/dislike buttons
                        if (!e.target.closest('.like-btn') && !e.target.closest('.dislike-btn')) {
                            showRouteReports(report.route_id);
                        }
                    });
                    
                    // Add like/dislike event listeners
                    const likeBtn = reportCard.querySelector('.like-btn');
                    const dislikeBtn = reportCard.querySelector('.dislike-btn');
                    
                    likeBtn.addEventListener('click', (e) => {
                        e.stopPropagation();
                        handleLike(report.id, 1, likeBtn, dislikeBtn);
                    });
                    
                    dislikeBtn.addEventListener('click', (e) => {
                        e.stopPropagation();
                        handleLike(report.id, -1, likeBtn, dislikeBtn);
                    });
                    
                    reportsList.appendChild(reportCard);
                    
                    // Add comment button to this report card
                    setTimeout(() => {
                        const reportId = report.id;
                        const card = reportCard;
                        const footer = card.querySelector('.report-footer');
                        if (footer && !footer.querySelector('.comment-btn')) {
                            const commentBtn = document.createElement('button');
                            commentBtn.className = 'comment-btn';
                            commentBtn.innerHTML = '<i class="far fa-comment"></i> <span class="comment-count">0</span>';
                            commentBtn.setAttribute('data-report-id', reportId);
                            commentBtn.addEventListener('click', (e) => {
                                e.stopPropagation();
                                openCommentModal(reportId);
                            });
                            
                            // Add to like-dislike container or create new container
                            const likeContainer = card.querySelector('.like-dislike-container');
                            if (likeContainer) {
                                likeContainer.appendChild(commentBtn);
                            } else {
                                footer.appendChild(commentBtn);
                            }
                            
                            // Load initial comment count
                            loadCommentCount(reportId, commentBtn);
                        }
                    }, 100);
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
                        // First try to get current position quickly
                        navigator.geolocation.getCurrentPosition(
                            (position) => {
                                centerMapOnUser(position);
                            },
                            (error) => {
                                console.log('Geolocation error:', error);
                                // If quick location fails, try with high accuracy
                                tryHighAccuracyGeolocation();
                            },
                            {
                                enableHighAccuracy: false,
                                timeout: 5000,
                                maximumAge: 300000 // 5 minutes
                            }
                        );
                    } else {
                        alert('Geolocation tidak didukung oleh browser Anda.');
                    }
                }
                
                function tryHighAccuracyGeolocation() {
                    if (navigator.geolocation) {
                        navigator.geolocation.getCurrentPosition(
                            (position) => {
                                centerMapOnUser(position);
                            },
                            (error) => {
                                console.log('High accuracy geolocation error:', error);
                                showGeolocationError(error);
                            },
                            {
                                enableHighAccuracy: true,
                                timeout: 10000,
                                maximumAge: 0
                            }
                        );
                    }
                }
                
                // Show geolocation error message
                function showGeolocationError(error) {
                    let errorMessage;
                    switch(error.code) {
                        case error.PERMISSION_DENIED:
                            errorMessage = "Akses lokasi ditolak. Silakan izinkan akses lokasi di pengaturan browser Anda.";
                            break;
                            case error.POSITION_UNAVAILABLE:
                                errorMessage = "Informasi lokasi tidak tersedia.";
                                break;
                                case error.TIMEOUT:
                                    errorMessage = "Permintaan lokasi waktu habis. Silakan coba lagi.";
                                    break;
                                    default:
                                    errorMessage = "Terjadi kesalahan tidak diketahui saat mengambil lokasi.";
                                }
                                
                                // Show error as a temporary notification
                                const errorDiv = document.createElement('div');
                                errorDiv.style.cssText = `
        position: fixed;
        top: 100px;
        left: 50%;
        transform: translateX(-50%);
        background-color: #ff6b6b;
        color: white;
        padding: 1rem;
        border-radius: 4px;
        z-index: 2000;
        max-width: 80%;
        text-align: center;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    `;
                                errorDiv.textContent = errorMessage;
                                document.body.appendChild(errorDiv);
                                
                                // Remove error message after 5 seconds
                                setTimeout(() => {
                                    if (document.body.contains(errorDiv)) {
                                        document.body.removeChild(errorDiv);
                                    }
                                }, 5000);
                            }
                            
                            // Add event listener for the location button
                            function setupLocationButton() {
                                const locationBtn = document.getElementById('locationBtn');
                                if (locationBtn) {
                                    locationBtn.addEventListener('click', () => {
                                        tryGeolocation();
                                    });
                                }
                            }
                            
                            const logoutBtn = document.getElementById('logoutBtn');
                            if (logoutBtn) {
                                logoutBtn.addEventListener('click', function(e) {
                                    e.preventDefault();
                                    const logoutUrl = this.href;
                                    
                                    if (confirm('Apakah Anda yakin ingin keluar?')) {
                                        window.location.href = logoutUrl;
                                    }
                                });
                            }
                            
                            
                            
                            // Center map on user's location
                            function centerMapOnUser(position) {
                                const userLat = position.coords.latitude;
                                const userLng = position.coords.longitude;
                                const accuracy = position.coords.accuracy;
                                
                                // Remove existing user marker if any
                                if (userLocationMarker) {
                                    map.removeLayer(userLocationMarker);
                                }
                                if (userLocationCircle) {
                                    map.removeLayer(userLocationCircle);
                                }
                                
                                // Add accuracy circle
                                userLocationCircle = L.circle([userLat, userLng], {
                                    radius: accuracy,
                                    color: '#4285F4',
                                    fillColor: '#4285F4',
                                    fillOpacity: 0.2,
                                    weight: 1
                                }).addTo(map);
                                
                                // Add user marker
                                userLocationMarker = L.marker([userLat, userLng], {
                                    icon: L.divIcon({
                                        className: 'user-location-marker',
                                    })
                                }).addTo(map).bindPopup('Lokasi Anda Saat Ini').openPopup();
                                
                                // Set map view to user's location
                                map.setView([userLat, userLng], 16);
                                
                                // Start watching position if not already watching
                                if (!watchId) {
                                    startWatchingPosition();
                                }
                            }
                            
                            
                            // Start watching user's position
                            function startWatchingPosition() {
                                if (navigator.geolocation) {
                                    watchId = navigator.geolocation.watchPosition(
                                        (position) => {
                                            const userLat = position.coords.latitude;
                                            const userLng = position.coords.longitude;
                                            const accuracy = position.coords.accuracy;
                                            
                                            // Update user marker position
                                            if (userLocationMarker) {
                                                userLocationMarker.setLatLng([userLat, userLng]);
                                            }
                                            
                                            // Update accuracy circle
                                            if (userLocationCircle) {
                                                userLocationCircle.setLatLng([userLat, userLng]);
                                                userLocationCircle.setRadius(accuracy);
                                            }
                                        },
                                        (error) => {
                                            console.log('Watch position error:', error);
                                        },
                                        {
                                            enableHighAccuracy: true,
                                            timeout: 10000,
                                            maximumAge: 0
                                        }
                                    );
                                }
                            }
                            
                            // Stop watching position
                            function stopWatchingPosition() {
                                if (watchId !== null) {
                                    navigator.geolocation.clearWatch(watchId);
                                    watchId = null;
                                }
                            }
                            
                            
                            
                            // Update the user avatar in the navbar
                            function updateUserAvatar() {
                                if (userLoggedIn && userData && userData.avatar) {
                                    const userAvatar = document.getElementById('userAvatar');
                                    userAvatar.innerHTML = `<img src="../uploads/${userData.avatar}" alt="${userData.name}" style="width: 100%; height: 100%; border-radius: 50%;">`;
                                }
                            }
                            
                            
                            function showUserProfile(userId) {
                                window.location.href = `profile/index.php?id=${userId}`;
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
                            
                            
                            // Clean up geolocation when page is unloaded
                            window.addEventListener('beforeunload', () => {
                                stopWatchingPosition();
                            });
                            
                            // Also clean up when user navigates away
                            window.addEventListener('pagehide', () => {
                                stopWatchingPosition();
                            });
                            
                            
                            // Make user avatar clickable
                            function setupAvatarClick() {
                                const userAvatar = document.getElementById('userAvatar');
                                const userAvatarLink = document.querySelector('.user-avatar-link');
                                
                                if (userAvatar && userLoggedIn) {
                                    // Add click event to the avatar
                                    userAvatar.addEventListener('click', (e) => {
                                        e.preventDefault();
                                        window.location.href = 'profile/index.php';
                                    });
                                    
                                    // Also make the username clickable
                                    const userName = document.getElementById('userName');
                                    if (userName) {
                                        userName.style.cursor = 'pointer';
                                        userName.addEventListener('click', (e) => {
                                            e.preventDefault();
                                            window.location.href = 'profile/index.php';
                                        });
                                        
                                        // Add hover effect to username
                                        userName.addEventListener('mouseenter', () => {
                                            userName.style.color = 'var(--primary-color)';
                                            userName.style.textDecoration = 'underline';
                                        });
                                        
                                        userName.addEventListener('mouseleave', () => {
                                            userName.style.color = 'black';
                                            userName.style.textDecoration = 'none';
                                        });
                                    }
                                }
                            }
                            
                            
                            
                            // Initialize the app
                            init();
                            </script>
                            </body>
                            </html>