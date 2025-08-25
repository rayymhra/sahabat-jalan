<?php
session_start();
include_once 'connection.php';

$routes = [];

if ($conn) {
    $routeQuery = "
        SELECT r.*, u.name AS creator_name 
        FROM routes r 
        LEFT JOIN users u ON r.created_by = u.id 
        ORDER BY r.created_at DESC
    ";
    $routeResult = $conn->query($routeQuery);

    if ($routeResult) {
        while ($route = $routeResult->fetch_assoc()) {
            $routeId = $route['id'];

            // Fetch reports for this route
            $reportQuery = "
                SELECT rep.*, u.name AS user_name 
                FROM reports rep 
                LEFT JOIN users u ON rep.user_id = u.id 
                WHERE rep.route_id = $routeId 
                ORDER BY rep.created_at DESC
            ";
            $reportResult = $conn->query($reportQuery);

            $routeReports = [];
            if ($reportResult) {
                while ($report = $reportResult->fetch_assoc()) {
                    $routeReports[] = $report;
                }
            }

            $route['reports'] = $routeReports;
            $routes[] = $route;
        }
        
        echo json_encode(['success' => true, 'routes' => $routes]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Gagal mengambil data rute']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Koneksi database gagal']);
}
?>