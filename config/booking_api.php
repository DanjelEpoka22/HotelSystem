<?php
require_once 'config.php';

class BookingAPI {
    private $api_key;
    private $api_url;
    
    public function __construct() {
        $this->api_key = BOOKING_API_KEY;
        $this->api_url = BOOKING_API_URL;
    }
    
    /**
     * Sync reservations from Booking.com
     */
    public function syncReservations() {
        try {
            $reservations = $this->fetchReservations();
            
            foreach ($reservations as $reservation) {
                $this->processBookingReservation($reservation);
            }
            
            return ['success' => true, 'synced' => count($reservations)];
            
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Fetch reservations from Booking.com API
     */
    private function fetchReservations() {
        $ch = curl_init();
        
        curl_setopt_array($ch, [
            CURLOPT_URL => $this->api_url . '/reservations',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $this->api_key,
                'Content-Type: application/json'
            ]
        ]);
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($http_code !== 200) {
            throw new Exception('Booking.com API error: ' . $http_code);
        }
        
        $data = json_decode($response, true);
        return $data['reservations'] ?? [];
    }
    
    /**
     * Process a single Booking.com reservation
     */
    private function processBookingReservation($reservation) {
        global $db;
        
        // Check if already synced
        $checkQuery = "SELECT id FROM booking_sync WHERE booking_reference = :reference";
        $checkStmt = $db->prepare($checkQuery);
        $checkStmt->bindParam(':reference', $reservation['reservation_id']);
        $checkStmt->execute();
        
        if ($checkStmt->rowCount() > 0) {
            return; // Already synced
        }
        
        // Find matching room
        $room = $this->findMatchingRoom($reservation['room_type']);
        if (!$room) {
            throw new Exception('No matching room found for: ' . $reservation['room_type']);
        }
        
        // Check room availability
        if (!isDateAvailable($room['id'], $reservation['check_in'], $reservation['check_out'])) {
            throw new Exception('Room not available for booking.com reservation');
        }
        
        // Create user for booking.com guest
        $user_id = $this->createOrFindUser($reservation['guest']);
        
        // Create reservation
        $reservationQuery = "INSERT INTO reservations 
                            (user_id, room_id, check_in, check_out, guests, total_price, status, source, booking_reference) 
                            VALUES (:user_id, :room_id, :check_in, :check_out, :guests, :total_price, 'confirmed', 'booking_com', :reference)";
        
        $reservationStmt = $db->prepare($reservationQuery);
        $reservationStmt->execute([
            'user_id' => $user_id,
            'room_id' => $room['id'],
            'check_in' => $reservation['check_in'],
            'check_out' => $reservation['check_out'],
            'guests' => $reservation['guests'],
            'total_price' => $reservation['total_price'],
            'reference' => $reservation['reservation_id']
        ]);
        
        // Log sync
        $syncQuery = "INSERT INTO booking_sync (booking_reference, room_id, check_in, check_out, guest_name, guest_email, guest_phone) 
                     VALUES (:reference, :room_id, :check_in, :check_out, :guest_name, :guest_email, :guest_phone)";
        
        $syncStmt = $db->prepare($syncQuery);
        $syncStmt->execute([
            'reference' => $reservation['reservation_id'],
            'room_id' => $room['id'],
            'check_in' => $reservation['check_in'],
            'check_out' => $reservation['check_out'],
            'guest_name' => $reservation['guest']['name'],
            'guest_email' => $reservation['guest']['email'],
            'guest_phone' => $reservation['guest']['phone']
        ]);
    }
    
    /**
     * Find matching room based on Booking.com room type
     */
    private function findMatchingRoom($booking_room_type) {
        global $db;
        
        $room_mapping = [
            'double_room' => 'deluxe_double',
            'twin_room' => 'deluxe_double',
            'triple_room' => 'deluxe_triple',
            'family_room' => 'family_studio',
            'apartment' => 'one_bedroom_apartment',
            'suite' => 'suite'
        ];
        
        $room_type = $room_mapping[$booking_room_type] ?? null;
        if (!$room_type) {
            return null;
        }
        
        $query = "SELECT * FROM rooms WHERE room_type = :room_type AND is_available = 1 LIMIT 1";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':room_type', $room_type);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Create or find user for Booking.com guest
     */
    private function createOrFindUser($guest_info) {
        global $db;
        
        // Check if user exists
        $query = "SELECT id FROM users WHERE email = :email";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':email', $guest_info['email']);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            return $user['id'];
        }
        
        // Create new user
        $name_parts = explode(' ', $guest_info['name'], 2);
        $first_name = $name_parts[0] ?? 'Guest';
        $last_name = $name_parts[1] ?? 'Booking';
        
        $username = strtolower($first_name . '.' . $last_name) . '.' . uniqid();
        $password = password_hash(uniqid(), PASSWORD_DEFAULT);
        
        $insertQuery = "INSERT INTO users (username, email, password, first_name, last_name, phone, role) 
                       VALUES (:username, :email, :password, :first_name, :last_name, :phone, 'user')";
        
        $insertStmt = $db->prepare($insertQuery);
        $insertStmt->execute([
            'username' => $username,
            'email' => $guest_info['email'],
            'password' => $password,
            'first_name' => $first_name,
            'last_name' => $last_name,
            'phone' => $guest_info['phone']
        ]);
        
        return $db->lastInsertId();
    }
    
    /**
     * Handle webhook from Booking.com
     */
    public function handleWebhook($data) {
        $event_type = $data['type'] ?? '';
        $reservation_data = $data['reservation'] ?? [];
        
        switch ($event_type) {
            case 'reservation.created':
                $this->processBookingReservation($reservation_data);
                break;
                
            case 'reservation.modified':
                $this->updateBookingReservation($reservation_data);
                break;
                
            case 'reservation.cancelled':
                $this->cancelBookingReservation($reservation_data['reservation_id']);
                break;
        }
        
        return ['success' => true];
    }
    
    /**
     * Update existing Booking.com reservation
     */
    private function updateBookingReservation($reservation) {
        global $db;
        
        $query = "UPDATE reservations r 
                 JOIN booking_sync bs ON r.booking_reference = bs.booking_reference 
                 SET r.check_in = :check_in, r.check_out = :check_out, r.guests = :guests, r.total_price = :total_price 
                 WHERE bs.booking_reference = :reference";
        
        $stmt = $db->prepare($query);
        $stmt->execute([
            'check_in' => $reservation['check_in'],
            'check_out' => $reservation['check_out'],
            'guests' => $reservation['guests'],
            'total_price' => $reservation['total_price'],
            'reference' => $reservation['reservation_id']
        ]);
    }
    
    /**
     * Cancel Booking.com reservation
     */
    private function cancelBookingReservation($reservation_id) {
        global $db;
        
        $query = "UPDATE reservations r 
                 JOIN booking_sync bs ON r.booking_reference = bs.booking_reference 
                 SET r.status = 'cancelled' 
                 WHERE bs.booking_reference = :reference";
        
        $stmt = $db->prepare($query);
        $stmt->bindParam(':reference', $reservation_id);
        $stmt->execute();
        
        // Update sync log
        $updateSync = "UPDATE booking_sync SET status = 'cancelled' WHERE booking_reference = :reference";
        $syncStmt = $db->prepare($updateSync);
        $syncStmt->bindParam(':reference', $reservation_id);
        $syncStmt->execute();
    }
}
?>