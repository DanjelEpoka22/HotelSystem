<?php
require_once '../config/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
requireRole('admin');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="../assets/css/main.css">
</head>
<body>
    <div class="admin-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="sidebar-header">
                <h2><?php echo SITE_NAME; ?></h2>
                <p>Admin Panel</p>
            </div>
            <ul class="sidebar-menu">
                <li><a href="index.php" class="active">Dashboard</a></li>
                <li><a href="rooms.php">Rooms Management</a></li>
                <li><a href="reservations.php">Reservations</a></li>
                <li><a href="calendar.php">Calendar</a></li>
                <li><a href="users.php">Users</a></li>
                <li><a href="staff.php">Staff</a></li>
                <li><a href="reports.php">Reports</a></li>
                <li><a href="settings.php">Settings</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <div class="content-header">
                <h1>Admin Dashboard</h1>
                <p>Welcome back, <?php echo $_SESSION['first_name']; ?>!</p>
            </div>

            <!-- Stats Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">🏨</div>
                    <div class="stat-info">
                        <h3>
                            <?php
                            $query = "SELECT COUNT(*) as total FROM rooms";
                            $stmt = $db->prepare($query);
                            $stmt->execute();
                            $result = $stmt->fetch(PDO::FETCH_ASSOC);
                            echo $result['total'];
                            ?>
                        </h3>
                        <p>Total Rooms</p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon">📅</div>
                    <div class="stat-info">
                        <h3>
                            <?php
                            $query = "SELECT COUNT(*) as total FROM reservations WHERE status = 'confirmed'";
                            $stmt = $db->prepare($query);
                            $stmt->execute();
                            $result = $stmt->fetch(PDO::FETCH_ASSOC);
                            echo $result['total'];
                            ?>
                        </h3>
                        <p>Active Reservations</p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon">👥</div>
                    <div class="stat-info">
                        <h3>
                            <?php
                            $query = "SELECT COUNT(*) as total FROM users WHERE role = 'user'";
                            $stmt = $db->prepare($query);
                            $stmt->execute();
                            $result = $stmt->fetch(PDO::FETCH_ASSOC);
                            echo $result['total'];
                            ?>
                        </h3>
                        <p>Registered Users</p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon">💰</div>
                    <div class="stat-info">
                        <h3>
                            <?php
                            $query = "SELECT SUM(total_price) as revenue FROM reservations 
                                     WHERE status IN ('confirmed', 'checked_in', 'checked_out')
                                     AND MONTH(created_at) = MONTH(CURRENT_DATE())";
                            $stmt = $db->prepare($query);
                            $stmt->execute();
                            $result = $stmt->fetch(PDO::FETCH_ASSOC);
                            echo '€' . ($result['revenue'] ?? 0);
                            ?>
                        </h3>
                        <p>Monthly Revenue</p>
                    </div>
                </div>
            </div>

            <!-- Recent Reservations -->
            <div class="dashboard-section">
                <h2>Recent Reservations</h2>
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Guest</th>
                                <th>Room</th>
                                <th>Check-in</th>
                                <th>Check-out</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $query = "SELECT r.*, u.first_name, u.last_name, rm.room_number, rm.room_type 
                                     FROM reservations r 
                                     JOIN users u ON r.user_id = u.id 
                                     JOIN rooms rm ON r.room_id = rm.id 
                                     ORDER BY r.created_at DESC LIMIT 5";
                            $stmt = $db->prepare($query);
                            $stmt->execute();
                            
                            while ($reservation = $stmt->fetch(PDO::FETCH_ASSOC)):
                            ?>
                            <tr>
                                <td>#<?php echo $reservation['id']; ?></td>
                                <td><?php echo $reservation['first_name'] . ' ' . $reservation['last_name']; ?></td>
                                <td><?php echo getRoomTypeName($reservation['room_type']) . ' (' . $reservation['room_number'] . ')'; ?></td>
                                <td><?php echo formatDate($reservation['check_in']); ?></td>
                                <td><?php echo formatDate($reservation['check_out']); ?></td>
                                <td>
                                    <span class="status-badge status-<?php echo $reservation['status']; ?>">
                                        <?php echo ucfirst($reservation['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="reservations.php?action=view&id=<?php echo $reservation['id']; ?>" class="btn btn-sm">View</a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Room Status Overview -->
            <div class="dashboard-section">
                <h2>Room Status Overview</h2>
                <div class="room-status-grid">
                    <?php
                    $query = "SELECT r.*, rs.status as room_status 
                             FROM rooms r 
                             LEFT JOIN room_status rs ON r.id = rs.room_id 
                             ORDER BY r.room_number";
                    $stmt = $db->prepare($query);
                    $stmt->execute();
                    
                    while ($room = $stmt->fetch(PDO::FETCH_ASSOC)):
                    ?>
                    <div class="room-status-card status-<?php echo $room['room_status'] ?? 'clean'; ?>">
                        <h4>Room <?php echo $room['room_number']; ?></h4>
                        <p><?php echo getRoomTypeName($room['room_type']); ?></p>
                        <span class="status-indicator"><?php echo ucfirst($room['room_status'] ?? 'clean'); ?></span>
                    </div>
                    <?php endwhile; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="../assets/js/main.js"></script>
</body>
</html>