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
    
    $reservation_id = $data['reservation_id'] ?? '';
    $user_id = $data['user_id'] ?? '';

    // Validate input
    if (empty($reservation_id) || empty($user_id)) {
        throw new Exception('Reservation ID and User ID are required');
    }

    // Get reservation details
    $query = "SELECT * FROM reservations WHERE id = :reservation_id AND user_id = :user_id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':reservation_id', $reservation_id);
    $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();
    $reservation = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$reservation) {
        throw new Exception('Reservation not found or access denied');
    }

    // Check if reservation can be cancelled
    if ($reservation['status'] === 'cancelled') {
        throw new Exception('Reservation is already cancelled');
    }

    if (!canCancelReservation($reservation['check_in'])) {
        throw new Exception('Cancellation not allowed. Less than ' . CANCELLATION_DAYS . ' days before check-in.');
    }

    // Start transaction
    $db->beginTransaction();

    try {
        // Update reservation status
        $updateQuery = "UPDATE reservations SET status = 'cancelled', updated_at = NOW() WHERE id = :reservation_id";
        $updateStmt = $db->prepare($updateQuery);
        $updateStmt->bindParam(':reservation_id', $reservation_id);
        $updateStmt->execute();

        // Refund payment if paid by card
        if ($reservation['payment_method'] === 'card' && $reservation['payment_status'] === 'paid') {
            $refundQuery = "UPDATE payments SET status = 'refunded' WHERE reservation_id = :reservation_id";
            $refundStmt = $db->prepare($refundQuery);
            $refundStmt->bindParam(':reservation_id', $reservation_id);
            $refundStmt->execute();
        }

        $db->commit();

        echo json_encode([
            'success' => true,
            'message' => 'Reservation cancelled successfully',
            'refund_processed' => ($reservation['payment_method'] === 'card' && $reservation['payment_status'] === 'paid')
        ]);

    } catch (Exception $e) {
        $db->rollBack();
        throw new Exception('Failed to cancel reservation: ' . $e->getMessage());
    }

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
}
?>