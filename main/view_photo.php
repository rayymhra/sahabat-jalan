<?php
session_start();
include_once '../connection.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die('Invalid request');
}

$reportId = intval($_GET['id']);

// Get photo information
$stmt = $conn->prepare("
    SELECT photo_url, file_name, mime_type, user_id 
    FROM reports 
    WHERE id = ? AND photo_url IS NOT NULL
");
$stmt->bind_param("i", $reportId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die('Photo not found');
}

$photo = $result->fetch_assoc();

// Check if user has permission to view (optional)
// You might want to implement additional access controls

// Serve the image
$filePath = '../uploads/' . $photo['photo_url'];
if (file_exists($filePath)) {
    header('Content-Type: ' . $photo['mime_type']);
    header('Content-Disposition: inline; filename="' . $photo['file_name'] . '"');
    readfile($filePath);
} else {
    die('File not found');
}
?>