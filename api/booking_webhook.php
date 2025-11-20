<?php
require_once '../config/config.php';
require_once '../config/booking_api.php';
require_once '../includes/functions.php';


// Verify webhook signature (in production, verify the signature from Booking.com)
$signature = $_SERVER['HTTP_X_BOOKING_SIGNATURE'] ?? '';
$payload = file_get_contents('php://input');

// Log webhook request
file_put_contents('../logs/booking_webhook.log', date('Y-m-d H:i:s') . " - " . $payload . "\n", FILE_APPEND);

try {
    $data = json_decode($payload, true);
    
    if (!$data) {
        throw new Exception('Invalid JSON payload');
    }

    $bookingAPI = new BookingAPI();
    $result = $bookingAPI->handleWebhook($data);

    http_response_code(200);
    echo json_encode($result);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
    
    // Log error
    file_put_contents('../logs/booking_webhook_errors.log', date('Y-m-d H:i:s') . " - ERROR: " . $e->getMessage() . "\n", FILE_APPEND);
}
?>