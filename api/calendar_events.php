<?php
require_once '../config/config.php';
require_once '../includes/functions.php';
header('Content-Type: application/json');

try {
    $start = $_GET['start'] ?? date('Y-m-01');
    $end = $_GET['end'] ?? date('Y-m-t');
    $room_id = $_GET['room_id'] ?? '';
    $source = $_GET['source'] ?? '';

    $query = "SELECT r.*, 
                     u.first_name, u.last_name,
                     rm.room_number, rm.room_type,
                     rs.status as room_status
              FROM reservations r
              JOIN users u ON r.user_id = u.id
              JOIN rooms rm ON r.room_id = rm.id
              LEFT JOIN room_status rs ON rm.id = rs.room_id
              WHERE r.status NOT IN ('cancelled')
              AND ((r.check_in BETWEEN :start AND :end) 
                   OR (r.check_out BETWEEN :start AND :end)
                   OR (r.check_in <= :start AND r.check_out >= :end))";

    $params = [
        'start' => $start,
        'end' => $end
    ];

    if (!empty($room_id)) {
        $query .= " AND r.room_id = :room_id";
        $params['room_id'] = $room_id;
    }

    if (!empty($source)) {
        $query .= " AND r.source = :source";
        $params['source'] = $source;
    }

    $stmt = $db->prepare($query);
    $stmt->execute($params);

    $events = [];

    while ($reservation = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $title = "Room {$reservation['room_number']} - {$reservation['first_name']} {$reservation['last_name']}";
        
        $events[] = [
            'id' => $reservation['id'],
            'title' => $title,
            'start' => $reservation['check_in'],
            'end' => date('Y-m-d', strtotime($reservation['check_out'] . ' +1 day')),
            'color' => getEventColor($reservation['source'], $reservation['status']),
            'extendedProps' => [
                'guestName' => "{$reservation['first_name']} {$reservation['last_name']}",
                'roomNumber' => $reservation['room_number'],
                'roomType' => getRoomTypeName($reservation['room_type']),
                'status' => $reservation['status'],
                'source' => $reservation['source'],
                'guests' => $reservation['guests']
            ]
        ];
    }

    // Add maintenance events
    $maintenanceQuery = "SELECT ri.*, rm.room_number 
                        FROM room_issues ri 
                        JOIN rooms rm ON ri.room_id = rm.id 
                        WHERE ri.status != 'resolved'";
    $maintenanceStmt = $db->prepare($maintenanceQuery);
    $maintenanceStmt->execute();

    while ($issue = $maintenanceStmt->fetch(PDO::FETCH_ASSOC)) {
        $events[] = [
            'id' => 'issue_' . $issue['id'],
            'title' => "Maintenance - Room {$issue['room_number']}",
            'start' => $issue['reported_at'],
            'color' => '#f39c12',
            'allDay' => true,
            'extendedProps' => [
                'type' => 'maintenance',
                'issueType' => $issue['issue_type'],
                'description' => $issue['description'],
                'priority' => $issue['priority']
            ]
        ];
    }

    echo json_encode($events);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to fetch calendar events: ' . $e->getMessage()]);
}

function getEventColor($source, $status) {
    if ($status === 'checked_in') return '#27ae60';
    if ($source === 'booking_com') return '#e74c3c';
    return '#3498db';
}
?>