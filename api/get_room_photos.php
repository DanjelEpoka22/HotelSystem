<?php
require_once '../config/config.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

try {
    $room_id = $_GET['room_id'] ?? null;
    
    if (!$room_id) {
        throw new Exception('Room ID is required');
    }

    // Get room photos from database
    $query = "SELECT id, room_id, photo_filename, display_order 
              FROM room_photos 
              WHERE room_id = :room_id 
              ORDER BY display_order ASC";
    
    $stmt = $db->prepare($query);
    $stmt->execute(['room_id' => $room_id]);
    
    $photos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'room_id' => $room_id,
        'photos' => $photos,
        'count' => count($photos)
    ]);

} catch (Exception $e) {
    error_log("Get room photos error: " . $e->getMessage());
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'photos' => []
    ]);
}
?>