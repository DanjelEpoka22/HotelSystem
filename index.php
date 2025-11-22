<?php
require_once 'config/config.php';
require_once 'includes/functions.php';
?>
<!DOCTYPE html>
<html lang="sq">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Villa Adrian - Luksoz në Zemër të Ksamilit</title>
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

        /* Modern Header */
        .header {
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 1000;
            transition: all 0.3s ease;
            padding: 1.5rem 0;
            background: transparent;
        }

        .header.scrolled {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            padding: 1rem 0;
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
            color: var(--white);
            text-shadow: 2px 2px 4px rgba(0,0,0,0.5);
            transition: all 0.3s ease;
        }

        .header.scrolled .nav-brand {
            color: var(--primary-color);
            text-shadow: none;
        }

        .logo {
            height: 50px;
            margin-right: 1rem;
            filter: drop-shadow(2px 2px 4px rgba(0,0,0,0.5));
            transition: all 0.3s ease;
        }

        .header.scrolled .logo {
            filter: none;
        }

        .nav-menu {
            display: flex;
            list-style: none;
            gap: 2rem;
            align-items: center;
        }

        .nav-link {
            text-decoration: none;
            color: var(--white);
            font-weight: 500;
            padding: 0.5rem 1rem;
            border-radius: 25px;
            transition: all 0.3s ease;
            position: relative;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.5);
        }

        .header.scrolled .nav-link {
            color: var(--dark-color);
            text-shadow: none;
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
            color: var(--white);
            cursor: pointer;
        }

        .header.scrolled .mobile-toggle {
            color: var(--dark-color);
        }

        /* Video Background Hero */
        .hero {
            position: relative;
            height: 100vh;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .video-background-container {
            position: absolute;
            top: 50%;
            left: 50%;
            width: 100%;
            height: 100%;
            transform: translate(-50%, -50%);
            z-index: -1;
            overflow: hidden;
        }

        /* YouTube Iframe Styling - Hide all controls */
        #player {
            position: absolute;
            top: 50%;
            left: 50%;
            width: 100vw;
            height: 100vh;
            transform: translate(-50%, -50%);
            pointer-events: none;
        }

        /* Hide YouTube logo and controls */
        .ytp-chrome-top,
        .ytp-show-cards-title,
        .ytp-title-text,
        .ytp-chrome-bottom,
        .ytp-pause-overlay,
        .ytp-watermark {
            display: none !important;
        }

        .video-fallback {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, var(--primary-color), var(--dark-color));
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--white);
            font-size: 1.5rem;
            z-index: 1;
        }

        .hero-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(44, 95, 124, 0.7), rgba(38, 70, 83, 0.5));
            z-index: 1;
        }

        .hero-content {
            position: relative;
            z-index: 2;
            text-align: center;
            color: var(--white);
            max-width: 900px;
            padding: 0 2rem;
            animation: fadeInUp 1s ease;
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

        .hero-content h1 {
            font-family: 'Playfair Display', serif;
            font-size: 4.5rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
            line-height: 1.2;
            text-shadow: 3px 3px 6px rgba(0,0,0,0.3);
        }

        .hero-content p {
            font-size: 1.5rem;
            margin-bottom: 2.5rem;
            font-weight: 300;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }

        .cta-buttons {
            display: flex;
            gap: 1.5rem;
            justify-content: center;
            flex-wrap: wrap;
        }

        .btn {
            padding: 1rem 2.5rem;
            border: none;
            border-radius: 50px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }

        .btn-primary {
            background: var(--secondary-color);
            color: var(--white);
            box-shadow: 0 4px 15px rgba(244, 162, 97, 0.4);
        }

        .btn-primary:hover {
            background: var(--accent-color);
            transform: translateY(-3px);
            box-shadow: 0 6px 25px rgba(231, 111, 81, 0.5);
        }

        .btn-secondary {
            background: transparent;
            color: var(--white);
            border: 2px solid var(--white);
        }

        .btn-secondary:hover {
            background: var(--white);
            color: var(--primary-color);
            transform: translateY(-3px);
        }

        .scroll-indicator {
            position: absolute;
            bottom: 2rem;
            left: 50%;
            transform: translateX(-50%);
            z-index: 2;
            animation: bounce 2s infinite;
        }

        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% {
                transform: translateX(-50%) translateY(0);
            }
            40% {
                transform: translateX(-50%) translateY(-20px);
            }
            60% {
                transform: translateX(-50%) translateY(-10px);
            }
        }

        .scroll-indicator i {
            font-size: 2rem;
            color: var(--white);
        }

        /* Featured Rooms Section */
        .featured-rooms {
            padding: 6rem 2rem;
            background: var(--light-color);
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
        }

        .section-header {
            text-align: center;
            margin-bottom: 4rem;
        }

        .section-header h2 {
            font-family: 'Playfair Display', serif;
            font-size: 3rem;
            color: var(--primary-color);
            margin-bottom: 1rem;
        }

        .section-header p {
            font-size: 1.2rem;
            color: #666;
        }

        .rooms-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 2.5rem;
        }

        .room-card {
            background: var(--white);
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }

        .room-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 50px rgba(0,0,0,0.15);
        }

        .room-slider {
            position: relative;
            height: 300px;
            overflow: hidden;
        }

        .slide {
            display: none;
            height: 100%;
        }

        .slide.active {
            display: block;
            animation: fadeIn 0.5s;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .slide img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }

        .room-card:hover .slide img {
            transform: scale(1.1);
        }

        .slider-btn {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            background: rgba(255,255,255,0.9);
            color: var(--primary-color);
            border: none;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            cursor: pointer;
            font-size: 1.2rem;
            transition: all 0.3s;
            z-index: 10;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .slider-btn:hover {
            background: var(--secondary-color);
            color: var(--white);
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
            background: rgba(255,255,255,0.5);
            cursor: pointer;
            transition: all 0.3s;
        }

        .dot.active,
        .dot:hover {
            background: var(--secondary-color);
            transform: scale(1.2);
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

        .room-price {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--accent-color);
            margin-bottom: 1rem;
        }

        .room-guests {
            color: #666;
            margin-bottom: 1.5rem;
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
                gap: 1rem;
            }

            .nav-menu.active {
                left: 0;
            }

            .nav-link {
                color: var(--dark-color);
                text-shadow: none;
            }

            .hero-content h1 {
                font-size: 2.5rem;
            }

            .hero-content p {
                font-size: 1.2rem;
            }

            .rooms-grid {
                grid-template-columns: 1fr;
            }

            .section-header h2 {
                font-size: 2rem;
            }

            /* Adjust video for mobile */
            #player {
                width: 300%;
                height: 100vh;
                left: 50%;
            }
        }

        /* For larger screens - ensure video covers properly */
        @media (min-width: 1200px) {
            #player {
                width: 100vw;
                height: 56.25vw; /* 16:9 aspect ratio */
                min-height: 100vh;
                min-width: 177.77vh; /* 16:9 aspect ratio */
            }
        }
    </style>
