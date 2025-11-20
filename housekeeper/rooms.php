<?php
require_once '../config/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
requireRole('housekeeper');

// Handle room status update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_status'])) {
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
}

// Get filter parameter
$status_filter = $_GET['status'] ?? 'all';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Room Status - <?php echo SITE_NAME; ?></title>
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
                <li><a href="index.php">Dashboard</a></li>
                <li><a href="rooms.php" class="active">Room Status</a></li>
                <li><a href="report_issue.php">Report Issue</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <div class="content-header">
                <h1>Room Status Management</h1>
                <p>Update room cleaning status and report issues</p>
            </div>

            <?php if (isset($success)): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>

            <?php if (isset($error)): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>

            <!-- Filters -->
            <div class="dashboard-section">
                <h2>Filter Rooms</h2>
                <form method="GET" class="filter-form">
                    <div class="form-group">
                        <label class="form-label">Room Status</label>
                        <select name="status" class="form-control" onchange="this.form.submit()">
                            <option value="all" <?php echo $status_filter === 'all' ? 'selected' : ''; ?>>All Rooms</option>
                            <option value="unclean" <?php echo $status_filter === 'unclean' ? 'selected' : ''; ?>>Need Cleaning</option>
                            <option value="clean" <?php echo $status_filter === 'clean' ? 'selected' : ''; ?>>Clean</option>
                            <option value="maintenance" <?php echo $status_filter === 'maintenance' ? 'selected' : ''; ?>>Maintenance</option>
                        </select>
                    </div>
                </form>
            </div>

            <!-- Rooms Grid -->
            <div class="dashboard-section">
                <h2>Room Status</h2>
                <div class="rooms-grid">
                    <?php
                    // Build query based on filter
                    $query = "SELECT r.*, rs.status as room_status, rs.notes, rs.last_cleaned 
                             FROM rooms r 
                             LEFT JOIN room_status rs ON r.id = rs.room_id";
                    
                    switch ($status_filter) {
                        case 'unclean':
                            $query .= " WHERE rs.status = 'unclean' OR rs.status IS NULL";
                            break;
                        case 'clean':
                            $query .= " WHERE rs.status = 'clean'";
                            break;
                        case 'maintenance':
                            $query .= " WHERE rs.status = 'maintenance'";
                            break;
                        default:
                            // Show all rooms
                            break;
                    }
                    
                    $query .= " ORDER BY r.room_number";
                    
                    $stmt = $db->prepare($query);
                    $stmt->execute();
                    
                    if ($stmt->rowCount() > 0):
                        while ($room = $stmt->fetch(PDO::FETCH_ASSOC)):
                    ?>
                    <div class="room-card status-<?php echo $room['room_status'] ?? 'unclean'; ?>">
                        <div class="room-header">
                            <h3>Room <?php echo $room['room_number']; ?></h3>
                            <span class="status-indicator"><?php echo ucfirst($room['room_status'] ?? 'unclean'); ?></span>
                        </div>
                        
                        <div class="room-info">
                            <p><strong>Type:</strong> <?php echo getRoomTypeName($room['room_type']); ?></p>
                            <p><strong>Max Guests:</strong> <?php echo $room['max_guests']; ?></p>
                            
                            <?php if ($room['last_cleaned']): ?>
                            <p><strong>Last Cleaned:</strong> <?php echo formatDate($room['last_cleaned'], 'M j, Y g:i A'); ?></p>
                            <?php endif; ?>
                            
                            <?php if ($room['notes']): ?>
                            <p><strong>Notes:</strong> <?php echo htmlspecialchars($room['notes']); ?></p>
                            <?php endif; ?>
                        </div>
                        
                        <form method="POST" class="status-form">
                            <input type="hidden" name="room_id" value="<?php echo $room['id']; ?>">
                            
                            <div class="form-group">
                                <label class="form-label">Update Status:</label>
                                <select name="status" class="form-control" required>
                                    <option value="clean" <?php echo ($room['room_status'] ?? '') === 'clean' ? 'selected' : ''; ?>>Clean</option>
                                    <option value="unclean" <?php echo ($room['room_status'] ?? '') === 'unclean' ? 'selected' : ''; ?>>Unclean</option>
                                    <option value="maintenance" <?php echo ($room['room_status'] ?? '') === 'maintenance' ? 'selected' : ''; ?>>Maintenance Needed</option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Notes:</label>
                                <textarea name="notes" class="form-control" rows="2" 
                                          placeholder="Any notes about this room..."><?php echo $room['notes'] ?? ''; ?></textarea>
                            </div>
                            
                            <button type="submit" name="update_status" class="btn btn-primary">Update Status</button>
                            
                            <?php if (($room['room_status'] ?? '') !== 'clean'): ?>
                            <button type="button" class="btn btn-warning" 
                                    onclick="reportIssue(<?php echo $room['id']; ?>, '<?php echo $room['room_number']; ?>')">
                                Report Issue
                            </button>
                            <?php endif; ?>
                        </form>
                    </div>
                    <?php 
                        endwhile;
                    else:
                    ?>
                    <div class="no-rooms">
                        <p>No rooms found with the selected filter.</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="dashboard-section">
                <h2>Quick Actions</h2>
                <div class="quick-actions-grid">
                    <div class="action-card">
                        <h3>Mark All as Clean</h3>
                        <p>Mark all unclean rooms as cleaned</p>
                        <button class="btn btn-success" onclick="markAllClean()">Mark All Clean</button>
                    </div>
                    
                    <div class="action-card">
                        <h3>Today's Checklist</h3>
                        <p>View today's cleaning schedule</p>
                        <button class="btn btn-primary" onclick="viewTodaysChecklist()">View Checklist</button>
                    </div>
                    
                    <div class="action-card">
                        <h3>Supplies Needed</h3>
                        <p>Report low supplies</p>
                        <button class="btn btn-warning" onclick="reportSupplies()">Report Supplies</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
    function reportIssue(roomId, roomNumber) {
        const issueType = prompt('Enter issue type (e.g., Broken TV, Leaky faucet, etc.):');
        if (issueType) {
            const description = prompt('Enter issue description:');
            if (description) {
                // Submit issue report
                fetch('report_issue.php?action=report', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        room_id: roomId,
                        issue_type: issueType,
                        description: description,
                        priority: 'medium'
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Issue reported successfully!');
                        location.reload();
                    } else {
                        alert('Error reporting issue: ' + data.error);
                    }
                });
            }
        }
    }

    function markAllClean() {
        if (confirm('Mark all unclean rooms as clean?')) {
            fetch('ajax/housekeeper_actions.php?action=mark_all_clean')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('All rooms marked as clean!');
                        location.reload();
                    } else {
                        alert('Error: ' + data.error);
                    }
                });
        }
    }

    function viewTodaysChecklist() {
        // Open checklist in new window
        window.open('checklist.php', '_blank');
    }

    function reportSupplies() {
        const supplies = prompt('Enter supplies needed (comma separated):');
        if (supplies) {
            // Report supplies (this would typically send a notification to management)
            alert('Supplies reported: ' + supplies);
        }
    }
    </script>
</body>
</html>