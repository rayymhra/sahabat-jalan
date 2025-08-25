<?php
session_start();
include_once 'connection.php';

if (!isset($_SESSION['user_id'])) {
    die(json_encode(['success' => false, 'message' => 'Silakan login terlebih dahulu']));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = $_SESSION['user_id'];
    $startLat = floatval($_POST['start_lat']);
    $startLng = floatval($_POST['start_lng']);
    $endLat = floatval($_POST['end_lat']);
    $endLng = floatval($_POST['end_lng']);
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';

    // Get route polyline from OSRM
    $osrmUrl = "http://router.project-osrm.org/route/v1/driving/$startLng,$startLat;$endLng,$endLat?overview=full&geometries=geojson";
    $osrmResponse = file_get_contents($osrmUrl);
    $osrmData = json_decode($osrmResponse, true);
    
    if ($osrmData && $osrmData['code'] === 'Ok' && !empty($osrmData['routes'])) {
        $polyline = json_encode($osrmData['routes'][0]['geometry']);
        
        // Save to database
        $stmt = $conn->prepare("INSERT INTO routes (created_by, start_latitude, start_longitude, end_latitude, end_longitude, name, polyline) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("iddddss", $userId, $startLat, $startLng, $endLat, $endLng, $name, $polyline);
        
        if ($stmt->execute()) {
            $routeId = $stmt->insert_id;
            echo json_encode(['success' => true, 'route_id' => $routeId, 'message' => 'Rute berhasil dibuat']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Gagal menyimpan rute: ' . $stmt->error]);
        }
        $stmt->close();
    } else {
        echo json_encode(['success' => false, 'message' => 'Gagal mendapatkan rute dari layanan routing']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Metode request tidak valid']);
}
?>