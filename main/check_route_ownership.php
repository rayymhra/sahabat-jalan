<?php
session_start();
include_once '../connection.php';

if (!isset($_SESSION['user_id']) || !isset($_GET['route_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$route_id = $_GET['route_id'];
$user_id = $_SESSION['user_id'];

$query = "SELECT created_by FROM routes WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $route_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $route = $result->fetch_assoc();
    echo json_encode(['success' => true, 'is_owner' => ($route['created_by'] == $user_id)]);
} else {
    echo json_encode(['success' => false, 'message' => 'Route not found']);
}
?>