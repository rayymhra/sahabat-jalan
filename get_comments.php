<?php
session_start();
include_once 'connection.php';

if (!isset($_GET['report_id'])) {
    echo json_encode(['success' => false, 'message' => 'Report ID not provided']);
    exit;
}

$report_id = intval($_GET['report_id']);
$comments = [];

$query = "SELECT c.*, u.name as user_name, u.avatar as user_avatar 
          FROM comments c 
          LEFT JOIN users u ON c.user_id = u.id 
          WHERE c.report_id = ? 
          ORDER BY c.created_at DESC";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $report_id);
$stmt->execute();
$result = $stmt->get_result();

while ($comment = $result->fetch_assoc()) {
    $comments[] = $comment;
}

echo json_encode(['success' => true, 'comments' => $comments]);
?>