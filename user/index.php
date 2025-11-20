<?php
require_once '../config/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
requireAuth();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/user.css">
    <link rel="stylesheet" href="../assets/css/main.css">
</head>
<body>
    <!-- Header -->
    <header class="header">
        <nav class="navbar">
            <div class="nav-brand">
                <img src="../assets/images/logo.png" alt="Villa Adrian Logo" class="logo">
                <span><?php echo SITE_NAME; ?></span>
            </div>
            <ul class="nav-menu">
                <li><a href="../index.php" class="nav-link">Home</a></li>
                <li><a href="../rooms.php" class="nav-link">Rooms</a></li>
                <li><a href="index.php" class="nav-link active">Dashboard</a></li>
                <li><a href="my_reservations.php" class="nav-link">My Reservations</a></li>
                <li><a href="profile.php" class="nav-link">Profile</a></li>
                <li><a href="logout.php" class="nav-link">Logout</a></li>
            </ul>
        </nav>
    </header>

    <div class="user-container">
        <div class="user-content">
            <div class="user-header">
                <h1>Welcome, <?php echo $_SESSION['first_name']; ?>!</h1>
                <p>Manage your bookings and profile</p>
            </div>

            <!-- Quick Actions -->
            <div class="quick-actions">
                <a href="book.php" class="action-card">
                    <div class="action-icon">🏨</div>
                    <h3>Book a Room</h3>
                    <p>Find available rooms and make a reservation</p>
                </a>
                
                <a href="my_reservations.php" class="action-card">
                    <div class="action-icon">📅</div>
                    <h3>My Reservations</h3>
                    <p>View and manage your bookings</p>
                </a>
                
                <a href="profile.php" class="action-card">
                    <div class="action-icon">👤</div>
                    <h3>Profile</h3>
                    <p>Update your personal information</p>
                </a>
            </div>

            <!-- Upcoming Reservations -->
            <div class="dashboard-section">
                <h2>Upcoming Reservations</h2>
                <?php
                $query = "SELECT r.*, rm.room_number, rm.room_type, rm.price_per_night 
                         FROM reservations r 
                         JOIN rooms rm ON r.room_id = rm.id 
                         WHERE r.user_id = :user_id 
                         AND r.status IN ('confirmed', 'checked_in')
                         AND r.check_in >= CURDATE()
                         ORDER BY r.check_in ASC 
                         LIMIT 3";
                $stmt = $db->prepare($query);
                $stmt->bindParam(':user_id', $_SESSION['user_id']);
                $stmt->execute();
                
                if ($stmt->rowCount() > 0):
                ?>
                <div class="reservations-grid">
                    <?php while ($reservation = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
                    <div class="reservation-card">
                        <div class="reservation-header">
                            <h3><?php echo getRoomTypeName($reservation['room_type']); ?></h3>
                            <span class="status-badge status-<?php echo $reservation['status']; ?>">
                                <?php echo ucfirst($reservation['status']); ?>
                            </span>
                        </div>
                        <div class="reservation-details">
                            <p><strong>Room:</strong> <?php echo $reservation['room_number']; ?></p>
                            <p><strong>Check-in:</strong> <?php echo formatDate($reservation['check_in']); ?></p>
                            <p><strong>Check-out:</strong> <?php echo formatDate($reservation['check_out']); ?></p>
                            <p><strong>Guests:</strong> <?php echo $reservation['guests']; ?></p>
                            <p><strong>Total:</strong> €<?php echo $reservation['total_price']; ?></p>
                        </div>
                        <div class="reservation-actions">
                            <?php if (canCancelReservation($reservation['check_in'])): ?>
                                <a href="cancel_booking.php?id=<?php echo $reservation['id']; ?>" 
                                   class="btn btn-sm btn-danger"
                                   onclick="return confirm('Are you sure you want to cancel this reservation?')">
                                    Cancel
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </div>
                <?php else: ?>
                <p>You don't have any upcoming reservations.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="../assets/js/main.js"></script>
</body>
</html>