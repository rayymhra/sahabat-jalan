<?php
session_start();
include_once '../connection.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Silakan masuk terlebih dahulu.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Metode request tidak valid.']);
    exit;
}

$route_id = $_POST['route_id'] ?? null;
$route_name = trim($_POST['route_name'] ?? '');

if (!$route_id) {
    echo json_encode(['success' => false, 'message' => 'ID rute tidak valid.']);
    exit;
}

// Check if user owns this route
$query = "SELECT created_by FROM routes WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $route_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Rute tidak ditemukan.']);
    exit;
}

$route = $result->fetch_assoc();
if ($route['created_by'] != $_SESSION['user_id']) {
    echo json_encode(['success' => false, 'message' => 'Anda tidak memiliki izin untuk mengedit rute ini.']);
    exit;
}

// Update route name
$query = "UPDATE routes SET name = ? WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("si", $route_name, $route_id);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Nama rute berhasil diperbarui.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Terjadi kesalahan saat memperbarui nama rute.']);
}

$stmt->close();
?>