<?php
require_once '../config/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
requireRole('admin');

// Handle room actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_room'])) {
        $room_data = [
            'room_number' => sanitizeInput($_POST['room_number']),
            'room_type' => sanitizeInput($_POST['room_type']),
            'description' => sanitizeInput($_POST['description']),
            'price_per_night' => $_POST['price_per_night'],
            'max_guests' => $_POST['max_guests'],
            'amenities' => json_encode($_POST['amenities'] ?? [])
        ];

        if (addRoom($room_data)) {
            $success = "Room added successfully!";
        } else {
            $error = "Failed to add room. Room number might already exist.";
        }
    }
}

function addRoom($room_data) {
    global $db;
    
    $query = "INSERT INTO rooms (room_number, room_type, description, price_per_night, max_guests, amenities) 
              VALUES (:room_number, :room_type, :description, :price_per_night, :max_guests, :amenities)";
    
    $stmt = $db->prepare($query);
    
    return $stmt->execute($room_data);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Room Management - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/admin.css">
    <style>
    .photo-management {
        margin-top: 1rem;
        padding: 1rem;
        background: #f8f9fa;
        border-radius: 5px;
    }
    .photos-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
        gap: 1rem;
        margin-top: 1rem;
    }
    .photo-item {
        position: relative;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }
    .photo-item img {
        width: 100%;
        height: 150px;
        object-fit: cover;
    }
    .photo-actions {
        position: absolute;
        top: 5px;
        right: 5px;
        display: flex;
        gap: 5px;
    }
    .photo-actions button {
        padding: 5px 10px;
        font-size: 12px;
        border: none;
        border-radius: 3px;
        cursor: pointer;
        background: rgba(255,255,255,0.9);
    }
    .photo-actions .delete-photo {
        background: #e74c3c;
        color: white;
    }
    .upload-area {
        border: 2px dashed #3498db;
        border-radius: 8px;
        padding: 2rem;
        text-align: center;
        cursor: pointer;
        transition: background 0.3s;
        margin-top: 1rem;
    }
    .upload-area:hover {
        background: #ecf0f1;
    }
    .upload-area.dragover {
        background: #d5e8f7;
        border-color: #2980b9;
    }
    /* Modal Styles */
    .modal {
        display: none;
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.5);
    }
    .modal-content {
        background: white;
        margin: 2% auto;
        padding: 2rem;
        border-radius: 10px;
        width: 90%;
        max-width: 800px;
        max-height: 90vh;
        overflow-y: auto;
    }
    .close {
        float: right;
        font-size: 28px;
        font-weight: bold;
        cursor: pointer;
        color: #aaa;
    }
    .close:hover {
        color: #000;
    }
    .preview-images {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
        gap: 10px;
        margin-top: 1rem;
    }
    .preview-item {
        position: relative;
    }
    .preview-item img {
        width: 100%;
        height: 100px;
        object-fit: cover;
        border-radius: 5px;
    }
    .remove-preview {
        position: absolute;
        top: 5px;
        right: 5px;
        background: #e74c3c;
        color: white;
        border: none;
        border-radius: 50%;
        width: 25px;
        height: 25px;
        cursor: pointer;
        font-size: 14px;
        line-height: 1;
    }
    </style>
