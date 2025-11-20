<?php
require_once '../config/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
requireRole('receptionist');

// Handle check-out
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['checkout_id'])) {
    $reservation_id = $_POST['checkout_id'];
    $checkout_notes = $_POST['checkout_notes'] ?? '';
    
    // Start transaction
    $db->beginTransaction();
    
    try {
        // Update reservation status
        $query = "UPDATE reservations SET status = 'checked_out' WHERE id = :id AND status = 'checked_in'";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':id', $reservation_id);
        $stmt->execute();
        
        // Get room ID
        $roomQuery = "SELECT room_id FROM reservations WHERE id = :id";
        $roomStmt = $db->prepare($roomQuery);
        $roomStmt->bindParam(':id', $reservation_id);
        $roomStmt->execute();
        $reservation = $roomStmt->fetch(PDO::FETCH_ASSOC);
        
        if ($reservation) {
            // Update room status to unclean
            $statusQuery = "INSERT INTO room_status (room_id, status, notes) 
                           VALUES (:room_id, 'unclean', 'Auto-marked unclean after checkout')
                           ON DUPLICATE KEY UPDATE status = 'unclean', notes = 'Auto-marked unclean after checkout'";
            $statusStmt = $db->prepare($statusQuery);
            $statusStmt->bindParam(':room_id', $reservation['room_id']);
            $statusStmt->execute();
        }
        
        $db->commit();
        $success = "Guest checked out successfully! Room marked for cleaning.";
        
    } catch (Exception $e) {
        $db->rollBack();
        $error = "Failed to check out guest: " . $e->getMessage();
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
              WHERE r.id = :id AND r.status = 'checked_in'";
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
    <title>Check-out - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
    <div class="admin-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="sidebar-header">
                <h2><?php echo SITE_NAME; ?></h2>
                <p>Receptionist Panel</p>
            </div>
            <ul class="sidebar-menu">
                <li><a href="index.php">Dashboard</a></li>
                <li><a href="calendar.php">Calendar</a></li>
                <li><a href="checkin.php">Check-in</a></li>
                <li><a href="checkout.php" class="active">Check-out</a></li>
                <li><a href="guests.php">Guests</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <div class="content-header">
                <h1>Guest Check-out</h1>
                <p>Process guest departures</p>
            </div>

            <?php if (isset($success)): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>

            <?php if (isset($error)): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>

            <?php if ($reservation): ?>
            <!-- Check-out Form -->
            <div class="dashboard-section">
                <h2>Check-out Details</h2>
                <div class="checkout-details">
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
                            <p><strong>Total Paid:</strong> €<?php echo $reservation['total_price']; ?></p>
                        </div>
                    </div>

                    <?php if ($reservation['special_requests']): ?>
                    <div class="detail-group">
                        <h3>Special Requests</h3>
                        <p><?php echo $reservation['special_requests']; ?></p>
                    </div>
                    <?php endif; ?>

                    <form method="POST" class="checkout-form">
                        <input type="hidden" name="checkout_id" value="<?php echo $reservation['id']; ?>">
                        
                        <div class="form-group">
                            <label class="form-label">Check-out Notes (Optional)</label>
                            <textarea name="checkout_notes" class="form-control" rows="3" 
                                      placeholder="Any notes about the check-out process, room condition, etc..."></textarea>
                        </div>
                        
                        <div class="payment-summary">
                            <h3>Payment Summary</h3>
                            <p><strong>Room Charges:</strong> €<?php echo $reservation['total_price']; ?></p>
                            <p><strong>Payment Status:</strong> 
                                <span class="status-badge status-<?php echo $reservation['payment_status']; ?>">
                                    <?php echo ucfirst($reservation['payment_status']); ?>
                                </span>
                            </p>
                            
                            <?php if ($reservation['payment_status'] !== 'paid'): ?>
                            <div class="alert alert-warning">
                                <strong>Payment Pending:</strong> Please collect payment before completing check-out.
                            </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="form-actions">
                            <a href="index.php" class="btn btn-secondary">Back</a>
                            <button type="submit" class="btn btn-primary" 
                                    <?php echo $reservation['payment_status'] !== 'paid' ? 'disabled' : ''; ?>>
                                Complete Check-out
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            <?php else: ?>
            <!-- Search for Reservation -->
            <div class="dashboard-section">
                <h2>Find Reservation for Check-out</h2>
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
                             AND r.status = 'checked_in'
                             AND r.check_out <= CURDATE()";
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
                                    <th>Payment Status</th>
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
                                        <span class="status-badge status-<?php echo $result['payment_status']; ?>">
                                            <?php echo ucfirst($result['payment_status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="checkout.php?id=<?php echo $result['id']; ?>" class="btn btn-sm btn-primary">Check-out</a>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <?php else: ?>
                <p>No checked-in reservations found matching your search criteria.</p>
                <?php endif; } ?>
            </div>

            <!-- Today's Departures -->
            <div class="dashboard-section">
                <h2>Today's Departures</h2>
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Guest Name</th>
                                <th>Room</th>
                                <th>Check-out</th>
                                <th>Payment Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $query = "SELECT r.*, u.first_name, u.last_name, rm.room_number, rm.room_type 
                                     FROM reservations r 
                                     JOIN users u ON r.user_id = u.id 
                                     JOIN rooms rm ON r.room_id = rm.id 
                                     WHERE r.check_out = CURDATE() 
                                     AND r.status = 'checked_in' 
                                     ORDER BY r.check_out";
                            $stmt = $db->prepare($query);
                            $stmt->execute();
                            
                            while ($reservation = $stmt->fetch(PDO::FETCH_ASSOC)):
                            ?>
                            <tr>
                                <td><?php echo $reservation['first_name'] . ' ' . $reservation['last_name']; ?></td>
                                <td><?php echo $reservation['room_number'] . ' - ' . getRoomTypeName($reservation['room_type']); ?></td>
                                <td><?php echo formatDate($reservation['check_out']); ?></td>
                                <td>
                                    <span class="status-badge status-<?php echo $reservation['payment_status']; ?>">
                                        <?php echo ucfirst($reservation['payment_status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="checkout.php?id=<?php echo $reservation['id']; ?>" class="btn btn-sm btn-primary">Check-out</a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>