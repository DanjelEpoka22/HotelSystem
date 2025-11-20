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
$room_id = $_GET['id'] ?? '';

try {
    switch ($action) {
        case 'delete':
            if (empty($room_id)) {
                throw new Exception('Room ID is required');
            }

            // Check if room has active reservations
            $checkQuery = "SELECT COUNT(*) as count FROM reservations 
                          WHERE room_id = :room_id 
                          AND status NOT IN ('cancelled', 'checked_out')";
            $checkStmt = $db->prepare($checkQuery);
            $checkStmt->bindParam(':room_id', $room_id);
            $checkStmt->execute();
            $result = $checkStmt->fetch(PDO::FETCH_ASSOC);

            if ($result['count'] > 0) {
                throw new Exception('Cannot delete room with active reservations');
            }

            // Delete room
            $deleteQuery = "DELETE FROM rooms WHERE id = :room_id";
            $deleteStmt = $db->prepare($deleteQuery);
            $deleteStmt->bindParam(':room_id', $room_id);
            
            if ($deleteStmt->execute()) {
                echo json_encode(['success' => true]);
            } else {
                throw new Exception('Failed to delete room');
            }
            break;

        default:
            throw new Exception('Invalid action');
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
}
?>