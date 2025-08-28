<?php
session_start();
include_once '../connection.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Silakan login terlebih dahulu']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $report_id = $_POST['report_id'];
    $type = $_POST['type'];
    $description = $_POST['description'];
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
        echo json_encode(['success' => false, 'message' => 'Anda tidak memiliki izin untuk mengedit laporan ini']);
        exit;
    }
    
    // Update the report
    $updateQuery = "UPDATE reports SET type = ?, description = ?, edited_at = NOW(), edit_count = edit_count + 1 WHERE id = ?";
    $stmt = $conn->prepare($updateQuery);
    $stmt->bind_param("ssi", $type, $description, $report_id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Laporan berhasil diperbarui']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Gagal memperbarui laporan: ' . $conn->error]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Metode request tidak valid']);
}
?>