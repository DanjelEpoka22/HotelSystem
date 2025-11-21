<?php
require_once '../../config/config.php';
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';

header('Content-Type: application/json');

// Check admin access
if (!isLoggedIn() || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

$action = $_GET['action'] ?? $_POST['action'] ?? '';

try {
    switch ($action) {
        case 'get_photos':
            getPhotos();
            break;
        
        case 'upload':
            uploadPhotos();
            break;
        
        case 'delete':
            deletePhoto();
            break;
        
        case 'reorder':
            reorderPhotos();
            break;
        
        default:
            echo json_encode(['success' => false, 'error' => 'Invalid action']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

function getPhotos() {
    global $db;
    
    $room_id = $_GET['room_id'] ?? 0;
    
    $query = "SELECT * FROM room_photos WHERE room_id = :room_id ORDER BY display_order ASC, id ASC";
    $stmt = $db->prepare($query);
    $stmt->execute(['room_id' => $room_id]);
    
    $photos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'photos' => $photos
    ]);
}

function uploadPhotos() {
    global $db;
    
    $room_id = $_POST['room_id'] ?? 0;
    
    if (!$room_id || !isset($_FILES['photos'])) {
        echo json_encode(['success' => false, 'error' => 'Missing room ID or photos']);
        return;
    }
    
    // Create upload directory if it doesn't exist
    $upload_dir = '../../assets/images/rooms/uploads/';
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    
    $uploaded_files = [];
    $errors = [];
    
    // Get current max display order
    $stmt = $db->prepare("SELECT COALESCE(MAX(display_order), 0) as max_order FROM room_photos WHERE room_id = :room_id");
    $stmt->execute(['room_id' => $room_id]);
    $max_order = $stmt->fetch(PDO::FETCH_ASSOC)['max_order'];
    
    $files = $_FILES['photos'];
    $file_count = count($files['name']);
    
    for ($i = 0; $i < $file_count; $i++) {
        if ($files['error'][$i] === UPLOAD_ERR_OK) {
            $tmp_name = $files['tmp_name'][$i];
            $original_name = $files['name'][$i];
            
            // Validate file type
            $file_info = getimagesize($tmp_name);
            if ($file_info === false) {
                $errors[] = "$original_name is not a valid image";
                continue;
            }
            
            $allowed_types = ['image/jpeg', 'image/png', 'image/webp', 'image/jpg'];
            if (!in_array($file_info['mime'], $allowed_types)) {
                $errors[] = "$original_name has invalid format";
                continue;
            }
            
            // Generate unique filename
            $extension = pathinfo($original_name, PATHINFO_EXTENSION);
            $new_filename = 'room_' . $room_id . '_' . time() . '_' . uniqid() . '.' . $extension;
            $destination = $upload_dir . $new_filename;
            
            if (move_uploaded_file($tmp_name, $destination)) {
                // Insert into database
                $max_order++;
                $stmt = $db->prepare("INSERT INTO room_photos (room_id, photo_filename, display_order) VALUES (:room_id, :filename, :display_order)");
                $stmt->execute([
                    'room_id' => $room_id,
                    'filename' => $new_filename,
                    'display_order' => $max_order
                ]);
                
                $uploaded_files[] = $new_filename;
            } else {
                $errors[] = "Failed to upload $original_name";
            }
        }
    }
    
    echo json_encode([
        'success' => true,
        'uploaded' => count($uploaded_files),
        'files' => $uploaded_files,
        'errors' => $errors
    ]);
}

function deletePhoto() {
    global $db;
    
    $photo_id = $_GET['id'] ?? $_POST['id'] ?? 0;
    
    if (!$photo_id) {
        echo json_encode(['success' => false, 'error' => 'Invalid photo ID']);
        return;
    }
    
    // Get photo info
    $stmt = $db->prepare("SELECT * FROM room_photos WHERE id = :id");
    $stmt->execute(['id' => $photo_id]);
    $photo = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$photo) {
        echo json_encode(['success' => false, 'error' => 'Photo not found']);
        return;
    }
    
    // Delete file
    $file_path = '../../assets/images/rooms/uploads/' . $photo['photo_filename'];
    if (file_exists($file_path)) {
        unlink($file_path);
    }
    
    // Delete from database
    $stmt = $db->prepare("DELETE FROM room_photos WHERE id = :id");
    $stmt->execute(['id' => $photo_id]);
    
    echo json_encode(['success' => true]);
}

function reorderPhotos() {
    global $db;
    
    $order = json_decode(file_get_contents('php://input'), true);
    
    if (!$order || !isset($order['photos'])) {
        echo json_encode(['success' => false, 'error' => 'Invalid order data']);
        return;
    }
    
    $db->beginTransaction();
    
    try {
        foreach ($order['photos'] as $index => $photo_id) {
            $stmt = $db->prepare("UPDATE room_photos SET display_order = :order WHERE id = :id");
            $stmt->execute([
                'order' => $index + 1,
                'id' => $photo_id
            ]);
        }
        
        $db->commit();
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        $db->rollBack();
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}
?>