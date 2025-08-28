<?php
session_start();
include_once 'connection.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login to comment']);
    exit;
}

if (!isset($_POST['report_id']) || !isset($_POST['content']) || empty(trim($_POST['content']))) {
    echo json_encode(['success' => false, 'message' => 'Invalid data']);
    exit;
}

$report_id = intval($_POST['report_id']);
$user_id = $_SESSION['user_id'];
$content = trim($_POST['content']);

$query = "INSERT INTO comments (report_id, user_id, content, created_at) 
          VALUES (?, ?, ?, NOW())";
$stmt = $conn->prepare($query);
$stmt->bind_param("iis", $report_id, $user_id, $content);

if ($stmt->execute()) {
    // Get the newly created comment with user info
    $new_comment_id = $stmt->insert_id;
    $comment_query = "SELECT c.*, u.name as user_name, u.avatar as user_avatar 
                     FROM comments c 
                     LEFT JOIN users u ON c.user_id = u.id 
                     WHERE c.id = ?";
    $comment_stmt = $conn->prepare($comment_query);
    $comment_stmt->bind_param("i", $new_comment_id);
    $comment_stmt->execute();
    $comment_result = $comment_stmt->get_result();
    $new_comment = $comment_result->fetch_assoc();
    
    echo json_encode(['success' => true, 'comment' => $new_comment]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to save comment']);
}
?>