<?php
session_start();
include_once '../connection.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Silakan login terlebih dahulu']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $report_id = $_POST['report_id'];
    $user_id = $_SESSION['user_id'];
    
    // Check if user owns the report
    $checkQuery = "SELECT user_id FROM reports WHERE id = ?";
    $stmt = $conn->prepare($checkQuery);
    $stmt->bind_param("i", $report_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Laporan tidak ditemukan']);
        exit;
    }
    
    $report = $result->fetch_assoc();
    if ($report['user_id'] != $user_id) {
        echo json_encode(['success' => false, 'message' => 'Anda tidak memiliki izin untuk menghapus laporan ini']);
        exit;
    }
    
    // Delete the report
    $deleteQuery = "DELETE FROM reports WHERE id = ?";
    $stmt = $conn->prepare($deleteQuery);
    $stmt->bind_param("i", $report_id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Laporan berhasil dihapus']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Gagal menghapus laporan: ' . $conn->error]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Metode request tidak valid']);
}
?>