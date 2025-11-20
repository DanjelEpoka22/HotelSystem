<?php
require_once '../config/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
requireRole('receptionist');

// Get filter parameters
$status_filter = $_GET['status'] ?? 'checked_in';
$search = $_GET['search'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Guest List - <?php echo SITE_NAME; ?></title>
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
                <li><a href="checkout.php">Check-out</a></li>
                <li><a href="guests.php" class="active">Guests</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <div class="content-header">
                <h1>Guest Management</h1>
                <p>View and manage current and past guests</p>
            </div>

            <!-- Filters -->
            <div class="dashboard-section">
                <h2>Filters</h2>
                <form method="GET" class="filter-form">
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Guest Status</label>
                            <select name="status" class="form-control" onchange="this.form.submit()">
                                <option value="checked_in" <?php echo $status_filter === 'checked_in' ? 'selected' : ''; ?>>Currently Checked-in</option>
                                <option value="all_current" <?php echo $status_filter === 'all_current' ? 'selected' : ''; ?>>All Current Guests</option>
                                <option value="arriving_today" <?php echo $status_filter === 'arriving_today' ? 'selected' : ''; ?>>Arriving Today</option>
                                <option value="departing_today" <?php echo $status_filter === 'departing_today' ? 'selected' : ''; ?>>Departing Today</option>
                                <option value="all" <?php echo $status_filter === 'all' ? 'selected' : ''; ?>>All Guests</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Search</label>
                            <input type="text" name="search" class="form-control" 
                                   placeholder="Search by guest name, room number..." 
                                   value="<?php echo htmlspecialchars($search); ?>">
                        </div>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">Apply Filters</button>
                        <a href="guests.php" class="btn btn-secondary">Clear Filters</a>
                    </div>
                </form>
            </div>

            <!-- Guest List -->
            <div class="dashboard-section">
                <h2>Guest List</h2>
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Guest Name</th>
                                <th>Contact Info</th>
                                <th>Room</th>
                                <th>Check-in</th>
                                <th>Check-out</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // Build query based on filters
                            $query = "SELECT r.*, u.first_name, u.last_name, u.email, u.phone, 
                                             rm.room_number, rm.room_type
                                     FROM reservations r 
                                     JOIN users u ON r.user_id = u.id 
                                     JOIN rooms rm ON r.room_id = rm.id 
                                     WHERE 1=1";
                            
                            $params = [];
                            
                            switch ($status_filter) {
                                case 'checked_in':
                                    $query .= " AND r.status = 'checked_in'";
                                    break;
                                case 'all_current':
                                    $query .= " AND r.status IN ('confirmed', 'checked_in') 
                                              AND r.check_in <= CURDATE() 
                                              AND r.check_out >= CURDATE()";
                                    break;
                                case 'arriving_today':
                                    $query .= " AND r.check_in = CURDATE() 
                                              AND r.status = 'confirmed'";
                                    break;
                                case 'departing_today':
                                    $query .= " AND r.check_out = CURDATE() 
                                              AND r.status = 'checked_in'";
                                    break;
                                case 'all':
                                    // No additional filters
                                    break;
                            }
                            
                            if (!empty($search)) {
                                $query .= " AND (u.first_name LIKE :search OR u.last_name LIKE :search 
                                          OR u.email LIKE :search OR rm.room_number LIKE :search)";
                                $params['search'] = "%$search%";
                            }
                            
                            $query .= " ORDER BY r.check_in DESC, r.status";
                            
                            $stmt = $db->prepare($query);
                            $stmt->execute($params);
                            
                            while ($reservation = $stmt->fetch(PDO::FETCH_ASSOC)):
                            ?>
                            <tr>
                                <td>
                                    <strong><?php echo $reservation['first_name'] . ' ' . $reservation['last_name']; ?></strong><br>
                                    <small>Guests: <?php echo $reservation['guests']; ?></small>
                                </td>
                                <td>
                                    <div><?php echo $reservation['email']; ?></div>
                                    <div><?php echo $reservation['phone']; ?></div>
                                </td>
                                <td>
                                    <strong>Room <?php echo $reservation['room_number']; ?></strong><br>
                                    <small><?php echo getRoomTypeName($reservation['room_type']); ?></small>
                                </td>
                                <td><?php echo formatDate($reservation['check_in']); ?></td>
                                <td><?php echo formatDate($reservation['check_out']); ?></td>
                                <td>
                                    <span class="status-badge status-<?php echo $reservation['status']; ?>">
                                        <?php echo ucfirst($reservation['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <?php if ($reservation['status'] === 'confirmed' && $reservation['check_in'] <= date('Y-m-d')): ?>
                                            <a href="checkin.php?id=<?php echo $reservation['id']; ?>" class="btn btn-sm btn-success">Check-in</a>
                                        <?php endif; ?>
                                        
                                        <?php if ($reservation['status'] === 'checked_in'): ?>
                                            <a href="checkout.php?id=<?php echo $reservation['id']; ?>" class="btn btn-sm btn-primary">Check-out</a>
                                        <?php endif; ?>
                                        
                                        <button class="btn btn-sm btn-secondary" 
                                                onclick="viewGuestDetails(<?php echo $reservation['id']; ?>)">
                                            Details
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Quick Stats -->
            <div class="dashboard-section">
                <h2>Quick Statistics</h2>
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon">👥</div>
                        <div class="stat-info">
                            <h3>
                                <?php
                                $query = "SELECT COUNT(DISTINCT user_id) as total FROM reservations 
                                         WHERE status = 'checked_in'";
                                $stmt = $db->prepare($query);
                                $stmt->execute();
                                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                                echo $result['total'];
                                ?>
                            </h3>
                            <p>Current Guests</p>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon">🏨</div>
                        <div class="stat-info">
                            <h3>
                                <?php
                                $query = "SELECT COUNT(DISTINCT room_id) as total FROM reservations 
                                         WHERE status = 'checked_in'";
                                $stmt = $db->prepare($query);
                                $stmt->execute();
                                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                                echo $result['total'];
                                ?>
                            </h3>
                            <p>Occupied Rooms</p>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon">📅</div>
                        <div class="stat-info">
                            <h3>
                                <?php
                                $query = "SELECT COUNT(*) as total FROM reservations 
                                         WHERE check_in = CURDATE() 
                                         AND status = 'confirmed'";
                                $stmt = $db->prepare($query);
                                $stmt->execute();
                                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                                echo $result['total'];
                                ?>
                            </h3>
                            <p>Arrivals Today</p>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon">🚪</div>
                        <div class="stat-info">
                            <h3>
                                <?php
                                $query = "SELECT COUNT(*) as total FROM reservations 
                                         WHERE check_out = CURDATE() 
                                         AND status = 'checked_in'";
                                $stmt = $db->prepare($query);
                                $stmt->execute();
                                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                                echo $result['total'];
                                ?>
                            </h3>
                            <p>Departures Today</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
    function viewGuestDetails(reservationId) {
        // Open guest details in modal or new page
        window.open('../admin/reservation_details.php?id=' + reservationId, '_blank');
    }

    function sendMessageToGuest(guestEmail) {
        const message = prompt('Enter message to send to guest:');
        if (message) {
            // In a real application, this would send an email or SMS
            alert('Message sent to guest: ' + message);
        }
    }
    </script>
</body>
</html>