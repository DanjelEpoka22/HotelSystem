<?php
require_once '../config/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
requireAuth();
?>
<!DOCTYPE html>
<html lang="sq">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rezervimet e Mia - Villa Adrian</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --primary-color: #2C5F7C;
            --secondary-color: #F4A261;
            --accent-color: #E76F51;
            --dark-color: #264653;
            --light-color: #F8F9FA;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: var(--light-color);
        }

        .header {
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 1000;
            padding: 1rem 0;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }

        .navbar {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .nav-brand {
            display: flex;
            align-items: center;
            font-family: 'Playfair Display', serif;
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary-color);
        }

        .logo {
            height: 40px;
            margin-right: 0.75rem;
        }

        .nav-menu {
            display: flex;
            list-style: none;
            gap: 1rem;
        }

        .nav-link {
            text-decoration: none;
            color: var(--dark-color);
            font-weight: 500;
            padding: 0.5rem 1rem;
            border-radius: 25px;
            transition: all 0.3s ease;
            font-size: 0.95rem;
        }

        .nav-link:hover,
        .nav-link.active {
            background: var(--secondary-color);
            color: white;
        }

        .user-container {
            max-width: 1400px;
            margin: 100px auto 2rem;
            padding: 0 2rem;
        }

        .user-header {
            text-align: center;
            margin-bottom: 3rem;
        }

        .user-header h1 {
            font-family: 'Playfair Display', serif;
            font-size: 3rem;
            color: var(--primary-color);
            margin-bottom: 0.5rem;
        }

        /* Tabs */
        .tabs {
            display: flex;
            gap: 1rem;
            margin-bottom: 2rem;
            background: white;
            padding: 1rem;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.05);
        }

        .tab-button {
            flex: 1;
            padding: 1rem 2rem;
            background: transparent;
            border: 2px solid transparent;
            border-radius: 12px;
            cursor: pointer;
            font-size: 1rem;
            font-weight: 600;
            color: #666;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .tab-button:hover {
            background: var(--light-color);
            color: var(--primary-color);
        }

        .tab-button.active {
            background: linear-gradient(135deg, var(--secondary-color), var(--accent-color));
            color: white;
            border-color: var(--secondary-color);
            box-shadow: 0 4px 15px rgba(244, 162, 97, 0.3);
        }

        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
            animation: fadeInUp 0.5s ease;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .dashboard-section {
            background: white;
            padding: 2.5rem;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
        }

        .dashboard-section h2 {
            font-family: 'Playfair Display', serif;
            font-size: 2rem;
            color: var(--primary-color);
            margin-bottom: 2rem;
        }

        .reservations-grid {
            display: grid;
            gap: 2rem;
        }

        .reservation-card {
            background: var(--light-color);
            border-radius: 15px;
            padding: 2rem;
            border-left: 5px solid var(--secondary-color);
            transition: all 0.3s ease;
        }

        .reservation-card:hover {
            transform: translateX(10px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }

        .reservation-card.past {
            border-left-color: #95a5a6;
            opacity: 0.9;
        }

        .reservation-card.cancelled {
            border-left-color: var(--accent-color);
            opacity: 0.85;
        }

        .reservation-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 1.5rem;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .reservation-header h3 {
            font-family: 'Playfair Display', serif;
            font-size: 1.8rem;
            color: var(--primary-color);
        }

        .reservation-status {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            gap: 0.5rem;
        }

        .status-badge {
            padding: 0.5rem 1.2rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .status-confirmed {
            background: #d1ecf1;
            color: #0c5460;
        }

        .status-checked_in {
            background: #d4edda;
            color: #155724;
        }

        .status-checked_out {
            background: #e2e3e5;
            color: #383d41;
        }

        .status-cancelled {
            background: #f8d7da;
            color: #721c24;
        }

        .status-pending {
            background: #fff3cd;
            color: #856404;
        }

        .room-number {
            color: #666;
            font-size: 0.9rem;
        }

        .reservation-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .detail-group p {
            margin: 0.75rem 0;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: #555;
        }

        .detail-group strong {
            color: var(--primary-color);
            min-width: 120px;
        }

        .special-requests {
            background: white;
            padding: 1rem;
            border-radius: 10px;
            margin: 1rem 0;
            border-left: 3px solid var(--secondary-color);
        }

        .reservation-actions {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
            margin-top: 1.5rem;
            padding-top: 1.5rem;
            border-top: 2px solid #e9ecef;
        }

        .btn {
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 25px;
            font-size: 0.95rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--secondary-color), var(--accent-color));
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(244, 162, 97, 0.4);
        }

        .btn-secondary {
            background: white;
            color: var(--primary-color);
            border: 2px solid var(--primary-color);
        }

        .btn-secondary:hover {
            background: var(--primary-color);
            color: white;
        }

        .btn-danger {
            background: var(--accent-color);
            color: white;
        }

        .btn-danger:hover {
            background: #c0392b;
            transform: translateY(-2px);
        }

        .btn-outline {
            background: transparent;
            border: 2px solid #ddd;
            color: #666;
        }

        .btn-outline:hover {
            border-color: var(--primary-color);
            color: var(--primary-color);
        }

        .no-reservations {
            text-align: center;
            padding: 4rem 2rem;
        }

        .no-reservations i {
            font-size: 5rem;
            color: #ddd;
            margin-bottom: 1.5rem;
        }

        .no-reservations h3 {
            font-size: 1.5rem;
            color: #666;
            margin-bottom: 0.5rem;
        }

        .cancellation-note {
            color: var(--accent-color);
            font-size: 0.9rem;
            margin: 0.5rem 0;
        }

        /* Modal */
        .modal {
            display: none;
            position: fixed;
            z-index: 2000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.8);
            backdrop-filter: blur(5px);
        }

        .modal-content {
            background-color: white;
            margin: 3% auto;
            padding: 3rem;
            border-radius: 25px;
            width: 90%;
            max-width: 700px;
            max-height: 85vh;
            overflow-y: auto;
        }

        .close {
            color: #aaa;
            float: right;
            font-size: 2rem;
            font-weight: bold;
            cursor: pointer;
        }

        .close:hover {
            color: var(--accent-color);
        }

        @media (max-width: 768px) {
            .user-container {
                margin: 80px auto 1rem;
                padding: 0 1rem;
            }

            .tabs {
                flex-direction: column;
            }

            .reservation-details {
                grid-template-columns: 1fr;
            }

            .reservation-actions {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <header class="header">
        <nav class="navbar">
            <div class="nav-brand">
                <img src="../assets/images/logo.png" alt="Logo" class="logo" onerror="this.style.display='none'">
                <span>Villa Adrian</span>
            </div>
            <ul class="nav-menu">
                <li><a href="../index.php" class="nav-link"><i class="fas fa-home"></i> Home</a></li>
                <li><a href="index.php" class="nav-link"><i class="fas fa-th-large"></i> Dashboard</a></li>
                <li><a href="book.php" class="nav-link"><i class="fas fa-calendar-plus"></i> Rezervo</a></li>
                <li><a href="my_reservations.php" class="nav-link active"><i class="fas fa-calendar"></i> Rezervimet</a></li>
                <li><a href="profile.php" class="nav-link"><i class="fas fa-user"></i> Profili</a></li>
                <li><a href="logout.php" class="nav-link"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </nav>
    </header>

    <div class="user-container">
        <div class="user-header" data-aos="fade-down">
            <h1>Rezervimet e Mia</h1>
            <p>Shikoni dhe menaxhoni rezervimet tuaja</p>
        </div>

        <div class="tabs" data-aos="fade-up">
            <button class="tab-button active" onclick="openTab(event, 'current')">
                <i class="fas fa-clock"></i> Aktuale & Të Ardhshme
            </button>
            <button class="tab-button" onclick="openTab(event, 'past')">
                <i class="fas fa-history"></i> Të Kaluara
            </button>
            <button class="tab-button" onclick="openTab(event, 'cancelled')">
                <i class="fas fa-times-circle"></i> Të Anuluara
            </button>
        </div>

        <!-- Current & Upcoming -->
        <div id="current" class="tab-content active">
            <div class="dashboard-section" data-aos="fade-up">
                <h2><i class="fas fa-calendar-check"></i> Rezervime Aktuale & Të Ardhshme</h2>
                <?php
                $query = "SELECT r.*, rm.room_number, rm.room_type, rm.price_per_night 
                         FROM reservations r 
                         JOIN rooms rm ON r.room_id = rm.id 
                         WHERE r.user_id = :user_id 
                         AND r.status IN ('pending', 'confirmed', 'checked_in')
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
                                <span class="room-number">Dhomë <?php echo $reservation['room_number']; ?></span>
                            </div>
                        </div>
                        
                        <div class="reservation-details">
                            <div class="detail-group">
                                <p><strong><i class="fas fa-calendar-check"></i> Check-in:</strong> <?php echo formatDate($reservation['check_in']); ?></p>
                                <p><strong><i class="fas fa-calendar-times"></i> Check-out:</strong> <?php echo formatDate($reservation['check_out']); ?></p>
                                <p><strong><i class="fas fa-users"></i> Mysafirë:</strong> <?php echo $reservation['guests']; ?></p>
                            </div>
                            
                            <div class="detail-group">
                                <p><strong><i class="fas fa-euro-sign"></i> Çmimi Total:</strong> €<?php echo $reservation['total_price']; ?></p>
                                <p><strong><i class="fas fa-credit-card"></i> Pagesa:</strong> 
                                    <span class="status-badge status-<?php echo $reservation['payment_status']; ?>">
                                        <?php echo ucfirst($reservation['payment_status']); ?>
                                    </span>
                                </p>
                                <p><strong><i class="fas fa-globe"></i> Burimi:</strong> <?php echo ucfirst($reservation['source']); ?></p>
                            </div>
                        </div>
                        
                        <?php if ($reservation['special_requests']): ?>
                        <div class="special-requests">
                            <p><strong><i class="fas fa-comment"></i> Kërkesa të Veçanta:</strong> <?php echo $reservation['special_requests']; ?></p>
                        </div>
                        <?php endif; ?>
                        
                        <div class="reservation-actions">
                            <?php 
                            $can_cancel = canCancelReservation($reservation['check_in']);
                            $show_cancellation = ($reservation['status'] === 'confirmed' || $reservation['status'] === 'pending');
                            
                            if ($show_cancellation && $can_cancel): ?>
                                <button class="btn btn-danger" 
                                        onclick="cancelReservation(<?php echo $reservation['id']; ?>)">
                                    <i class="fas fa-times-circle"></i> Anulo Rezervimin
                                </button>
                            <?php elseif ($show_cancellation && !$can_cancel): ?>
                                <p class="cancellation-note">
                                    <i class="fas fa-info-circle"></i> Anulimi nuk lejohet. Më pak se <?php echo CANCELLATION_DAYS; ?> ditë para check-in.
                                </p>
                            <?php endif; ?>
                            
                            <?php if ($reservation['payment_status'] === 'pending' && $reservation['payment_method'] === 'card'): ?>
                                <button class="btn btn-primary" 
                                        onclick="processPayment(<?php echo $reservation['id']; ?>)">
                                    <i class="fas fa-credit-card"></i> Paguaj Tani
                                </button>
                            <?php endif; ?>
                            
                            <button class="btn btn-secondary" 
                                    onclick="viewReservationDetails(<?php echo $reservation['id']; ?>)">
                                <i class="fas fa-eye"></i> Shiko Detajet
                            </button>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </div>
                <?php else: ?>
                <div class="no-reservations">
                    <i class="fas fa-calendar-times"></i>
                    <h3>Nuk keni rezervime aktuale</h3>
                    <p>Rezervoni një dhomë për pushimet tuaja</p>
                    <a href="book.php" class="btn btn-primary" style="margin-top: 1rem;">
                        <i class="fas fa-plus-circle"></i> Rezervo Dhomë
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Past Reservations -->
        <div id="past" class="tab-content">
            <div class="dashboard-section" data-aos="fade-up">
                <h2><i class="fas fa-history"></i> Rezervime të Kaluara</h2>
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
                                <span class="status-badge status-checked_out">Përfunduar</span>
                                <span class="room-number">Dhomë <?php echo $reservation['room_number']; ?></span>
                            </div>
                        </div>
                        
                        <div class="reservation-details">
                            <div class="detail-group">
                                <p><strong><i class="fas fa-calendar"></i> Check-in:</strong> <?php echo formatDate($reservation['check_in']); ?></p>
                                <p><strong><i class="fas fa-calendar"></i> Check-out:</strong> <?php echo formatDate($reservation['check_out']); ?></p>
                            </div>
                            
                            <div class="detail-group">
                                <p><strong><i class="fas fa-euro-sign"></i> Totali Paguar:</strong> €<?php echo $reservation['total_price']; ?></p>
                                <p><strong><i class="fas fa-moon"></i> Kohëzgjatja:</strong> 
                                    <?php 
                                    $nights = floor((strtotime($reservation['check_out']) - strtotime($reservation['check_in'])) / 86400);
                                    echo $nights . ' net' . ($nights != 1 ? 'ë' : '');
                                    ?>
                                </p>
                            </div>
                        </div>
                        
                        <div class="reservation-actions">
                            <button class="btn btn-outline" onclick="bookAgain(<?php echo $reservation['room_id']; ?>)">
                                <i class="fas fa-redo"></i> Rezervo Përsëri
                            </button>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </div>
                <?php else: ?>
                <div class="no-reservations">
                    <i class="fas fa-inbox"></i>
                    <h3>Nuk keni rezervime të kaluara</h3>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Cancelled -->
        <div id="cancelled" class="tab-content">
            <div class="dashboard-section" data-aos="fade-up">
                <h2><i class="fas fa-times-circle"></i> Rezervime të Anuluara</h2>
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
                                <span class="status-badge status-cancelled">Anuluar</span>
                            </div>
                        </div>
                        
                        <div class="reservation-details">
                            <div class="detail-group">
                                <p><strong>Check-in Original:</strong> <?php echo formatDate($reservation['check_in']); ?></p>
                                <p><strong>Anuluar Më:</strong> <?php echo formatDate($reservation['updated_at']); ?></p>
                            </div>
                        </div>
                        
                        <div class="reservation-actions">
                            <button class="btn btn-outline" onclick="bookAgain(<?php echo $reservation['room_id']; ?>)">
                                <i class="fas fa-search"></i> Gjej Dhomë të Ngjashme
                            </button>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </div>
                <?php else: ?>
                <div class="no-reservations">
                    <i class="fas fa-check-circle"></i>
                    <h3>Nuk keni rezervime të anuluara</h3>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div id="reservationModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeReservationModal()">&times;</span>
            <div id="reservationDetails"></div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"></script>
    <script src="../assets/js/main.js"></script>
    <script>
        AOS.init({ duration: 800, once: true });

        function openTab(event, tabName) {
            document.querySelectorAll('.tab-content').forEach(tab => tab.classList.remove('active'));
            document.querySelectorAll('.tab-button').forEach(btn => btn.classList.remove('active'));
            
            document.getElementById(tabName).classList.add('active');
            event.currentTarget.classList.add('active');
        }

        function cancelReservation(reservationId) {
            if (confirm('Jeni i sigurt që dëshironi të anuloni këtë rezervim?')) {
                fetch('../api/cancel_booking.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({
                        reservation_id: reservationId,
                        user_id: <?php echo $_SESSION['user_id']; ?>
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Rezervimi u anulua me sukses!');
                        location.reload();
                    } else {
                        alert('Gabim: ' + data.error);
                    }
                });
            }
        }

        function processPayment(reservationId) {
            window.location.href = 'payment.php?id=' + reservationId;
        }

        function viewReservationDetails(reservationId) {
            // Implementation from original code
        }

        function closeReservationModal() {
            document.getElementById('reservationModal').style.display = 'none';
        }

        function bookAgain(roomId) {
            window.location.href = 'book.php?room_id=' + roomId;
        }
    </script>
</body>
</html>