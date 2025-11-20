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
    <title>My Reservations - <?php echo SITE_NAME; ?></title>
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
                <li><a href="index.php" class="nav-link">Dashboard</a></li>
                <li><a href="book.php" class="nav-link">Book Room</a></li>
                <li><a href="my_reservations.php" class="nav-link active">My Reservations</a></li>
                <li><a href="profile.php" class="nav-link">Profile</a></li>
                <li><a href="logout.php" class="nav-link">Logout</a></li>
            </ul>
        </nav>
    </header>

    <div class="user-container">
        <div class="user-content">
            <div class="user-header">
                <h1>My Reservations</h1>
                <p>View and manage your hotel bookings</p>
            </div>

            <!-- Reservations Tabs -->
            <div class="tabs">
                <button class="tab-button active" onclick="openTab('current')">Current & Upcoming</button>
                <button class="tab-button" onclick="openTab('past')">Past Reservations</button>
                <button class="tab-button" onclick="openTab('cancelled')">Cancelled</button>
            </div>

            <!-- Current & Upcoming Reservations -->
            <div id="current" class="tab-content active">
                <div class="dashboard-section">
                    <h2>Current & Upcoming Reservations</h2>
                    <?php
                    $query = "SELECT r.*, rm.room_number, rm.room_type, rm.price_per_night 
                             FROM reservations r 
                             JOIN rooms rm ON r.room_id = rm.id 
                             WHERE r.user_id = :user_id 
                             AND r.status IN ('confirmed', 'checked_in')
                             AND r.check_out >= CURDATE()
                             ORDER BY r.check_in ASC";
                    $stmt = $db->prepare($query);
                    $stmt->bindParam(':user_id', $_SESSION['user_id']);
                    $stmt->execute();
                    
                    if ($stmt->rowCount() > 0):
                    ?>
                    <div class="reservations-grid">
                        <?php while ($reservation = $stmt->fetch(PDO::FETCH_ASSOC)): 
                            $can_cancel = canCancelReservation($reservation['check_in']);
                        ?>
                        <div class="reservation-card">
                            <div class="reservation-header">
                                <h3><?php echo getRoomTypeName($reservation['room_type']); ?></h3>
                                <div class="reservation-status">
                                    <span class="status-badge status-<?php echo $reservation['status']; ?>">
                                        <?php echo ucfirst($reservation['status']); ?>
                                    </span>
                                    <span class="room-number">Room <?php echo $reservation['room_number']; ?></span>
                                </div>
                            </div>
                            
                            <div class="reservation-details">
                                <div class="detail-group">
                                    <p><strong>Check-in:</strong> <?php echo formatDate($reservation['check_in']); ?></p>
                                    <p><strong>Check-out:</strong> <?php echo formatDate($reservation['check_out']); ?></p>
                                    <p><strong>Guests:</strong> <?php echo $reservation['guests']; ?></p>
                                </div>
                                
                                <div class="detail-group">
                                    <p><strong>Total Price:</strong> €<?php echo $reservation['total_price']; ?></p>
                                    <p><strong>Payment:</strong> 
                                        <span class="payment-status status-<?php echo $reservation['payment_status']; ?>">
                                            <?php echo ucfirst($reservation['payment_status']); ?>
                                        </span>
                                    </p>
                                    <p><strong>Booking Source:</strong> <?php echo ucfirst($reservation['source']); ?></p>
                                </div>
                            </div>
                            
                            <?php if ($reservation['special_requests']): ?>
                            <div class="special-requests">
                                <p><strong>Special Requests:</strong> <?php echo $reservation['special_requests']; ?></p>
                            </div>
                            <?php endif; ?>
                            
                            <div class="reservation-actions">
                                <?php if ($can_cancel && $reservation['status'] === 'confirmed'): ?>
                                    <button class="btn btn-danger" 
                                            onclick="cancelReservation(<?php echo $reservation['id']; ?>)">
                                        Cancel Reservation
                                    </button>
                                <?php elseif (!$can_cancel && $reservation['status'] === 'confirmed'): ?>
                                    <p class="cancellation-note">
                                        Cancellation not allowed. Less than <?php echo CANCELLATION_DAYS; ?> days before check-in.
                                    </p>
                                <?php endif; ?>
                                
                                <?php if ($reservation['payment_status'] === 'pending' && $reservation['payment_method'] === 'card'): ?>
                                    <button class="btn btn-primary" 
                                            onclick="processPayment(<?php echo $reservation['id']; ?>)">
                                        Pay Now
                                    </button>
                                <?php endif; ?>
                                
                                <button class="btn btn-secondary" 
                                        onclick="viewReservationDetails(<?php echo $reservation['id']; ?>)">
                                    View Details
                                </button>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    </div>
                    <?php else: ?>
                    <div class="no-reservations">
                        <p>You don't have any current or upcoming reservations.</p>
                        <a href="book.php" class="btn btn-primary">Book a Room</a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Past Reservations -->
            <div id="past" class="tab-content">
                <div class="dashboard-section">
                    <h2>Past Reservations</h2>
                    <?php
                    $query = "SELECT r.*, rm.room_number, rm.room_type 
                             FROM reservations r 
                             JOIN rooms rm ON r.room_id = rm.id 
                             WHERE r.user_id = :user_id 
                             AND r.status = 'checked_out'
                             ORDER BY r.check_out DESC";
                    $stmt = $db->prepare($query);
                    $stmt->bindParam(':user_id', $_SESSION['user_id']);
                    $stmt->execute();
                    
                    if ($stmt->rowCount() > 0):
                    ?>
                    <div class="reservations-grid">
                        <?php while ($reservation = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
                        <div class="reservation-card past">
                            <div class="reservation-header">
                                <h3><?php echo getRoomTypeName($reservation['room_type']); ?></h3>
                                <div class="reservation-status">
                                    <span class="status-badge status-<?php echo $reservation['status']; ?>">
                                        Completed
                                    </span>
                                    <span class="room-number">Room <?php echo $reservation['room_number']; ?></span>
                                </div>
                            </div>
                            
                            <div class="reservation-details">
                                <div class="detail-group">
                                    <p><strong>Check-in:</strong> <?php echo formatDate($reservation['check_in']); ?></p>
                                    <p><strong>Check-out:</strong> <?php echo formatDate($reservation['check_out']); ?></p>
                                    <p><strong>Guests:</strong> <?php echo $reservation['guests']; ?></p>
                                </div>
                                
                                <div class="detail-group">
                                    <p><strong>Total Paid:</strong> €<?php echo $reservation['total_price']; ?></p>
                                    <p><strong>Stay Duration:</strong> 
                                        <?php 
                                        $nights = floor((strtotime($reservation['check_out']) - strtotime($reservation['check_in'])) / (60 * 60 * 24));
                                        echo $nights . ' night' . ($nights != 1 ? 's' : '');
                                        ?>
                                    </p>
                                </div>
                            </div>
                            
                            <div class="reservation-actions">
                                <button class="btn btn-secondary" 
                                        onclick="viewReservationDetails(<?php echo $reservation['id']; ?>)">
                                    View Details
                                </button>
                                <button class="btn btn-outline" 
                                        onclick="bookAgain(<?php echo $reservation['room_id']; ?>)">
                                    Book Again
                                </button>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    </div>
                    <?php else: ?>
                    <div class="no-reservations">
                        <p>You don't have any past reservations.</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Cancelled Reservations -->
            <div id="cancelled" class="tab-content">
                <div class="dashboard-section">
                    <h2>Cancelled Reservations</h2>
                    <?php
                    $query = "SELECT r.*, rm.room_number, rm.room_type 
                             FROM reservations r 
                             JOIN rooms rm ON r.room_id = rm.id 
                             WHERE r.user_id = :user_id 
                             AND r.status = 'cancelled'
                             ORDER BY r.created_at DESC";
                    $stmt = $db->prepare($query);
                    $stmt->bindParam(':user_id', $_SESSION['user_id']);
                    $stmt->execute();
                    
                    if ($stmt->rowCount() > 0):
                    ?>
                    <div class="reservations-grid">
                        <?php while ($reservation = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
                        <div class="reservation-card cancelled">
                            <div class="reservation-header">
                                <h3><?php echo getRoomTypeName($reservation['room_type']); ?></h3>
                                <div class="reservation-status">
                                    <span class="status-badge status-<?php echo $reservation['status']; ?>">
                                        Cancelled
                                    </span>
                                    <span class="room-number">Room <?php echo $reservation['room_number']; ?></span>
                                </div>
                            </div>
                            
                            <div class="reservation-details">
                                <div class="detail-group">
                                    <p><strong>Original Check-in:</strong> <?php echo formatDate($reservation['check_in']); ?></p>
                                    <p><strong>Original Check-out:</strong> <?php echo formatDate($reservation['check_out']); ?></p>
                                    <p><strong>Guests:</strong> <?php echo $reservation['guests']; ?></p>
                                </div>
                                
                                <div class="detail-group">
                                    <p><strong>Original Total:</strong> €<?php echo $reservation['total_price']; ?></p>
                                    <p><strong>Cancelled On:</strong> <?php echo formatDate($reservation['updated_at']); ?></p>
                                </div>
                            </div>
                            
                            <div class="reservation-actions">
                                <button class="btn btn-outline" 
                                        onclick="bookAgain(<?php echo $reservation['room_id']; ?>)">
                                    Book Similar Room
                                </button>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    </div>
                    <?php else: ?>
                    <div class="no-reservations">
                        <p>You don't have any cancelled reservations.</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Reservation Details Modal -->
    <div id="reservationModal" class="modal" style="display: none;">
        <div class="modal-content">
            <span class="close" onclick="closeReservationModal()">&times;</span>
            <h2>Reservation Details</h2>
            <div id="reservationDetails"></div>
        </div>
    </div>

    <script src="../assets/js/main.js"></script>
    <script>
    // Tab functionality
    function openTab(tabName) {
        // Hide all tab contents
        document.querySelectorAll('.tab-content').forEach(tab => {
            tab.classList.remove('active');
        });
        
        // Remove active class from all buttons
        document.querySelectorAll('.tab-button').forEach(button => {
            button.classList.remove('active');
        });
        
        // Show selected tab and activate button
        document.getElementById(tabName).classList.add('active');
        event.currentTarget.classList.add('active');
    }

    function cancelReservation(reservationId) {
        if (confirm('Are you sure you want to cancel this reservation?')) {
            fetch('../api/cancel_booking.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    reservation_id: reservationId,
                    user_id: <?php echo $_SESSION['user_id']; ?>
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Reservation cancelled successfully!');
                    location.reload();
                } else {
                    alert('Error: ' + data.error);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while cancelling the reservation.');
            });
        }
    }

    function processPayment(reservationId) {
        // Redirect to payment page
        window.location.href = 'payment.php?id=' + reservationId;
    }

    function viewReservationDetails(reservationId) {
        fetch('../api/get_reservation_details.php?id=' + reservationId)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const reservation = data.reservation;
                    const modal = document.getElementById('reservationModal');
                    const details = document.getElementById('reservationDetails');
                    
                    details.innerHTML = `
                        <div class="reservation-detail">
                            <h3>Reservation #${reservation.id}</h3>
                            <div class="detail-section">
                                <h4>Guest Information</h4>
                                <p><strong>Name:</strong> ${reservation.first_name} ${reservation.last_name}</p>
                                <p><strong>Email:</strong> ${reservation.email}</p>
                                <p><strong>Phone:</strong> ${reservation.phone || 'Not provided'}</p>
                            </div>
                            
                            <div class="detail-section">
                                <h4>Stay Details</h4>
                                <p><strong>Room:</strong> ${reservation.room_number} - ${reservation.room_type_name}</p>
                                <p><strong>Check-in:</strong> ${reservation.check_in}</p>
                                <p><strong>Check-out:</strong> ${reservation.check_out}</p>
                                <p><strong>Guests:</strong> ${reservation.guests}</p>
                                <p><strong>Total Price:</strong> €${reservation.total_price}</p>
                            </div>
                            
                            <div class="detail-section">
                                <h4>Status Information</h4>
                                <p><strong>Status:</strong> <span class="status-badge status-${reservation.status}">${reservation.status}</span></p>
                                <p><strong>Payment Status:</strong> <span class="status-badge status-${reservation.payment_status}">${reservation.payment_status}</span></p>
                                <p><strong>Payment Method:</strong> ${reservation.payment_method}</p>
                                <p><strong>Booking Source:</strong> ${reservation.source}</p>
                            </div>
                            
                            ${reservation.special_requests ? `
                            <div class="detail-section">
                                <h4>Special Requests</h4>
                                <p>${reservation.special_requests}</p>
                            </div>
                            ` : ''}
                            
                            <div class="detail-section">
                                <h4>Reservation Timeline</h4>
                                <p><strong>Booked On:</strong> ${reservation.created_at}</p>
                                ${reservation.updated_at !== reservation.created_at ? `<p><strong>Last Updated:</strong> ${reservation.updated_at}</p>` : ''}
                            </div>
                        </div>
                    `;
                    
                    modal.style.display = 'block';
                } else {
                    alert('Error loading reservation details: ' + data.error);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while loading reservation details.');
            });
    }

    function closeReservationModal() {
        document.getElementById('reservationModal').style.display = 'none';
    }

    function bookAgain(roomId) {
        // Redirect to booking page with room pre-selected
        window.location.href = 'book.php?room_id=' + roomId;
    }

    // Close modal when clicking outside
    window.onclick = function(event) {
        const modal = document.getElementById('reservationModal');
        if (event.target === modal) {
            closeReservationModal();
        }
    }
    </script>

    <style>
    .tabs {
        display: flex;
        border-bottom: 2px solid #ecf0f1;
        margin-bottom: 2rem;
    }

    .tab-button {
        padding: 1rem 2rem;
        background: none;
        border: none;
        border-bottom: 3px solid transparent;
        cursor: pointer;
        font-size: 1rem;
        transition: all 0.3s;
    }

    .tab-button.active {
        border-bottom-color: #3498db;
        color: #3498db;
        font-weight: 600;
    }

    .tab-button:hover {
        background: #f8f9fa;
    }

    .tab-content {
        display: none;
    }

    .tab-content.active {
        display: block;
    }

    .reservation-card.past {
        opacity: 0.8;
        border-left-color: #95a5a6;
    }

    .reservation-card.cancelled {
        opacity: 0.6;
        border-left-color: #e74c3c;
    }

    .reservation-status {
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .room-number {
        color: #7f8c8d;
        font-size: 0.9rem;
    }

    .reservation-details {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1rem;
        margin: 1rem 0;
    }

    .detail-group p {
        margin: 0.5rem 0;
    }

    .special-requests {
        background: #f8f9fa;
        padding: 1rem;
        border-radius: 5px;
        margin: 1rem 0;
    }

    .reservation-actions {
        display: flex;
        gap: 0.5rem;
        flex-wrap: wrap;
    }

    .cancellation-note {
        color: #e74c3c;
        font-size: 0.9rem;
        margin: 0.5rem 0;
    }

    .payment-status {
        padding: 0.2rem 0.5rem;
        border-radius: 3px;
        font-size: 0.8rem;
    }

    .no-reservations {
        text-align: center;
        padding: 3rem;
        color: #7f8c8d;
    }

    .modal {
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0,0,0,0.5);
    }

    .modal-content {
        background-color: white;
        margin: 5% auto;
        padding: 2rem;
        border-radius: 10px;
        width: 80%;
        max-width: 600px;
        max-height: 80vh;
        overflow-y: auto;
    }

    .close {
        color: #aaa;
        float: right;
        font-size: 1.5rem;
        font-weight: bold;
        cursor: pointer;
    }

    .close:hover {
        color: #000;
    }

    .reservation-detail h3 {
        color: #2c3e50;
        margin-bottom: 1rem;
    }

    .detail-section {
        margin-bottom: 1.5rem;
        padding-bottom: 1rem;
        border-bottom: 1px solid #ecf0f1;
    }

    .detail-section h4 {
        color: #3498db;
        margin-bottom: 0.5rem;
    }

    .detail-section p {
        margin: 0.25rem 0;
    }

    .btn-outline {
        background: transparent;
        border: 1px solid #3498db;
        color: #3498db;
    }

    .btn-outline:hover {
        background: #3498db;
        color: white;
    }
    </style>
</body>
</html>