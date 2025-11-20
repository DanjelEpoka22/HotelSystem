<?php
require_once '../config/config.php';
require_once '../includes/functions.php';
header('Content-Type: application/json');

$room_id = $_GET['id'] ?? '';

if (empty($room_id)) {
    http_response_code(400);
    echo json_encode(['error' => 'Room ID is required']);
    exit;
}

try {
    $query = "SELECT * FROM rooms WHERE id = :room_id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':room_id', $room_id);
    $stmt->execute();
    $room = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$room) {
        http_response_code(404);
        echo json_encode(['error' => 'Room not found']);
        exit;
    }

    // Format amenities
    $room['amenities'] = json_decode($room['amenities'] ?? '[]', true);
    $room['room_type_name'] = getRoomTypeName($room['room_type']);

    echo json_encode([
        'success' => true,
        'room' => $room
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to fetch room details: ' . $e->getMessage()]);
}
?>