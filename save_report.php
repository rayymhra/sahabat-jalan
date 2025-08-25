<?php
session_start();
include_once 'connection.php';

if (!isset($_SESSION['user_id'])) {
    die(json_encode(['success' => false, 'message' => 'Silakan login terlebih dahulu']));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = $_SESSION['user_id'];
    $routeId = intval($_POST['route_id']);
    $type = $_POST['type'];
    $description = trim($_POST['description']);
    
    // Get midpoint of the route for report location
    $routeQuery = $conn->prepare("SELECT start_latitude, start_longitude, end_latitude, end_longitude FROM routes WHERE id = ?");
    $routeQuery->bind_param("i", $routeId);
    $routeQuery->execute();
    $routeResult = $routeQuery->get_result();
    
    if ($routeResult->num_rows > 0) {
        $route = $routeResult->fetch_assoc();
        $lat = ($route['start_latitude'] + $route['end_latitude']) / 2;
        $lng = ($route['start_longitude'] + $route['end_longitude']) / 2;
        
        $stmt = $conn->prepare("INSERT INTO reports (user_id, route_id, latitude, longitude, type, description) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("iiddss", $userId, $routeId, $lat, $lng, $type, $description);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Laporan berhasil ditambahkan']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Gagal menyimpan laporan: ' . $stmt->error]);
        }
        $stmt->close();
    } else {
        echo json_encode(['success' => false, 'message' => 'Rute tidak ditemukan']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Metode request tidak valid']);
}
?>