</head>
<body>
    <div class="admin-container">
        <!-- Sidebar -->
        <?php include 'sidebar.php'; ?>

        <!-- Main Content -->
        <div class="main-content">
            <div class="content-header">
                <h1>Room Management</h1>
                <p>Manage all rooms and their availability</p>
            </div>

            <?php if (isset($success)): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>

            <?php if (isset($error)): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>

            <!-- Add Room Form -->
            <div class="dashboard-section">
                <h2>Add New Room</h2>
                <form method="POST" class="room-form">
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Room Number</label>
                            <input type="text" name="room_number" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Room Type</label>
                            <select name="room_type" class="form-control" required>
                                <option value="">Select Room Type</option>
                                <option value="one_bedroom_apartment">One-Bedroom Apartment</option>
                                <option value="deluxe_double">Deluxe Double Room</option>
                                <option value="deluxe_triple">Deluxe Triple Room</option>
                                <option value="deluxe_quadruple">Deluxe Quadruple Room</option>
                                <option value="suite">Suite</option>
                                <option value="deluxe_studio">Deluxe Studio</option>
                                <option value="family_studio">Family Studio</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="3"></textarea>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Price per Night (€)</label>
                            <input type="number" name="price_per_night" step="0.01" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Max Guests</label>
                            <input type="number" name="max_guests" class="form-control" required min="1">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Amenities</label>
                        <div class="amenities-checkboxes">
                            <label><input type="checkbox" name="amenities[]" value="WiFi"> WiFi</label>
                            <label><input type="checkbox" name="amenities[]" value="AC"> Air Conditioning</label>
                            <label><input type="checkbox" name="amenities[]" value="TV"> TV</label>
                            <label><input type="checkbox" name="amenities[]" value="Mini Bar"> Mini Bar</label>
                            <label><input type="checkbox" name="amenities[]" value="Balcony"> Balcony</label>
                            <label><input type="checkbox" name="amenities[]" value="Sea View"> Sea View</label>
                            <label><input type="checkbox" name="amenities[]" value="Jacuzzi"> Jacuzzi</label>
                            <label><input type="checkbox" name="amenities[]" value="Living Room"> Living Room</label>
                        </div>
                    </div>
                    
                    <button type="submit" name="add_room" class="btn btn-primary">Add Room</button>
                </form>
            </div>

            <!-- Rooms List -->
            <div class="dashboard-section">
                <h2>All Rooms</h2>
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Room Number</th>
                                <th>Type</th>
                                <th>Price/Night</th>
                                <th>Max Guests</th>
                                <th>Photos</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $query = "SELECT r.*, rs.status as room_status, 
                                     (SELECT COUNT(*) FROM room_photos WHERE room_id = r.id) as photo_count
                                     FROM rooms r 
                                     LEFT JOIN room_status rs ON r.id = rs.room_id 
                                     ORDER BY r.room_number";
                            $stmt = $db->prepare($query);
                            $stmt->execute();
                            
                            while ($room = $stmt->fetch(PDO::FETCH_ASSOC)):
                            ?>
                            <tr>
                                <td><?php echo $room['room_number']; ?></td>
                                <td><?php echo getRoomTypeName($room['room_type']); ?></td>
                                <td>€<?php echo $room['price_per_night']; ?></td>
                                <td><?php echo $room['max_guests']; ?></td>
                                <td>
                                    <span class="badge"><?php echo $room['photo_count']; ?> photos</span>
                                </td>
                                <td>
                                    <span class="status-badge status-<?php echo $room['room_status'] ?? 'clean'; ?>">
                                        <?php echo ucfirst($room['room_status'] ?? 'clean'); ?>
                                    </span>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-secondary" onclick="editRoom(<?php echo $room['id']; ?>)">Edit</button>
                                    <button class="btn btn-sm btn-primary" onclick="managePhotos(<?php echo $room['id']; ?>, '<?php echo addslashes($room['room_number']); ?>')">Photos</button>
                                    <button class="btn btn-sm btn-danger" onclick="deleteRoom(<?php echo $room['id']; ?>)">Delete</button>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Photo Management Modal -->
    <div id="photoModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closePhotoModal()">&times;</span>
            <h2>Manage Photos - Room <span id="modalRoomNumber"></span></h2>
            
            <div class="upload-area" id="uploadArea">
                <input type="file" id="photoInput" multiple accept="image/*" style="display:none;">
                <p>📸 Click or drag photos here to upload</p>
                <p style="font-size: 0.9rem; color: #666;">Supports: JPG, PNG, WEBP</p>
            </div>

            <div id="previewContainer" class="preview-images" style="display:none;"></div>
            
            <button id="uploadBtn" class="btn btn-primary" style="display:none; margin-top:1rem;">Upload Photos</button>

            <div class="photo-management">
                <h3>Current Photos</h3>
                <div id="currentPhotos" class="photos-grid">
                    <p>Loading photos...</p>
                </div>
            </div>
        </div>
    </div>

    <script src="../assets/js/admin-photos.js"></script>
    <script>
    function editRoom(roomId) {
        alert('Edit functionality - Room ID: ' + roomId);
    }

    function deleteRoom(roomId) {
        if (confirm('Are you sure you want to delete this room?')) {
            fetch('ajax/room_actions.php?action=delete&id=' + roomId)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Error deleting room: ' + data.error);
                    }
                });
        }
    }
    </script>
</body>
</html>