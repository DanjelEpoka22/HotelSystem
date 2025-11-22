<?php
// Global helper functions

function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

function formatDate($date, $format = 'Y-m-d') {
    return date($format, strtotime($date));
}

function calculateTotalPrice($room_price, $check_in, $check_out) {
    $start = new DateTime($check_in);
    $end = new DateTime($check_out);
    $nights = $start->diff($end)->days;
    return $room_price * $nights;
}

function isDateAvailable($room_id, $check_in, $check_out, $exclude_reservation_id = null) {
    global $db;
    
    $query = "SELECT COUNT(*) as count FROM reservations 
              WHERE room_id = :room_id 
              AND status NOT IN ('cancelled', 'checked_out')
              AND ((check_in < :check_out AND check_out > :check_in))";
    
    if ($exclude_reservation_id) {
        $query .= " AND id != :exclude_id";
    }
    
    $stmt = $db->prepare($query);
    $stmt->bindParam(':room_id', $room_id);
    $stmt->bindParam(':check_in', $check_in);
    $stmt->bindParam(':check_out', $check_out);
    
    if ($exclude_reservation_id) {
        $stmt->bindParam(':exclude_id', $exclude_reservation_id);
    }
    
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    return $result['count'] == 0;
}

function getRoomTypeName($room_type) {
    $types = [
        'one_bedroom_apartment' => 'One-Bedroom Apartment',
        'deluxe_double' => 'Deluxe Double Room',
        'deluxe_triple' => 'Deluxe Triple Room',
        'deluxe_quadruple' => 'Deluxe Quadruple Room',
        'suite' => 'Suite',
        'deluxe_studio' => 'Deluxe Studio',
        'family_studio' => 'Family Studio'
    ];
    
    return $types[$room_type] ?? 'Unknown Room Type';
}

function canCancelReservation($check_in) {
    $check_in_date = new DateTime($check_in);
    $today = new DateTime();
    $today->setTime(0, 0, 0); // Set to beginning of day for accurate comparison
    
    $cancellation_deadline = clone $check_in_date;
    $cancellation_deadline->modify('-' . CANCELLATION_DAYS . ' days');
    
    // Debug output (remove after testing)
    error_log("Check-in: " . $check_in_date->format('Y-m-d'));
    error_log("Today: " . $today->format('Y-m-d'));
    error_log("Cancellation deadline: " . $cancellation_deadline->format('Y-m-d'));
    error_log("Can cancel: " . ($today < $cancellation_deadline ? 'YES' : 'NO'));
    
    return $today < $cancellation_deadline;
}
function getRoomPhotos($room_id) {
    global $db;
    
    $query = "SELECT * FROM room_photos WHERE room_id = :room_id ORDER BY display_order ASC, id ASC";
    $stmt = $db->prepare($query);
    $stmt->execute(['room_id' => $room_id]);
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getFirstRoomPhoto($room_id) {
    global $db;
    
    $query = "SELECT photo_filename FROM room_photos WHERE room_id = :room_id ORDER BY display_order ASC, id ASC LIMIT 1";
    $stmt = $db->prepare($query);
    $stmt->execute(['room_id' => $room_id]);
    
    $photo = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($photo) {
        return 'assets/images/rooms/uploads/' . $photo['photo_filename'];
    }
    
    return 'assets/images/rooms/default.jpg';
}
?>