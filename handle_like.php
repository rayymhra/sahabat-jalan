<?php
session_start();
include_once 'connection.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please log in to like/dislike reports']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$user_id = $_SESSION['user_id'];
$report_id = isset($_POST['report_id']) ? intval($_POST['report_id']) : 0;
$value = isset($_POST['value']) ? intval($_POST['value']) : 0;

if ($report_id <= 0 || ($value !== 1 && $value !== -1)) {
    echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
    exit;
}

// Initialize response array
$response = ['success' => true];

// Check if the user already liked/disliked this report
$check_stmt = $conn->prepare("SELECT id, value FROM likes WHERE user_id = ? AND report_id = ?");
$check_stmt->bind_param("ii", $user_id, $report_id);
$check_stmt->execute();
$existing_like = $check_stmt->get_result()->fetch_assoc();

if ($existing_like) {
    if ($existing_like['value'] == $value) {
        // User is trying to set the same value again, so remove the like/dislike
        $delete_stmt = $conn->prepare("DELETE FROM likes WHERE id = ?");
        $delete_stmt->bind_param("i", $existing_like['id']);
        $delete_stmt->execute();
        $response['action'] = 'removed';
        $response['value'] = 0;
    } else {
        // User is changing their vote
        $update_stmt = $conn->prepare("UPDATE likes SET value = ? WHERE id = ?");
        $update_stmt->bind_param("ii", $value, $existing_like['id']);
        $update_stmt->execute();
        $response['action'] = 'updated';
        $response['value'] = $value;
    }
} else {
    // New like/dislike
    $insert_stmt = $conn->prepare("INSERT INTO likes (user_id, report_id, value) VALUES (?, ?, ?)");
    $insert_stmt->bind_param("iii", $user_id, $report_id, $value);
    $insert_stmt->execute();
    $response['action'] = 'added';
    $response['value'] = $value;
}

// Get updated counts
$count_stmt = $conn->prepare("
    SELECT 
        SUM(CASE WHEN value = 1 THEN 1 ELSE 0 END) as likes,
        SUM(CASE WHEN value = -1 THEN 1 ELSE 0 END) as dislikes,
        (SELECT value FROM likes WHERE user_id = ? AND report_id = ?) as user_vote
    FROM likes 
    WHERE report_id = ?
");
$count_stmt->bind_param("iii", $user_id, $report_id, $report_id);
$count_stmt->execute();
$result = $count_stmt->get_result()->fetch_assoc();

// Add counts to response
$response['likes'] = $result['likes'] ?? 0;
$response['dislikes'] = $result['dislikes'] ?? 0;
$response['user_vote'] = $result['user_vote'] ?? 0;

// Output single JSON response
echo json_encode($response);
exit;
?>