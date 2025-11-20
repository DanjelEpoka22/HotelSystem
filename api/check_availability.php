<?php
require_once '../config/config.php';
require_once '../includes/functions.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

try {
    $data = json_decode(file_get_contents('php://input'), true);
    
    $check_in = $data['check_in'] ?? '';
    $check_out = $data['check_out'] ?? '';
    $guests = $data['guests'] ?? 1;
    $room_type = $data['room_type'] ?? '';

    // Validate input
    if (empty($check_in) || empty($check_out)) {
        throw new Exception('Check-in and check-out dates are required');
    }

    if (strtotime($check_in) >= strtotime($check_out)) {
        throw new Exception('Check-out date must be after check-in date');
    }

    // Debug: Log the request
    error_log("Availability check: $check_in to $check_out, guests: $guests, type: $room_type");

    // Build query
    $query = "SELECT r.*, rs.status as room_status 
              FROM rooms r 
              LEFT JOIN room_status rs ON r.id = rs.room_id 
              WHERE r.is_available = 1 
              AND r.max_guests >= :guests
              AND (rs.status IS NULL OR rs.status != 'maintenance')";

    $params = ['guests' => $guests];

    if (!empty($room_type)) {
        $query .= " AND r.room_type = :room_type";
        $params['room_type'] = $room_type;
    }

    $stmt = $db->prepare($query);
    $stmt->execute($params);

    $available_rooms = [];
    $total_rooms = 0;

    while ($room = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $total_rooms++;
        // Check if room is available for the selected dates
        if (isDateAvailable($room['id'], $check_in, $check_out)) {
            $available_rooms[] = [
                'id' => $room['id'],
                'room_number' => $room['room_number'],
                'room_type' => $room['room_type'],
                'room_type_name' => getRoomTypeName($room['room_type']),
                'description' => $room['description'],
                'price_per_night' => $room['price_per_night'],
                'max_guests' => $room['max_guests'],
                'amenities' => json_decode($room['amenities'] ?? '[]', true),
                'total_price' => calculateTotalPrice($room['price_per_night'], $check_in, $check_out),
                'nights' => floor((strtotime($check_out) - strtotime($check_in)) / (60 * 60 * 24))
            ];
        }
    }
    error_log("Found {$total_rooms} total rooms, " . count($available_rooms) . " available");

    echo json_encode([
        'success' => true,
        'check_in' => $check_in,
        'check_out' => $check_out,
        'guests' => $guests,
        'available_rooms' => $available_rooms,
        'total_available' => count($available_rooms),
        'total_checked' => $total_rooms
    ]);

} catch (Exception $e) {
    error_log("Availability error: " . $e->getMessage());
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
}
?>