</head>
<body>
    <!-- Modern Header -->
    <header class="header" id="header">
        <nav class="navbar">
            <div class="nav-brand">
                <img src="assets/images/logo.png" alt="Villa Adrian Logo" class="logo" onerror="this.style.display='none'">
                <span>Villa Adrian</span>
            </div>
            <button class="mobile-toggle" id="mobileToggle">
                <i class="fas fa-bars"></i>
            </button>
            <ul class="nav-menu" id="navMenu">
                <li><a href="index.php" class="nav-link active">Home</a></li>
                <li><a href="rooms.php" class="nav-link">Dhomat</a></li>
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

    <!-- Hero Section with Video Background -->
    <section class="hero">
        <div class="video-background-container">
            <div id="player"></div>
            <div class="video-fallback" id="videoFallback">
                <div>
                    <i class="fas fa-image fa-3x" style="margin-bottom: 1rem;"></i>
                    <p>Villa Adrian - Ksamil</p>
                </div>
            </div>
        </div>
        <div class="hero-overlay"></div>
        <div class="hero-content" data-aos="fade-up">
            <h1>Mirë se vini në Villa Adrian</h1>
            <p>Përjetoni luksin në zemër të Ksamilit të bukur</p>
            <div class="cta-buttons">
                <a href="rooms.php" class="btn btn-primary">
                    <i class="fas fa-bed"></i> Rezervo Tani
                </a>
                <a href="about.php" class="btn btn-secondary">
                    <i class="fas fa-info-circle"></i> Zbulo Më Shumë
                </a>
            </div>
        </div>
        <div class="scroll-indicator">
            <i class="fas fa-chevron-down"></i>
        </div>
    </section>

    <!-- Featured Rooms Section -->
    <section class="featured-rooms">
        <div class="container">
            <div class="section-header" data-aos="fade-up">
                <h2>Dhomat Tona Luksoze</h2>
                <p>Zgjidhni nga dhomat tona të bukura me pamje spektakolare të detit</p>
            </div>
            <div class="rooms-grid">
                <?php
                $query = "SELECT * FROM rooms WHERE is_available = 1 LIMIT 3";
                $stmt = $db->prepare($query);
                $stmt->execute();
                
                while ($room = $stmt->fetch(PDO::FETCH_ASSOC)):
                    $photos = getRoomPhotos($room['id']);
                ?>
                <div class="room-card" data-aos="fade-up" data-aos-delay="100">
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
                    </div>
                    <?php endif; ?>
                    
                    <div class="room-info">
                        <h3><?php echo getRoomTypeName($room['room_type']); ?></h3>
                        <p class="room-price">€<?php echo $room['price_per_night']; ?>/natë</p>
                        <p class="room-guests">
                            <i class="fas fa-users"></i> Deri në <?php echo $room['max_guests']; ?> mysafirë
                        </p>
                        <a href="rooms.php?room=<?php echo $room['id']; ?>" class="btn btn-primary" style="width: 100%; text-align: center;">
                            <i class="fas fa-arrow-right"></i> Shiko Detajet
                        </a>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        </div>
    </section>

    <!-- Modern Footer -->
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

    <!-- Scripts -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"></script>
    <script>
        // Initialize AOS
        AOS.init({
            duration: 1000,
            once: true
        });

        // Header scroll effect
        window.addEventListener('scroll', () => {
            const header = document.getElementById('header');
            if (window.scrollY > 50) {
                header.classList.add('scrolled');
            } else {
                header.classList.remove('scrolled');
            }
        });

        // Mobile menu toggle
        const mobileToggle = document.getElementById('mobileToggle');
        const navMenu = document.getElementById('navMenu');
        
        mobileToggle.addEventListener('click', () => {
            navMenu.classList.toggle('active');
            const icon = mobileToggle.querySelector('i');
            icon.classList.toggle('fa-bars');
            icon.classList.toggle('fa-times');
        });

        // YouTube Video Background with enhanced hiding
        let player;
        let videoLoaded = false;

        // Load YouTube IFrame API
        const tag = document.createElement('script');
        tag.src = "https://www.youtube.com/iframe_api";
        const firstScriptTag = document.getElementsByTagName('script')[0];
        firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);

        function onYouTubeIframeAPIReady() {
            player = new YT.Player('player', {
                height: '100%',
                width: '100%',
                videoId: '1uRhWxRpHKM', // Replace with your actual YouTube video ID
                playerVars: {
                    'autoplay': 1,
                    'controls': 0,
                    'showinfo': 0,
                    'modestbranding': 1,
                    'loop': 1,
                    'playlist': '1uRhWxRpHKM',
                    'fs': 0,
                    'cc_load_policy': 0,
                    'iv_load_policy': 3,
                    'autohide': 0,
                    'mute': 1,
                    'rel': 0,
                    'enablejsapi': 1,
                    'widget_referrer': window.location.href,
                    'origin': window.location.origin
                },
                events: {
                    'onReady': onPlayerReady,
                    'onStateChange': onPlayerStateChange,
                    'onError': onPlayerError
                }
            });
        }

        function onPlayerReady(event) {
            videoLoaded = true;
            document.getElementById('videoFallback').style.display = 'none';
            event.target.mute();
            event.target.playVideo();
            
            // Additional hiding for YouTube elements
            hideYouTubeElements();
        }

        function onPlayerStateChange(event) {
            if (event.data === YT.PlayerState.ENDED) {
                player.playVideo();
            }
            // Re-hide elements when state changes
            setTimeout(hideYouTubeElements, 100);
        }

        function onPlayerError(event) {
            console.error('YouTube Player Error:', event.data);
            document.getElementById('videoFallback').style.display = 'flex';
        }

        function hideYouTubeElements() {
            // Hide YouTube controls and branding
            const iframe = document.getElementById('player');
            if (iframe) {
                const iframeDoc = iframe.contentDocument || iframe.contentWindow.document;
                if (iframeDoc) {
                    // Hide various YouTube elements
                    const styles = `
                        .ytp-chrome-top,
                        .ytp-chrome-bottom,
                        .ytp-pause-overlay,
                        .ytp-watermark,
                        .ytp-title-text,
                        .ytp-show-cards-title,
                        .ytp-cards-button,
                        .ytp-button,
                        .ytp-menuitem,
                        .ytp-panel-menu,
                        .ytp-share-button,
                        .ytp-copylink-button,
                        .ytp-pip-button,
                        .ytp-size-button,
                        .ytp-settings-button,
                        .ytp-fullscreen-button,
                        .ytp-impression-link,
                        .ytp-contextmenu-link {
                            display: none !important;
                            opacity: 0 !important;
                            visibility: hidden !important;
                        }
                    `;
                    const styleSheet = iframeDoc.createElement("style");
                    styleSheet.type = "text/css";
                    styleSheet.innerText = styles;
                    iframeDoc.head.appendChild(styleSheet);
                }
            }
        }

        // Fallback if YouTube API fails to load
        setTimeout(() => {
            if (!videoLoaded) {
                console.log('YouTube API loading timeout - showing fallback');
                document.getElementById('videoFallback').style.display = 'flex';
            }
        }, 5000);

        // Room Slider Functions
        function moveSlide(btn, direction) {
            const slider = btn.closest('.room-slider');
            const slides = slider.querySelectorAll('.slide');
            const dots = slider.querySelectorAll('.dot');
            let currentIndex = Array.from(slides).findIndex(slide => slide.classList.contains('active'));
            
            slides[currentIndex].classList.remove('active');
            if (dots.length > 0) dots[currentIndex].classList.remove('active');
            
            currentIndex += direction;
            if (currentIndex < 0) currentIndex = slides.length - 1;
            if (currentIndex >= slides.length) currentIndex = 0;
            
            slides[currentIndex].classList.add('active');
            if (dots.length > 0) dots[currentIndex].classList.add('active');
        }

        function goToSlide(dot, index) {
            const slider = dot.closest('.room-slider');
            const slides = slider.querySelectorAll('.slide');
            const dots = slider.querySelectorAll('.dot');
            
            slides.forEach(slide => slide.classList.remove('active'));
            dots.forEach(d => d.classList.remove('active'));
            
            slides[index].classList.add('active');
            dots[index].classList.add('active');
        }

        // Auto-slide
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.room-slider').forEach(slider => {
                if (slider.querySelectorAll('.slide').length > 1) {
                    setInterval(() => {
                        const nextBtn = slider.querySelector('.next');
                        if (nextBtn) moveSlide(nextBtn, 1);
                    }, 5000);
                }
            });
        });
    </script>
</body>
</html>