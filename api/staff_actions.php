<?php
require_once '../../config/config.php';
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['error' => 'Access denied']);
    exit;
}

header('Content-Type: application/json');

$action = $_GET['action'] ?? '';
$user_id = $_GET['id'] ?? '';

try {
    switch ($action) {
        case 'demote':
            if (empty($user_id)) {
                throw new Exception('User ID is required');
            }

            // Demote staff to regular user
            $query = "UPDATE users SET role = 'user' WHERE id = :id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':id', $user_id);
            
            if ($stmt->execute()) {
                // Also remove from staff table if exists
                $deleteQuery = "DELETE FROM staff WHERE user_id = :user_id";
                $deleteStmt = $db->prepare($deleteQuery);
                $deleteStmt->bindParam(':user_id', $user_id);
                $deleteStmt->execute();
                
                echo json_encode(['success' => true, 'message' => 'Staff member demoted successfully']);
            } else {
                throw new Exception('Failed to demote staff member');
            }
            break;

        case 'remove':
            if (empty($user_id)) {
                throw new Exception('Staff ID is required');
            }

            // Remove from staff table but keep user role
            $query = "DELETE FROM staff WHERE id = :id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':id', $user_id);
            
            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Staff record removed successfully']);
            } else {
                throw new Exception('Failed to remove staff record');
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