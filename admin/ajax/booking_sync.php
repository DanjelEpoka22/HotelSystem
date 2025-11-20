<?php
require_once '../../config/config.php';
require_once '../../includes/auth.php';
require_once '../includes/functions.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['error' => 'Access denied']);
    exit;
}

header('Content-Type: application/json');

$action = $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'sync':
            require_once '../../config/booking_api.php';
            $bookingAPI = new BookingAPI();
            $result = $bookingAPI->syncReservations();
            
            echo json_encode($result);
            break;

        case 'get_sync_log':
            $query = "SELECT bs.*, r.room_number 
                     FROM booking_sync bs 
                     JOIN rooms r ON bs.room_id = r.id 
                     ORDER BY bs.synced_at DESC 
                     LIMIT 50";
            $stmt = $db->prepare($query);
            $stmt->execute();
            $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode(['success' => true, 'logs' => $logs]);
            break;

        default:
            throw new Exception('Invalid action');
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
}
?>