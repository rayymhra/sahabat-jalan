<?php
session_start();
include_once '../connection.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die('Invalid request method');
}

if (!isset($_SESSION['user_id'])) {
    die('Please login to submit reports');
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
    die('Route not found');
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
        die('Invalid file type. Only JPG, PNG and GIF are allowed.');
    }
    
    if ($_FILES['photo']['size'] > 2097152) { // 2MB
        die('File too large. Maximum size is 2MB.');
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
    echo json_encode(['success' => true, 'message' => 'Report submitted successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Error submitting report: ' . $stmt->error]);
}
?>