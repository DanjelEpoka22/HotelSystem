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
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $query = "SELECT r.*, rs.status as room_status 
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
                                    <span class="status-badge status-<?php echo $room['room_status'] ?? 'clean'; ?>">
                                        <?php echo ucfirst($room['room_status'] ?? 'clean'); ?>
                                    </span>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-secondary" onclick="editRoom(<?php echo $room['id']; ?>)">Edit</button>
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

    <script>
    function editRoom(roomId) {
        // Implement edit functionality
        alert('Edit room: ' + roomId);
    }

    function deleteRoom(roomId) {
        if (confirm('Are you sure you want to delete this room?')) {
            // Implement delete functionality
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