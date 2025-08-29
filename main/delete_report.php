<?php
session_start();
include_once '../connection.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if (!isset($_POST['report_id'])) {
    echo json_encode(['success' => false, 'message' => 'Report ID required']);
    exit;
}

$report_id = intval($_POST['report_id']);
$user_id = $_SESSION['user_id'];

// Check if user owns the report or is admin
$check_sql = "SELECT user_id FROM reports WHERE id = ?";
$check_stmt = $conn->prepare($check_sql);
$check_stmt->bind_param("i", $report_id);
$check_stmt->execute();
$check_result = $check_stmt->get_result();

if ($check_result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Laporan tidak ditemukan']);
    exit;
}

$report = $check_result->fetch_assoc();

// Allow deletion if user is owner or admin
if ($report['user_id'] != $user_id && $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Delete related data first (likes, comments)
$conn->begin_transaction();

try {
    // Delete likes
    $delete_likes = $conn->prepare("DELETE FROM likes WHERE report_id = ?");
    $delete_likes->bind_param("i", $report_id);
    $delete_likes->execute();
    
    // Delete comments
    $delete_comments = $conn->prepare("DELETE FROM comments WHERE report_id = ?");
    $delete_comments->bind_param("i", $report_id);
    $delete_comments->execute();
    
    // Delete report
    $delete_report = $conn->prepare("DELETE FROM reports WHERE id = ?");
    $delete_report->bind_param("i", $report_id);
    $delete_report->execute();
    
    $conn->commit();
    echo json_encode(['success' => true, 'message' => 'Laporan berhasil dihapus']);
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>