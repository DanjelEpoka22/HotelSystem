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
    <title>Dashboard - Villa Adrian</title>
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
            --white: #FFFFFF;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: var(--light-color);
            color: var(--dark-color);
        }

        /* Header */
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
            align-items: center;
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
            color: var(--white);
            transform: translateY(-2px);
        }

        /* Dashboard Container */
        .user-container {
            max-width: 1400px;
            margin: 100px auto 2rem;
            padding: 0 2rem;
        }

        .user-header {
            text-align: center;
            margin-bottom: 3rem;
            animation: fadeInDown 0.8s ease;
        }

        @keyframes fadeInDown {
            from {
                opacity: 0;
                transform: translateY(-30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .user-header h1 {
            font-family: 'Playfair Display', serif;
            font-size: 3rem;
            color: var(--primary-color);
            margin-bottom: 0.5rem;
        }

        .user-header p {
            font-size: 1.2rem;
            color: #666;
        }

        /* Quick Actions */
        .quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin-bottom: 4rem;
        }

        .action-card {
            background: white;
            padding: 2.5rem;
            border-radius: 20px;
            text-align: center;
            text-decoration: none;
            color: inherit;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
            transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
            border-top: 4px solid var(--secondary-color);
        }

        .action-card:hover {
            transform: translateY(-15px);
            box-shadow: 0 20px 50px rgba(0,0,0,0.15);
        }

        .action-icon {
            font-size: 4rem;
            margin-bottom: 1.5rem;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% {
                transform: scale(1);
            }
            50% {
                transform: scale(1.05);
            }
        }

        .action-card h3 {
            font-family: 'Playfair Display', serif;
            font-size: 1.8rem;
            color: var(--primary-color);
            margin-bottom: 1rem;
        }

        .action-card p {
            color: #666;
            line-height: 1.7;
        }

        /* Dashboard Section */
        .dashboard-section {
            background: white;
            padding: 2.5rem;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
            margin-bottom: 2rem;
        }

        .dashboard-section h2 {
            font-family: 'Playfair Display', serif;
            font-size: 2.5rem;
            color: var(--primary-color);
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 3px solid var(--secondary-color);
        }

        /* Reservations Grid */
        .reservations-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
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

        .reservation-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .reservation-header h3 {
            font-family: 'Playfair Display', serif;
            font-size: 1.5rem;
            color: var(--primary-color);
        }

        .status-badge {
            padding: 0.4rem 1rem;
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

        .reservation-details p {
            margin: 0.75rem 0;
            color: #555;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .reservation-details strong {
            color: var(--primary-color);
            min-width: 100px;
        }

        .reservation-actions {
            margin-top: 1.5rem;
            padding-top: 1.5rem;
            border-top: 1px solid #ddd;
        }

        .btn {
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 25px;
            font-size: 0.95rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-sm {
            padding: 0.5rem 1rem;
            font-size: 0.85rem;
        }

        .btn-danger {
            background: var(--accent-color);
            color: white;
        }

        .btn-danger:hover {
            background: #c0392b;
            transform: translateY(-2px);
        }

        /* No Content State */
        .no-content {
            text-align: center;
            padding: 4rem 2rem;
            color: #999;
        }

        .no-content i {
            font-size: 5rem;
            color: #ddd;
            margin-bottom: 1.5rem;
        }

        .no-content h3 {
            font-size: 1.5rem;
            color: #666;
            margin-bottom: 0.5rem;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .user-container {
                margin: 80px auto 1rem;
                padding: 0 1rem;
            }

            .user-header h1 {
                font-size: 2rem;
            }

            .quick-actions {
                grid-template-columns: 1fr;
                gap: 1.5rem;
            }

            .reservations-grid {
                grid-template-columns: 1fr;
            }

            .nav-menu {
                flex-wrap: wrap;
                gap: 0.5rem;
            }

            .dashboard-section {
                padding: 1.5rem;
            }

            .dashboard-section h2 {
                font-size: 1.8rem;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <nav class="navbar">
            <div class="nav-brand">
                <img src="../assets/images/logo.png" alt="Villa Adrian Logo" class="logo" onerror="this.style.display='none'">
                <span>Villa Adrian</span>
            </div>
            <ul class="nav-menu">
                <li><a href="../index.php" class="nav-link"><i class="fas fa-home"></i> Home</a></li>
                <li><a href="../rooms.php" class="nav-link"><i class="fas fa-bed"></i> Dhomat</a></li>
                <li><a href="index.php" class="nav-link active"><i class="fas fa-th-large"></i> Dashboard</a></li>
                <li><a href="my_reservations.php" class="nav-link"><i class="fas fa-calendar"></i> Rezervimet</a></li>
                <li><a href="profile.php" class="nav-link"><i class="fas fa-user"></i> Profili</a></li>
                <li><a href="logout.php" class="nav-link"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </nav>
    </header>

    <div class="user-container">
        <div class="user-header" data-aos="fade-down">
            <h1>Mirë se vini, <?php echo $_SESSION['first_name']; ?>!</h1>
            <p>Menaxhoni rezervimet dhe profilin tuaj</p>
        </div>

        <!-- Quick Actions -->
        <div class="quick-actions">
            <a href="book.php" class="action-card" data-aos="zoom-in">
                <div class="action-icon">🏨</div>
                <h3>Rezervo Dhomë</h3>
                <p>Gjeni dhoma të disponueshme dhe bëni një rezervim të ri</p>
            </a>
            
            <a href="my_reservations.php" class="action-card" data-aos="zoom-in" data-aos-delay="100">
                <div class="action-icon">📅</div>
                <h3>Rezervimet e Mia</h3>
                <p>Shikoni dhe menaxhoni rezervimet tuaja aktuale</p>
            </a>
            
            <a href="profile.php" class="action-card" data-aos="zoom-in" data-aos-delay="200">
                <div class="action-icon">👤</div>
                <h3>Profili Im</h3>
                <p>Përditësoni informacionin tuaj personal</p>
            </a>
        </div>

        <!-- Upcoming Reservations -->
        <div class="dashboard-section" data-aos="fade-up">
            <h2><i class="fas fa-calendar-alt"></i> Rezervimet e Ardhshme</h2>
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
                        <p><strong><i class="fas fa-door-open"></i> Dhoma:</strong> <?php echo $reservation['room_number']; ?></p>
                        <p><strong><i class="fas fa-calendar-check"></i> Check-in:</strong> <?php echo formatDate($reservation['check_in']); ?></p>
                        <p><strong><i class="fas fa-calendar-times"></i> Check-out:</strong> <?php echo formatDate($reservation['check_out']); ?></p>
                        <p><strong><i class="fas fa-users"></i> Mysafirë:</strong> <?php echo $reservation['guests']; ?></p>
                        <p><strong><i class="fas fa-euro-sign"></i> Totali:</strong> €<?php echo $reservation['total_price']; ?></p>
                    </div>
                    <?php 
                    $can_cancel = canCancelReservation($reservation['check_in']);
                    $show_cancellation = ($reservation['status'] === 'confirmed' || $reservation['status'] === 'pending');

                    if ($show_cancellation && $can_cancel): ?>
                    <div class="reservation-actions">
                        <a href="cancel_booking.php?id=<?php echo $reservation['id']; ?>" 
                        class="btn btn-sm btn-danger"
                        onclick="return confirm('Jeni i sigurt që dëshironi të anuloni këtë rezervim?')">
                            <i class="fas fa-times-circle"></i> Anulo
                        </a>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endwhile; ?>
            </div>
            <?php else: ?>
            <div class="no-content">
                <i class="fas fa-calendar-times"></i>
                <h3>Nuk keni rezervime të ardhshme</h3>
                <p>Rezervoni një dhomë për pushimet tuaja në Ksamil</p>
                <a href="book.php" class="btn btn-primary" style="margin-top: 1rem; background: linear-gradient(135deg, var(--secondary-color), var(--accent-color)); color: white; padding: 1rem 2rem;">
                    <i class="fas fa-plus-circle"></i> Rezervo Tani
                </a>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"></script>
    <script>
        AOS.init({ duration: 800, once: true });
    </script>
</body>
</html>