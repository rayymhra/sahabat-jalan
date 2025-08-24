<?php
session_start();
include "connection.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../index.php?error=invalid_request");
    exit;
}

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php?error=unauthorized");
    exit;
}

// Get form data
$lat = floatval($_POST['lat']);
$lng = floatval($_POST['lng']);
$type = trim($_POST['type']);
$description = trim($_POST['description']);
$route_id = isset($_POST['route_id']) ? intval($_POST['route_id']) : null;

// Validate input
if (empty($type) || empty($description)) {
    header("Location: ../index.php?error=missing_fields");
    exit;
}

// Validate report type
$valid_types = ['crime', 'accident', 'hazard', 'safe_spot'];
if (!in_array($type, $valid_types)) {
    header("Location: ../index.php?error=invalid_type");
    exit;
}

try {
    // Insert report
    $query = "INSERT INTO reports 
              (user_id, route_id, latitude, longitude, type, description, created_at, updated_at) 
              VALUES 
              (?, ?, ?, ?, ?, ?, NOW(), NOW())";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("iiddss", $_SESSION['user_id'], $route_id, $lat, $lng, $type, $description);
    
    if ($stmt->execute()) {
        header("Location: ../index.php?success=report_added");
    } else {
        header("Location: ../index.php?error=db_error");
    }
    
    $stmt->close();
} catch (Exception $e) {
    error_log("Save report error: " . $e->getMessage());
    header("Location: ../index.php?error=server_error");
}

$conn->close();
exit;
?>