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
    
    $room_id = $data['room_id'] ?? '';
    $check_in = $data['check_in'] ?? '';
    $check_out = $data['check_out'] ?? '';
    $guests = $data['guests'] ?? 1;
    $user_id = $data['user_id'] ?? '';
    $special_requests = $data['special_requests'] ?? '';
    $payment_method = $data['payment_method'] ?? 'cash';

    // Validate input
    if (empty($room_id) || empty($check_in) || empty($check_out) || empty($user_id)) {
        throw new Exception('All required fields must be filled');
    }

    // Check room availability
    if (!isDateAvailable($room_id, $check_in, $check_out)) {
        throw new Exception('Room is not available for the selected dates');
    }

    // Get room details
    $roomQuery = "SELECT * FROM rooms WHERE id = :room_id";
    $roomStmt = $db->prepare($roomQuery);
    $roomStmt->bindParam(':room_id', $room_id);
    $roomStmt->execute();
    $room = $roomStmt->fetch(PDO::FETCH_ASSOC);

    if (!$room) {
        throw new Exception('Room not found');
    }

    // Calculate total price
    $total_price = calculateTotalPrice($room['price_per_night'], $check_in, $check_out);

    // Start transaction
    $db->beginTransaction();

    try {
        // Create reservation
        $reservationQuery = "INSERT INTO reservations 
                            (user_id, room_id, check_in, check_out, guests, total_price, special_requests, payment_method) 
                            VALUES (:user_id, :room_id, :check_in, :check_out, :guests, :total_price, :special_requests, :payment_method)";
        
        $reservationStmt = $db->prepare($reservationQuery);
        $reservationStmt->execute([
            'user_id' => $user_id,
            'room_id' => $room_id,
            'check_in' => $check_in,
            'check_out' => $check_out,
            'guests' => $guests,
            'total_price' => $total_price,
            'special_requests' => $special_requests,
            'payment_method' => $payment_method
        ]);

        $reservation_id = $db->lastInsertId();

        // If payment is by card, create payment record
        if ($payment_method === 'card') {
            $paymentQuery = "INSERT INTO payments (reservation_id, amount, payment_method, status) 
                            VALUES (:reservation_id, :amount, 'card', 'pending')";
            $paymentStmt = $db->prepare($paymentQuery);
            $paymentStmt->execute([
                'reservation_id' => $reservation_id,
                'amount' => $total_price
            ]);
        }

        $db->commit();

        echo json_encode([
            'success' => true,
            'reservation_id' => $reservation_id,
            'message' => 'Reservation created successfully'
        ]);

    } catch (Exception $e) {
        $db->rollBack();
        throw new Exception('Failed to create reservation: ' . $e->getMessage());
    }

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
}
?>