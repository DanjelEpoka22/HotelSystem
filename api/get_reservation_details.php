<?php
require_once '../config/config.php';
require_once '../includes/functions.php';
header('Content-Type: application/json');

$reservation_id = $_GET['id'] ?? '';

if (empty($reservation_id)) {
    http_response_code(400);
    echo json_encode(['error' => 'Reservation ID is required']);
    exit;
}

try {
    // Check if user is logged in and has permission to view this reservation
    session_start();
    $user_id = $_SESSION['user_id'] ?? null;
    $user_role = $_SESSION['role'] ?? null;
    
    $query = "SELECT r.*, u.first_name, u.last_name, u.email, u.phone, 
                     rm.room_number, rm.room_type,
                     rm.room_type as room_type_name
              FROM reservations r 
              JOIN users u ON r.user_id = u.id 
              JOIN rooms rm ON r.room_id = rm.id 
              WHERE r.id = :reservation_id";
    
    // Add permission check for regular users
    if ($user_role === 'user') {
        $query .= " AND r.user_id = :user_id";
    }
    
    $stmt = $db->prepare($query);
    $stmt->bindParam(':reservation_id', $reservation_id);
    
    if ($user_role === 'user') {
        $stmt->bindParam(':user_id', $user_id);
    }
    
    $stmt->execute();
    $reservation = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$reservation) {
        http_response_code(404);
        echo json_encode(['error' => 'Reservation not found or access denied']);
        exit;
    }

    // Format room type name
    $reservation['room_type_name'] = getRoomTypeName($reservation['room_type']);

    echo json_encode([
        'success' => true,
        'reservation' => $reservation
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to fetch reservation details: ' . $e->getMessage()]);
}
?>