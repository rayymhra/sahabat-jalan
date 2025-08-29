<?php
session_start();
include_once '../connection.php';

if (!isset($_SESSION['user_id']) || !isset($_POST['route_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$route_id = $_POST['route_id'];
$user_id = $_SESSION['user_id'];

// Check if user owns the route
$query = "SELECT created_by FROM routes WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $route_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Route not found']);
    exit;
}

$route = $result->fetch_assoc();
if ($route['created_by'] != $user_id) {
    echo json_encode(['success' => false, 'message' => 'You can only delete your own routes']);
    exit;
}

// Begin transaction
$conn->begin_transaction();

try {
    // Delete all reports associated with this route
    $delete_reports = $conn->prepare("DELETE FROM reports WHERE route_id = ?");
    $delete_reports->bind_param("i", $route_id);
    $delete_reports->execute();
    
    // Delete the route
    $delete_route = $conn->prepare("DELETE FROM routes WHERE id = ?");
    $delete_route->bind_param("i", $route_id);
    $delete_route->execute();
    
    // Commit transaction
    $conn->commit();
    
    echo json_encode(['success' => true, 'message' => 'Route and associated reports deleted successfully']);
} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => 'Error deleting route: ' . $e->getMessage()]);
}
?>