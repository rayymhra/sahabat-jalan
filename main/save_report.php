<?php
session_start();
include_once '../connection.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Please login to submit reports']);
    exit;
}

$userId = $_SESSION['user_id'];
$routeId = intval($_POST['route_id']);
$type = $_POST['type'];
$description = $_POST['description'];

// Get route information to calculate midpoint for latitude/longitude
$routeStmt = $conn->prepare("
    SELECT start_latitude, start_longitude, end_latitude, end_longitude 
    FROM routes 
    WHERE id = ?
");
$routeStmt->bind_param("i", $routeId);
$routeStmt->execute();
$routeResult = $routeStmt->get_result();

if ($routeResult->num_rows === 0) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Route not found']);
    exit;
}

$route = $routeResult->fetch_assoc();

// Calculate midpoint of the route for the report location
$latitude = ($route['start_latitude'] + $route['end_latitude']) / 2;
$longitude = ($route['start_longitude'] + $route['end_longitude']) / 2;

// Handle file upload
$photoUrl = null;
$fileName = null;
$mimeType = null;

if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
    $fileType = $_FILES['photo']['type'];
    
    if (!in_array($fileType, $allowedTypes)) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Invalid file type. Only JPG, PNG and GIF are allowed.']);
        exit;
    }
    
    if ($_FILES['photo']['size'] > 2097152) { // 2MB
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'File too large. Maximum size is 2MB.']);
        exit;
    }
    
    // Generate unique filename
    $extension = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
    $fileName = uniqid() . '_' . time() . '.' . $extension;
    $uploadPath = '../uploads/' . $fileName;
    
    if (move_uploaded_file($_FILES['photo']['tmp_name'], $uploadPath)) {
        $photoUrl = $fileName;
        $fileName = $_FILES['photo']['name'];
        $mimeType = $_FILES['photo']['type'];
    }
}

// Save to database
$stmt = $conn->prepare("
    INSERT INTO reports (user_id, route_id, type, description, latitude, longitude, photo_url, file_name, mime_type) 
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
");
$stmt->bind_param("iissddsss", $userId, $routeId, $type, $description, $latitude, $longitude, $photoUrl, $fileName, $mimeType);

if ($stmt->execute()) {
    $newReportId = $stmt->insert_id;
    
    // Fetch the complete report data with user information
    $reportQuery = $conn->prepare("
        SELECT r.*, u.name AS user_name, u.avatar AS user_avatar 
        FROM reports r 
        LEFT JOIN users u ON r.user_id = u.id 
        WHERE r.id = ?
    ");
    $reportQuery->bind_param("i", $newReportId);
    $reportQuery->execute();
    $reportResult = $reportQuery->get_result();
    
    if ($reportResult->num_rows > 0) {
        $newReport = $reportResult->fetch_assoc();
        
        // Add like information (default values)
        $newReport['likes'] = 0;
        $newReport['dislikes'] = 0;
        $newReport['user_vote'] = 0;
        $newReport['comment_count'] = 0;
        
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'message' => 'Laporan berhasil ditambahkan',
            'report' => $newReport
        ]);
    } else {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'Laporan berhasil disimpan tetapi tidak dapat mengambil data'
        ]);
    }
} else {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Gagal menambahkan laporan: ' . $conn->error
    ]);
}

$stmt->close();
$conn->close();
?>