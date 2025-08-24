<?php
include "connection.php";

header('Content-Type: application/json');

$type_filter = isset($_GET['type']) && $_GET['type'] !== 'all' ? $_GET['type'] : null;

try {
    if ($type_filter) {
        $query = "SELECT r.*, u.username, 
                         COUNT(DISTINCT l.id) as like_count,
                         COUNT(DISTINCT c.id) as comment_count
                  FROM reports r 
                  LEFT JOIN users u ON r.user_id = u.id 
                  LEFT JOIN likes l ON r.id = l.report_id AND l.value = 1 
                  LEFT JOIN comments c ON r.id = c.report_id
                  WHERE r.type = ? 
                  GROUP BY r.id 
                  ORDER BY r.created_at DESC";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $type_filter);
    } else {
        $query = "SELECT r.*, u.username, 
                         COUNT(DISTINCT l.id) as like_count,
                         COUNT(DISTINCT c.id) as comment_count
                  FROM reports r 
                  LEFT JOIN users u ON r.user_id = u.id 
                  LEFT JOIN likes l ON r.id = l.report_id AND l.value = 1 
                  LEFT JOIN comments c ON r.id = c.report_id
                  GROUP BY r.id 
                  ORDER BY r.created_at DESC";
        $stmt = $conn->prepare($query);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    $reports = $result->fetch_all(MYSQLI_ASSOC);
    
    echo json_encode($reports);
    
    $stmt->close();
} catch (Exception $e) {
    error_log("Get reports error: " . $e->getMessage());
    echo json_encode(['error' => 'Failed to load reports']);
}

$conn->close();
exit;
?>