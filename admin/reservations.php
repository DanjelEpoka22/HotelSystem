<?php
require_once '../config/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

requireRole('admin');

// Handle reservation actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_status'])) {
        $reservation_id = $_POST['reservation_id'];
        $status = $_POST['status'];
        
        $query = "UPDATE reservations SET status = :status WHERE id = :id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':id', $reservation_id);
        
        if ($stmt->execute()) {
            $success = "Reservation status updated successfully!";
        } else {
            $error = "Failed to update reservation status.";
        }
    }
}

// Get filter parameters
$status_filter = $_GET['status'] ?? '';
$source_filter = $_GET['source'] ?? '';
$date_from = $_GET['date_from'] ?? '';
$date_to = $_GET['date_to'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reservation Management - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
    <div class="admin-container">
        <!-- Sidebar -->
        <?php include 'sidebar.php'; ?>

        <!-- Main Content -->
        <div class="main-content">
            <div class="content-header">
                <h1>Reservation Management</h1>
                <p>Manage all hotel reservations</p>
            </div>

            <?php if (isset($success)): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>

            <?php if (isset($error)): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>

            <!-- Filters -->
            <div class="dashboard-section">
                <h2>Filters</h2>
                <form method="GET" class="filter-form">
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-control">
                                <option value="">All Statuses</option>
                                <option value="pending" <?php echo $status_filter === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                <option value="confirmed" <?php echo $status_filter === 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                                <option value="checked_in" <?php echo $status_filter === 'checked_in' ? 'selected' : ''; ?>>Checked In</option>
                                <option value="checked_out" <?php echo $status_filter === 'checked_out' ? 'selected' : ''; ?>>Checked Out</option>
                                <option value="cancelled" <?php echo $status_filter === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Source</label>
                            <select name="source" class="form-control">
                                <option value="">All Sources</option>
                                <option value="website" <?php echo $source_filter === 'website' ? 'selected' : ''; ?>>Website</option>
                                <option value="booking_com" <?php echo $source_filter === 'booking_com' ? 'selected' : ''; ?>>Booking.com</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Date From</label>
                            <input type="date" name="date_from" class="form-control" value="<?php echo $date_from; ?>">
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Date To</label>
                            <input type="date" name="date_to" class="form-control" value="<?php echo $date_to; ?>">
                        </div>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">Apply Filters</button>
                        <a href="reservations.php" class="btn btn-secondary">Clear Filters</a>
                    </div>
                </form>
            </div>

            <!-- Reservations List -->
            <div class="dashboard-section">
                <h2>All Reservations</h2>
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Guest</th>
                                <th>Room</th>
                                <th>Check-in</th>
                                <th>Check-out</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th>Source</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // Build query with filters
                            $query = "SELECT r.*, u.first_name, u.last_name, u.email, rm.room_number, rm.room_type 
                                     FROM reservations r 
                                     JOIN users u ON r.user_id = u.id 
                                     JOIN rooms rm ON r.room_id = rm.id 
                                     WHERE 1=1";
                            
                            $params = [];
                            
                            if (!empty($status_filter)) {
                                $query .= " AND r.status = :status";
                                $params['status'] = $status_filter;
                            }
                            
                            if (!empty($source_filter)) {
                                $query .= " AND r.source = :source";
                                $params['source'] = $source_filter;
                            }
                            
                            if (!empty($date_from)) {
                                $query .= " AND r.check_in >= :date_from";
                                $params['date_from'] = $date_from;
                            }
                            
                            if (!empty($date_to)) {
                                $query .= " AND r.check_out <= :date_to";
                                $params['date_to'] = $date_to;
                            }
                            
                            $query .= " ORDER BY r.created_at DESC";
                            
                            $stmt = $db->prepare($query);
                            $stmt->execute($params);
                            
                            while ($reservation = $stmt->fetch(PDO::FETCH_ASSOC)):
                            ?>
                            <tr>
                                <td>#<?php echo $reservation['id']; ?></td>
                                <td>
                                    <strong><?php echo $reservation['first_name'] . ' ' . $reservation['last_name']; ?></strong><br>
                                    <small><?php echo $reservation['email']; ?></small>
                                </td>
                                <td>
                                    <?php echo $reservation['room_number']; ?><br>
                                    <small><?php echo getRoomTypeName($reservation['room_type']); ?></small>
                                </td>
                                <td><?php echo formatDate($reservation['check_in']); ?></td>
                                <td><?php echo formatDate($reservation['check_out']); ?></td>
                                <td>€<?php echo $reservation['total_price']; ?></td>
                                <td>
                                    <form method="POST" class="status-form">
                                        <input type="hidden" name="reservation_id" value="<?php echo $reservation['id']; ?>">
                                        <select name="status" onchange="this.form.submit()" class="status-select">
                                            <option value="pending" <?php echo $reservation['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                            <option value="confirmed" <?php echo $reservation['status'] === 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                                            <option value="checked_in" <?php echo $reservation['status'] === 'checked_in' ? 'selected' : ''; ?>>Checked In</option>
                                            <option value="checked_out" <?php echo $reservation['status'] === 'checked_out' ? 'selected' : ''; ?>>Checked Out</option>
                                            <option value="cancelled" <?php echo $reservation['status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                        </select>
                                    </form>
                                </td>
                                <td>
                                    <span class="source-badge source-<?php echo $reservation['source']; ?>">
                                        <?php echo strtoupper($reservation['source']); ?>
                                    </span>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-secondary" onclick="viewReservation(<?php echo $reservation['id']; ?>)">View</button>
                                    <button class="btn btn-sm btn-danger" onclick="cancelReservation(<?php echo $reservation['id']; ?>)">Cancel</button>
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
    function viewReservation(reservationId) {
        window.open('reservation_details.php?id=' + reservationId, '_blank');
    }

    function cancelReservation(reservationId) {
        if (confirm('Are you sure you want to cancel this reservation?')) {
            fetch('ajax/reservation_actions.php?action=cancel&id=' + reservationId)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Error: ' + data.error);
                    }
                });
        }
    }
    </script>
</body>
</html>