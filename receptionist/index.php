<?php
require_once '../config/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
requireRole('receptionist');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receptionist Dashboard - <?php echo SITE_NAME; ?></title>
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
                <li><a href="index.php" class="active">Dashboard</a></li>
                <li><a href="calendar.php">Calendar</a></li>
                <li><a href="checkin.php">Check-in</a></li>
                <li><a href="checkout.php">Check-out</a></li>
                <li><a href="guests.php">Guests</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <div class="content-header">
                <h1>Receptionist Dashboard</h1>
                <p>Welcome, <?php echo $_SESSION['first_name']; ?>!</p>
            </div>

            <!-- Today's Arrivals and Departures -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">📥</div>
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
                        <p>Today's Arrivals</p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon">📤</div>
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
                        <p>Today's Departures</p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon">🏨</div>
                    <div class="stat-info">
                        <h3>
                            <?php
                            $query = "SELECT COUNT(*) as total FROM reservations 
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
                    <div class="stat-icon">🔄</div>
                    <div class="stat-info">
                        <h3>
                            <?php
                            $query = "SELECT COUNT(*) as total FROM room_status 
                                     WHERE status = 'unclean'";
                            $stmt = $db->prepare($query);
                            $stmt->execute();
                            $result = $stmt->fetch(PDO::FETCH_ASSOC);
                            echo $result['total'];
                            ?>
                        </h3>
                        <p>Rooms to Clean</p>
                    </div>
                </div>
            </div>

            <!-- Today's Arrivals -->
            <div class="dashboard-section">
                <h2>Today's Arrivals</h2>
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Guest Name</th>
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
                                     WHERE r.check_in = CURDATE() 
                                     AND r.status = 'confirmed' 
                                     ORDER BY r.check_in";
                            $stmt = $db->prepare($query);
                            $stmt->execute();
                            
                            while ($reservation = $stmt->fetch(PDO::FETCH_ASSOC)):
                            ?>
                            <tr>
                                <td><?php echo $reservation['first_name'] . ' ' . $reservation['last_name']; ?></td>
                                <td><?php echo $reservation['room_number'] . ' - ' . getRoomTypeName($reservation['room_type']); ?></td>
                                <td><?php echo formatDate($reservation['check_in']); ?></td>
                                <td><?php echo formatDate($reservation['check_out']); ?></td>
                                <td>
                                    <span class="status-badge status-<?php echo $reservation['status']; ?>">
                                        <?php echo ucfirst($reservation['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="checkin.php?id=<?php echo $reservation['id']; ?>" class="btn btn-sm btn-success">Check-in</a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
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
                                    <span class="status-badge status-<?php echo $reservation['status']; ?>">
                                        <?php echo ucfirst($reservation['status']); ?>
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
        </div>
    </div>
</body>
</html>