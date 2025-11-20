<?php
require_once '../config/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
requireRole('housekeeper');

// Handle room status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $room_id = $_POST['room_id'];
    $status = $_POST['status'];
    $notes = $_POST['notes'] ?? '';
    
    // Check if room status record exists
    $checkQuery = "SELECT id FROM room_status WHERE room_id = :room_id";
    $checkStmt = $db->prepare($checkQuery);
    $checkStmt->bindParam(':room_id', $room_id);
    $checkStmt->execute();
    
    if ($checkStmt->rowCount() > 0) {
        // Update existing record
        $query = "UPDATE room_status SET status = :status, notes = :notes, 
                  last_cleaned = CASE WHEN :status = 'clean' THEN NOW() ELSE last_cleaned END,
                  housekeeper_id = :housekeeper_id 
                  WHERE room_id = :room_id";
    } else {
        // Insert new record
        $query = "INSERT INTO room_status (room_id, status, notes, last_cleaned, housekeeper_id) 
                  VALUES (:room_id, :status, :notes, CASE WHEN :status = 'clean' THEN NOW() ELSE NULL END, :housekeeper_id)";
    }
    
    $stmt = $db->prepare($query);
    $stmt->bindParam(':room_id', $room_id);
    $stmt->bindParam(':status', $status);
    $stmt->bindParam(':notes', $notes);
    $stmt->bindParam(':housekeeper_id', $_SESSION['user_id']);
    
    if ($stmt->execute()) {
        $success = "Room status updated successfully!";
    } else {
        $error = "Failed to update room status.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Housekeeper Dashboard - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
    <div class="admin-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="sidebar-header">
                <h2><?php echo SITE_NAME; ?></h2>
                <p>Housekeeper Panel</p>
            </div>
            <ul class="sidebar-menu">
                <li><a href="index.php" class="active">Dashboard</a></li>
                <li><a href="rooms.php">Room Status</a></li>
                <li><a href="report_issue.php">Report Issue</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <div class="content-header">
                <h1>Housekeeper Dashboard</h1>
                <p>Welcome, <?php echo $_SESSION['first_name']; ?>!</p>
            </div>

            <?php if (isset($success)): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>

            <?php if (isset($error)): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>

            <!-- Room Status Overview -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">🧹</div>
                    <div class="stat-info">
                        <h3>
                            <?php
                            $query = "SELECT COUNT(*) as total FROM room_status WHERE status = 'unclean'";
                            $stmt = $db->prepare($query);
                            $stmt->execute();
                            $result = $stmt->fetch(PDO::FETCH_ASSOC);
                            echo $result['total'];
                            ?>
                        </h3>
                        <p>Rooms to Clean</p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon">✅</div>
                    <div class="stat-info">
                        <h3>
                            <?php
                            $query = "SELECT COUNT(*) as total FROM room_status WHERE status = 'clean'";
                            $stmt = $db->prepare($query);
                            $stmt->execute();
                            $result = $stmt->fetch(PDO::FETCH_ASSOC);
                            echo $result['total'];
                            ?>
                        </h3>
                        <p>Clean Rooms</p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon">🔧</div>
                    <div class="stat-info">
                        <h3>
                            <?php
                            $query = "SELECT COUNT(*) as total FROM room_status WHERE status = 'maintenance'";
                            $stmt = $db->prepare($query);
                            $stmt->execute();
                            $result = $stmt->fetch(PDO::FETCH_ASSOC);
                            echo $result['total'];
                            ?>
                        </h3>
                        <p>Maintenance Needed</p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon">📋</div>
                    <div class="stat-info">
                        <h3>
                            <?php
                            $query = "SELECT COUNT(*) as total FROM room_issues WHERE status = 'reported'";
                            $stmt = $db->prepare($query);
                            $stmt->execute();
                            $result = $stmt->fetch(PDO::FETCH_ASSOC);
                            echo $result['total'];
                            ?>
                        </h3>
                        <p>Open Issues</p>
                    </div>
                </div>
            </div>

            <!-- Rooms Needing Cleaning -->
            <div class="dashboard-section">
                <h2>Rooms Needing Cleaning</h2>
                <div class="rooms-grid">
                    <?php
                    $query = "SELECT r.*, rs.status, rs.notes, rs.last_cleaned 
                             FROM rooms r 
                             LEFT JOIN room_status rs ON r.id = rs.room_id 
                             WHERE rs.status = 'unclean' OR rs.status IS NULL
                             ORDER BY r.room_number";
                    $stmt = $db->prepare($query);
                    $stmt->execute();
                    
                    if ($stmt->rowCount() > 0):
                        while ($room = $stmt->fetch(PDO::FETCH_ASSOC)):
                    ?>
                    <div class="room-card status-<?php echo $room['status'] ?? 'unclean'; ?>">
                        <div class="room-header">
                            <h3>Room <?php echo $room['room_number']; ?></h3>
                            <span class="status-indicator"><?php echo ucfirst($room['status'] ?? 'unclean'); ?></span>
                        </div>
                        <div class="room-info">
                            <p><strong>Type:</strong> <?php echo getRoomTypeName($room['room_type']); ?></p>
                            <p><strong>Max Guests:</strong> <?php echo $room['max_guests']; ?></p>
                            <?php if ($room['last_cleaned']): ?>
                            <p><strong>Last Cleaned:</strong> <?php echo formatDate($room['last_cleaned'], 'M j, Y'); ?></p>
                            <?php endif; ?>
                        </div>
                        <form method="POST" class="status-form">
                            <input type="hidden" name="room_id" value="<?php echo $room['id']; ?>">
                            <div class="form-group">
                                <label class="form-label">Update Status:</label>
                                <select name="status" class="form-control" required>
                                    <option value="clean">Clean</option>
                                    <option value="unclean" selected>Unclean</option>
                                    <option value="maintenance">Maintenance Needed</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Notes (Optional):</label>
                                <textarea name="notes" class="form-control" rows="2" 
                                          placeholder="Any notes about this room..."><?php echo $room['notes'] ?? ''; ?></textarea>
                            </div>
                            <button type="submit" name="update_status" class="btn btn-primary">Update Status</button>
                        </form>
                    </div>
                    <?php 
                        endwhile;
                    else:
                    ?>
                    <div class="no-rooms">
                        <p>All rooms are clean! 🎉</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>