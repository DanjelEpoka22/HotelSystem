<?php
require_once '../config/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
requireRole('receptionist');

// Handle check-in
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['checkin_id'])) {
    $reservation_id = $_POST['checkin_id'];
    
    $query = "UPDATE reservations SET status = 'checked_in' WHERE id = :id AND status = 'confirmed'";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $reservation_id);
    
    if ($stmt->execute()) {
        $success = "Guest checked in successfully!";
    } else {
        $error = "Failed to check in guest.";
    }
}

// Get reservation details if ID provided
$reservation = null;
if (isset($_GET['id'])) {
    $query = "SELECT r.*, u.first_name, u.last_name, u.phone, u.email, 
                     rm.room_number, rm.room_type, rm.price_per_night
              FROM reservations r 
              JOIN users u ON r.user_id = u.id 
              JOIN rooms rm ON r.room_id = rm.id 
              WHERE r.id = :id AND r.status = 'confirmed'";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $_GET['id']);
    $stmt->execute();
    $reservation = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Check-in - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
    <div class="admin-container">
        <!-- Sidebar -->
        <?php include 'sidebar.php'; ?>

        <!-- Main Content -->
        <div class="main-content">
            <div class="content-header">
                <h1>Guest Check-in</h1>
                <p>Process guest arrivals</p>
            </div>

            <?php if (isset($success)): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>

            <?php if (isset($error)): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>

            <?php if ($reservation): ?>
            <!-- Check-in Form -->
            <div class="dashboard-section">
                <h2>Check-in Details</h2>
                <div class="checkin-details">
                    <div class="detail-row">
                        <div class="detail-group">
                            <h3>Guest Information</h3>
                            <p><strong>Name:</strong> <?php echo $reservation['first_name'] . ' ' . $reservation['last_name']; ?></p>
                            <p><strong>Email:</strong> <?php echo $reservation['email']; ?></p>
                            <p><strong>Phone:</strong> <?php echo $reservation['phone']; ?></p>
                        </div>
                        
                        <div class="detail-group">
                            <h3>Reservation Details</h3>
                            <p><strong>Room:</strong> <?php echo $reservation['room_number'] . ' - ' . getRoomTypeName($reservation['room_type']); ?></p>
                            <p><strong>Check-in:</strong> <?php echo formatDate($reservation['check_in']); ?></p>
                            <p><strong>Check-out:</strong> <?php echo formatDate($reservation['check_out']); ?></p>
                            <p><strong>Guests:</strong> <?php echo $reservation['guests']; ?></p>
                            <p><strong>Total Price:</strong> €<?php echo $reservation['total_price']; ?></p>
                        </div>
                    </div>

                    <?php if ($reservation['special_requests']): ?>
                    <div class="detail-group">
                        <h3>Special Requests</h3>
                        <p><?php echo $reservation['special_requests']; ?></p>
                    </div>
                    <?php endif; ?>

                    <form method="POST" class="checkin-form">
                        <input type="hidden" name="checkin_id" value="<?php echo $reservation['id']; ?>">
                        
                        <div class="form-group">
                            <label class="form-label">Additional Notes (Optional)</label>
                            <textarea name="checkin_notes" class="form-control" rows="3" 
                                      placeholder="Any additional notes for this check-in..."></textarea>
                        </div>
                        
                        <div class="form-actions">
                            <a href="index.php" class="btn btn-secondary">Back</a>
                            <button type="submit" class="btn btn-success">Complete Check-in</button>
                        </div>
                    </form>
                </div>
            </div>
            <?php else: ?>
            <!-- Search for Reservation -->
            <div class="dashboard-section">
                <h2>Find Reservation</h2>
                <form method="GET" class="search-form">
                    <div class="form-group">
                        <label class="form-label">Reservation ID or Guest Name</label>
                        <input type="text" name="search" class="form-control" 
                               placeholder="Enter reservation ID or guest name..." required>
                    </div>
                    <button type="submit" class="btn btn-primary">Search</button>
                </form>

                <?php
                if (isset($_GET['search'])) {
                    $search = '%' . $_GET['search'] . '%';
                    $query = "SELECT r.*, u.first_name, u.last_name, rm.room_number, rm.room_type
                             FROM reservations r 
                             JOIN users u ON r.user_id = u.id 
                             JOIN rooms rm ON r.room_id = rm.id 
                             WHERE (r.id LIKE :search OR u.first_name LIKE :search OR u.last_name LIKE :search)
                             AND r.status = 'confirmed'
                             AND r.check_in <= CURDATE() 
                             AND r.check_out >= CURDATE()";
                    $stmt = $db->prepare($query);
                    $stmt->bindParam(':search', $search);
                    $stmt->execute();
                    
                    if ($stmt->rowCount() > 0):
                ?>
                <div class="search-results">
                    <h3>Search Results</h3>
                    <div class="table-container">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Reservation ID</th>
                                    <th>Guest Name</th>
                                    <th>Room</th>
                                    <th>Check-in</th>
                                    <th>Check-out</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($result = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
                                <tr>
                                    <td>#<?php echo $result['id']; ?></td>
                                    <td><?php echo $result['first_name'] . ' ' . $result['last_name']; ?></td>
                                    <td><?php echo $result['room_number'] . ' - ' . getRoomTypeName($result['room_type']); ?></td>
                                    <td><?php echo formatDate($result['check_in']); ?></td>
                                    <td><?php echo formatDate($result['check_out']); ?></td>
                                    <td>
                                        <a href="checkin.php?id=<?php echo $result['id']; ?>" class="btn btn-sm btn-success">Check-in</a>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <?php else: ?>
                <p>No reservations found matching your search criteria.</p>
                <?php endif; } ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>