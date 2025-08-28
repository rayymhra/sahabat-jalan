<?php
session_start();
include_once '../connection.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Silakan masuk terlebih dahulu']);
    exit;
}

if (!isset($_POST['comment_id'])) {
    echo json_encode(['success' => false, 'message' => 'ID komentar tidak valid']);
    exit;
}

$comment_id = $_POST['comment_id'];
$user_id = $_SESSION['user_id'];

// Check if comment exists and belongs to the user
$check_query = "SELECT * FROM comments WHERE id = ? AND user_id = ?";
$check_stmt = $conn->prepare($check_query);
$check_stmt->bind_param("ii", $comment_id, $user_id);
$check_stmt->execute();
$result = $check_stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Komentar tidak ditemukan atau Anda tidak memiliki izin']);
    exit;
}

// Delete the comment
$delete_query = "DELETE FROM comments WHERE id = ?";
$delete_stmt = $conn->prepare($delete_query);
$delete_stmt->bind_param("i", $comment_id);

if ($delete_stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Komentar berhasil dihapus']);
} else {
    echo json_encode(['success' => false, 'message' => 'Gagal menghapus komentar']);
}

$delete_stmt->close();
$conn->close();
?>