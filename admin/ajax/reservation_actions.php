<?php
require_once '../../config/config.php';
require_once '../../includes/auth.php';
require_once '../includes/functions.php';

if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'receptionist')) {
    http_response_code(403);
    echo json_encode(['error' => 'Access denied']);
    exit;
}

header('Content-Type: application/json');

$action = $_GET['action'] ?? '';
$reservation_id = $_GET['id'] ?? '';

try {
    switch ($action) {
        case 'cancel':
            if (empty($reservation_id)) {
                throw new Exception('Reservation ID is required');
            }

            // Get reservation details
            $query = "SELECT * FROM reservations WHERE id = :id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':id', $reservation_id);
            $stmt->execute();
            $reservation = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$reservation) {
                throw new Exception('Reservation not found');
            }

            // Check if cancellation is allowed
            if (!canCancelReservation($reservation['check_in'])) {
                throw new Exception('Cancellation not allowed. Less than ' . CANCELLATION_DAYS . ' days before check-in.');
            }

            // Update reservation status
            $updateQuery = "UPDATE reservations SET status = 'cancelled' WHERE id = :id";
            $updateStmt = $db->prepare($updateQuery);
            $updateStmt->bindParam(':id', $reservation_id);
            
            if ($updateStmt->execute()) {
                // Refund payment if paid by card
                if ($reservation['payment_method'] === 'card' && $reservation['payment_status'] === 'paid') {
                    $refundQuery = "UPDATE payments SET status = 'refunded' WHERE reservation_id = :reservation_id";
                    $refundStmt = $db->prepare($refundQuery);
                    $refundStmt->bindParam(':reservation_id', $reservation_id);
                    $refundStmt->execute();
                }
                
                echo json_encode(['success' => true, 'message' => 'Reservation cancelled successfully']);
            } else {
                throw new Exception('Failed to cancel reservation');
            }
            break;

        case 'get_details':
            if (empty($reservation_id)) {
                throw new Exception('Reservation ID is required');
            }

            $query = "SELECT r.*, u.first_name, u.last_name, u.email, u.phone, 
                             rm.room_number, rm.room_type, rm.price_per_night,
                             p.status as payment_status, p.transaction_id
                     FROM reservations r 
                     JOIN users u ON r.user_id = u.id 
                     JOIN rooms rm ON r.room_id = rm.id 
                     LEFT JOIN payments p ON r.id = p.reservation_id 
                     WHERE r.id = :id";
            
            $stmt = $db->prepare($query);
            $stmt->bindParam(':id', $reservation_id);
            $stmt->execute();
            $reservation = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($reservation) {
                echo json_encode(['success' => true, 'reservation' => $reservation]);
            } else {
                throw new Exception('Reservation not found');
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