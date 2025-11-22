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
                                        <input type="hidden" name="update_status" value="1">
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

    <!-- Reservation Details Modal -->
    <div id="reservationModal" class="modal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.5);align-items:center;justify-content:center;">
        <div class="modal-content" style="background:#fff;padding:20px;width:90%;max-width:700px;border-radius:6px;position:relative;">
            <button id="modalClose" style="position:absolute;right:10px;top:10px;">&times;</button>
            <h3>Reservation Details</h3>
            <div id="reservationDetails">
                <!-- Populated by JS -->
                <p><strong>ID:</strong> <span id="res_id"></span></p>
                <p><strong>Guest:</strong> <span id="res_guest"></span></p>
                <p><strong>Email:</strong> <span id="res_email"></span></p>
                <p><strong>Phone:</strong> <span id="res_phone"></span></p>
                <p><strong>Room:</strong> <span id="res_room"></span></p>
                <p><strong>Room Type:</strong> <span id="res_room_type"></span></p>
                <p><strong>Check-in:</strong> <span id="res_checkin"></span></p>
                <p><strong>Check-out:</strong> <span id="res_checkout"></span></p>
                <p><strong>Total:</strong> €<span id="res_total"></span></p>
                <p><strong>Payment:</strong> <span id="res_payment_status"></span> <small id="res_transaction"></small></p>
                <p><strong>Status:</strong> <span id="res_status"></span></p>
                <p><strong>Source:</strong> <span id="res_source"></span></p>
                <p><strong>Notes:</strong> <span id="res_notes"></span></p>
            </div>

            <div style="margin-top:15px;text-align:right;">
                <button id="modalCancelBtn" class="btn btn-danger" style="display:none;">Cancel Reservation</button>
                <button id="modalCloseBtn" class="btn btn-secondary">Close</button>
            </div>
        </div>
    </div>

    <script>
    // Show reservation details in modal by calling admin/ajax/reservation_actions.php?action=get_details
    function viewReservation(reservationId) {
        fetch('ajax/reservation_actions.php?action=get_details&id=' + encodeURIComponent(reservationId))
            .then(response => response.json())
            .then(data => {
                if (data.success && data.reservation) {
                    const r = data.reservation;
                    document.getElementById('res_id').textContent = r.id;
                    document.getElementById('res_guest').textContent = (r.first_name || '') + ' ' + (r.last_name || '');
                    document.getElementById('res_email').textContent = r.email || '';
                    document.getElementById('res_phone').textContent = r.phone || '';
                    document.getElementById('res_room').textContent = r.room_number || '';
                    document.getElementById('res_room_type').textContent = r.room_type_name || r.room_type || '';
                    document.getElementById('res_checkin').textContent = r.check_in ? new Date(r.check_in).toLocaleDateString() : '';
                    document.getElementById('res_checkout').textContent = r.check_out ? new Date(r.check_out).toLocaleDateString() : '';
                    document.getElementById('res_total').textContent = r.total_price || r.total || '';
                    document.getElementById('res_payment_status').textContent = r.payment_status || 'N/A';
                    document.getElementById('res_transaction').textContent = r.transaction_id ? ('(tx: '+r.transaction_id+')') : '';
                    document.getElementById('res_status').textContent = r.status || '';
                    document.getElementById('res_source').textContent = r.source || '';
                    document.getElementById('res_notes').textContent = r.notes || '';

                    // Show cancel button only if not already cancelled/checked_out
                    const cancelBtn = document.getElementById('modalCancelBtn');
                    if (r.status && (r.status === 'cancelled' || r.status === 'checked_out')) {
                        cancelBtn.style.display = 'none';
                    } else {
                        cancelBtn.style.display = 'inline-block';
                        // attach handler
                        cancelBtn.onclick = function() {
                            if (!confirm('Are you sure you want to cancel reservation #' + r.id + '?')) return;
                            cancelReservation(r.id, true);
                        };
                    }

                    // show modal
                    const modal = document.getElementById('reservationModal');
                    modal.style.display = 'flex';
                } else {
                    alert('Error fetching reservation details: ' + (data.error || 'Unknown error'));
                }
            })
            .catch(err => {
                alert('Failed to fetch reservation details: ' + err.message);
            });
    }

    // Cancel reservation via AJAX. If fromModal = true, close modal on success.
    function cancelReservation(reservationId, fromModal = false) {
        if (!fromModal && !confirm('Are you sure you want to cancel this reservation?')) return;

        fetch('ajax/reservation_actions.php?action=cancel&id=' + encodeURIComponent(reservationId))
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    if (fromModal) {
                        // close modal
                        document.getElementById('reservationModal').style.display = 'none';
                    }
                    // reload to reflect changes
                    location.reload();
                } else {
                    alert('Error cancelling reservation: ' + (data.error || 'Unknown error'));
                }
            })
            .catch(err => {
                alert('Failed to cancel reservation: ' + err.message);
            });
    }

    // Modal close handlers
    document.getElementById('modalClose').addEventListener('click', function(){ document.getElementById('reservationModal').style.display = 'none'; });
    document.getElementById('modalCloseBtn').addEventListener('click', function(){ document.getElementById('reservationModal').style.display = 'none'; });

    // close modal when clicking outside content
    document.getElementById('reservationModal').addEventListener('click', function(e){
        if (e.target === this) this.style.display = 'none';
    });
    </script>
</body>
</html>