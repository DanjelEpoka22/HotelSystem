<?php
require_once 'config/config.php';
require_once 'includes/functions.php';
?>
<!DOCTYPE html>
<html lang="sq">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dhomat Tona - Villa Adrian</title>
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
            overflow-x: hidden;
            color: var(--dark-color);
        }

        /* Header Styles (same as index) */
        .header {
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 1000;
            transition: all 0.3s ease;
            padding: 1.5rem 0;
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
            font-size: 2rem;
            font-weight: 700;
            color: var(--primary-color);
        }

        .logo {
            height: 50px;
            margin-right: 1rem;
        }

        .nav-menu {
            display: flex;
            list-style: none;
            gap: 2rem;
            align-items: center;
        }

        .nav-link {
            text-decoration: none;
            color: var(--dark-color);
            font-weight: 500;
            padding: 0.5rem 1rem;
            border-radius: 25px;
            transition: all 0.3s ease;
        }

        .nav-link:hover,
        .nav-link.active {
            background: var(--secondary-color);
            color: var(--white);
            transform: translateY(-2px);
        }

        .mobile-toggle {
            display: none;
            background: none;
            border: none;
            font-size: 1.5rem;
            color: var(--dark-color);
            cursor: pointer;
        }

        /* Page Hero */
        .page-hero {
            height: 60vh;
            background: linear-gradient(rgba(44, 95, 124, 0.8), rgba(38, 70, 83, 0.7)), 
                        url('assets/images/hotel/rooms-header.jpg');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            color: white;
            margin-top: 80px;
            position: relative;
            overflow: hidden;
        }

        .page-hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: radial-gradient(circle at 50% 50%, transparent 0%, rgba(0,0,0,0.3) 100%);
        }

        .page-hero-content {
            position: relative;
            z-index: 1;
        }

        .page-hero h1 {
            font-family: 'Playfair Display', serif;
            font-size: 4rem;
            margin-bottom: 1rem;
            text-shadow: 3px 3px 6px rgba(0,0,0,0.3);
            animation: fadeInDown 1s ease;
        }

        .page-hero p {
            font-size: 1.5rem;
            animation: fadeInUp 1s ease 0.3s both;
        }

        /* Filter Section */
        .filter-section {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            padding: 3rem 0;
            box-shadow: 0 4px 20px rgba(0,0,0,0.05);
        }

        .filter-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 2rem;
        }

        .filter-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
        }

        .filter-group label {
            display: block;
            font-weight: 600;
            color: var(--primary-color);
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .filter-group select {
            width: 100%;
            padding: 1rem;
            border: 2px solid #ddd;
            border-radius: 12px;
            font-size: 1rem;
            background: white;
            cursor: pointer;
            transition: all 0.3s ease;
            font-family: 'Poppins', sans-serif;
        }

        .filter-group select:focus {
            outline: none;
            border-color: var(--secondary-color);
            box-shadow: 0 0 0 3px rgba(244, 162, 97, 0.1);
        }

        /* Rooms Section */
        .rooms-section {
            padding: 4rem 2rem;
            background: var(--light-color);
        }

        .rooms-container {
            max-width: 1400px;
            margin: 0 auto;
        }

        .rooms-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(380px, 1fr));
            gap: 2.5rem;
        }

        .room-card {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 10px 40px rgba(0,0,0,0.08);
            transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
        }

        .room-card:hover {
            transform: translateY(-15px);
            box-shadow: 0 20px 60px rgba(0,0,0,0.15);
        }

        .room-slider {
            position: relative;
            height: 300px;
            overflow: hidden;
            background: #000;
        }

        .slide {
            display: none;
            height: 100%;
        }

        .slide.active {
            display: block;
            animation: zoomIn 0.6s ease;
        }

        @keyframes zoomIn {
            from {
                opacity: 0;
                transform: scale(1.1);
            }
            to {
                opacity: 1;
                transform: scale(1);
            }
        }

        @keyframes fadeInDown {
            from {
                opacity: 0;
                transform: translateY(-50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .slide img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }

        .room-card:hover .slide img {
            transform: scale(1.15);
        }

        .room-badge {
            position: absolute;
            top: 20px;
            right: 20px;
            background: var(--secondary-color);
            color: white;
            padding: 0.5rem 1.5rem;
            border-radius: 25px;
            font-weight: 600;
            font-size: 1.1rem;
            z-index: 10;
            box-shadow: 0 4px 15px rgba(244, 162, 97, 0.4);
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }

        .slider-btn {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            background: rgba(255,255,255,0.95);
            color: var(--primary-color);
            border: none;
            width: 45px;
            height: 45px;
            border-radius: 50%;
            cursor: pointer;
            transition: all 0.3s;
            z-index: 10;
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
        }

        .room-card:hover .slider-btn {
            opacity: 1;
        }

        .slider-btn:hover {
            background: var(--secondary-color);
            color: white;
            transform: translateY(-50%) scale(1.1);
        }

        .slider-btn.prev { left: 15px; }
        .slider-btn.next { right: 15px; }

        .slider-dots {
            position: absolute;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            display: flex;
            gap: 10px;
            z-index: 10;
        }

        .dot {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: rgba(255,255,255,0.6);
            cursor: pointer;
            transition: all 0.3s;
            border: 2px solid transparent;
        }

        .dot.active {
            background: var(--secondary-color);
            transform: scale(1.3);
            border-color: white;
        }

        .room-info {
            padding: 2rem;
        }

        .room-info h3 {
            font-family: 'Playfair Display', serif;
            font-size: 1.8rem;
            color: var(--primary-color);
            margin-bottom: 1rem;
        }

        .room-number {
            color: #999;
            font-size: 0.9rem;
            margin-bottom: 1rem;
        }

        .room-features {
            display: flex;
            gap: 1.5rem;
            margin-bottom: 1.5rem;
            flex-wrap: wrap;
        }

        .feature {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.95rem;
            color: #666;
        }

        .feature i {
            color: var(--secondary-color);
        }

        .room-description {
            color: #666;
            line-height: 1.7;
            margin-bottom: 1.5rem;
        }

        .room-amenities {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            margin-bottom: 1.5rem;
        }

        .amenity-tag {
            background: linear-gradient(135deg, #f0f4f8, #e0e7ef);
            padding: 0.4rem 1rem;
            border-radius: 20px;
            font-size: 0.85rem;
            color: var(--primary-color);
            border: 1px solid rgba(44, 95, 124, 0.1);
            transition: all 0.3s;
        }

        .amenity-tag:hover {
            background: var(--secondary-color);
            color: white;
            transform: translateY(-2px);
        }

        .room-actions {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }

        .btn {
            padding: 1rem;
            border: none;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--secondary-color), var(--accent-color));
            color: white;
            box-shadow: 0 4px 15px rgba(244, 162, 97, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 25px rgba(231, 111, 81, 0.4);
        }

        .btn-secondary {
            background: white;
            color: var(--primary-color);
            border: 2px solid var(--primary-color);
        }

        .btn-secondary:hover {
            background: var(--primary-color);
            color: white;
            transform: translateY(-3px);
        }

        .no-rooms {
            text-align: center;
            padding: 4rem 2rem;
            color: #999;
        }

        .no-rooms i {
            font-size: 4rem;
            color: #ddd;
            margin-bottom: 1rem;
        }

        /* CTA Section */
        .cta-section {
            background: linear-gradient(135deg, var(--primary-color), var(--dark-color));
            color: white;
            padding: 5rem 2rem;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .cta-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg width="100" height="100" xmlns="http://www.w3.org/2000/svg"><defs><pattern id="grid" width="100" height="100" patternUnits="userSpaceOnUse"><path d="M 100 0 L 0 0 0 100" fill="none" stroke="rgba(255,255,255,0.05)" stroke-width="1"/></pattern></defs><rect width="100%" height="100%" fill="url(%23grid)"/></svg>');
            opacity: 0.3;
        }

        .cta-content {
            position: relative;
            z-index: 1;
            max-width: 800px;
            margin: 0 auto;
        }

        .cta-content h2 {
            font-family: 'Playfair Display', serif;
            font-size: 3rem;
            margin-bottom: 1.5rem;
        }

        .cta-content p {
            font-size: 1.3rem;
            margin-bottom: 2.5rem;
            opacity: 0.9;
        }

        .btn-large {
            padding: 1.2rem 3rem;
            font-size: 1.2rem;
        }

        /* Footer */
        .footer {
            background: linear-gradient(135deg, var(--dark-color), var(--primary-color));
            color: var(--white);
            padding: 4rem 2rem 2rem;
        }

        .footer-content {
            max-width: 1400px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 3rem;
            margin-bottom: 3rem;
        }

        .footer-section h3 {
            font-family: 'Playfair Display', serif;
            font-size: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .footer-section p,
        .footer-section a {
            color: rgba(255,255,255,0.8);
            line-height: 1.8;
            text-decoration: none;
            display: block;
            margin-bottom: 0.5rem;
            transition: color 0.3s;
        }

        .footer-section a:hover {
            color: var(--secondary-color);
        }

        .social-links {
            display: flex;
            gap: 1rem;
            margin-top: 1rem;
        }

        .social-link {
            width: 40px;
            height: 40px;
            background: rgba(255,255,255,0.1);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            transition: all 0.3s;
        }

        .social-link:hover {
            background: var(--secondary-color);
            transform: translateY(-3px);
        }

        .footer-bottom {
            text-align: center;
            padding-top: 2rem;
            border-top: 1px solid rgba(255,255,255,0.1);
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
            padding: 2rem;
            border-radius: 20px;
            width: 90%;
            max-width: 1000px;
            max-height: 85vh;
            overflow-y: auto;
        }

        .close {
            color: #aaa;
            float: right;
            font-size: 2rem;
            font-weight: bold;
            cursor: pointer;
            transition: color 0.3s;
        }

        .close:hover {
            color: var(--accent-color);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .mobile-toggle {
                display: block;
            }

            .nav-menu {
                position: fixed;
                left: -100%;
                top: 80px;
                flex-direction: column;
                background: rgba(255,255,255,0.98);
                width: 100%;
                padding: 2rem;
                transition: left 0.3s;
            }

            .nav-menu.active {
                left: 0;
            }

            .page-hero h1 {
                font-size: 2.5rem;
            }

            .rooms-grid {
                grid-template-columns: 1fr;
            }

            .room-actions {
                grid-template-columns: 1fr;
            }

            .filter-grid {
                grid-template-columns: 1fr;
            }

            .cta-content h2 {
                font-size: 2rem;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <nav class="navbar">
            <div class="nav-brand">
                <img src="assets/images/logo.png" alt="Villa Adrian Logo" class="logo" onerror="this.style.display='none'">
                <span>Villa Adrian</span>
            </div>
            <button class="mobile-toggle" id="mobileToggle">
                <i class="fas fa-bars"></i>
            </button>
            <ul class="nav-menu" id="navMenu">
                <li><a href="index.php" class="nav-link">Home</a></li>
                <li><a href="rooms.php" class="nav-link active">Dhomat</a></li>
                <li><a href="about.php" class="nav-link">Rreth Ksamilit</a></li>
                <li><a href="contact.php" class="nav-link">Kontakt</a></li>
                <?php if (isLoggedIn()): ?>
                    <li><a href="user/index.php" class="nav-link"><i class="fas fa-user"></i> Dashboard</a></li>
                    <li><a href="user/logout.php" class="nav-link">Logout</a></li>
                <?php else: ?>
                    <li><a href="login.php" class="nav-link">Login</a></li>
                    <li><a href="register.php" class="nav-link btn-primary" style="padding: 0.5rem 1.5rem;">Regjistrohu</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </header>

    <!-- Page Hero -->
    <section class="page-hero">
        <div class="page-hero-content" data-aos="fade-up">
            <h1>Dhomat & Suitat Tona</h1>
            <p>Zgjidhni strehin tuaj të përkryer në parajsë</p>
        </div>
    </section>

    <!-- Filter Section -->
    <section class="filter-section">
        <div class="filter-container">
            <form id="roomsFilter">
                <div class="filter-grid">
                    <div class="filter-group" data-aos="fade-up">
                        <label><i class="fas fa-door-open"></i> Lloji i Dhomës</label>
                        <select id="roomTypeFilter">
                            <option value="">Të gjitha llojet</option>
                            <option value="one_bedroom_apartment">Apartament 1 dhomë gjumi</option>
                            <option value="deluxe_double">Dhomë Deluxe Dyshe</option>
                            <option value="deluxe_triple">Dhomë Deluxe Treshe</option>
                            <option value="deluxe_quadruple">Dhomë Deluxe Katërshe</option>
                            <option value="suite">Suite</option>
                            <option value="deluxe_studio">Studio Deluxe</option>
                            <option value="family_studio">Studio Familjare</option>
                        </select>
                    </div>
                    
                    <div class="filter-group" data-aos="fade-up" data-aos-delay="100">
                        <label><i class="fas fa-users"></i> Numri i Mysafirëve</label>
                        <select id="guestsFilter">
                            <option value="">Çdo numër</option>
                            <option value="1">1 Mysafir</option>
                            <option value="2">2 Mysafirë</option>
                            <option value="3">3 Mysafirë</option>
                            <option value="4">4 Mysafirë</option>
                            <option value="5">5+ Mysafirë</option>
                        </select>
                    </div>
                    
                    <div class="filter-group" data-aos="fade-up" data-aos-delay="200">
                        <label><i class="fas fa-euro-sign"></i> Çmimi për Natë</label>
                        <select id="priceFilter">
                            <option value="">Çdo çmim</option>
                            <option value="0-100">Nën €100</option>
                            <option value="100-150">€100 - €150</option>
                            <option value="150-200">€150 - €200</option>
                            <option value="200-250">€200 - €250</option>
                            <option value="250-999">Mbi €250</option>
                        </select>
                    </div>
                </div>
            </form>
        </div>
    </section>

    <!-- Rooms Grid -->
    <section class="rooms-section">
        <div class="rooms-container">
            <div id="roomsContainer" class="rooms-grid">
                <?php
                $query = "SELECT * FROM rooms WHERE is_available = 1 ORDER BY price_per_night";
                $stmt = $db->prepare($query);
                $stmt->execute();
                
                $delay = 0;
                while ($room = $stmt->fetch(PDO::FETCH_ASSOC)):
                    $photos = getRoomPhotos($room['id']);
                    $delay += 100;
                ?>
                <div class="room-card" data-type="<?php echo $room['room_type']; ?>" 
                     data-guests="<?php echo $room['max_guests']; ?>" 
                     data-price="<?php echo $room['price_per_night']; ?>"
                     data-aos="fade-up"
                     data-aos-delay="<?php echo $delay; ?>">
                    
                    <?php if (!empty($photos)): ?>
                    <div class="room-slider">
                        <div class="slider-container">
                            <?php foreach ($photos as $index => $photo): ?>
                            <div class="slide <?php echo $index === 0 ? 'active' : ''; ?>">
                                <img src="assets/images/rooms/uploads/<?php echo $photo['photo_filename']; ?>" 
                                     alt="<?php echo getRoomTypeName($room['room_type']); ?>">
                            </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <div class="room-badge">€<?php echo $room['price_per_night']; ?>/natë</div>
                        
                        <?php if (count($photos) > 1): ?>
                        <button class="slider-btn prev" onclick="moveSlide(this, -1)">
                            <i class="fas fa-chevron-left"></i>
                        </button>
                        <button class="slider-btn next" onclick="moveSlide(this, 1)">
                            <i class="fas fa-chevron-right"></i>
                        </button>
                        
                        <div class="slider-dots">
                            <?php foreach ($photos as $index => $photo): ?>
                            <span class="dot <?php echo $index === 0 ? 'active' : ''; ?>" 
                                  onclick="goToSlide(this, <?php echo $index; ?>)"></span>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php else: ?>
                    <div class="room-slider">
                        <div class="slide active">
                            <img src="assets/images/rooms/default.jpg" alt="Room">
                        </div>
                        <div class="room-badge">€<?php echo $room['price_per_night']; ?>/natë</div>
                    </div>
                    <?php endif; ?>
                    
                    <div class="room-info">
                        <h3><?php echo getRoomTypeName($room['room_type']); ?></h3>
                        <p class="room-number">Dhomë Nr. <?php echo $room['room_number']; ?></p>
                        
                        <div class="room-features">
                            <span class="feature">
                                <i class="fas fa-users"></i>
                                <?php echo $room['max_guests']; ?> mysafirë
                            </span>
                            <span class="feature">
                                <i class="fas fa-bed"></i>
                                <?php
                                switch($room['room_type']) {
                                    case 'one_bedroom_apartment':
                                    case 'deluxe_quadruple':
                                    case 'family_studio':
                                        echo '2 Twin + 1 Full';
                                        break;
                                    case 'deluxe_double':
                                        echo '1 Queen';
                                        break;
                                    case 'deluxe_triple':
                                    case 'deluxe_studio':
                                        echo '1 Twin + 1 Queen';
                                        break;
                                    case 'suite':
                                        echo '2 Twin + 1 Queen + 1 Sofa';
                                        break;
                                    default:
                                        echo 'Të ndryshme';
                                }
                                ?>
                            </span>
                        </div>
                        
                        <p class="room-description"><?php echo $room['description']; ?></p>
                        
                        <div class="room-amenities">
                            <?php
                            $amenities = json_decode($room['amenities'] ?? '[]', true);
                            if ($amenities) {
                                foreach (array_slice($amenities, 0, 4) as $amenity) {
                                    echo '<span class="amenity-tag">' . $amenity . '</span>';
                                }
                                if (count($amenities) > 4) {
                                    echo '<span class="amenity-tag">+' . (count($amenities) - 4) . ' më shumë</span>';
                                }
                            }
                            ?>
                        </div>
                        
                        <div class="room-actions">
                            <a href="user/book.php?room_id=<?php echo $room['id']; ?>" class="btn btn-primary">
                                <i class="fas fa-calendar-check"></i> Rezervo
                            </a>
                            <button class="btn btn-secondary" onclick="viewRoomDetails(<?php echo $room['id']; ?>)">
                                <i class="fas fa-info-circle"></i> Detaje
                            </button>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
            
            <?php if ($stmt->rowCount() === 0): ?>
            <div class="no-rooms">
                <i class="fas fa-bed"></i>
                <h3>Nuk ka dhoma të disponueshme</h3>
                <p>Na vjen keq, por nuk ka dhoma të lira aktualisht.</p>
            </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Modal -->
    <div id="roomModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeRoomModal()">&times;</span>
            <div id="roomModalContent"></div>
        </div>
    </div>

    <!-- CTA Section -->
    <section class="cta-section">
        <div class="cta-content" data-aos="fade-up">
            <h2>Gati për të Rezervuar?</h2>
            <p>Përjetoni bukurinë e Ksamilit me akomodimin tonë të shkëlqyer</p>
            <?php if (isLoggedIn()): ?>
                <a href="user/book.php" class="btn btn-primary btn-large">
                    <i class="fas fa-calendar-alt"></i> Rezervo Tani
                </a>
            <?php else: ?>
                <a href="register.php" class="btn btn-primary btn-large">
                    <i class="fas fa-user-plus"></i> Krijo Llogari për të Rezervuar
                </a>
            <?php endif; ?>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="footer-content">
            <div class="footer-section" data-aos="fade-up">
                <h3>Villa Adrian</h3>
                <p>Hoteli më i bukur në Ksamil, me pamje të mrekullueshme të detit Jonit dhe shërbim ekskluziv.</p>
                <div class="social-links">
                    <a href="#" class="social-link"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" class="social-link"><i class="fab fa-instagram"></i></a>
                    <a href="#" class="social-link"><i class="fab fa-tripadvisor"></i></a>
                    <a href="#" class="social-link"><i class="fab fa-booking"></i></a>
                </div>
            </div>
            
            <div class="footer-section" data-aos="fade-up" data-aos-delay="100">
                <h3>Navigim i Shpejtë</h3>
                <a href="index.php">Home</a>
                <a href="rooms.php">Dhomat</a>
                <a href="about.php">Rreth Nesh</a>
                <a href="contact.php">Kontakt</a>
            </div>
            
            <div class="footer-section" data-aos="fade-up" data-aos-delay="200">
                <h3>Kontakti</h3>
                <p><i class="fas fa-map-marker-alt"></i> Ksamil Beach Road, Sarandë, Albania</p>
                <p><i class="fas fa-phone"></i> +355 69 123 4567</p>
                <p><i class="fas fa-envelope"></i> info@villaadrian.com</p>
            </div>
            
            <div class="footer-section" data-aos="fade-up" data-aos-delay="300">
                <h3>Orari</h3>
                <p>Check-in: 14:00</p>
                <p>Check-out: 11:00</p>
                <p>Reception: 24/7</p>
            </div>
        </div>
        
        <div class="footer-bottom">
            <p>&copy; <?php echo date('Y'); ?> Villa Adrian. Të gjitha të drejtat e rezervuara. | Dizenjuar me ❤️ për Ksamilin</p>
        </div>
    </footer>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"></script>
    <script>
        AOS.init({ duration: 1000, once: true });

        // Mobile menu
        const mobileToggle = document.getElementById('mobileToggle');
        const navMenu = document.getElementById('navMenu');
        
        mobileToggle.addEventListener('click', () => {
            navMenu.classList.toggle('active');
            const icon = mobileToggle.querySelector('i');
            icon.classList.toggle('fa-bars');
            icon.classList.toggle('fa-times');
        });

        // Filter functionality
        ['roomTypeFilter', 'guestsFilter', 'priceFilter'].forEach(id => {
            document.getElementById(id)?.addEventListener('change', filterRooms);
        });

        function filterRooms() {
            const roomType = document.getElementById('roomTypeFilter').value;
            const guests = document.getElementById('guestsFilter').value;
            const priceRange = document.getElementById('priceFilter').value;
            
            const rooms = document.querySelectorAll('.room-card');
            let visibleCount = 0;
            
            rooms.forEach(room => {
                const matches = (
                    (!roomType || room.dataset.type === roomType) &&
                    (!guests || (guests === '5' ? parseInt(room.dataset.guests) >= 5 : parseInt(room.dataset.guests) == guests)) &&
                    (!priceRange || (() => {
                        const [min, max] = priceRange.split('-').map(Number);
                        const price = parseFloat(room.dataset.price);
                        return price >= min && price <= max;
                    })())
                );
                
                room.style.display = matches ? 'block' : 'none';
                if (matches) visibleCount++;
            });
        }

        // Slider functions
        function moveSlide(btn, direction) {
            const slider = btn.closest('.room-slider');
            const slides = slider.querySelectorAll('.slide');
            const dots = slider.querySelectorAll('.dot');
            let currentIndex = Array.from(slides).findIndex(s => s.classList.contains('active'));
            
            slides[currentIndex].classList.remove('active');
            if (dots.length > 0) dots[currentIndex].classList.remove('active');
            
            currentIndex = (currentIndex + direction + slides.length) % slides.length;
            
            slides[currentIndex].classList.add('active');
            if (dots.length > 0) dots[currentIndex].classList.add('active');
        }

        function goToSlide(dot, index) {
            const slider = dot.closest('.room-slider');
            slider.querySelectorAll('.slide, .dot').forEach(el => el.classList.remove('active'));
            slider.querySelectorAll('.slide')[index].classList.add('active');
            slider.querySelectorAll('.dot')[index].classList.add('active');
        }

        // Auto-slide
        document.querySelectorAll('.room-slider').forEach(slider => {
            if (slider.querySelectorAll('.slide').length > 1) {
                setInterval(() => {
                    const nextBtn = slider.querySelector('.next');
                    if (nextBtn) moveSlide(nextBtn, 1);
                }, 5000);
            }
        });

        function viewRoomDetails(roomId) {
            fetch(`api/get_room_details.php?id=${roomId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const room = data.room;
                        const modal = document.getElementById('roomModal');
                        const content = document.getElementById('roomModalContent');
                        
                        content.innerHTML = `
                            <div class="room-details">
                                <h2>${room.room_type_name}</h2>
                                <div class="room-gallery">
                                    <img src="assets/images/rooms/${room.id}/1.jpg" alt="${room.room_type_name}">
                                </div>
                                
                                <div class="details-grid">
                                    <div class="detail-section">
                                        <h3>Informacioni i Dhomës</h3>
                                        <p><strong>Numri i Dhomës:</strong> ${room.room_number}</p>
                                        <p><strong>Max Mysafirë:</strong> ${room.max_guests}</p>
                                        <p><strong>Çmimi për Natë:</strong> €${room.price_per_night}</p>
                                    </div>
                                    
                                    <div class="detail-section">
                                        <h3>Përshkrimi</h3>
                                        <p>${room.description}</p>
                                    </div>
                                    
                                    <div class="detail-section">
                                        <h3>Amenities</h3>
                                        <div class="amenities-list">
                                            ${room.amenities ? room.amenities.map(amenity => 
                                                `<span class="amenity-tag">${amenity}</span>`
                                            ).join('') : 'Nuk ka amenities'}
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="room-actions">
                                    <a href="user/book.php?room_id=${room.id}" class="btn btn-primary">Rezervo Këtë Dhomë</a>
                                    <button class="btn btn-secondary" onclick="closeRoomModal()">Mbyll</button>
                                </div>
                            </div>
                        `;
                        
                        modal.style.display = 'block';
                    } else {
                        alert('Gabim në ngarkimin e detajeve: ' + data.error);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Ndodhi një gabim gjatë ngarkimit të detajeve.');
                });
        }

        function closeRoomModal() {
            document.getElementById('roomModal').style.display = 'none';
        }

        window.onclick = (e) => {
            const modal = document.getElementById('roomModal');
            if (e.target === modal) closeRoomModal();
        };
    </script>
</body>
</html>